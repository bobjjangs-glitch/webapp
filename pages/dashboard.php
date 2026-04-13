<?php
// pages/dashboard.php - 대시보드

// $pdo 전역 변수 확보
if (!isset($pdo) || $pdo === null) {
    $pdo = isset($GLOBALS['pdo']) ? $GLOBALS['pdo'] : DB::connect();
}

// ── API 연동 현황 (api_keys 테이블 직접 조회) ──────────────────
$apiServices = [
    'naver_search' => ['name' => '네이버 검색',  'icon' => '🔍', 'color' => '#03c75a'],
    'naver_ad'     => ['name' => '네이버 광고',  'icon' => '📢', 'color' => '#ff6b35'],
    'naver_place'  => ['name' => '네이버 플레이스','icon' => '📍', 'color' => '#03c75a'],
    'openai'       => ['name' => 'OpenAI',        'icon' => '🤖', 'color' => '#10a37f'],
    'instagram'    => ['name' => '인스타그램',    'icon' => '📸', 'color' => '#e1306c'],
    'kakao'        => ['name' => '카카오',        'icon' => '💬', 'color' => '#fee500'],
    'google'       => ['name' => 'Google',        'icon' => '🔵', 'color' => '#4285f4'],
];

$apiKeyMap      = [];
$connectedCount = 0;
$totalServices  = count($apiServices);

if ($pdo && isset($currentUser['id'])) {
    try {
        $stmt = $pdo->prepare(
            "SELECT service, api_key, status, updated_at
               FROM api_keys
              WHERE user_id = ?
              ORDER BY updated_at DESC"
        );
        $stmt->execute([$currentUser['id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $svc = $row['service'];
            if (!isset($apiKeyMap[$svc])) {
                $apiKeyMap[$svc] = $row;
                // status = 'active' 이고 api_key가 실제로 존재할 때만 연동됨 처리
                if ($row['status'] === 'active' && !empty($row['api_key'])) {
                    $connectedCount++;
                }
            }
        }
    } catch (Exception $e) {
        error_log('dashboard api_keys 조회 오류: ' . $e->getMessage());
    }
}

// SVG 도넛 차트 계산
$r    = 30;
$circ = round(2 * M_PI * $r, 2);
$dash = $totalServices > 0 ? round($circ * $connectedCount / $totalServices, 2) : 0;
?>

<!-- ===== 대시보드 콘텐츠 ===== -->
<div class="dashboard-wrap">

    <!-- API 시뮬레이션 경고 (미연동 서비스 있을 때) -->
    <?php if ($connectedCount < $totalServices): ?>
    <div class="alert-banner">
        <i class="fas fa-exclamation-triangle"></i>
        <span>일부 API가 연동되지 않아 시뮬레이션 모드로 동작 중입니다.</span>
        <a href="index.php?route=settings" class="alert-link">API 설정 →</a>
    </div>
    <?php endif; ?>

    <!-- 상단 4개 통계 카드 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6c5ce7,#a29bfe)">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">오늘 방문자</div>
                <div class="stat-value" id="statVisitors">-</div>
                <div class="stat-change" id="statVisitorsChg"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#00b894,#00cec9)">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">플레이스 평균 순위</div>
                <div class="stat-value" id="statRank">-</div>
                <div class="stat-change" id="statRankChg"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#e1306c,#fd1d1d)">
                <i class="fab fa-instagram"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">인스타 팔로워</div>
                <div class="stat-value" id="statFollowers">-</div>
                <div class="stat-change" id="statFollowersChg"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#ff6b6b,#ee5a24)">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">실행 중 부스팅</div>
                <div class="stat-value" id="statBoosts">-</div>
                <div class="stat-change" id="statBoostsChg"></div>
            </div>
        </div>
    </div>

    <!-- 중단 2열: API 연동 현황 + 유입 채널 -->
    <div class="mid-grid">

        <!-- API 연동 현황 카드 -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🔌 API 연동 현황</span>
                <a href="index.php?route=settings" class="card-action">관리 →</a>
            </div>

            <div class="api-status-wrap">
                <!-- 도넛 차트 -->
                <div class="donut-wrap">
                    <svg width="90" height="90" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="<?= $r ?>"
                                fill="none" stroke="#e2e8f0" stroke-width="10"/>
                        <circle cx="45" cy="45" r="<?= $r ?>"
                                fill="none" stroke="#00b894" stroke-width="10"
                                stroke-dasharray="<?= $dash ?> <?= $circ ?>"
                                stroke-dashoffset="<?= round($circ / 4, 2) ?>"
                                stroke-linecap="round"
                                id="donutCircle"/>
                    </svg>
                    <div class="donut-label">
                        <span class="donut-count"><?= $connectedCount ?></span>
                        <span class="donut-total">/ <?= $totalServices ?></span>
                    </div>
                </div>

                <!-- 서비스 목록 -->
                <div class="api-list">
                    <?php foreach ($apiServices as $svcKey => $svcInfo):
                        $row        = $apiKeyMap[$svcKey] ?? null;
                        $isActive   = $row && $row['status'] === 'active' && !empty($row['api_key']);
                        $badgeClass = $isActive ? 'badge-ok' : 'badge-no';
                        $badgeText  = $isActive ? '✓ 연동됨' : '미연동';
                        $updatedAt  = $row ? date('m/d H:i', strtotime($row['updated_at'])) : '';
                    ?>
                    <div class="api-row">
                        <span class="api-icon"><?= $svcInfo['icon'] ?></span>
                        <span class="api-name"><?= htmlspecialchars($svcInfo['name']) ?></span>
                        <?php if ($updatedAt): ?>
                        <span class="api-date"><?= $updatedAt ?></span>
                        <?php endif; ?>
                        <span class="api-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 오늘 유입 채널 -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📊 오늘 유입 채널</span>
            </div>
            <div id="channelList">
                <div style="text-align:center;padding:30px;color:var(--text-mute)">
                    <i class="fas fa-spinner fa-spin"></i> 로딩 중...
                </div>
            </div>
        </div>
    </div>

    <!-- 빠른 실행 -->
    <div class="card" style="margin-top:16px">
        <div class="card-header">
            <span class="card-title">⚡ 빠른 실행</span>
        </div>
        <div class="quick-grid">
            <a href="index.php?route=place-boost"   class="quick-btn qb-boost">   <i class="fas fa-rocket"></i>   플레이스 부스팅</a>
            <a href="index.php?route=analytics"     class="quick-btn qb-analytics"><i class="fas fa-chart-bar"></i> 유입 분석</a>
            <a href="index.php?route=auto-post"     class="quick-btn qb-autopost"> <i class="fas fa-calendar-alt"></i>자동 포스팅</a>
            <a href="index.php?route=place-rank"    class="quick-btn qb-rank">     <i class="fas fa-chart-line"></i>순위 추적</a>
            <a href="index.php?route=seo"           class="quick-btn qb-seo">      <i class="fas fa-search"></i>   SEO 분석</a>
            <a href="index.php?route=settings"      class="quick-btn qb-settings"> <i class="fas fa-plug"></i>      API 설정</a>
        </div>
    </div>

    <!-- 최근 부스팅 현황 -->
    <div class="card" style="margin-top:16px">
        <div class="card-header">
            <span class="card-title">🚀 최근 부스팅 현황</span>
            <a href="index.php?route=place-boost" class="card-action">전체 보기 →</a>
        </div>
        <div id="recentTasksWrap">
            <table class="data-table" id="recentTasksTable">
                <thead>
                    <tr>
                        <th>업체명</th>
                        <th>키워드</th>
                        <th>상태</th>
                        <th>진행률</th>
                        <th>시작일</th>
                    </tr>
                </thead>
                <tbody id="recentTasksBody">
                    <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-mute)">
                        <i class="fas fa-spinner fa-spin"></i> 로딩 중...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== 대시보드 스타일 ===== -->
<style>
.dashboard-wrap { max-width: 1200px; }

/* 경고 배너 */
.alert-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: var(--radius);
    margin-bottom: 16px;
    font-size: 13px;
    color: #795548;
}
.alert-banner i { color: #f9a825; }
.alert-link {
    margin-left: auto;
    color: var(--primary);
    font-weight: 600;
    white-space: nowrap;
}

/* 상단 통계 그리드 */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}
.stat-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
.stat-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-label { font-size: 11px; color: var(--text-mute); margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 700; color: var(--text); line-height: 1; }
.stat-change { font-size: 11px; color: var(--text-mute); margin-top: 4px; }

/* 중단 2열 */
.mid-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 0;
}

/* API 연동 현황 */
.api-status-wrap {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.donut-wrap {
    position: relative;
    flex-shrink: 0;
    width: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.donut-label {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    line-height: 1.2;
}
.donut-count { font-size: 18px; font-weight: 800; color: var(--text); }
.donut-total { font-size: 11px; color: var(--text-mute); display: block; }
.api-list { flex: 1; display: flex; flex-direction: column; gap: 7px; }
.api-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}
.api-icon { font-size: 14px; flex-shrink: 0; }
.api-name { flex: 1; color: var(--text); font-weight: 500; }
.api-date { font-size: 10px; color: var(--text-mute); white-space: nowrap; }
.api-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    white-space: nowrap;
    flex-shrink: 0;
}
.badge-ok  { background: #e8f8f0; color: #00b894; }
.badge-no  { background: #f5f6fa; color: #b2bec3; }

/* 카드 공통 */
.card-action {
    font-size: 12px;
    color: var(--primary);
    font-weight: 600;
}

/* 채널 목록 */
.channel-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.channel-row:last-child { border-bottom: none; }
.channel-bar-wrap {
    flex: 1;
    height: 6px;
    background: var(--border);
    border-radius: 3px;
    overflow: hidden;
}
.channel-bar { height: 100%; border-radius: 3px; transition: width .6s ease; }
.channel-cnt { font-size: 12px; color: var(--text-mute); min-width: 28px; text-align: right; }

/* 빠른 실행 */
.quick-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.quick-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 600;
    transition: var(--transition);
    color: #fff;
}
.quick-btn:hover { transform: translateY(-2px); filter: brightness(1.1); }
.quick-btn i { font-size: 15px; }
.qb-boost     { background: linear-gradient(135deg,#ff6b6b,#ee5a24); }
.qb-analytics { background: linear-gradient(135deg,#6c5ce7,#a29bfe); }
.qb-autopost  { background: linear-gradient(135deg,#00b894,#00cec9); }
.qb-rank      { background: linear-gradient(135deg,#0984e3,#74b9ff); }
.qb-seo       { background: linear-gradient(135deg,#fdcb6e,#e17055); }
.qb-settings  { background: linear-gradient(135deg,#636e72,#b2bec3); }

/* 테이블 */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table th {
    padding: 8px 12px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-mute);
    border-bottom: 2px solid var(--border);
    text-transform: uppercase;
    letter-spacing: .5px;
}
.data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #f8f9fe; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.status-running  { background:#e8f5e9; color:#2e7d32; }
.status-done     { background:#e3f2fd; color:#1565c0; }
.status-pending  { background:#fff8e1; color:#f57f17; }
.status-error    { background:#fce4ec; color:#c62828; }

.progress-bar-wrap {
    background: var(--border);
    border-radius: 4px;
    height: 6px;
    width: 80px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg,#6c5ce7,#a29bfe);
    border-radius: 4px;
    transition: width .4s ease;
}

/* 반응형 */
@media(max-width:900px){
    .stats-grid { grid-template-columns: repeat(2,1fr); }
    .mid-grid   { grid-template-columns: 1fr; }
    .quick-grid { grid-template-columns: repeat(2,1fr); }
}
@media(max-width:480px){
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .quick-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<!-- ===== 대시보드 JS ===== -->
<script>
(function(){
    // 채널 설정
    const CHANNEL_CONFIG = {
        naver:     { emoji:'🟢', name:'네이버',       color:'#03c75a' },
        google:    { emoji:'🔵', name:'구글',         color:'#4285f4' },
        instagram: { emoji:'📸', name:'인스타그램',   color:'#e1306c' },
        kakao:     { emoji:'💬', name:'카카오',       color:'#fee500' },
        direct:    { emoji:'🔗', name:'직접 유입',    color:'#636e72' },
        other:     { emoji:'🌐', name:'기타',         color:'#b2bec3' },
    };

    // 방문 추적
    function trackVisit(){
        const ch = (() => {
            const ref = document.referrer.toLowerCase();
            if(ref.includes('naver.com'))     return 'naver';
            if(ref.includes('google.com'))    return 'google';
            if(ref.includes('instagram.com')) return 'instagram';
            if(ref.includes('kakao.com'))     return 'kakao';
            if(ref === '')                    return 'direct';
            return 'other';
        })();
        axios.post('index.php?route=api/dashboard/track-visit', {
            channel: ch,
            page:    window.location.pathname + window.location.search,
            referrer: document.referrer
        }).catch(()=>{});
    }

    // 채널 렌더링
    function renderChannels(channels){
        const el = document.getElementById('channelList');
        if(!el) return;
        if(!channels || channels.length === 0){
            el.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-mute)">오늘 유입 데이터가 없습니다.</div>';
            return;
        }
        const max = Math.max(...channels.map(c => c.count || 0), 1);
        el.innerHTML = channels.map(c => {
            const cfg   = CHANNEL_CONFIG[c.channel] || CHANNEL_CONFIG.other;
            const width = Math.round((c.count / max) * 100);
            return `<div class="channel-row">
                <span style="font-size:16px">${cfg.emoji}</span>
                <span style="min-width:70px;font-weight:500">${cfg.name}</span>
                <div class="channel-bar-wrap">
                    <div class="channel-bar" style="width:${width}%;background:${cfg.color}"></div>
                </div>
                <span class="channel-cnt">${c.count}</span>
            </div>`;
        }).join('');
    }

    // 대시보드 데이터 로드
    function loadDashboard(){
        axios.get('index.php?route=api/dashboard/summary')
            .then(res => {
                if(!res.data || !res.data.success) return;
                const d = res.data.data || {};

                // 통계 카드 업데이트
                const sv = document.getElementById('statVisitors');
                const sr = document.getElementById('statRank');
                const sf = document.getElementById('statFollowers');
                const sb = document.getElementById('statBoosts');
                if(sv) sv.textContent = (d.today_visitors ?? 0).toLocaleString();
                if(sr) sr.textContent = d.avg_rank ? d.avg_rank + '위' : '-';
                if(sf) sf.textContent = (d.instagram_followers ?? 0).toLocaleString();
                if(sb) sb.textContent = (d.running_boosts ?? 0).toLocaleString();

                // 채널 렌더링
                if(d.channels) renderChannels(d.channels);
            })
            .catch(()=>{});
    }

    // 최근 부스팅 로드
    function loadRecentTasks(){
        axios.get('index.php?route=api/place-boost/tasks&limit=5')
            .then(res => {
                const tbody = document.getElementById('recentTasksBody');
                if(!tbody) return;
                const tasks = res.data?.data?.tasks || res.data?.tasks || [];
                if(tasks.length === 0){
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-mute)">진행 중인 부스팅 작업이 없습니다.</td></tr>';
                    return;
                }
                const statusMap = {
                    running: ['status-running','실행 중'],
                    completed:['status-done','완료'],
                    pending:  ['status-pending','대기'],
                    error:    ['status-error','오류'],
                    paused:   ['status-pending','일시정지'],
                };
                tbody.innerHTML = tasks.map(t => {
                    const [cls, label] = statusMap[t.status] || ['status-pending', t.status];
                    const progress = t.progress ?? 0;
                    const startDate = t.created_at ? new Date(t.created_at).toLocaleDateString('ko-KR') : '-';
                    return `<tr>
                        <td><strong>${escHtml(t.business_name||'-')}</strong></td>
                        <td>${escHtml(t.keyword||'-')}</td>
                        <td><span class="status-badge ${cls}">${label}</span></td>
                        <td>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width:${progress}%"></div>
                            </div>
                            <small style="color:var(--text-mute)">${progress}%</small>
                        </td>
                        <td style="color:var(--text-mute)">${startDate}</td>
                    </tr>`;
                }).join('');
            })
            .catch(()=>{
                const tbody = document.getElementById('recentTasksBody');
                if(tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-mute)">데이터를 불러올 수 없습니다.</td></tr>';
            });
    }

    function escHtml(str){
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.addEventListener('DOMContentLoaded', function(){
        trackVisit();
        loadDashboard();
        loadRecentTasks();
        // 자동 갱신
        setInterval(loadDashboard,  30000);
        setInterval(loadRecentTasks, 60000);
    });
})();
</script>
