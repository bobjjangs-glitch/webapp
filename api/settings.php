<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/config.php';

try { $pdo = getDB(); } catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB 연결 실패: '.$e->getMessage()], JSON_UNESCAPED_UNICODE); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// 설정 전체를 key=>value 객체로 반환
function getAllSettings(PDO $pdo): array {
  $rows = $pdo->query('SELECT `key`, `value` FROM settings')->fetchAll(PDO::FETCH_ASSOC);
  $out  = [];
  foreach ($rows as $r) {
    $v = $r['value'];
    // JSON 값 자동 파싱
    $decoded = json_decode($v, true);
    $out[$r['key']] = ($decoded !== null && json_last_error() === JSON_ERROR_NONE) ? $decoded : $v;
  }
  return $out;
}

if ($method === 'GET') {
  echo json_encode(['success'=>true,'data'=>getAllSettings($pdo)], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $stmt = $pdo->prepare('INSERT INTO settings (`key`,`value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
  foreach ($b as $k => $v) {
    $stmt->execute([$k, is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string)$v]);
  }
  echo json_encode(['success'=>true,'data'=>getAllSettings($pdo)], JSON_UNESCAPED_UNICODE); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
