<div class="card">
  <div class="card-header"><div class="card-title">📸 인스타그램 분석</div></div>
  <div style="display:flex;gap:10px;">
    <input type="text" class="form-control" id="igUsername" placeholder="인스타그램 아이디 (@ 제외)">
    <button class="btn btn-primary" style="white-space:nowrap;" onclick="analyzeIg()">🔍 분석</button>
  </div>
</div>

<div class="result-section" id="igResult">
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon si-pink">👥</div><div class="stat-body"><div class="slabel">팔로워</div><div class="sval" id="igFollowers">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red">❤️</div><div class="stat-body"><div class="slabel">평균 좋아요</div><div class="sval" id="igLikes">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">📊</div><div class="stat-body"><div class="slabel">참여율</div><div class="sval" id="igEngagement">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange">📈</div><div class="stat-body"><div class="slabel">성장률</div><div class="sval" id="igGrowth">-</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">⏰ 최적 게시 시간</div><div id="igBestTime" class="badge badge-green">-</div></div>
    <div style="margin-top:8px;font-size:13px;color:#666;">이 시간대에 게시하면 참여율이 가장 높습니다.</div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🏷️ 인기 해시태그</div></div>
    <div id="igHashtags" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
  </div>
</div>

<script>
async function analyzeIg() {
  const uname = $('igUsername').value.trim();
  if (!uname) { alert('아이디를 입력하세요.'); return; }
  showLoading('인스타그램 분석 중...');
  try {
    const res  = await fetch('index.php?route=api/instagram/analyze',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:uname})});
    const data = (await res.json()).data;
    showResult('igResult');
    $('igFollowers').textContent  = fmt(data.followers);
    $('igLikes').textContent      = fmt(data.avgLikes);
    $('igEngagement').textContent = data.engagementRate + '%';
    $('igGrowth').textContent     = data.growthRate + '%';
    $('igBestTime').textContent   = data.bestPostTime;
    $('igHashtags').innerHTML     = data.topHashtags.map(h=>`<span class="badge badge-blue">${h.tag} <span style="font-size:10px;opacity:0.7;">❤️${fmt(h.avgLikes)}</span></span>`).join('');
  } finally { hideLoading(); }
}
</script>
