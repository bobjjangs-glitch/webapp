<?php
require_once __DIR__ . '/config.php';
setHeaders();
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

switch ($method) {
  case 'GET':
    ok($db->query('SELECT * FROM faqs ORDER BY id DESC')->fetchAll());

  case 'POST':
    $b = getBody();
    if (!empty($b['id'])) {
      $db->prepare('UPDATE faqs SET cat=:cat,q=:q,a=:a WHERE id=:id')
         ->execute([':cat'=>$b['cat']??'주문·결제',':q'=>$b['q']??'',':a'=>$b['a']??'',':id'=>(int)$b['id']]);
      ok($db->query("SELECT * FROM faqs WHERE id=".(int)$b['id'])->fetch(), 'FAQ가 수정되었습니다.');
    } else {
      $db->prepare('INSERT INTO faqs (cat,q,a) VALUES (:cat,:q,:a)')
         ->execute([':cat'=>$b['cat']??'주문·결제',':q'=>$b['q']??'',':a'=>$b['a']??'']);
      $id = (int)$db->lastInsertId();
      ok($db->query("SELECT * FROM faqs WHERE id=$id")->fetch(), 'FAQ가 등록되었습니다.');
    }

  case 'DELETE':
    $id = (int)($_GET['id'] ?? getBody()['id'] ?? 0);
    if (!$id) fail('id 필요');
    $db->prepare('DELETE FROM faqs WHERE id=:id')->execute([':id' => $id]);
    ok(null, '삭제되었습니다.');

  default: fail('지원하지 않는 메서드', 405);
}
