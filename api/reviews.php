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

function toReview(array $r): array {
  return [
    'id'         => (int)$r['id'],
    'productId'  => (int)($r['product_id'] ?? 0),
    'productName'=> $r['product_name'] ?? '',
    'author'     => $r['author'] ?? '',
    'rating'     => (float)($r['rating'] ?? 0),
    'content'    => $r['content'] ?? '',
    'active'     => (bool)($r['active'] ?? true),
    'createdAt'  => $r['created_at'] ?? '',
  ];
}

if ($method === 'GET') {
  $where = []; $params = [];
  if (isset($_GET['active']) && $_GET['active'] !== '') {
    $where[] = 'active=?'; $params[] = (int)$_GET['active'];
  }
  if (!empty($_GET['product_id'])) {
    $where[] = 'product_id=?'; $params[] = (int)$_GET['product_id'];
  }
  $limit = isset($_GET['limit']) ? max(1,min(100,(int)$_GET['limit'])) : 50;
  $sql = 'SELECT * FROM reviews' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY id DESC LIMIT '.$limit;
  $stmt = $pdo->prepare($sql); $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['success'=>true,'data'=>array_map('toReview',$rows)], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id          = !empty($b['id']) ? (int)$b['id'] : null;
  $productId   = (int)($b['productId'] ?? $b['product_id'] ?? 0);
  $productName = $b['productName'] ?? $b['product_name'] ?? '';
  $author      = $b['author']  ?? '';
  $rating      = (float)($b['rating'] ?? 5);
  $content     = $b['content'] ?? '';
  $active      = isset($b['active']) ? (int)(bool)$b['active'] : 1;
  if (!$author || !$content) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'작성자·내용 필수'], JSON_UNESCAPED_UNICODE); exit; }
  if ($id) {
    $pdo->prepare('UPDATE reviews SET product_id=?,product_name=?,author=?,rating=?,content=?,active=? WHERE id=?')
        ->execute([$productId,$productName,$author,$rating,$content,$active,$id]);
  } else {
    $pdo->prepare('INSERT INTO reviews (product_id,product_name,author,rating,content,active,created_at) VALUES (?,?,?,?,?,?,NOW())')
        ->execute([$productId,$productName,$author,$rating,$content,$active]);
    $id = (int)$pdo->lastInsertId();
  }
  $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id=?'); $stmt->execute([$id]);
  echo json_encode(['success'=>true,'data'=>toReview($stmt->fetch(PDO::FETCH_ASSOC))], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'DELETE') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = (int)($b['id'] ?? $_GET['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id 필수'], JSON_UNESCAPED_UNICODE); exit; }
  $pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
