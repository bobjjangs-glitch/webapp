<?php
// ================================================================
// index.php - 메인 라우터
// ================================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// ★★★ 핵심 수정: $pdo 전역 변수 생성 ★★★
$pdo = DB::connect();
$GLOBALS['pdo'] = $pdo;

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
    ini_set('display_startup_errors', 0);
    error_reporting(0);

    // 치명적 오류를 JSON으로 변환
    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            while (ob_get_level() > 0) ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => '서버 내부 오류가 발생했습니다.',
                'detail'  => $err['message'] . ' (' . $err['file'] . ':' . $err['line'] . ')'
            ], JSON_UNESCAPED_UNICODE);
        }
    });

    // 출력 버퍼 정리 (세션은 이미 시작됐으므로 헤더는 유지)
    while (ob_get_level() > 0) ob_end_clean();

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
}// ── route → activeMenu 매핑 ──────────────────────────────────
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
function handleApiRoute(string $route): void
{
    global $pdo;

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];

    // 로그인 불필요 API
    $openApis = ['api/auth/login', 'api/auth/register'];
    if (!in_array($route, $openApis)) {
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(
                ['success' => false, 'error' => '로그인이 필요합니다.'],
                JSON_UNESCAPED_UNICODE
            );
            return;
        }
    }

    // ── api/settings 최우선 처리 ───────────────────────────
    if (strpos($route, 'api/settings') === 0) {
        $f = __DIR__ . '/api/settings.php';
        if (file_exists($f)) { require $f; return; }
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'settings API 파일 없음'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // ── PATCH /api/place-boost/tasks/{id} ──────────────────
    if (preg_match('#^api/place-boost/tasks/(\d+)$#', $route, $m)) {
        $_GET['task_id'] = $m[1];
        $f = __DIR__ . '/api/place-boost.php';
        if (file_exists($f)) { require $f; return; }
    }

    // ── PATCH /api/notifications/{id}/read ─────────────────
    if (preg_match('#^api/notifications/(\d+)/read$#', $route, $m)) {
        $_GET['notif_id'] = $m[1];
        $f = __DIR__ . '/api/notifications.php';
        if (file_exists($f)) { require $f; return; }
    }

    // ── 일반 API 라우트 맵 ──────────────────────────────────
    $apiMap = [
        'api/auth/login'                    => 'api/auth.php',
        'api/auth/logout'                   => 'api/auth.php',
        'api/auth/register'                 => 'api/auth.php',
        'api/dashboard/summary'             => 'api/dashboard.php',
        'api/dashboard/track-visit'         => 'api/dashboard.php',
        'api/dashboard/channels'            => 'api/dashboard.php',
        'api/dashboard/api-status'          => 'api/dashboard.php',
        'api/notifications'                 => 'api/notifications.php',
        'api/place-boost/places'            => 'api/place-boost.php',
        'api/place-boost/start'             => 'api/place-boost.php',
        'api/place-boost/tasks'             => 'api/place-boost.php',
        'api/place-boost/tasks/delete'      => 'api/place-boost.php',
        'api/place-boost/check-rank'        => 'api/place-boost.php',
        'api/analytics/overview'            => 'api/analytics.php',
        'api/analytics/track'               => 'api/analytics.php',
        'api/analytics/realtime'            => 'api/analytics.php',
        'api/credits/balance'               => 'api/credits.php',
        'api/credits/charge'                => 'api/credits.php',
        'api/credits/transactions'          => 'api/credits.php',
        'api/auto-post/instagram/schedules' => 'api/auto-post.php',
        'api/auto-post/blog/schedules'      => 'api/auto-post.php',
        'api/auto-post/hashtag-suggestions' => 'api/auto-post.php',
        'api/place-analyze/analyze'         => 'api/analyze.php',
        'api/place-rank/track'              => 'api/analyze.php',
        'api/naver-blog/analyze'            => 'api/analyze.php',
        'api/naver-blog/analyze-url'        => 'api/analyze.php',
        'api/seo/analyze'                   => 'api/analyze.php',
        'api/instagram/analyze'             => 'api/analyze.php',
        'api/place-ads/analyze'             => 'api/analyze.php',
        'api/blog-rank/track'               => 'api/analyze.php',
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

    http_response_code(404);
    echo json_encode([
        'success'          => false,
        'error'            => 'API를 찾을 수 없습니다.',
        'route'            => $route,
        'method'           => $method,
        'available_routes' => array_keys($apiMap),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
