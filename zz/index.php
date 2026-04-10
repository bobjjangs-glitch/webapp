<?php
// ================================================================
// index.php - 메인 라우터
// ================================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

session_name('SM_SESSION');
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ── 라우트 파싱 ──────────────────────────────────────────────
$route = trim($_GET['route'] ?? '', '/');
if ($route === '') $route = 'dashboard';

// ── API 요청 처리 ────────────────────────────────────────────
if (strpos($route, 'api/') === 0) {
    ini_set('display_errors', 0);
    error_reporting(0);
    header('Content-Type: application/json; charset=utf-8');
    error_log("API 요청: route={$route}, method={$_SERVER['REQUEST_METHOD']}");
    handleApiRoute($route);
    exit;
}

// ── 일반 페이지 에러 표시 ────────────────────────────────────
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ── 공개 페이지 ──────────────────────────────────────────────
$publicPages = ['login', 'install'];
if (!in_array($route, $publicPages)) {
    requireLogin();
}

// ── route → activeMenu 매핑 ──────────────────────────────────
$routeToMenuMap = [
    'dashboard'     => 'dashboard',
    'place-analyze' => 'place-analyze',
    'place-boost'   => 'place-boost',
    'place-rank'    => 'place-rank',
    'place-ads'     => 'place-ads',
    'analytics'     => 'analytics',
    'auto-post'     => 'auto-post',
    'settings'      => 'settings',
    'naver-blog'    => 'naver-blog',
    'instagram'     => 'instagram',
    'seo'           => 'seo',
    'blog-rank'     => 'blog-rank',
    'credits'       => 'credits',
];
$activeMenu = $routeToMenuMap[$route] ?? $route;

// ── 페이지 라우팅 ────────────────────────────────────────────
$pageMap = [
    'dashboard'     => 'pages/dashboard.php',
    'place-analyze' => 'pages/place-analyze.php',
    'place-boost'   => 'pages/place-boost.php',
    'place-rank'    => 'pages/place-rank.php',
    'place-ads'     => 'pages/place-ads.php',
    'analytics'     => 'pages/analytics.php',
    'auto-post'     => 'pages/auto-post.php',
    'settings'      => 'pages/settings.php',
    'naver-blog'    => 'pages/naver-blog.php',
    'instagram'     => 'pages/instagram.php',
    'seo'           => 'pages/seo.php',
    'blog-rank'     => 'pages/blog-rank.php',
    'credits'       => 'pages/credits.php',
    'login'         => 'pages/login.php',
    'install'       => 'pages/install.php',
    'logout'        => 'pages/logout.php',
];

$pagePath = $pageMap[$route] ?? null;

if ($pagePath && file_exists(__DIR__ . '/' . $pagePath)) {
    if (!in_array($route, ['login', 'install', 'logout'])) {
        $pageTitle = getPageTitle($route);
        require_once __DIR__ . '/includes/layout_top.php';
        require_once __DIR__ . '/' . $pagePath;
        require_once __DIR__ . '/includes/layout_bottom.php';
    } else {
        require_once __DIR__ . '/' . $pagePath;
    }
} else {
    http_response_code(404);
    $pageTitle  = '페이지를 찾을 수 없음';
    $activeMenu = '';
    require_once __DIR__ . '/includes/layout_top.php';
    echo '<div style="text-align:center;padding:80px 20px;">'
        . '<div style="font-size:60px;margin-bottom:16px;">😅</div>'
        . '<h2 style="font-size:24px;font-weight:700;margin-bottom:8px;">페이지를 찾을 수 없습니다</h2>'
        . '<p style="color:#888;margin-bottom:24px;">요청하신 페이지가 존재하지 않습니다.</p>'
        . '<a href="index.php?route=dashboard" class="btn btn-primary">대시보드로 돌아가기</a>'
        . '</div>';
    require_once __DIR__ . '/includes/layout_bottom.php';
}

// ================================================================
// API 라우트 핸들러
// ================================================================
function handleApiRoute(string $route): void {
    // ★ 함수 스코프 안이므로 반드시 global 선언 필요
    global $pdo;

    $method = $_SERVER['REQUEST_METHOD'];
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];

    // 로그인 불필요 API
    $openApis = ['api/auth/login', 'api/auth/register'];
    if (!in_array($route, $openApis)) {
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    // ── api/settings 최우선 처리 ────────────────────────────
    if (strpos($route, 'api/settings') === 0) {
        $apiFile = __DIR__ . '/api/settings.php';
        if (file_exists($apiFile)) {
            require $apiFile;
            return;
        }
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'api/settings.php 파일을 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // ── PATCH /api/place-boost/tasks/{숫자} ─────────────────
    if (preg_match('#^api/place-boost/tasks/(\d+)$#', $route, $m)) {
        $_GET['task_id'] = $m[1];
        $apiFile = __DIR__ . '/api/place-boost.php';
        if (file_exists($apiFile)) { require $apiFile; return; }
    }

    // ── PATCH /api/notifications/{숫자}/read ────────────────
    if (preg_match('#^api/notifications/(\d+)/read$#', $route, $m)) {
        $_GET['notif_id'] = $m[1];
        $apiFile = __DIR__ . '/api/notifications.php';
        if (file_exists($apiFile)) { require $apiFile; return; }
    }

    // ── 일반 API 라우트 맵 ───────────────────────────────────
    $apiMap = [
        // 인증
        'api/auth/login'                    => 'api/auth.php',
        'api/auth/logout'                   => 'api/auth.php',
        'api/auth/register'                 => 'api/auth.php',

        // 대시보드
        'api/dashboard/summary'             => 'api/dashboard.php',
        'api/dashboard/track-visit'         => 'api/dashboard.php',
        'api/dashboard/channels'            => 'api/dashboard.php',
        'api/dashboard/api-status'          => 'api/dashboard.php',

        // 알림
        'api/notifications'                 => 'api/notifications.php',

        // 플레이스 부스트
        'api/place-boost/places'            => 'api/place-boost.php',
        'api/place-boost/start'             => 'api/place-boost.php',
        'api/place-boost/tasks'             => 'api/place-boost.php',
        'api/place-boost/tasks/delete'      => 'api/place-boost.php',
        'api/place-boost/check-rank'        => 'api/place-boost.php',

        // 분석
        'api/analytics/overview'            => 'api/analytics.php',
        'api/analytics/track'               => 'api/analytics.php',
        'api/analytics/realtime'            => 'api/analytics.php',

        // 크레딧
        'api/credits/balance'               => 'api/credits.php',
        'api/credits/charge'                => 'api/credits.php',
        'api/credits/transactions'          => 'api/credits.php',

        // 자동 포스팅
        'api/auto-post/instagram/schedules' => 'api/auto-post.php',
        'api/auto-post/blog/schedules'      => 'api/auto-post.php',
        'api/auto-post/hashtag-suggestions' => 'api/auto-post.php',

        // AI 분석
        'api/place-analyze/analyze'         => 'api/analyze.php',
        'api/place-rank/track'              => 'api/analyze.php',
        'api/naver-blog/analyze'            => 'api/analyze.php',
        'api/seo/analyze'                   => 'api/analyze.php',
        'api/instagram/analyze'             => 'api/analyze.php',
        'api/place-ads/analyze'             => 'api/analyze.php',
        'api/blog-rank/track'               => 'api/analyze.php',

        // 설정
        'api/settings/api-keys'             => 'api/settings.php',
        'api/settings/test-naver-place'     => 'api/settings.php',
        'api/settings/profile'              => 'api/settings.php',
        'api/settings/plan'                 => 'api/settings.php',
    ];

    $apiFile = $apiMap[$route] ?? null;
    if ($apiFile && file_exists(__DIR__ . '/' . $apiFile)) {
        require __DIR__ . '/' . $apiFile;
        return;
    }

    // 404
    http_response_code(404);
    echo json_encode([
        'success'          => false,
        'error'            => 'API를 찾을 수 없습니다.',
        'route'            => $route,
        'method'           => $method,
        'available_routes' => array_keys($apiMap),
        'debug_info'       => [
            'request_uri'  => $_SERVER['REQUEST_URI']  ?? '',
            'query_string' => $_SERVER['QUERY_STRING'] ?? '',
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
