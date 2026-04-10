<?php
// ================================================================
// pages/dashboard.php - 대시보드 페이지
// ================================================================
global $pdo;

$apiServices = [
    'naver_search' => ['name'=>'네이버 검색',    'icon'=>'🔍','color'=>'#03c75a'],
    'naver_ad'     => ['name'=>'네이버 광고',    'icon'=>'📢','color'=>'#ff6b35'],
    'naver_place'  => ['name'=>'네이버 플레이스','icon'=>'📍','color'=>'#ff4757'],
    'openai'       => ['name'=>'OpenAI',         'icon'=>'🤖','color'=>'#10a37f'],
    'instagram'    => ['name'=>'인스타그램',     'icon'=>'📸','color'=>'#e1306c'],
    'kakao'        => ['name'=>'카카오',         'icon'=>'💛','color'=>'#fee500'],
    'google'       => ['name'=>'Google',         'icon'=>'🔵','color'=>'#4285f4'],
];

$apiKeyMap      = [];
$connectedCount = 0;

if ($pdo && isset($currentUser['id'])) {
    try {
        $s = $pdo->prepare("SELECT service, status, updated_at FROM api_keys WHERE user_id=?");
        $s->execute([$currentUser['id']]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $apiKeyMap[$row['service']] = $row;
            if ($row['status'] === 'active') $connectedCount++;
        }
    } catch (Exception $e) {
        error_log("dashboard api_keys 오류: " . $e->getMessage());
    }
}
$totalServices = count($apiServices);

// SVG 도넛 계산
$r    = 28;
$circ = 2 * M_PI * $r;
$dash = $totalServices > 0 ? ($connectedCount / $totalServices) * $circ : 0;
?>

<style>
.db-top-grid{display:grid;grid-template-columns:300px 1fr;gap:18px;margin-bottom:18px;}
.db-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:0;}
.db-bottom-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;}

/* API 연동 카드 */
.api-conn-top{display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid #f0f0f5;}
.api-donut{position:relative;width:70px;height:70px;flex-shrink:0;}
.api-donut svg{transform:rotate(-90deg);}
.api-donut-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;color:#e94560;}
.api-conn-info h4{font-size:14px;font-weight:700;margin-bottom:4px;}
.api-conn-info p{font-size:12px;color:#888;line-height:1.5;}
.api-svc-list{padding:10px 12px 14px;display:flex;flex-direction:column;gap:6px;}
.api-svc-item{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:9px;background:#f9f9fc;font-size:12.5px;}
.api-svc-icon{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.api-svc-name{flex:1;font-weight:500;}
.api-svc-badge{font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;}
.api-svc-badge.on{background:#e0f7f0;color:#00b894;}
.api-svc-badge.off{background:#f5f5f5;color:#aaa;}

/* 채널 */
.ch-list{display:flex;flex-direction:column;gap:12px;padding:2px 0;}
.ch-item{}
.ch-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;font-size:13px;}
.ch-name{display:flex;align-items:center;gap:6px;font-weight:500;}
.ch-count{color:#888;font-size:12px;}
.ch-bar-bg{height:7px;background:#f0f0f5;border-radius:4px;overflow:hidden;}
.ch-bar-fill{height:100%;border-radius:4px;transition:width .7s ease;}

/* 빠른 실행 */
.quick-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:2px 0;}
.quick-item{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 8px;border-radius:12px;background:#f9f9fc;border:1px solid #f0f0f5;cursor:pointer;text-decoration:none;color:var(--text);transition:all .2s;text-align:center;}
.quick-item:hover{background:#fff0f3;border-color:#e94560;transform:translateY(-2px);box-shadow:0 4px 14px rgba(233,69,96,.12);}
.quick-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.quick-label{font-size:11.5px;font-weight:600;}

@media(max-width:1100px){.db-stats-row{grid-template-columns:repeat(2,1fr);}}
@media(max-width:900px){.db-top-grid,.db-bottom-grid{grid-template-columns:1fr;}}
@media(max-width:600px){.db-stats-row{grid-template-columns:1fr 1fr;}.quick-grid{grid-template-columns:repeat(2,1fr);}}
</style>

<!-- ══ 상단: API현황 + 스탯 ══ -->
<div class="db-top-grid">

    <!-- API 연동 현황 -->
    <div class="card" style="margin-bottom:0;padding:0;overflow:hidden;">
        <div class="card-header" style="padding:14px 16px 12px;">
            <div class="card-title">🔌 API 연동 현황</div>
            <a href="index.php?route=settings" class="btn btn-sm btn-secondary">설정</a>
        </div>
        <div class="api-conn-top">
            <div class="api-donut">
                <svg width="70" height="70" viewBox="0 0 70 70">
                    <circle cx="35" cy="35" r="<?= $r ?>" fill="none" stroke="#f0f0f5" stroke-width="8"/>
                    <circle cx="35" cy="35" r="<?= $r ?>" fill="none" stroke="#e94560" stroke-width="8"
                        stroke-dasharray="<?= round($dash,2) ?> <?= round($circ,2) ?>"
                        stroke-linecap="round"/>
                </svg>
                <div class="api-donut-num"><?= $connectedCount ?></div>
            </div>
            <div class="api-conn-info">
                <h4><?= $connectedCount ?> / <?= $totalServices ?> 연동됨</h4>
                <p>API 키를 연동하면<br>더 많은 기능을 사용할 수 있습니다</p>
            </div>
        </div>
        <div class="api-svc-list">
            <?php foreach ($apiServices as $svc => $info):
                $connected = isset($apiKeyMap[$svc]) && $apiKeyMap[$svc]['status'] === 'active';
            ?>
            <div class="api-svc-item">
                <div class="api-svc-icon" style="background:<?= $info['color'] ?>22;">
                    <?= $info['icon'] ?>
                </div>
                <span class="api-svc-name"><?= $info['name'] ?></span>
                <span class="api-svc-badge <?= $connected ? 'on' : 'off' ?>">
                    <?= $connected ? '✓ 연동됨' : '미연동' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 오른쪽: 알림 + 4개 스탯 -->
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="alert alert-warning" style="margin-bottom:0;">
            ⚠️ <strong>시뮬레이션 모드</strong> — 실제 API 연동 전까지 모든 데이터는 시뮬레이션으로 처리됩니다.
            <a href="index.php?route=settings" style="font-weight:700;color:inherit;text-decoration:underline;margin-left:6px;">API 설정 →</a>
        </div>
        <div class="db-stats-row">
            <div class="stat-card">
                <div class="stat-icon si-red">👥</div>
                <div class="stat-body">
                    <div class="slabel">오늘 방문자</div>
                    <div class="sval" id="todayVisitors">-</div>
                    <div class="schange neutral" id="visitorChange">⏳ 로딩 중</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-blue">📊</div>
                <div class="stat-body">
                    <div class="slabel">플레이스 평균 순위</div>
                    <div class="sval" id="placeRank">-</div>
                    <div class="schange neutral" id="rankChange">⏳ 로딩 중</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-pink">📸</div>
                <div class="stat-body">
                    <div class="slabel">인스타 팔로워</div>
                    <div class="sval" id="igFollowers">-</div>
                    <div class="schange neutral">⏳ 로딩 중</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon si-orange">⚡</div>
                <div class="stat-body">
                    <div class="slabel">실행 중인 부스트</div>
                    <div class="sval" id="runningBoosts">-</div>
                    <div class="schange neutral"><span class="live-dot"></span>실시간</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ 하단: 채널 + 빠른실행 ══ -->
<div class="db-bottom-grid">
    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">
                📊 오늘 유입 채널
                <span style="font-size:11px;font-weight:400;color:#888;margin-left:6px;">총 <strong id="channelTotal">0</strong>명</span>
            </div>
        </div>
        <div id="channelList" style="padding:4px 16px 16px;">
            <div style="text-align:center;padding:28px 0;color:#aaa;">⏳ 데이터 로딩 중...</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:0;">
        <div class="card-header">
            <div class="card-title">⚡ 빠른 실행</div>
        </div>
        <div class="quick-grid" style="padding:4px 16px 16px;">
            <a href="index.php?route=place-boost" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#ffe0e5,#ffc5cc);">⚡</div>
                <span class="quick-label">순위 상승</span>
            </a>
            <a href="index.php?route=analytics" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#e0eeff,#c5d8ff);">👁️</div>
                <span class="quick-label">유입 분석</span>
            </a>
            <a href="index.php?route=auto-post" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#e0f7f0,#c5efe4);">🤖</div>
                <span class="quick-label">자동 포스팅</span>
            </a>
            <a href="index.php?route=place-rank" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#fff3e0,#ffe5b5);">🏆</div>
                <span class="quick-label">순위 추적</span>
            </a>
            <a href="index.php?route=seo" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#ede7f6,#d5c8f0);">🔍</div>
                <span class="quick-label">SEO 분석</span>
            </a>
            <a href="index.php?route=settings" class="quick-item">
                <div class="quick-icon" style="background:linear-gradient(135deg,#f5f5f5,#e8e8e8);">⚙️</div>
                <span class="quick-label">API 설정</span>
            </a>
        </div>
    </div>
</div>

<!-- ══ 최근 부스팅 현황 ══ -->
<div class="card">
    <div class="card-header">
        <div class="card-title">🚀 최근 부스팅 현황</div>
        <a href="index.php?route=place-boost" class="btn btn-sm btn-secondary">전체 보기</a>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>플레이스명</th><th>부스트 유형</th><th>키워드</th>
                    <th>진행률</th><th>상태</th><th>시작일</th>
                </tr>
            </thead>
            <tbody id="recentTasksBody">
                <tr><td colspan="6" style="text-align:center;padding:32px;color:#aaa;">⏳ 로딩 중...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
var CHANNEL_CONFIG={
    naver    :{emoji:'🟢',name:'네이버',    color:'#03c75a'},
    google   :{emoji:'🔍',name:'구글',      color:'#4285f4'},
    instagram:{emoji:'📸',name:'인스타그램',color:'#e1306c'},
    kakao    :{emoji:'💛',name:'카카오',    color:'#fee500'},
    direct   :{emoji:'🔵',name:'직접 방문',color:'#0066ff'},
    other    :{emoji:'⚫',name:'기타',      color:'#888888'},
};

function trackVisit(){
    var ref=document.referrer||'';
    var ch='direct';
    if(ref.indexOf('naver.com')>-1)ch='naver';
    else if(ref.indexOf('google.')>-1)ch='google';
    else if(ref.indexOf('instagram.com')>-1)ch='instagram';
    else if(ref.indexOf('kakao.com')>-1)ch='kakao';
    else if(ref.length>0)ch='other';
    var sid=sessionStorage.getItem('sm_sid');
    if(!sid){sid='ss_'+Date.now()+'_'+Math.random().toString(36).substr(2,8);sessionStorage.setItem('sm_sid',sid);}
    fetch(apiUrl('api/dashboard/track-visit'),{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({channel:ch,page:window.location.pathname+window.location.search,session_id:sid,referrer:ref})
    }).catch(function(){});
}

function renderChannels(channels){
    var wrap=document.getElementById('channelList');
    var tot=document.getElementById('channelTotal');
    if(!wrap)return;
    var sum=0;Object.values(channels).forEach(function(v){sum+=Number(v);});
    if(tot)tot.textContent=sum.toLocaleString('ko-KR');
    if(sum===0){
        wrap.innerHTML='<div style="text-align:center;padding:28px;color:#aaa;"><div style="font-size:26px;margin-bottom:8px;">📭</div><div style="font-size:13px;">오늘 방문 데이터가 없습니다</div></div>';
        return;
    }
    var html='<div class="ch-list">';
    Object.entries(channels).forEach(function(e){
        var key=e[0],cnt=Number(e[1]);
        var cfg=CHANNEL_CONFIG[key]||{emoji:'⚪',name:key,color:'#bbb'};
        var pct=sum>0?Math.round(cnt/sum*100):0;
        html+='<div class="ch-item">'
            +'<div class="ch-meta"><span class="ch-name">'+cfg.emoji+' '+cfg.name+'</span>'
            +'<span class="ch-count">'+cnt.toLocaleString('ko-KR')+'명 ('+pct+'%)</span></div>'
            +'<div class="ch-bar-bg"><div class="ch-bar-fill" style="width:'+pct+'%;background:'+cfg.color+';"></div></div>'
            +'</div>';
    });
    html+='</div>';
    wrap.innerHTML=html;
}

function loadDashboard(){
    fetch(apiUrl('api/dashboard/summary'))
        .then(function(r){return r.json();})
        .then(function(d){
            if(!d.success)return;
            var tv=document.getElementById('todayVisitors');if(tv)tv.textContent=fmtNum(d.todayVisitors||0);
            var vc=document.getElementById('visitorChange');
            if(vc){
                var ch=Number(d.visitorChange||0);
                if(ch>0){vc.className='schange up';vc.innerHTML='▲ 어제 대비 +'+ch+'%';}
                else if(ch<0){vc.className='schange down';vc.innerHTML='▼ 어제 대비 '+ch+'%';}
                else{vc.className='schange neutral';vc.innerHTML='─ 어제와 동일';}
            }
            var pr=document.getElementById('placeRank');if(pr)pr.textContent=Number(d.placeAvgRank)>0?d.placeAvgRank+'위':'-';
            var ig=document.getElementById('igFollowers');if(ig)ig.textContent=fmtNum(d.instagramFollowers||0);
            var rb=document.getElementById('runningBoosts');if(rb)rb.textContent=fmtNum(d.runningBoosts||0);
            renderChannels(d.todayChannels||{});
        })
        .catch(function(){renderChannels({});});
}

function loadRecentTasks(){
    fetch(apiUrl('api/place-boost/tasks'))
        .then(function(r){return r.json();})
        .then(function(d){
            var tbody=document.getElementById('recentTasksBody');if(!tbody)return;
            var tasks=d.tasks||d.data||[];
            if(!tasks.length){
                tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:32px;color:#aaa;"><div style="font-size:24px;margin-bottom:8px;">📭</div>진행 중인 부스팅이 없습니다</td></tr>';
                return;
            }
            var sm={running:'<span class="badge badge-green">실행중</span>',paused:'<span class="badge badge-orange">일시정지</span>',completed:'<span class="badge badge-blue">완료</span>',failed:'<span class="badge badge-red">실패</span>'};
            var html='';
            tasks.slice(0,5).forEach(function(t){
                var prog=Number(t.progress||0);
                html+='<tr>'
                    +'<td><strong>'+(t.place_name||'-')+'</strong></td>'
                    +'<td>'+(t.boost_type||'-')+'</td>'
                    +'<td>'+(t.keyword||'-')+'</td>'
                    +'<td style="min-width:110px;"><div class="progress-wrap" style="margin-bottom:3px;"><div class="progress-fill pf-red" style="width:'+prog+'%;"></div></div><span style="font-size:11px;color:#888;">'+prog+'%</span></td>'
                    +'<td>'+(sm[t.status]||t.status||'-')+'</td>'
                    +'<td style="font-size:12px;color:#aaa;">'+(t.start_date||t.created_at||'-').substring(0,10)+'</td>'
                    +'</tr>';
            });
            tbody.innerHTML=html;
        })
        .catch(function(){
            var tbody=document.getElementById('recentTasksBody');
            if(tbody)tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:24px;color:#aaa;">데이터를 불러올 수 없습니다.</td></tr>';
        });
}

document.addEventListener('DOMContentLoaded',function(){
    trackVisit();
    loadDashboard();
    loadRecentTasks();
    setInterval(loadDashboard,30000);
    setInterval(loadRecentTasks,60000);
});
</script>
