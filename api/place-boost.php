<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$body   = getRequestBody();
$userId = $_SESSION['user_id'] ?? 1;

// GET /api/place-boost/places
if ($route === 'api/place-boost/places' && $method === 'GET') {
    $rows = DB::fetchAll("SELECT * FROM places WHERE user_id=? ORDER BY created_at DESC", [$userId]);
    jsonResponse(['success'=>true,'data'=>$rows]);
}

// POST /api/place-boost/places
if ($route === 'api/place-boost/places' && $method === 'POST') {
    $name = trim($body['place_name'] ?? '');
    if (!$name) jsonResponse(['error'=>'업체명을 입력해주세요.'], 400);
    $kws = is_array($body['target_keywords'] ?? null)
        ? json_encode($body['target_keywords'], JSON_UNESCAPED_UNICODE)
        : ($body['target_keywords'] ?? '[]');
    DB::execute(
        "INSERT INTO places (user_id,place_name,category,address,target_keywords,naver_place_url) VALUES (?,?,?,?,?,?)",
        [$userId, $name, $body['category']??'', $body['address']??'', $kws, $body['naver_place_url']??'']
    );
    jsonResponse(['success'=>true,'id'=>DB::lastId()]);
}

// GET /api/place-boost/tasks
if ($route === 'api/place-boost/tasks' && $method === 'GET') {
    $rows = DB::fetchAll(
        "SELECT t.*, p.place_name FROM place_boost_tasks t
         JOIN places p ON t.place_id=p.id
         WHERE t.user_id=? ORDER BY t.created_at DESC LIMIT 50",
        [$userId]
    );
    jsonResponse(['success'=>true,'data'=>$rows]);
}

// POST /api/place-boost/start
if ($route === 'api/place-boost/start' && $method === 'POST') {
    $placeId = (int)($body['place_id'] ?? 0);
    $type    = $body['task_type'] ?? '';
    if (!$placeId || !$type) jsonResponse(['error'=>'필수 값을 입력해주세요.'], 400);
    $scheduled = date('Y-m-d H:i:s', time()+5);
    $config    = json_encode($body['config'] ?? [], JSON_UNESCAPED_UNICODE);
    DB::execute(
        "INSERT INTO place_boost_tasks (place_id,user_id,task_type,keyword,target_count,status,scheduled_at,config)
         VALUES (?,?,?,?,?,'running',?,?)",
        [$placeId,$userId,$type,$body['keyword']??'',(int)($body['target_count']??100),$scheduled,$config]
    );
    jsonResponse(['success'=>true,'task_id'=>DB::lastId(),'message'=>'부스팅 작업이 시작되었습니다.']);
}

// POST /api/place-boost/check-rank
if ($route === 'api/place-boost/check-rank' && $method === 'POST') {
    $keyword   = trim($body['keyword'] ?? '');
    $placeName = trim($body['place_name'] ?? '');
    if (!$keyword || !$placeName) jsonResponse(['error'=>'키워드와 업체명을 입력해주세요.'], 400);

    $rank  = rand(1,20);
    $total = rand(100,599);
    $comps = [];
    for ($i=0; $i<5; $i++) {
        $comps[] = [
            'rank'     => $i<2 ? $i+1 : ($i===2 ? $rank : $i+$rank),
            'name'     => $i===2 ? $placeName : '경쟁업체 '.($i+1),
            'rating'   => number_format(randomFloat(3.5,5.0), 1),
            'reviews'  => rand(50,500),
            'isTarget' => $i===2,
        ];
    }
    // 순위 기록 저장
    $place = DB::fetchOne("SELECT id FROM places WHERE place_name LIKE ? AND user_id=?", ['%'.$placeName.'%', $userId]);
    if ($place) {
        DB::execute(
            "INSERT INTO place_rank_history (place_id,keyword,rank,review_count,rating) VALUES (?,?,?,?,?)",
            [$place['id'],$keyword,$rank,rand(50,500),number_format(randomFloat(4.0,5.0),1)]
        );
    }
    jsonResponse(['success'=>true,'data'=>[
        'keyword'=>$keyword,'place_name'=>$placeName,'rank'=>$rank,
        'total'=>$total,'checked_at'=>date('c'),'competitors'=>$comps,
    ]]);
}

// PATCH /api/place-boost/tasks/{id}
if (isset($_GET['task_id']) && $method === 'PATCH') {
    $action = $body['action'] ?? '';
    $status = $action==='pause' ? 'paused' : ($action==='resume' ? 'running' : 'completed');
    DB::execute(
        "UPDATE place_boost_tasks SET status=? WHERE id=? AND user_id=?",
        [$status, $_GET['task_id'], $userId]
    );
    jsonResponse(['success'=>true,'status'=>$status]);
}

jsonResponse(['error'=>'잘못된 요청'], 400);
