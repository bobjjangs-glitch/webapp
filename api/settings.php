<?php
// ================================================================
// api/settings.php
// ================================================================

// 출력 버퍼 초기화
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// 에러 핸들러
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

// 사용자 ID 확인
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    ob_clean();
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => '로그인이 필요합니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 라우트 및 메서드
$route  = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 입력 데이터 읽기
$inputRaw = file_get_contents('php://input');
$body = json_decode($inputRaw, true) ?? [];

// ============================================================
// GET /api/settings/api-keys - API 키 목록 조회
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'GET') {
    try {
        $rows = DB::fetchAll(
            "SELECT service, api_key, api_secret, access_token, status, updated_at 
             FROM api_keys 
             WHERE user_id = ?
             ORDER BY service ASC",
            [$userId]
        );
        
        $map = [];
        foreach ($rows as $r) {
            // 보안을 위해 키 일부만 표시
            $maskedKey = '';
            if (!empty($r['api_key'])) {
                $keyLen = strlen($r['api_key']);
                if ($keyLen > 10) {
                    $maskedKey = substr($r['api_key'], 0, 6) . '****' . substr($r['api_key'], -4);
                } else {
                    $maskedKey = '****';
                }
            }
            
            $map[$r['service']] = [
                'service' => $r['service'],
                'api_key_masked' => $maskedKey,
                'has_secret' => !empty($r['api_secret']),
                'has_token' => !empty($r['access_token']),
                'status' => $r['status'],
                'updated_at' => $r['updated_at']
            ];
        }
        
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $map
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'DB 조회 오류: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// POST /api/settings/api-keys - API 키 저장
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'POST') {
    try {
        $service = trim($body['service'] ?? '');
        $apiKey  = trim($body['api_key'] ?? '');
        $apiSecret = trim($body['api_secret'] ?? '');
        $accessToken = trim($body['access_token'] ?? '');
        $customerId = trim($body['customer_id'] ?? '');

        // 유효성 검사
        if (empty($service)) {
            throw new Exception('서비스를 선택해주세요.');
        }
        
        if (empty($apiKey)) {
            throw new Exception('API 키를 입력해주세요.');
        }

        // 허용된 서비스 목록
        $allowedServices = [
            'naver_search', 'naver_ad', 'naver_place',
            'openai', 'instagram', 'facebook', 'kakao', 'google'
        ];
        
        if (!in_array($service, $allowedServices)) {
            throw new Exception('지원하지 않는 서비스입니다: ' . $service);
        }

        // 추가 데이터 (네이버 광고 고객 ID 등)
        $extraData = [];
        if ($service === 'naver_ad' && !empty($customerId)) {
            $extraData['customer_id'] = $customerId;
        }
        
        $extraDataJson = !empty($extraData) ? json_encode($extraData) : null;

        // DB에 저장
        $sql = "INSERT INTO api_keys 
                (user_id, service, api_key, api_secret, access_token, extra_data, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    api_key = VALUES(api_key),
                    api_secret = VALUES(api_secret),
                    access_token = VALUES(access_token),
                    extra_data = VALUES(extra_data),
                    status = 'active',
                    updated_at = NOW()";
        
        $params = [
            $userId,
            $service,
            $apiKey,
            $apiSecret ?: null,
            $accessToken ?: null,
            $extraDataJson
        ];
        
        DB::execute($sql, $params);

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'API 키가 성공적으로 저장되었습니다.',
            'service' => $service,
            'saved_at' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// DELETE /api/settings/api-keys - API 키 삭제
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'DELETE') {
    try {
        $service = $body['service'] ?? '';
        
        if (empty($service)) {
            throw new Exception('삭제할 서비스를 지정해주세요.');
        }
        
        DB::execute(
            "DELETE FROM api_keys WHERE user_id = ? AND service = ?",
            [$userId, $service]
        );
        
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'API 키가 삭제되었습니다.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// 매칭되는 라우트가 없음
// ============================================================
ob_clean();
http_response_code(400);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => false,
    'error' => '잘못된 요청입니다.',
    'route' => $route,
    'method' => $method
], JSON_UNESCAPED_UNICODE);
exit;
