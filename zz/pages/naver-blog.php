<div class="card">
  <div class="card-header"><div class="card-title">📝 네이버 블로그 분석</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">키워드 또는 블로그 URL</label>
      <input type="text" class="form-control" id="nbKeyword" placeholder="예) 강남 맛집 또는 블로그 URL">
    </div>
    <div class="form-group" style="display:flex;align-items:flex-end;">
      <button class="btn btn-primary btn-block" onclick="analyzeNaverBlog()">🔍 분석 시작</button>
    </div>
  </div>
</div>

<div class="result-section" id="nbResult">
  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-card"><div class="stat-icon si-blue">📊</div><div class="stat-body"><div class="slabel">검색 결과 수</div><div class="sval" id="nbTotal">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-green">👁️</div><div class="stat-body"><div class="slabel">평균 조회수</div><div class="sval" id="nbAvgViews">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-orange">📈</div><div class="stat-body"><div class="slabel">경쟁 강도</div><div class="sval" id="nbCompete">-</div></div></div>
    <div class="stat-card"><div class="stat-icon si-red">💡</div><div class="stat-body"><div class="slabel">기회 지수</div><div class="sval" id="nbOpportunity">-</div></div></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🏆 상위 노출 포스트</div></div>
    <div class="table-wrapper">
      <table>
        <thead><tr><th>순위</th><th>제목</th><th>작성자</th><th>조회수</th><th>댓글</th><th>점수</th></tr></thead>
        <tbody id="nbPosts"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
async function analyzeNaverBlog() {
  const kw = $('nbKeyword').value.trim();
  if (!kw) { alert('키워드를 입력하세요.'); return; }
  showLoading('블로그 분석 중...');
  try {
    const res  = await fetch('index.php?route=api/naver-blog/analyze',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({keyword:kw})});
    const data = (await res.json()).data;
    showResult('nbResult');
    $('nbTotal').textContent       = fmt(data.totalResults);
    $('nbAvgViews').textContent    = fmt(data.avgViews);
    $('nbCompete').textContent     = data.competition;
    $('nbOpportunity').textContent = data.opportunity + '점';
    $('nbPosts').innerHTML = data.posts.map(p=>`<tr>
      <td><span class="rank-badge ${p.rank<=3?'rb-'+p.rank:''}">${p.rank}</span></td>
      <td style="font-size:13px;max-width:240px;">${p.isTop?'🔥 ':''} ${p.title}</td>
      <td style="font-size:12px;color:#888;">${p.author}</td>
      <td>${fmtNum(p.views)}</td>
      <td>${fmtNum(p.comments)}</td>
      <td><span class="badge badge-blue">${p.score}점</span></td>
    </tr>`).join('');
  } finally { hideLoading(); }
}
</script>
