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

function toEvent(array $r): array {
  return [
    'id'          => (int)$r['id'],
    'title'       => $r['title'] ?? '',
    'description' => $r['description'] ?? '',
    'image'       => $r['image'] ?? '',
    'startDate'   => $r['start_date'] ?? '',
    'endDate'     => $r['end_date'] ?? '',
    'active'      => (bool)($r['active'] ?? true),
    'createdAt'   => $r['created_at'] ?? '',
  ];
}

if ($method === 'GET') {
  $where = []; $params = [];
  if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id=? LIMIT 1');
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'data'=> $row ? toEvent($row) : null], JSON_UNESCAPED_UNICODE); exit;
  }
  if (isset($_GET['active']) && $_GET['active'] !== '') {
    $where[] = 'active=?'; $params[] = (int)$_GET['active'];
  }
  $sql = 'SELECT * FROM events' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY id DESC';
  $stmt = $pdo->prepare($sql); $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['success'=>true,'data'=>array_map('toEvent',$rows)], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'POST') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = !empty($b['id']) ? (int)$b['id'] : null;
  $title = $b['title'] ?? '';
  $desc  = $b['description'] ?? '';
  $image = $b['image'] ?? '';
  $start = $b['startDate'] ?? $b['start_date'] ?? null;
  $end   = $b['endDate']   ?? $b['end_date']   ?? null;
  $active = isset($b['active']) ? (int)(bool)$b['active'] : 1;
  if (!$title) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'제목 필수'], JSON_UNESCAPED_UNICODE); exit; }
  if ($id) {
    $pdo->prepare('UPDATE events SET title=?,description=?,image=?,start_date=?,end_date=?,active=? WHERE id=?')
        ->execute([$title,$desc,$image,$start,$end,$active,$id]);
  } else {
    $pdo->prepare('INSERT INTO events (title,description,image,start_date,end_date,active,created_at) VALUES (?,?,?,?,?,?,NOW())')
        ->execute([$title,$desc,$image,$start,$end,$active]);
    $id = (int)$pdo->lastInsertId();
  }
  $stmt = $pdo->prepare('SELECT * FROM events WHERE id=?'); $stmt->execute([$id]);
  echo json_encode(['success'=>true,'data'=>toEvent($stmt->fetch(PDO::FETCH_ASSOC))], JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'DELETE') {
  $b = json_decode(file_get_contents('php://input'), true) ?? [];
  $id = (int)($b['id'] ?? $_GET['id'] ?? 0);
  if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id 필수'], JSON_UNESCAPED_UNICODE); exit; }
  $pdo->prepare('DELETE FROM events WHERE id=?')->execute([$id]);
  echo json_encode(['success'=>true], JSON_UNESCAPED_UNICODE); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
