<?php
// ================================================================
// api/settings.php
// ================================================================

while (ob_get_level()) ob_end_clean();
ob_start();

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>$errstr,'file'=>basename($errfile),'line'=>$errline], JSON_UNESCAPED_UNICODE);
    exit;
});

set_exception_handler(function($e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
});

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    ob_clean(); http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'error'=>'로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$route  = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// ============================================================
// GET /api/settings/api-keys
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'GET') {
    $rows = DB::fetchAll(
        "SELECT service, api_key, api_secret, access_token, extra_data, status, updated_at
         FROM api_keys WHERE user_id = ? ORDER BY service ASC",
        [$userId]
    );

    $map = [];
    foreach ($rows as $r) {
        $k   = $r['api_key'] ?? '';
        $masked = strlen($k) > 10
            ? substr($k,0,6).'****'.substr($k,-4)
            : (strlen($k) > 0 ? '****' : '');

        $extra = $r['extra_data'] ? json_decode($r['extra_data'], true) : [];

        $map[$r['service']] = [
            'service'       => $r['service'],
            'api_key_masked'=> $masked,
            'has_secret'    => !empty($r['api_secret']),
            'has_token'     => !empty($r['access_token']),
            'business_id'   => $extra['business_id'] ?? '',
            'customer_id'   => $extra['customer_id'] ?? '',
            'status'        => $r['status'],
            'updated_at'    => $r['updated_at']
        ];
    }

    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>true,'data'=>$map], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// POST /api/settings/api-keys
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'POST') {
    try {
        $service     = trim($body['service']      ?? '');
        $apiKey      = trim($body['api_key']       ?? '');
        $apiSecret   = trim($body['api_secret']    ?? '');
        $accessToken = trim($body['access_token']  ?? '');
        $customerId  = trim($body['customer_id']   ?? '');
        $businessId  = trim($body['business_id']   ?? '');  // ★ 신규

        if (empty($service)) throw new Exception('서비스를 선택해주세요.');
        if (empty($apiKey))  throw new Exception('API 키를 입력해주세요.');

        $allowedServices = [
            'naver_search', 'naver_ad', 'naver_place',
            'openai', 'instagram', 'facebook', 'kakao', 'google'
        ];
        if (!in_array($service, $allowedServices))
            throw new Exception('지원하지 않는 서비스입니다: '.$service);

        if ($service === 'naver_ad' && empty($customerId))
            throw new Exception('네이버 광고는 고객 ID가 필수입니다.');

        // extra_data 구성 ─ 서비스별로 필요한 추가 정보 저장
        $extraData = [];
        if ($service === 'naver_ad'    && !empty($customerId)) $extraData['customer_id']  = $customerId;
        if ($service === 'naver_place' && !empty($businessId)) $extraData['business_id']  = $businessId;

        $extraDataJson = !empty($extraData) ? json_encode($extraData) : null;

        DB::execute(
            "INSERT INTO api_keys
             (user_id, service, api_key, api_secret, access_token, extra_data, status, created_at, updated_at)
             VALUES (?,?,?,?,?,?,'active',NOW(),NOW())
             ON DUPLICATE KEY UPDATE
               api_key=VALUES(api_key), api_secret=VALUES(api_secret),
               access_token=VALUES(access_token), extra_data=VALUES(extra_data),
               status='active', updated_at=NOW()",
            [$userId, $service, $apiKey, $apiSecret ?: null, $accessToken ?: null, $extraDataJson]
        );

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'message'  => 'API 키가 저장되었습니다.',
            'service'  => $service,
            'saved_at' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {
        ob_clean(); http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// POST /api/settings/test-naver-place  ★ 신규: 연결 테스트
// ============================================================
if ($route === 'api/settings/test-naver-place' && $method === 'POST') {
    try {
        $apiKey    = trim($body['api_key']    ?? '');
        $apiSecret = trim($body['api_secret'] ?? '');

        if (!$apiKey || !$apiSecret)
            throw new Exception('Client ID와 Client Secret이 필요합니다.');

        // 네이버 검색 API로 간단한 테스트 호출 (지역 검색)
        $testUrl = 'https://openapi.naver.com/v1/search/local.json?query='.urlencode('카페').'&display=1';
        $ch = curl_init($testUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-Naver-Client-Id: '     . $apiKey,
                'X-Naver-Client-Secret: ' . $apiSecret
            ]
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err)    throw new Exception('cURL 오류: '.$err);
        if ($code === 401) throw new Exception('인증 실패: Client ID 또는 Secret이 올바르지 않습니다.');
        if ($code === 403) throw new Exception('권한 없음: 앱에서 검색 API 사용을 허가해주세요.');
        if ($code !== 200) throw new Exception('API 오류: HTTP '.$code);

        $data  = json_decode($resp, true);
        $total = $data['total'] ?? 0;

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => '연결 성공',
            'total'   => $total
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {
        ob_clean(); http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// DELETE /api/settings/api-keys
// ============================================================
if ($route === 'api/settings/api-keys' && $method === 'DELETE') {
    try {
        $service = $body['service'] ?? '';
        if (empty($service)) throw new Exception('삭제할 서비스를 지정해주세요.');
        DB::execute("DELETE FROM api_keys WHERE user_id=? AND service=?", [$userId, $service]);
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>true,'message'=>'API 키가 삭제되었습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        ob_clean(); http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ============================================================
// 매칭 없음
// ============================================================
ob_clean(); http_response_code(400);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>false,'error'=>'잘못된 요청입니다.','route'=>$route,'method'=>$method], JSON_UNESCAPED_UNICODE);
exit;
