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

function toOrder(array $r): array {
  $items = json_decode($r['items'] ?? '[]', true);
  return [
    'id'          => (int)$r['id'],
    'orderNo'     => $r['order_no']    ?? '',
    'customerName'=> $r['customer_name'] ?? '',
    'customerPhone'=>$r['customer_phone'] ?? '',
    'status'      => $r['status']      ?? 'pending',
    'totalAmount' => (int)($r['total_amount'] ?? 0),
    'items'       => is_array($items) ? $items : [],
    'memo'        => $r['memo']        ?? '',
    'createdAt'   => $r['created_at']  ?? '',
    'updatedAt'   => $r['updated_at']  ?? '',
  ];
}

if ($method === 'GET') {
  $where = []; $params = [];
  if (!empty($_GET['status'])) { $where[] = 'status=?'; $params[] = $_GET['status']; }
  if (!empty($_GET['id']))     { $where[] = 'id=?';     $params[] = (int)$_GET['id']; }
  $limit  = isset($_GET['limit'])  ? max(1,min(500,(int)$_GET['limit']))  : 200;
  $offset = isset($_GET['offset']) ? max(0,(int)$_GET['offset']) : 0;
  $sql = 'SELECT * FROM orders' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY id DESC LIMIT '.$limit.' OFFSET '.$offset;
  $stmt = $pdo->prepare($sql); $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['success'=>true,'data'=>array_map('toOrder',$rows)], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id     = !empty($b['id']) ? (int)$b['id'] : null;
  $name   = $b['customerName']  ?? $b['customer_name']  ?? '';
  $phone  = $b['customerPhone'] ?? $b['customer_phone'] ?? '';
  $status = $b['status'] ?? 'pending';
  $total  = (int)($b['totalAmount'] ?? $b['total_amount'] ?? 0);
  $items  = json_encode($b['items'] ?? [], JSON_UNESCAPED_UNICODE);
  $memo   = $b['memo'] ?? '';
  if ($id) {
    $pdo->prepare('UPDATE orders SET customer_name=?,customer_phone=?,status=?,total_amount=?,items=?,memo=?,updated_at=NOW() WHERE id=?')
        ->execute([$name,$phone,$status,$total,$items,$memo,$id]);
  } else {
    $orderNo = 'ORD-'.date('Ymd').'-'.strtoupper(substr(uniqid(),0,6));
    $pdo->prepare('INSERT INTO orders (order_no,customer_name,customer_phone,status,total_amount,items,memo,created_at,updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())')
        ->execute([$orderNo,$name,$phone,$status,$total,$items,$memo]);
    $id = (int)$pdo->lastInsertId();
  }
  $stmt = $pdo->prepare('SELECT * FROM orders WHERE id=?'); $stmt->execute([$id]);
  echo json_encode(['success'=>true,'data'=>toOrder($stmt->fetch(PDO::FETCH_ASSOC))], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'PUT') {
  $b  = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = (int)($b['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id 필수'], JSON_UNESCAPED_UNICODE); exit; }
  $fields = []; $params = [];
  if (isset($b['status'])) { $fields[] = 'status=?'; $params[] = $b['status']; }
  if (isset($b['memo']))   { $fields[] = 'memo=?';   $params[] = $b['memo'];   }
  if (empty($fields)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'변경 필드 없음'], JSON_UNESCAPED_UNICODE); exit; }
  $fields[] = 'updated_at=NOW()'; $params[] = $id;
  $pdo->prepare('UPDATE orders SET '.implode(',',$fields).' WHERE id=?')->execute($params);
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'DELETE') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = (int)($b['id'] ?? $_GET['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id 필수'], JSON_UNESCAPED_UNICODE); exit; }
  $pdo->prepare('DELETE FROM orders WHERE id=?')->execute([$id]);
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
