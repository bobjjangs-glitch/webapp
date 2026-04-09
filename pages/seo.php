<div class="card">
  <div class="card-header"><div class="card-title">🔍 SEO 분석</div></div>
  <div style="display:flex;gap:10px;">
    <input type="url" class="form-control" id="seoUrl" placeholder="https://example.com">
    <button class="btn btn-primary" style="white-space:nowrap;" onclick="analyzeSeo()">🔍 분석</button>
  </div>
</div>

<div class="result-section" id="seoResult">
  <div class="grid-2">
    <div class="card" style="text-align:center;">
      <div class="card-header"><div class="card-title">📊 SEO 점수</div></div>
      <div id="seoScoreCircle" class="score-circle average" style="margin:10px auto;">
        <span id="seoScore">-</span>
        <span class="score-label">점</span>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">⚡ 성능 지표</div></div>
      <div id="seoPerf"></div>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🔧 개선 권고사항</div></div>
    <div id="seoRecs"></div>
  </div>
</div>

<script>
async function analyzeSeo() {
  const url = $('seoUrl').value.trim();
  if (!url) { alert('URL을 입력하세요.'); return; }
  showLoading('SEO 분석 중...');
  try {
    const res  = await fetch('index.php?route=api/seo/analyze',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url})});
    const data = (await res.json()).data;
    showResult('seoResult');
    const sc = data.score;
    const cls = sc>=80?'excellent':sc>=65?'good':sc>=50?'average':'poor';
    $('seoScoreCircle').className = 'score-circle '+cls;
    $('seoScore').textContent = sc;
    $('seoPerf').innerHTML = Object.entries(data.performance).map(([k,v])=>`<div class="info-row"><span class="info-label">${k.toUpperCase()}</span><span class="info-val">${v}s</span></div>`).join('');
    const priorityColor = {high:'#e94560',medium:'#f5a623',low:'#00b894'};
    const priorityLabel = {high:'높음',medium:'보통',low:'낮음'};
    $('seoRecs').innerHTML = data.recommendations.map(r=>`<div style="display:flex;gap:12px;padding:12px;background:#f9f9fc;border-radius:10px;margin-bottom:8px;">
      <span class="badge badge-${r.priority==='high'?'red':r.priority==='medium'?'orange':'green'}" style="flex-shrink:0;height:fit-content;">${priorityLabel[r.priority]}</span>
      <div><div style="font-size:13px;font-weight:700;margin-bottom:3px;">${r.item}</div><div style="font-size:12px;color:#888;">${r.detail}</div></div>
    </div>`).join('');
  } finally { hideLoading(); }
}
</script>
