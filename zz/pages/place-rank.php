<div class="card">
  <div class="card-header"><div class="card-title">🏆 플레이스 순위 추적</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">검색 키워드</label>
      <input type="text" class="form-control" id="prKeyword" placeholder="예) 강남 맛집">
    </div>
    <div class="form-group">
      <label class="form-label">업체명</label>
      <input type="text" class="form-control" id="prPlace" placeholder="예) 맛있는 식당">
    </div>
  </div>
  <button class="btn btn-primary" onclick="trackPlaceRank()">🔍 순위 추적 시작</button>
</div>

<div class="result-section" id="prResult">
  <div class="grid-2">
    <div class="card">
      <div class="card-header"><div class="card-title">📊 현재 순위</div></div>
      <div style="text-align:center;padding:20px;">
        <div style="font-size:60px;font-weight:800;color:#e94560;" id="prCurrentRank">-</div>
        <div style="font-size:14px;color:#888;margin-top:4px;" id="prRankInfo">-</div>
        <div style="margin-top:16px;display:flex;justify-content:center;gap:20px;">
          <div style="text-align:center;"><div style="font-size:11px;color:#888;">이전 순위</div><div style="font-size:20px;font-weight:700;" id="prPrevRank">-</div></div>
          <div style="text-align:center;"><div style="font-size:11px;color:#888;">전체 플레이스</div><div style="font-size:20px;font-weight:700;" id="prTotal">-</div></div>
          <div style="text-align:center;"><div style="font-size:11px;color:#888;">최적화 점수</div><div style="font-size:20px;font-weight:700;color:#00b894;" id="prScore">-</div></div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">💡 개선 팁</div></div>
      <div id="prTips" style="display:flex;flex-direction:column;gap:8px;"></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">📈 30일 순위 추이</div></div>
    <div class="chart-wrap"><canvas id="prHistoryChart"></canvas></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🥊 경쟁업체 분석</div></div>
    <div class="table-wrapper">
      <table>
        <thead><tr><th>순위</th><th>업체명</th><th>평점</th><th>리뷰수</th><th>점수</th></tr></thead>
        <tbody id="prCompetitors"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
let prChart;
async function trackPlaceRank() {
  const keyword = $('prKeyword').value.trim();
  const place   = $('prPlace').value.trim();
  if (!keyword||!place) { alert('키워드와 업체명을 입력하세요.'); return; }
  showLoading('순위 추적 중...');
  try {
    const res  = await fetch('index.php?route=api/place-rank/track',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({keyword,placeName:place})});
    const data = (await res.json()).data;
    showResult('prResult');
    $('prCurrentRank').textContent = data.currentRank + '위';
    $('prRankInfo').textContent    = `"${keyword}" 검색 · 전체 ${fmtNum(data.totalPlaces)}개 중`;
    $('prPrevRank').textContent    = data.previousRank + '위';
    $('prTotal').textContent       = fmtNum(data.totalPlaces) + '개';
    $('prScore').textContent       = data.optimizationScore + '점';
    $('prTips').innerHTML = data.tips.map(t=>`<div style="display:flex;align-items:flex-start;gap:8px;padding:8px;background:#f9f9fc;border-radius:8px;font-size:13px;"><span>💡</span><span>${t}</span></div>`).join('');
    if (prChart) prChart.destroy();
    prChart = new Chart($('prHistoryChart'),{type:'line',data:{labels:data.history.map(h=>h.date),datasets:[{label:'순위',data:data.history.map(h=>h.rank),borderColor:'#e94560',backgroundColor:'rgba(233,69,96,0.08)',borderWidth:2,fill:true,tension:0.4}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{reverse:true,min:1,grid:{color:'#f0f0f5'}},x:{grid:{display:false}}},plugins:{legend:{display:false}}}});
    $('prCompetitors').innerHTML = data.competitors.map(c=>`<tr style="${c.isTarget?'background:#fff8f0;font-weight:700;':''}">
      <td><span class="rank-badge ${c.rank<=3?'rb-'+c.rank:''}">${c.rank}</span></td>
      <td>${c.isTarget?'⭐ ':''} ${c.name}</td>
      <td>⭐ ${c.rating}</td>
      <td>${fmtNum(c.reviewCount)}</td>
      <td><div class="progress-wrap" style="width:60px;display:inline-block;"><div class="progress-fill pf-green" style="width:${c.score}%"></div></div> ${c.score}점</td>
    </tr>`).join('');
  } finally { hideLoading(); }
}
</script>
