<div class="card">
  <div class="card-header"><div class="card-title">📍 플레이스 광고 분석</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">키워드</label>
      <input type="text" class="form-control" id="paKeyword" placeholder="예) 강남 맛집">
    </div>
    <div class="form-group">
      <label class="form-label">지역</label>
      <input type="text" class="form-control" id="paRegion" placeholder="예) 강남구" value="강남구">
    </div>
  </div>
  <button class="btn btn-primary" onclick="analyzePlaceAds()">🔍 광고 분석</button>
</div>

<div class="result-section" id="paResult">
  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card"><div class="stat-icon si-orange">💰</div><div class="stat-body"><div class="slabel">예상 CPC</div><div class="sval" id="paCpc">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-blue">🔍</div><div class="stat-body"><div class="slabel">월 검색량</div><div class="sval" id="paSearchVol">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red">⚔️</div><div class="stat-body"><div class="slabel">경쟁 강도</div><div class="sval" id="paCompete">-</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🏆 경쟁업체 광고 현황</div></div>
    <div class="table-wrapper">
      <table>
        <thead><tr><th>순위</th><th>업체명</th><th>광고여부</th><th>평점</th><th>리뷰수</th><th>예상 CPC</th></tr></thead>
        <tbody id="paCompetitors"></tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">💡 광고 전략 팁</div></div>
    <div id="paTips" style="display:flex;flex-direction:column;gap:8px;"></div>
  </div>
</div>

<script>
async function analyzePlaceAds() {
  const kw     = $('paKeyword').value.trim();
  const region = $('paRegion').value.trim();
  if (!kw) { alert('키워드를 입력하세요.'); return; }
  showLoading('광고 분석 중...');
  try {
    const res  = await fetch('index.php?route=api/place-ads/analyze',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({keyword:kw,region})});
    const data = (await res.json()).data;
    showResult('paResult');
    $('paCpc').textContent        = fmtNum(data.estimatedCpc) + '원';
    $('paSearchVol').textContent  = fmt(data.monthlySearchVolume);
    $('paCompete').textContent    = data.competition;
    $('paCompetitors').innerHTML  = data.competitors.map(c=>`<tr>
      <td><span class="rank-badge ${c.rank<=3?'rb-'+c.rank:''}">${c.rank}</span></td>
      <td style="font-weight:600;">${c.name}</td>
      <td>${c.isAd?'<span class="badge badge-orange">광고</span>':'<span class="badge badge-gray">일반</span>'}</td>
      <td>⭐ ${c.rating}</td>
      <td>${fmtNum(c.reviews)}</td>
      <td>${fmtNum(c.cpc)}원</td>
    </tr>`).join('');
    $('paTips').innerHTML = data.tips.map(t=>`<div style="display:flex;gap:8px;padding:10px;background:#f9f9fc;border-radius:9px;font-size:13px;"><span>💡</span><span>${t}</span></div>`).join('');
  } finally { hideLoading(); }
}
</script>
