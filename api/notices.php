<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/config.php';

try { $pdo = getDB(); } catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB 연결 실패: '.$e->getMessage()], JSON_UNESCAPED_UNICODE); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function toNotice(array $r): array {
  return [
    'id'        => (int)$r['id'],
    'title'     => $r['title'] ?? '',
    'content'   => $r['content'] ?? '',
    'category'  => $r['category'] ?? 'general',
    'active'    => (bool)($r['active'] ?? true),
    'createdAt' => $r['created_at'] ?? '',
  ];
}

if ($method === 'GET') {
  $where = []; $params = [];
  if (isset($_GET['active']) && $_GET['active'] !== '') {
    $where[] = 'active=?'; $params[] = (int)$_GET['active'];
  }
  if (!empty($_GET['category'])) {
    $where[] = 'category=?'; $params[] = $_GET['category'];
  }
  $sql = 'SELECT * FROM notices' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY id DESC';
  $stmt = $pdo->prepare($sql); $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['success'=>true,'data'=>array_map('toNotice',$rows)], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id       = !empty($b['id']) ? (int)$b['id'] : null;
  $title    = $b['title']    ?? '';
  $content  = $b['content']  ?? '';
  $category = $b['category'] ?? 'general';
  $active   = isset($b['active']) ? (int)(bool)$b['active'] : 1;
  if (!$title) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'제목 필수'], JSON_UNESCAPED_UNICODE); exit; }
  if ($id) {
    $pdo->prepare('UPDATE notices SET title=?,content=?,category=?,active=? WHERE id=?')
        ->execute([$title,$content,$category,$active,$id]);
  } else {
    $pdo->prepare('INSERT INTO notices (title,content,category,active,created_at) VALUES (?,?,?,?,NOW())')
        ->execute([$title,$content,$category,$active]);
    $id = (int)$pdo->lastInsertId();
  }
  $stmt = $pdo->prepare('SELECT * FROM notices WHERE id=?'); $stmt->execute([$id]);
  echo json_encode(['success'=>true,'data'=>toNotice($stmt->fetch(PDO::FETCH_ASSOC))], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'DELETE') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = (int)($b['id'] ?? $_GET['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id 필수'], JSON_UNESCAPED_UNICODE); exit; }
  $pdo->prepare('DELETE FROM notices WHERE id=?')->execute([$id]);
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
