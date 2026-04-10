<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$userId = $_SESSION['user_id'] ?? 1;

// GET /api/notifications
if ($route === 'api/notifications' && $method === 'GET') {
    $rows = DB::fetchAll(
        "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
        [$userId]
    );
    if (empty($rows)) {
        // 데모 알림
        $rows = [
            ['id'=>1,'title'=>'플레이스 순위 상승!','message'=>'"강남 맛집" 키워드 1위 달성!','type'=>'success','is_read'=>0,'created_at'=>date('Y-m-d H:i:s')],
            ['id'=>2,'title'=>'블로그 상위 노출','message'=>'강남 맛집 포스트 2위 진입','type'=>'info','is_read'=>0,'created_at'=>date('Y-m-d H:i:s',time()-3600)],
            ['id'=>3,'title'=>'광고 예산 주의','message'=>'플레이스 광고 예산 80% 소진','type'=>'warning','is_read'=>1,'created_at'=>date('Y-m-d H:i:s',time()-7200)],
        ];
    }
    jsonResponse(['success'=>true,'data'=>$rows]);
}

// PATCH /api/notifications/{id}/read
if (isset($_GET['notif_id']) && $method === 'PATCH') {
    DB::execute(
        "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
        [$_GET['notif_id'], $userId]
    );
    jsonResponse(['success'=>true]);
}

jsonResponse(['error'=>'잘못된 요청'], 400);
