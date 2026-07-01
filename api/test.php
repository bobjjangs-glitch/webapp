<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

/* ── 본인 정보로 수정 ── */
$host = 'localhost';
$db   = 'bobjjangs1231';   // phpMyAdmin 왼쪽 DB명
$user = 'bobjjangs1231';   // MySQL 계정
$pass = 'ssy201029@';    // MySQL 비밀번호

$result = [
  'php_version'    => PHP_VERSION,
  'step1_php'      => 'OK',
  'step2_db'       => 'FAIL',
  'step3_table'    => 'FAIL',
  'step4_count'    => 0,
  'step5_sample'   => null,
  'step6_api_path' => __FILE__,
  'error'          => null,
];

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8mb4",
    $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
  $result['step2_db'] = 'OK - DB 연결 성공';

  /* 테이블 확인 */
  $tables = $pdo->query("SHOW TABLES LIKE 'products'")->fetchAll(PDO::FETCH_COLUMN);
  if (count($tables) > 0) {
    $result['step3_table'] = 'OK - products 테이블 존재';
    $result['step4_count'] = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $result['step5_sample'] = $pdo->query(
      "SELECT id, name, category, active, price, sale_price FROM products LIMIT 3"
    )->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $result['step3_table'] = 'FAIL - products 테이블 없음 → SQL 다시 실행 필요';
  }

} catch (PDOException $e) {
  $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
