<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$body   = getRequestBody();

// POST /api/analytics/track
if ($route === 'api/analytics/track' && $method === 'POST') {
    $visitorId = $body['visitor_id'] ?? bin2hex(random_bytes(16));
    $channel   = 'direct';
    $referrer  = $body['referrer'] ?? '';
    if (str_contains($referrer,'naver.com'))     $channel='naver';
    elseif (str_contains($referrer,'google'))    $channel='google';
    elseif (str_contains($referrer,'instagram')) $channel='instagram';
    elseif (str_contains($referrer,'kakao'))     $channel='kakao';
    elseif ($body['utm_source'] ?? '')           $channel=$body['utm_source'];
    $ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = (str_contains(strtolower($ua),'mobile')||str_contains(strtolower($ua),'android')) ? 'mobile' : 'desktop';
    try {
        DB::execute(
            "INSERT INTO user_visits (visitor_id,page_url,referrer,utm_source,utm_medium,utm_campaign,channel,device_type,browser,ip_address,is_new_visitor,duration_seconds) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            [$visitorId,$body['page_url']??'',$referrer,$body['utm_source']??'',$body['utm_medium']??'',$body['utm_campaign']??'',$channel,$device,'Unknown',$_SERVER['REMOTE_ADDR']??'',1,(int)($body['duration_seconds']??0)]
        );
    } catch(Exception $e) {}
    jsonResponse(['success'=>true,'visitor_id'=>$visitorId]);
}

// GET /api/analytics/overview
if ($route === 'api/analytics/overview' && $method === 'GET') {
    $days = max(1, min(365, (int)($_GET['days'] ?? 30)));
    try {
        $visitors = DB::fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as total_visits, SUM(is_new_visitor) as new_visitors, COUNT(DISTINCT visitor_id) as unique_visitors
             FROM user_visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) ORDER BY date",
            [$days]
        );
        $channels = DB::fetchAll(
            "SELECT channel, COUNT(*) as visits, COUNT(DISTINCT visitor_id) as unique_visitors, AVG(duration_seconds) as avg_duration
             FROM user_visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY channel ORDER BY visits DESC",
            [$days]
        );
        $devices = DB::fetchAll(
            "SELECT device_type, COUNT(*) as visits FROM user_visits
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY device_type",
            [$days]
        );
    } catch(Exception $e) {
        $visitors=$channels=$devices=[];
    }

    // 데이터 없으면 데모
    if (empty($visitors)) {
        $visitors = generateDemoVisitors($days);
        $channels = [
            ['channel'=>'naver',     'visits'=>rand(200,500),'unique_visitors'=>rand(180,400),'avg_duration'=>round(randomFloat(60,300))],
            ['channel'=>'instagram', 'visits'=>rand(100,300),'unique_visitors'=>rand(80,250), 'avg_duration'=>round(randomFloat(30,200))],
            ['channel'=>'direct',    'visits'=>rand(80,200), 'unique_visitors'=>rand(60,180), 'avg_duration'=>round(randomFloat(120,400))],
            ['channel'=>'google',    'visits'=>rand(50,150), 'unique_visitors'=>rand(40,120), 'avg_duration'=>round(randomFloat(90,300))],
            ['channel'=>'kakao',     'visits'=>rand(30,100), 'unique_visitors'=>rand(20,80),  'avg_duration'=>round(randomFloat(40,150))],
        ];
        $devices = [
            ['device_type'=>'mobile', 'visits'=>rand(400,800)],
            ['device_type'=>'desktop','visits'=>rand(200,400)],
        ];
    }
    jsonResponse(['success'=>true,'data'=>['visitors'=>$visitors,'channels'=>$channels,'devices'=>$devices]]);
}

// GET /api/analytics/realtime
if ($route === 'api/analytics/realtime' && $method === 'GET') {
    try {
        $active = DB::fetchOne("SELECT COUNT(DISTINCT visitor_id) as cnt FROM user_visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $recent = DB::fetchAll("SELECT page_url,channel,device_type,created_at FROM user_visits ORDER BY created_at DESC LIMIT 10");
    } catch(Exception $e) { $active=['cnt'=>rand(3,15)]; $recent=[]; }
    jsonResponse(['success'=>true,'active'=>(int)($active['cnt']??rand(3,15)),'recent'=>$recent]);
}

function generateDemoVisitors(int $days): array {
    $rows = [];
    for ($i=$days-1; $i>=0; $i--) {
        $rows[] = [
            'date'=>date('Y-m-d',strtotime("-{$i} days")),
            'total_visits'=>rand(50,300),
            'new_visitors'=>rand(20,100),
            'unique_visitors'=>rand(40,250),
        ];
    }
    return $rows;
}
