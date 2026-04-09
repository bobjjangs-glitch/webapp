<?php
// ================================================================
// api/dashboard.php - 대시보드 API
// ================================================================

$route  = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// GET /api/dashboard/summary - 대시보드 요약 정보
// ============================================================
if ($route === 'api/dashboard/summary' && $method === 'GET') {
    try {
        // API 연동 상태
        $apiStatuses = getAllApiKeyStatuses($userId);
        
        // 통계 데이터 (실제 데이터로 교체 필요)
        $stats = [
            'total_analyses' => DB::fetchColumn(
                "SELECT COUNT(*) FROM analyses WHERE user_id = ?",
                [$userId]
            ) ?? 0,
            'today_views' => rand(100, 1000), // 예시
            'active_campaigns' => DB::fetchColumn(
                "SELECT COUNT(*) FROM campaigns WHERE user_id = ? AND status = 'active'",
                [$userId]
            ) ?? 0,
            'credits_balance' => DB::fetchColumn(
                "SELECT balance FROM user_credits WHERE user_id = ?",
                [$userId]
            ) ?? 0
        ];
        
        // API 연동 현황
        $services = [
            'naver_search' => ['name' => '네이버 검색', 'icon' => '🟢'],
            'naver_ad' => ['name' => '네이버 광고', 'icon' => '📍'],
            'openai' => ['name' => 'OpenAI', 'icon' => '🤖'],
            'instagram' => ['name' => '인스타그램', 'icon' => '📸'],
            'kakao' => ['name' => '카카오', 'icon' => '💛'],
            'google' => ['name' => 'Google', 'icon' => '📊']
        ];
        
        $apiConnections = [];
        foreach ($services as $key => $service) {
            $apiConnections[$key] = [
                'name' => $service['name'],
                'icon' => $service['icon'],
                'connected' => isset($apiStatuses[$key]),
                'updated_at' => $apiStatuses[$key]['updated_at'] ?? null
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'api_connections' => $apiConnections,
                'connected_count' => count($apiStatuses),
                'total_services' => count($services)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================================
// GET /api/dashboard/api-status - API 연동 상태만 조회
// ============================================================
if ($route === 'api/dashboard/api-status' && $method === 'GET') {
    try {
        $apiStatuses = getAllApiKeyStatuses($userId);
        
        echo json_encode([
            'success' => true,
            'data' => $apiStatuses,
            'count' => count($apiStatuses)
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================================
// 매칭되는 라우트 없음
// ============================================================
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => '요청한 API를 찾을 수 없습니다.',
    'route' => $route
], JSON_UNESCAPED_UNICODE);
