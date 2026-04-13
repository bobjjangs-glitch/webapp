<?php
// api/dashboard.php - 대시보드 API

define('IS_API', true);
header('Content-Type: application/json; charset=utf-8');

// $pdo 전역 변수 확보
if (!isset($pdo) || $pdo === null) {
    $pdo = isset($GLOBALS['pdo']) ? $GLOBALS['pdo'] : DB::connect();
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'로그인이 필요합니다.']);
    exit;
}
$userId = (int)$currentUser['id'];
$route  = trim($_GET['route'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];

try {

    // ── GET /api/dashboard/summary ───────────────────────────────
    if ($route === 'api/dashboard/summary' && $method === 'GET') {
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        // 오늘 방문자
        $todayVisitors = (int) DB::fetchColumn(
            "SELECT COUNT(*) FROM user_visits
              WHERE user_id = ? AND DATE(created_at) = ?",
            [$userId, $today]
        );
        $yesterdayVisitors = (int) DB::fetchColumn(
            "SELECT COUNT(*) FROM user_visits
              WHERE user_id = ? AND DATE(created_at) = ?",
            [$userId, $yesterday]
        );

        // 오늘 채널별 방문
        $channelRows = DB::fetchAll(
            "SELECT channel, COUNT(*) as count
               FROM user_visits
              WHERE user_id = ? AND DATE(created_at) = ?
              GROUP BY channel
              ORDER BY count DESC",
            [$userId, $today]
        );

        // 실행 중 부스트
        $runningBoosts = (int) DB::fetchColumn(
            "SELECT COUNT(*) FROM boost_tasks
              WHERE user_id = ? AND status = 'running'",
            [$userId]
        );

        // 크레딧
        $creditBalance = (int) DB::fetchColumn(
            "SELECT balance FROM credit_balance WHERE user_id = ? LIMIT 1",
            [$userId]
        );
        if ($creditBalance === false) {
            $creditBalance = (int) DB::fetchColumn(
                "SELECT credits_balance FROM users WHERE id = ? LIMIT 1",
                [$userId]
            );
        }

        // 평균 순위
        $avgRank = DB::fetchColumn(
            "SELECT ROUND(AVG(rank_position), 1)
               FROM place_rankings
              WHERE user_id = ? AND DATE(checked_at) = ?",
            [$userId, $today]
        );

        // 인스타 팔로워
        $igFollowers = (int) DB::fetchColumn(
            "SELECT followers_count FROM instagram_stats
              WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [$userId]
        );

        // API 연동 현황
        $apiRows = DB::fetchAll(
            "SELECT service, status FROM api_keys WHERE user_id = ?",
            [$userId]
        );
        $connectedApis = 0;
        foreach ($apiRows as $ar) {
            if ($ar['status'] === 'active') $connectedApis++;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'today_visitors'     => $todayVisitors,
                'yesterday_visitors' => $yesterdayVisitors,
                'channels'           => $channelRows,
                'running_boosts'     => $runningBoosts,
                'credit_balance'     => $creditBalance,
                'avg_rank'           => $avgRank ?: null,
                'instagram_followers'=> $igFollowers,
                'connected_apis'     => $connectedApis,
            ],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── POST /api/dashboard/track-visit ──────────────────────────
    if ($route === 'api/dashboard/track-visit' && $method === 'POST') {
        $input   = json_decode(file_get_contents('php://input'), true) ?? [];
        $channel = preg_replace('/[^a-z0-9_]/', '', strtolower($input['channel'] ?? 'other'));
        $page    = substr($input['page']    ?? '/', 0, 255);
        $ref     = substr($input['referrer'] ?? '', 0, 500);
        $sessId  = session_id();
        $ip      = $_SERVER['HTTP_X_FORWARDED_FOR']
                    ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
                    : ($_SERVER['REMOTE_ADDR'] ?? '');

        // 같은 세션 + 같은 채널 + 5분 내 중복 방지
        $exists = DB::fetchColumn(
            "SELECT id FROM user_visits
              WHERE user_id = ? AND session_id = ? AND channel = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
              LIMIT 1",
            [$userId, $sessId, $channel]
        );
        if ($exists) {
            echo json_encode(['success'=>true,'duplicate'=>true]);
            exit;
        }

        $ok = DB::execute(
            "INSERT INTO user_visits (user_id, channel, page, referrer, session_id, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$userId, $channel, $page, $ref, $sessId, $ip]
        );
        echo json_encode([
            'success' => $ok,
            'id'      => $ok ? DB::lastInsertId() : null,
        ]);
        exit;
    }

    // ── GET /api/dashboard/channels ──────────────────────────────
    if ($route === 'api/dashboard/channels' && $method === 'GET') {
        $days = min((int)($_GET['days'] ?? 7), 90);
        $rows = DB::fetchAll(
            "SELECT channel, COUNT(*) as count
               FROM user_visits
              WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
              GROUP BY channel
              ORDER BY count DESC",
            [$userId, $days]
        );
        echo json_encode(['success'=>true,'data'=>$rows], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── GET /api/dashboard/api-status ────────────────────────────
    if ($route === 'api/dashboard/api-status' && $method === 'GET') {
        $rows = DB::fetchAll(
            "SELECT service, status, updated_at FROM api_keys WHERE user_id = ?",
            [$userId]
        );
        echo json_encode(['success'=>true,'data'=>$rows], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>'API를 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('dashboard API 오류: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'서버 오류'], JSON_UNESCAPED_UNICODE);
}
