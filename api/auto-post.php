<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$body   = getRequestBody();
$userId = $_SESSION['user_id'] ?? 1;

// GET /api/auto-post/instagram/schedules
if ($route === 'api/auto-post/instagram/schedules' && $method === 'GET') {
    $rows = DB::fetchAll("SELECT * FROM instagram_schedules WHERE user_id=? ORDER BY created_at DESC LIMIT 30", [$userId]);
    jsonResponse(['success'=>true,'data'=>$rows]);
}

// POST /api/auto-post/instagram/schedules
if ($route === 'api/auto-post/instagram/schedules' && $method === 'POST') {
    $caption     = $body['caption'] ?? '';
    $hashtags    = $body['hashtags'] ?? '';
    $scheduledAt = $body['scheduled_at'] ?? null;
    $postType    = $body['post_type'] ?? 'image';
    $status      = $scheduledAt ? 'scheduled' : 'draft';
    DB::execute(
        "INSERT INTO instagram_schedules (user_id,caption,hashtags,post_type,status,scheduled_at) VALUES (?,?,?,?,?,?)",
        [$userId,$caption,$hashtags,$postType,$status,$scheduledAt]
    );
    jsonResponse(['success'=>true,'id'=>DB::lastId(),'message'=>$status==='scheduled'?'예약이 완료되었습니다.':'임시저장 되었습니다.']);
}

// GET /api/auto-post/blog/schedules
if ($route === 'api/auto-post/blog/schedules' && $method === 'GET') {
    $rows = DB::fetchAll("SELECT * FROM blog_schedules WHERE user_id=? ORDER BY created_at DESC LIMIT 30", [$userId]);
    jsonResponse(['success'=>true,'data'=>$rows]);
}

// POST /api/auto-post/blog/schedules
if ($route === 'api/auto-post/blog/schedules' && $method === 'POST') {
    $title       = $body['title'] ?? '';
    $content     = $body['content'] ?? '';
    $keywords    = $body['keywords'] ?? '';
    $category    = $body['category'] ?? '';
    $scheduledAt = $body['scheduled_at'] ?? null;
    $isAi        = (int)($body['is_ai_generated'] ?? 0);
    $status      = $scheduledAt ? 'scheduled' : 'draft';
    if (!$title) jsonResponse(['error'=>'제목을 입력해주세요.'], 400);
    DB::execute(
        "INSERT INTO blog_schedules (user_id,title,content,keywords,category,status,scheduled_at,is_ai_generated) VALUES (?,?,?,?,?,?,?,?)",
        [$userId,$title,$content,$keywords,$category,$status,$scheduledAt,$isAi]
    );
    jsonResponse(['success'=>true,'id'=>DB::lastId(),'message'=>$status==='scheduled'?'예약이 완료되었습니다.':'임시저장 되었습니다.']);
}

// GET /api/auto-post/hashtag-suggestions
if ($route === 'api/auto-post/hashtag-suggestions' && $method === 'GET') {
    $keyword = $_GET['keyword'] ?? '마케팅';
    $tags = generateHashtags($keyword);
    jsonResponse(['success'=>true,'data'=>$tags]);
}

function generateHashtags(string $keyword): array {
    $base = ['#마케팅','#브랜딩','#SNS마케팅','#인스타그램','#네이버','#소상공인','#홍보'];
    $kw   = [
        "#{$keyword}", "#{$keyword}추천", "#{$keyword}맛집", "#{$keyword}정보",
        "#{$keyword}후기", "#{$keyword}리뷰"
    ];
    $tags = array_merge($kw, $base);
    shuffle($tags);
    return array_slice($tags, 0, 15);
}
