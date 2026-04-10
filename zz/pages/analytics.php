<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-blue">👁️</div>
    <div class="stat-body"><div class="slabel">오늘 방문자</div><div class="sval" id="todayVisit">-</div><div class="schange up" id="todayNew">-</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">🆕</div>
    <div class="stat-body"><div class="slabel">신규 방문자</div><div class="sval" id="newVisit">-</div><div class="schange neutral">오늘</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-orange">⚡</div>
    <div class="stat-body"><div class="slabel">실시간 방문자</div><div class="sval" id="realtimeCount">-</div><div class="schange neutral"><span class="live-dot"></span>Live</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-purple">⏱️</div>
    <div class="stat-body"><div class="slabel">평균 체류시간</div><div class="sval" id="avgDuration">-</div><div class="schange neutral">초</div></div>
  </div>
</div>

<div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
  <div class="tabs" style="margin-bottom:0;">
    <button class="tab-btn active" onclick="loadAnalytics(7,this)">7일</button>
    <button class="tab-btn" onclick="loadAnalytics(30,this)">30일</button>
    <button class="tab-btn" onclick="loadAnalytics(90,this)">90일</button>
  </div>
</div>

<div class="grid-col-3-2">
  <div class="card">
    <div class="card-header"><div class="card-title">📈 방문자 추이</div></div>
    <div class="chart-wrap"><canvas id="visitorChart"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">📱 디바이스 비율</div></div>
    <div class="chart-sm"><canvas id="deviceChart"></canvas></div>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">🌐 유입 채널 분석</div></div>
  <div id="channelDetail" style="display:flex;flex-direction:column;gap:10px;"></div>
</div>

<!-- UTM 생성기 -->
<div class="card">
  <div class="card-header"><div class="card-title">🔗 UTM 링크 생성기</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">랜딩 URL</label>
      <input type="url" class="form-control" id="utmUrl" placeholder="https://yoursite.com">
    </div>
    <div class="form-group">
      <label class="form-label">소스 (utm_source)</label>
      <input type="text" class="form-control" id="utmSource" placeholder="naver, instagram, kakao">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">미디움 (utm_medium)</label>
      <input type="text" class="form-control" id="utmMedium" placeholder="cpc, social, email">
    </div>
    <div class="form-group">
      <label class="form-label">캠페인 (utm_campaign)</label>
      <input type="text" class="form-control" id="utmCampaign" placeholder="spring_sale, brand_awareness">
    </div>
  </div>
  <button class="btn btn-primary" onclick="generateUtm()">🔗 UTM 링크 생성</button>
  <div id="utmResult" style="display:none;margin-top:14px;">
    <div id="utmOutput" style="background:#f0f2f5;border-radius:9px;padding:12px;font-size:12px;font-family:monospace;word-break:break-all;border:1px solid #e0e0e0;cursor:pointer;" onclick="copyUtm()"></div>
    <div style="font-size:11px;color:#888;margin-top:6px;text-align:center;">클릭하면 복사됩니다</div>
  </div>
</div>

<script>
let visitorChartInst, deviceChartInst;

async function loadAnalytics(days=30, btn=null) {
  if (btn) { document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); }
  showLoading('분석 데이터 로딩 중...');
  try {
    const res  = await fetch('index.php?route=api/analytics/overview?days='+days);
    const json = await res.json();
    const data = json.data || {};

    // 통계 카드
    const visitors = data.visitors || [];
    const todayV   = visitors[visitors.length-1] || {};
    $('todayVisit').textContent  = fmtNum(todayV.total_visits || 0);
    $('newVisit').textContent    = fmtNum(todayV.new_visitors || 0);
    $('todayNew').textContent    = '신규 방문자 ' + fmtNum(todayV.new_visitors || 0) + '명';

    const channels = data.channels || [];
    const avgDur   = channels.reduce((s,c)=>s+(+c.avg_duration||0),0) / (channels.length||1);
    $('avgDuration').textContent = fmtSec(Math.round(avgDur));

    // 방문자 추이 차트
    const labels = visitors.map(v => v.date.slice(5));
    const vals   = visitors.map(v => v.total_visits);
    if (visitorChartInst) visitorChartInst.destroy();
    visitorChartInst = new Chart($('visitorChart'), {
      type:'line',
      data:{labels,datasets:[{label:'방문자',data:vals,borderColor:'#e94560',backgroundColor:'rgba(233,69,96,0.08)',borderWidth:2,fill:true,tension:0.4,pointRadius:3}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,grid:{color:'#f0f0f5'}},x:{grid:{display:false}}}}
    });

    // 디바이스 차트
    const devices   = data.devices || [];
    const dLabels   = devices.map(d=>d.device_type==='mobile'?'모바일':'데스크탑');
    const dVals     = devices.map(d=>d.visits);
    if (deviceChartInst) deviceChartInst.destroy();
    deviceChartInst = new Chart($('deviceChart'), {
      type:'doughnut',
      data:{labels:dLabels,datasets:[{data:dVals,backgroundColor:['#e94560','#0066ff','#00b894'],borderWidth:0}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
    });

    // 채널 상세
    const channelEmoji={naver:'🟢',instagram:'📸',direct:'🔵',google:'🔍',kakao:'💛',other:'⚫'};
    const channelName ={naver:'네이버',instagram:'인스타그램',direct:'직접유입',google:'구글',kakao:'카카오',other:'기타'};
    const total = channels.reduce((s,c)=>s+(+c.visits),0)||1;
    $('channelDetail').innerHTML = channels.map(ch=>`
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-size:20px;min-width:28px;">${channelEmoji[ch.channel]||'⚫'}</span>
        <div style="flex:1;">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:13px;font-weight:600;">${channelName[ch.channel]||ch.channel}</span>
            <span style="font-size:12px;color:#888;">${fmtNum(ch.visits)}명 · 평균 ${fmtSec(Math.round(ch.avg_duration||0))}</span>
          </div>
          <div class="progress-wrap">
            <div class="progress-fill pf-green" style="width:${Math.round(ch.visits/total*100)}%"></div>
          </div>
        </div>
        <span style="font-size:12px;font-weight:700;min-width:36px;text-align:right;">${Math.round(ch.visits/total*100)}%</span>
      </div>`).join('') || '<div style="color:#888;text-align:center;padding:20px;">데이터가 없습니다</div>';
  } finally { hideLoading(); }
}

async function loadRealtime() {
  try {
    const res  = await fetch('index.php?route=api/analytics/realtime');
    const data = await res.json();
    $('realtimeCount').textContent = data.active || 0;
  } catch(e) {}
}

function generateUtm() {
  const url      = $('utmUrl').value.trim();
  const source   = $('utmSource').value.trim();
  const medium   = $('utmMedium').value.trim();
  const campaign = $('utmCampaign').value.trim();
  if (!url||!source) { alert('URL과 소스를 입력하세요.'); return; }
  const params = new URLSearchParams();
  if (source)   params.set('utm_source',source);
  if (medium)   params.set('utm_medium',medium);
  if (campaign) params.set('utm_campaign',campaign);
  const full = url + (url.includes('?')?'&':'?') + params.toString();
  $('utmOutput').textContent = full;
  $('utmResult').style.display = 'block';
}

function copyUtm() {
  navigator.clipboard.writeText($('utmOutput').textContent).then(()=>alert('✅ 복사되었습니다!')).catch(()=>{});
}

loadAnalytics(7);
loadRealtime();
setInterval(loadRealtime, 30000);
</script>
