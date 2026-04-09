<div class="card">
  <div class="card-header"><div class="card-title">📈 블로그 순위 추적</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">추적 키워드</label>
      <input type="text" class="form-control" id="brKeyword" placeholder="예) 강남 맛집">
    </div>
    <div class="form-group">
      <label class="form-label">블로그 URL</label>
      <input type="text" class="form-control" id="brUrl" placeholder="https://blog.naver.com/yourId">
    </div>
  </div>
  <button class="btn btn-primary" onclick="trackBlogRank()">🔍 순위 추적</button>
</div>

<div class="result-section" id="brResult">
  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-card"><div class="stat-icon si-red">🏆</div><div class="stat-body"><div class="slabel">현재 순위</div><div class="sval" id="brCurrentRank">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">⭐</div><div class="stat-body"><div class="slabel">최고 순위</div><div class="sval" id="brBestRank">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">🔍</div><div class="stat-body"><div class="slabel">월 검색량</div><div class="sval" id="brSearchVol">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange">⚔️</div><div class="stat-body"><div class="slabel">경쟁 강도</div><div class="sval" id="brCompete">-</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">📈 30일 순위 추이</div></div>
    <div class="chart-wrap"><canvas id="brChart"></canvas></div>
  </div>
</div>

<script>
let brChart;
async function trackBlogRank() {
  const kw  = $('brKeyword').value.trim();
  const url = $('brUrl').value.trim();
  if (!kw||!url) { alert('키워드와 URL을 입력하세요.'); return; }
  showLoading('블로그 순위 추적 중...');
  try {
    const res  = await fetch('index.php?route=api/blog-rank/track',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({keyword:kw,blogUrl:url})});
    const data = (await res.json()).data;
    showResult('brResult');
    $('brCurrentRank').textContent = data.currentRank + '위';
    $('brBestRank').textContent    = data.bestRank + '위';
    $('brSearchVol').textContent   = fmt(data.totalSearchVolume);
    $('brCompete').textContent     = data.competition;
    if (brChart) brChart.destroy();
    brChart = new Chart($('brChart'),{type:'line',data:{labels:data.history.map(h=>h.date),datasets:[{label:'순위',data:data.history.map(h=>h.rank),borderColor:'#0066ff',backgroundColor:'rgba(0,102,255,0.08)',borderWidth:2,fill:true,tension:0.4}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{reverse:true,min:1,grid:{color:'#f0f0f5'}},x:{grid:{display:false}}},plugins:{legend:{display:false}}}});
  } finally { hideLoading(); }
}
</script>
