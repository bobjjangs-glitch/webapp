<?php
// ================================================================
// api/dashboard.php - 대시보드 API
// ================================================================
global $pdo;

header('Content-Type: application/json; charset=utf-8');

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB 연결 없음'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method      = $_SERVER['REQUEST_METHOD'];
$route       = trim($_GET['route'] ?? '', '/');
$currentUser = getCurrentUser();

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$userId = (int)$currentUser['id'];

try {

    /* ── GET summary ─────────────────────────────────────────── */
    if ($route === 'api/dashboard/summary' && $method === 'GET') {
        $today = date('Y-m-d');
        $yest  = date('Y-m-d', strtotime('-1 day'));

        $s = $pdo->prepare("SELECT COUNT(*) FROM user_visits WHERE user_id=? AND DATE(created_at)=?");
        $s->execute([$userId, $today]); $todayV = (int)$s->fetchColumn();
        $s->execute([$userId, $yest]);  $yestV  = (int)$s->fetchColumn();
        $vch = $yestV > 0 ? round(($todayV-$yestV)/$yestV*100,1) : ($todayV>0?100:0);

        $s = $pdo->prepare("SELECT channel, COUNT(*) as cnt FROM user_visits WHERE user_id=? AND DATE(created_at)=? GROUP BY channel ORDER BY cnt DESC");
        $s->execute([$userId, $today]);
        $todayCh = [];
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $todayCh[$r['channel']]=(int)$r['cnt'];

        $runBoosts = 0;
        try {
            $s = $pdo->prepare("SELECT COUNT(*) FROM boost_tasks WHERE user_id=? AND status='running'");
            $s->execute([$userId]); $runBoosts=(int)$s->fetchColumn();
        } catch(Exception $e){}

        $credits = 0;
        try {
            $s = $pdo->prepare("SELECT balance FROM credit_balance WHERE user_id=? LIMIT 1");
            $s->execute([$userId]); $credits=(int)($s->fetchColumn()?:0);
        } catch(Exception $e){
            try {
                $s = $pdo->prepare("SELECT credits_balance FROM users WHERE id=? LIMIT 1");
                $s->execute([$userId]); $credits=(int)($s->fetchColumn()?:0);
            } catch(Exception $e2){}
        }

        $avgRank = 0;
        try {
            $s = $pdo->prepare("SELECT AVG(current_rank) FROM boost_tasks WHERE user_id=? AND current_rank>0");
            $s->execute([$userId]); $avgRank=round((float)($s->fetchColumn()?:0),1);
        } catch(Exception $e){}

        $apiStatus=[]; $apiConn=0;
        try {
            $s=$pdo->prepare("SELECT service,status,updated_at FROM api_keys WHERE user_id=?");
            $s->execute([$userId]);
            foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r){
                $apiStatus[$r['service']]=['status'=>$r['status'],'updated_at'=>$r['updated_at']];
                if($r['status']==='active')$apiConn++;
            }
        } catch(Exception $e){}

        echo json_encode([
            'success'=>true,
            'todayVisitors'=>$todayV,'visitorChange'=>$vch,
            'todayChannels'=>$todayCh,
            'placeAvgRank'=>$avgRank,'rankChange'=>0,
            'instagramFollowers'=>0,'followerChange'=>0,
            'runningBoosts'=>$runBoosts,'credits'=>$credits,
            'apiConnectedCount'=>$apiConn,'apiStatus'=>$apiStatus,
            'generatedAt'=>date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ── POST track-visit ────────────────────────────────────── */
    if ($route === 'api/dashboard/track-visit' && $method === 'POST') {
        $in  = json_decode(file_get_contents('php://input'),true)??[];
        $ch  = preg_replace('/[^a-z_]/','',strtolower($in['channel']??'direct'));
        $pg  = substr($in['page']??'/',0,500);
        $sid = substr($in['session_id']??'',0,100);
        $ref = substr($in['referrer']??'',0,500);
        $ip  = substr($_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']??'',0,45);
        $ua  = substr($_SERVER['HTTP_USER_AGENT']??'',0,300);

        if ($sid) {
            $chk=$pdo->prepare("SELECT id FROM user_visits WHERE user_id=? AND session_id=? AND DATE(created_at)=CURDATE() LIMIT 1");
            $chk->execute([$userId,$sid]);
            if($chk->fetchColumn()){echo json_encode(['success'=>true,'skipped'=>true],JSON_UNESCAPED_UNICODE);exit;}
        }

        $s=$pdo->prepare("INSERT INTO user_visits(user_id,session_id,channel,page_url,referrer,ip_address,browser,created_at) VALUES(?,?,?,?,?,?,?,NOW())");
        $s->execute([$userId,$sid,$ch,$pg,$ref,$ip,$ua]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()],JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ── GET channels ────────────────────────────────────────── */
    if ($route === 'api/dashboard/channels' && $method === 'GET') {
        $days=min((int)($_GET['days']??7),90);
        $s=$pdo->prepare("SELECT channel,COUNT(*) as total FROM user_visits WHERE user_id=? AND created_at>=DATE_SUB(CURDATE(),INTERVAL ? DAY) GROUP BY channel ORDER BY total DESC");
        $s->execute([$userId,$days]);
        $rows=$s->fetchAll(PDO::FETCH_ASSOC);
        $grand=array_sum(array_column($rows,'total'));
        $res=array_map(function($c)use($grand){
            return['channel'=>$c['channel'],'count'=>(int)$c['total'],'percent'=>$grand>0?round($c['total']/$grand*100,1):0];
        },$rows);
        echo json_encode(['success'=>true,'days'=>$days,'total'=>$grand,'channels'=>$res],JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ── GET api-status ──────────────────────────────────────── */
    if ($route === 'api/dashboard/api-status' && $method === 'GET') {
        $s=$pdo->prepare("SELECT service,status,updated_at FROM api_keys WHERE user_id=?");
        $s->execute([$userId]);
        $st=[];
        foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r)
            $st[$r['service']]=['status'=>$r['status'],'updated_at'=>$r['updated_at']];
        $conn=count(array_filter($st,function($v){return $v['status']==='active';}));
        echo json_encode(['success'=>true,'api_status'=>$st,'connected_count'=>$conn,'total_count'=>count($st)],JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success'=>false,'error'=>'API를 찾을 수 없습니다.','route'=>$route],JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("dashboard API DB 오류: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB 오류'],JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("dashboard API 오류: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'서버 오류'],JSON_UNESCAPED_UNICODE);
}
