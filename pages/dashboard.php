<?php
// ================================================================
// pages/dashboard.php - 대시보드 페이지
// ================================================================

$user = getCurrentUser();

// API 연동 상태 가져오기
$apiStatuses = getAllApiKeyStatuses();

$services = [
    'naver_search' => ['name' => '네이버 검색', 'icon' => '🟢'],
    'naver_ad' => ['name' => '네이버 광고', 'icon' => '📍'],
    'openai' => ['name' => 'OpenAI', 'icon' => '🤖'],
    'instagram' => ['name' => '인스타그램', 'icon' => '📸'],
    'kakao' => ['name' => '카카오', 'icon' => '💛'],
    'google' => ['name' => 'Google', 'icon' => '📊'],
];

$connectedCount = count($apiStatuses);
$totalServices = count($services);
?>

<!-- API 연동 현황 카드 -->
<div class="card">
  <div class="card-header">
    <div class="card-title">🔗 API 연동 현황</div>
    <a href="index.php?route=settings" class="btn btn-sm btn-primary">설정 가기</a>
  </div>
  <div class="card-body">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
      <div style="flex: 0 0 auto;">
        <div class="connection-circle">
          <span class="api-connected-count"><?php echo $connectedCount; ?></span>
          <span class="connection-divider">/</span>
          <span class="connection-total"><?php echo $totalServices; ?></span>
        </div>
      </div>
      <div style="flex: 1;">
        <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 600;">
          <?php echo $connectedCount; ?>개 서비스 연동됨
        </h3>
        <p style="margin: 0; color: #666; font-size: 14px;">
          전체 <?php echo $totalServices; ?>개 중 <?php echo $connectedCount; ?>개의 API가 활성화되어 있습니다.
        </p>
        <?php if ($connectedCount < $totalServices): ?>
        <a href="index.php?route=settings" class="link-button" style="display: inline-block; margin-top: 12px;">
          → 추가 API 연동하기
        </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="api-status-grid">
      <?php foreach ($services as $serviceKey => $service): ?>
        <?php 
        $isConnected = isset($apiStatuses[$serviceKey]);
        $updatedAt = $isConnected ? $apiStatuses[$serviceKey]['updated_at'] : null;
        ?>
        <div class="api-status-item <?php echo $isConnected ? 'connected' : 'disconnected'; ?>" 
             data-service="<?php echo $serviceKey; ?>">
          <div class="api-status-header">
            <span class="api-icon"><?php echo $service['icon']; ?></span>
            <div class="api-info">
              <div class="api-name"><?php echo $service['name']; ?></div>
              <div class="api-status-badge">
                <?php if ($isConnected): ?>
                  <span class="status-connected">✓ 연동됨</span>
                <?php else: ?>
                  <span class="status-disconnected">미연동</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php if ($isConnected && $updatedAt): ?>
            <div class="api-update-time">
              마지막 업데이트: <?php echo timeAgo($updatedAt); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- 통계 카드 -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-red">👁️</div>
    <div class="stat-body">
      <div class="slabel">오늘 방문자</div>
      <div class="sval" id="todayVisitors">-</div>
      <div class="schange up" id="visitorChange">로딩 중</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">🏆</div>
    <div class="stat-body">
      <div class="slabel">플레이스 평균순위</div>
      <div class="sval" id="placeRank">-</div>
      <div class="schange up" id="rankChange">로딩 중</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue">📸</div>
    <div class="stat-body">
      <div class="slabel">인스타 팔로워</div>
      <div class="sval" id="igFollowers">-</div>
      <div class="schange up" id="followerChange">로딩 중</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-orange">⚡</div>
    <div class="stat-body">
      <div class="slabel">진행 중 부스팅</div>
      <div class="sval" id="runningBoosts">-</div>
      <div class="schange neutral">작업 중</div>
    </div>
  </div>
</div>

<div class="grid-2">
  <!-- 유입 채널 현황 -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 오늘 유입 채널</div>
      <span style="font-size:12px;color:#888;"><span class="live-dot"></span>실시간</span>
    </div>
    <div id="channelList" style="display:flex;flex-direction:column;gap:10px;">
      <div style="text-align:center;padding:30px;color:#888;font-size:13px;">로딩 중...</div>
    </div>
  </div>

  <!-- 빠른 메뉴 -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🚀 빠른 실행</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <?php
      $quickMenus = [
        ['href'=>'index.php?route=place-boost','icon'=>'⚡','label'=>'순위 상승','color'=>'#e94560'],
        ['href'=>'index.php?route=analytics',  'icon'=>'📊','label'=>'유입 분석','color'=>'#0066ff'],
        ['href'=>'index.php?route=auto-post',  'icon'=>'🤖','label'=>'자동 포스팅','color'=>'#00b894'],
        ['href'=>'index.php?route=place-rank', 'icon'=>'🏆','label'=>'순위 추적','color'=>'#f5a623'],
        ['href'=>'index.php?route=seo',        'icon'=>'🔍','label'=>'SEO 분석','color'=>'#7c4dff'],
        ['href'=>'index.php?route=settings',   'icon'=>'⚙️','label'=>'API 설정','color'=>'#888'],
      ];
      foreach($quickMenus as $m): ?>
      <a href="<?= $m['href'] ?>" style="display:flex;align-items:center;gap:10px;padding:14px;background:#f9f9fc;border-radius:12px;text-decoration:none;transition:all .2s;border:1px solid #f0f0f5;" onmouseover="this.style.background='#f0f2f5'" onmouseout="this.style.background='#f9f9fc'">
        <div style="width:38px;height:38px;border-radius:10px;background:<?= $m['color'] ?>18;display:flex;align-items:center;justify-content:center;font-size:18px;"><?= $m['icon'] ?></div>
        <span style="font-size:13px;font-weight:600;color:#333;"><?= $m['label'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- 최근 부스팅 작업 -->
<div class="card">
  <div class="card-header">
    <div class="card-title">⚡ 최근 부스팅 작업</div>
    <a href="index.php?route=place-boost" class="btn btn-primary btn-sm">전체 보기</a>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr><th>업체명</th><th>작업유형</th><th>키워드</th><th>진행률</th><th>상태</th></tr>
      </thead>
      <tbody id="recentTasks">
        <tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">로딩 중...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
const channelEmoji = {naver:'🟢',instagram:'📸',direct:'🔵',google:'🔍',kakao:'💛',other:'⚫'};
const channelName  = {naver:'네이버',instagram:'인스타그램',direct:'직접유입',google:'구글',kakao:'카카오',other:'기타'};

// ============================================================
// 대시보드 데이터 로드
// ============================================================
async function loadDashboard() {
  try {
    const res  = await fetch('index.php?route=api/dashboard/summary');
    const result = await res.json();
    
    if (!result.success || !result.data) {
      console.error('대시보드 데이터 로드 실패:', result);
      return;
    }
    
    const data = result.data;

    // 통계 업데이트
    $('todayVisitors').textContent  = fmtNum(data.todayVisitors || 0);
    $('visitorChange').textContent  = data.visitorChange || '-';
    $('placeRank').textContent      = (data.placeAvgRank || '-') + (data.placeAvgRank ? '위' : '');
    $('rankChange').textContent     = data.rankChange || '-';
    $('igFollowers').textContent    = fmtNum(data.instagramFollowers || 0);
    $('followerChange').textContent = data.followerChange || '-';
    $('runningBoosts').textContent  = data.runningBoosts || 0;

    // 채널 현황
    const ch    = data.todayChannels || {};
    const total = Object.values(ch).reduce((a,b)=>a+b,0) || 1;
    const sorted = Object.entries(ch).sort((a,b)=>b[1]-a[1]);
    
    if (sorted.length > 0) {
      $('channelList').innerHTML = sorted.map(([k,v])=>`
        <div style="display:flex;align-items:center;gap:10px;">
          <span style="font-size:18px;min-width:24px;">${channelEmoji[k]||'⚫'}</span>
          <div style="flex:1;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
              <span style="font-size:12px;font-weight:600;">${channelName[k]||k}</span>
              <span style="font-size:12px;color:#888;">${fmtNum(v)}명 (${Math.round(v/total*100)}%)</span>
            </div>
            <div class="progress-wrap">
              <div class="progress-fill pf-green" style="width:${Math.round(v/total*100)}%"></div>
            </div>
          </div>
        </div>`).join('');
    } else {
      $('channelList').innerHTML = '<div style="text-align:center;padding:30px;color:#888;font-size:13px;">오늘 유입 데이터가 없습니다</div>';
    }
    
    console.log('대시보드 데이터 로드 완료:', data);
  } catch(e) {
    console.error('대시보드 로드 오류:', e);
    $('channelList').innerHTML = '<div style="text-align:center;padding:30px;color:#888;font-size:13px;">데이터 로드 실패</div>';
  }
}

// ============================================================
// 최근 부스팅 작업 로드
// ============================================================
async function loadRecentTasks() {
  try {
    const res   = await fetch('index.php?route=api/place-boost/tasks');
    const result = await res.json();
    const tasks = result.data || [];
    
    const typeNames = {
      view_boost:'👁️ 조회수',
      keyword_search:'🔍 키워드',
      review_request:'⭐ 리뷰',
      smart_boost:'🤖 스마트'
    };
    
    const statusBadge = {
      running:   '<span class="badge badge-green">▶ 실행 중</span>',
      paused:    '<span class="badge badge-orange">⏸ 일시정지</span>',
      completed: '<span class="badge badge-blue">✓ 완료</span>',
      failed:    '<span class="badge badge-red">✗ 실패</span>',
    };
    
    if (tasks.length > 0) {
      $('recentTasks').innerHTML = tasks.slice(0,5).map(t => {
        const pct = t.target_count > 0 ? Math.round(t.completed_count/t.target_count*100) : 0;
        return `<tr>
          <td style="font-weight:600;">${t.place_name || '업체명 없음'}</td>
          <td>${typeNames[t.task_type]||t.task_type}</td>
          <td>${t.keyword||'-'}</td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div class="progress-wrap" style="width:80px;">
                <div class="progress-fill ${t.status==='completed'?'pf-blue':'pf-green'}" style="width:${pct}%"></div>
              </div>
              <span style="font-size:12px;font-weight:700;">${pct}%</span>
            </div>
          </td>
          <td>${statusBadge[t.status]||t.status}</td>
        </tr>`;
      }).join('');
    } else {
      $('recentTasks').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">작업 내역이 없습니다</td></tr>';
    }
  } catch(e) {
    console.error('부스팅 작업 로드 오류:', e);
    $('recentTasks').innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">데이터 로드 실패</td></tr>';
  }
}

// ============================================================
// API 연동 상태 실시간 업데이트
// ============================================================
async function refreshApiStatus() {
  try {
    const response = await fetch('index.php?route=api/dashboard/api-status');
    const result = await response.json();
    
    if (result.success) {
      console.log('API 연동 상태 업데이트:', result.data);
      
      // 연동 개수 업데이트
      const countElement = document.querySelector('.api-connected-count');
      if (countElement) {
        countElement.textContent = result.count;
      }
      
      // 각 서비스 상태 업데이트
      document.querySelectorAll('.api-status-item').forEach(item => {
        const service = item.getAttribute('data-service');
        if (result.data[service]) {
          item.classList.add('connected');
          item.classList.remove('disconnected');
        } else {
          item.classList.add('disconnected');
          item.classList.remove('connected');
        }
      });
    }
  } catch (error) {
    console.error('API 상태 업데이트 오류:', error);
  }
}

// ============================================================
// 유틸리티 함수
// ============================================================
function $(id) {
  return document.getElementById(id);
}

function fmtNum(num) {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toLocaleString();
}

// ============================================================
// localStorage 변경 감지 (설정 페이지에서 API 업데이트 시)
// ============================================================
window.addEventListener('storage', function(e) {
  if (e.key === 'api_updated') {
    console.log('API 설정이 업데이트됨, 새로고침...');
    refreshApiStatus();
  }
});

// 같은 탭에서도 감지
let lastApiCheck = sessionStorage.getItem('last_api_check') || '0';
setInterval(function() {
  const lastUpdate = localStorage.getItem('api_updated');
  
  if (lastUpdate && parseInt(lastUpdate) > parseInt(lastApiCheck)) {
    lastApiCheck = lastUpdate;
    sessionStorage.setItem('last_api_check', lastUpdate);
    console.log('API 업데이트 감지, 새로고침...');
    refreshApiStatus();
  }
}, 3000);

// ============================================================
// 페이지 로드 시 실행
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
  console.log('대시보드 초기화');
  loadDashboard();
  loadRecentTasks();
  refreshApiStatus();
  
  // 30초마다 자동 새로고침
  setInterval(loadDashboard, 30000);
  setInterval(loadRecentTasks, 60000);
});
</script>

<style>
/* API 연동 현황 스타일 */
.connection-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.api-connected-count {
  font-size: 32px;
  line-height: 1;
}

.connection-divider {
  font-size: 20px;
  opacity: 0.7;
}

.connection-total {
  font-size: 24px;
  line-height: 1;
}

.link-button {
  color: #007bff;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s;
}

.link-button:hover {
  color: #0056b3;
  text-decoration: underline;
}

.api-status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.api-status-item {
  padding: 14px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  background: #fafafa;
  transition: all 0.3s;
  cursor: default;
}

.api-status-item.connected {
  border-color: #28a745;
  background: linear-gradient(135deg, #f0fff4 0%, #e8f5e9 100%);
}

.api-status-item.disconnected {
  border-color: #e0e0e0;
  background: #fafafa;
}

.api-status-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.api-status-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.api-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.api-info {
  flex: 1;
  min-width: 0;
}

.api-name {
  font-weight: 600;
  font-size: 13px;
  margin-bottom: 3px;
}

.api-status-badge {
  font-size: 11px;
}

.status-connected {
  color: #28a745;
  font-weight: 600;
}

.status-disconnected {
  color: #999;
  font-weight: 500;
}

.api-update-time {
  font-size: 10px;
  color: #888;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(0,0,0,0.1);
}

/* 기존 스타일 유지 */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.si-red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.si-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.si-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.si-orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.si-purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

.stat-body {
  flex: 1;
  min-width: 0;
}

.slabel {
  font-size: 12px;
  color: #888;
  margin-bottom: 4px;
}

.sval {
  font-size: 24px;
  font-weight: 700;
  color: #333;
  line-height: 1.2;
}

.schange {
  font-size: 11px;
  font-weight: 600;
  margin-top: 4px;
}

.schange.up { color: #00b894; }
.schange.down { color: #e94560; }
.schange.neutral { color: #888; }

.grid-2 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.live-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #00b894;
  margin-right: 6px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.progress-wrap {
  height: 6px;
  background: #f0f0f0;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.5s;
}

.pf-green { background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%); }
.pf-blue { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); }

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .api-status-grid {
    grid-template-columns: 1fr;
  }
  
  .grid-2 {
    grid-template-columns: 1fr;
  }
}
</style>
