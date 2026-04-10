<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

session_name(ADMIN_SESSION_NAME);
session_set_cookie_params(['lifetime'=>ADMIN_SESSION_LIFE,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
session_start();

$p = trim($_GET['p'] ?? 'dashboard');

// 로그인 처리
if ($p === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = trim($_POST['admin_id']  ?? '');
        $pw = trim($_POST['admin_pw']  ?? '');
        if (adminLogin($id, $pw)) {
            header('Location: index.php?p=dashboard');
            exit;
        }
        $loginError = '아이디 또는 비밀번호가 올바르지 않습니다.';
    }
    require __DIR__ . '/pages/login.php';
    exit;
}

if ($p === 'logout') {
    adminLogout();
    header('Location: index.php?p=login');
    exit;
}

adminRequireLogin();

$pageMap = [
    'dashboard' => ['title'=>'대시보드',      'file'=>'dashboard.php'],
    'charges'   => ['title'=>'충전 요청 관리', 'file'=>'charges.php'],
    'users'     => ['title'=>'회원 관리',      'file'=>'users.php'],
    'credits'   => ['title'=>'크레딧 내역',    'file'=>'credits.php'],
    'stats'     => ['title'=>'통계',           'file'=>'stats.php'],
];

// AJAX API 처리
if ($p === 'api') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'] ?? '';
    require __DIR__ . '/api/handler.php';
    exit;
}

$page     = $pageMap[$p] ?? $pageMap['dashboard'];
$pageFile = __DIR__ . '/pages/' . $page['file'];
$pageTitle= $page['title'];

require __DIR__ . '/includes/layout_top.php';
if (file_exists($pageFile)) {
    require $pageFile;
} else {
    echo '<div style="padding:40px;text-align:center;color:#888;">페이지를 찾을 수 없습니다.</div>';
}
require __DIR__ . '/includes/layout_bottom.php';
