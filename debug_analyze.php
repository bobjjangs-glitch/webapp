<?php
// ============================================================
// debug_analyze.php - 오류 원인 진단 (확인 후 반드시 삭제!)
// ============================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: text/plain; charset=utf-8');

echo "PHP 버전: " . PHP_VERSION . "\n";
echo "ob_get_level: " . ob_get_level() . "\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "DEBUG_MODE: " . (defined('DEBUG_MODE') ? (DEBUG_MODE ? 'true' : 'false') : '미정의') . "\n\n";

// 함수 존재 확인
$funcs = ['isLoggedIn','getUserCredits','getApiKey','deductCredits','jsonResponse','getPageTitle'];
foreach ($funcs as $f) {
    echo "function {$f}: " . (function_exists($f) ? "OK" : "없음 ❌") . "\n";
}
echo "\n";

// DB 연결
try {
    $pdo = DB::connect();
    echo "DB 연결: 성공 ✅\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "테이블: " . implode(', ', $tables) . "\n\n";
} catch (Exception $e) {
    echo "DB 오류: " . $e->getMessage() . " ❌\n\n";
}

// analyze.php 파일 확인
$f = __DIR__ . '/api/analyze.php';
echo "api/analyze.php 존재: " . (file_exists($f) ? "YES" : "NO ❌") . "\n";
if (file_exists($f)) {
    echo "파일 크기: " . filesize($f) . " bytes\n";
    echo "수정시간: " . date('Y-m-d H:i:s', filemtime($f)) . "\n";
    // 첫 5줄만 확인
    $lines = file($f);
    echo "\nanalyze.php 첫 5줄:\n";
    for ($i = 0; $i < min(5, count($lines)); $i++) {
        echo ($i+1) . ": " . rtrim($lines[$i]) . "\n";
    }
    // ob_clean 포함 여부
    $src = file_get_contents($f);
    echo "\nob_clean 포함 여부: " . (strpos($src, 'ob_clean') !== false ? "있음 ❌ (이게 문제!)" : "없음 ✅") . "\n";
    echo "ob_end_clean 포함 여부: " . (strpos($src, 'ob_end_clean') !== false ? "있음" : "없음") . "\n";
}

// index.php 확인
$idx = __DIR__ . '/index.php';
echo "\nindex.php 수정시간: " . date('Y-m-d H:i:s', filemtime($idx)) . "\n";
$idxSrc = file_get_contents($idx);
echo "index.php ob_end_clean 포함: " . (strpos($idxSrc, 'ob_end_clean') !== false ? "있음 ✅" : "없음 ❌") . "\n";
echo "index.php GLOBALS pdo 포함: " . (strpos($idxSrc, 'GLOBALS') !== false ? "있음 ✅" : "없음 ❌") . "\n";

// config.php 확인
$cfgSrc = file_get_contents(__DIR__ . '/config/config.php');
echo "\nconfig.php display_errors=0 강제: " . (strpos($cfgSrc, "ini_set('display_errors', 0)") !== false ? "있음 ✅" : "없음 ❌") . "\n";
echo "config.php DEBUG_MODE false: " . (strpos($cfgSrc, "define('DEBUG_MODE', false)") !== false ? "있음 ✅" : "없음 ❌") . "\n";
