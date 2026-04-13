<?php
// api/settings.php - API 키 설정 관리

define('IS_API', true);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// $pdo 전역 변수 확보
if (!isset($pdo) || $pdo === null) {
    $pdo = isset($GLOBALS['pdo']) ? $GLOBALS['pdo'] : DB::connect();
}

// 오류 핸들러
set_error_handler(function($errno, $errstr, $errfile, $errline){
    error_log("settings API 오류: [$errno] $errstr in $errfile:$errline");
    return true;
});
set_exception_handler(function($e){
    error_log("settings API 예외: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'서버 오류가 발생했습니다.']);
    exit;
});

// 로그인 확인
$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'로그인이 필요합니다.']);
    exit;
}
$userId = (int)$currentUser['id'];

// 라우트 & 메서드
$route  = trim($_GET['route'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// 허용 서비스 목록 (dashboard.php의 $apiServices 키와 반드시 일치)
$allowedServices = ['naver_search','naver_ad','naver_place','openai','instagram','kakao','google'];

try {

    // ── GET /api/settings/api-keys ────────────────────────────────
    if ($route === 'api/settings/api-keys' && $method === 'GET') {
        $rows = $pdo->prepare(
            "SELECT service, api_key, api_secret, access_token,
                    extra_data, status, created_at, updated_at
               FROM api_keys
              WHERE user_id = ?
              ORDER BY service"
        );
        $rows->execute([$userId]);
        $keys = [];
        foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $extra = json_decode($row['extra_data'] ?? '{}', true) ?: [];
            $keys[$row['service']] = [
                'service'     => $row['service'],
                'has_key'     => !empty($row['api_key']),
                'key_masked'  => !empty($row['api_key'])
                                    ? substr($row['api_key'], 0, 6) . str_repeat('*', max(0, strlen($row['api_key'])-6))
                                    : '',
                'has_secret'  => !empty($row['api_secret']),
                'has_token'   => !empty($row['access_token']),
                'customer_id' => $extra['customer_id'] ?? '',
                'business_id' => $extra['business_id'] ?? '',
                'status'      => $row['status'],
                'updated_at'  => $row['updated_at'],
            ];
        }
        echo json_encode(['success'=>true,'data'=>$keys], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── POST /api/settings/api-keys ──────────────────────────────
    if ($route === 'api/settings/api-keys' && $method === 'POST') {
        $service     = trim($body['service']      ?? '');
        $apiKey      = trim($body['api_key']      ?? '');
        $apiSecret   = trim($body['api_secret']   ?? '');
        $accessToken = trim($body['access_token'] ?? '');
        $customerId  = trim($body['customer_id']  ?? '');
        $businessId  = trim($body['business_id']  ?? '');

        if (!in_array($service, $allowedServices, true)) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'허용되지 않는 서비스입니다.']);
            exit;
        }
        if (empty($apiKey)) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'API 키를 입력해주세요.']);
            exit;
        }

        // extra_data 구성
        $extraData = [];
        if ($service === 'naver_ad'    && $customerId) $extraData['customer_id'] = $customerId;
        if ($service === 'naver_place' && $businessId) $extraData['business_id'] = $businessId;

        // UPSERT (INSERT … ON DUPLICATE KEY UPDATE)
        $stmt = $pdo->prepare(
            "INSERT INTO api_keys
                (user_id, service, api_key, api_secret, access_token, extra_data, status, created_at, updated_at)
             VALUES
                (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                api_key      = VALUES(api_key),
                api_secret   = VALUES(api_secret),
                access_token = VALUES(access_token),
                extra_data   = VALUES(extra_data),
                status       = 'active',
                updated_at   = NOW()"
        );
        $stmt->execute([
            $userId,
            $service,
            $apiKey,
            $apiSecret   ?: null,
            $accessToken ?: null,
            $extraData   ? json_encode($extraData, JSON_UNESCAPED_UNICODE) : null,
        ]);

        echo json_encode([
            'success' => true,
            'message' => htmlspecialchars($service) . ' API 키가 저장되었습니다.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── DELETE /api/settings/api-keys ────────────────────────────
    if ($route === 'api/settings/api-keys' && $method === 'DELETE') {
        $service = trim($body['service'] ?? $_GET['service'] ?? '');
        if (!in_array($service, $allowedServices, true)) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'허용되지 않는 서비스입니다.']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM api_keys WHERE user_id = ? AND service = ?");
        $stmt->execute([$userId, $service]);
        echo json_encode(['success'=>true,'message'=>'API 키가 삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── POST /api/settings/test-naver-place ──────────────────────
    if ($route === 'api/settings/test-naver-place' && $method === 'POST') {
        $testKey    = trim($body['api_key']    ?? '');
        $testSecret = trim($body['api_secret'] ?? '');
        if (!$testKey || !$testSecret) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>'API 키와 시크릿 키를 입력해주세요.']);
            exit;
        }

        $ch = curl_init();
        $encQuery = urlencode('스타벅스');
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://openapi.naver.com/v1/search/local.json?query='.$encQuery.'&display=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-Naver-Client-Id: '     . $testKey,
                'X-Naver-Client-Secret: ' . $testSecret,
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            echo json_encode(['success'=>false,'error'=>'네트워크 오류: '.$curlErr]);
            exit;
        }
        $result = json_decode($response, true);
        if ($httpCode === 200 && isset($result['total'])) {
            echo json_encode([
                'success' => true,
                'message' => '네이버 플레이스 API 연결 성공! (총 ' . $result['total'] . '건)',
            ]);
        } else {
            $errMsg = $result['errorMessage'] ?? $result['message'] ?? '알 수 없는 오류';
            echo json_encode(['success'=>false,'error'=>'API 오류 ('.$httpCode.'): '.$errMsg]);
        }
        exit;
    }

    // ── 매칭되지 않는 라우트 ────────────────────────────────────
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'잘못된 API 요청입니다.'], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('settings API DB 오류: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'데이터베이스 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('settings API 일반 오류: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'서버 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
}
