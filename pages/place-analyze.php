<div class="alert alert-info" style="margin-bottom:20px;">
  📡 <strong>네이버 플레이스 분석</strong> — 업체명 또는 플레이스 URL을 입력하면 경쟁사 대비 최적화 현황, 리뷰 분석, 키워드 노출 현황을 확인합니다.
</div>

<!-- 검색 입력 -->
<div class="card" style="margin-bottom:20px;">
  <div class="card-header"><div class="card-title">🔍 플레이스 분석</div></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">업체명</label>
      <input type="text" class="form-control" id="paPlaceName" placeholder="예) 강남 맛있는 식당">
    </div>
    <div class="form-group">
      <label class="form-label">대표 키워드</label>
      <input type="text" class="form-control" id="paKeyword" placeholder="예) 강남 맛집">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">네이버 플레이스 URL <span style="color:#aaa;font-weight:400;">(선택)</span></label>
      <input type="text" class="form-control" id="paUrl" placeholder="https://map.naver.com/v5/entry/place/...">
    </div>
    <div class="form-group">
      <label class="form-label">업종 카테고리</label>
      <select class="form-control" id="paCategory">
        <option value="restaurant">음식점/카페</option>
        <option value="beauty">미용/뷰티</option>
        <option value="medical">의료/병원</option>
        <option value="education">교육/학원</option>
        <option value="retail">쇼핑/소매</option>
        <option value="service">서비스업</option>
        <option value="accommodation">숙박</option>
        <option value="etc">기타</option>
      </select>
    </div>
  </div>
  <button class="btn btn-primary" onclick="analyzePlaceNaver()" id="analyzeBtn">
    🚀 플레이스 분석 시작
  </button>
</div>

<!-- 결과 영역 -->
<div class="result-section" id="paResult">

  <!-- 종합 점수 -->
  <div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
      <div class="stat-icon si-red">🏆</div>
      <div class="stat-body">
        <div class="slabel">종합 최적화 점수</div>
        <div class="sval" id="paTotalScore">-</div>
        <div class="schange" id="paTotalGrade">분석 전</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-blue">📍</div>
      <div class="stat-body">
        <div class="slabel">현재 순위 (대표 키워드)</div>
        <div class="sval" id="paRank">-</div>
        <div class="schange" id="paRankChange">-</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-green">⭐</div>
      <div class="stat-body">
        <div class="slabel">평점 / 리뷰 수</div>
        <div class="sval" id="paRating">-</div>
        <div class="schange" id="paReviewCount">-</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon si-orange">👁️</div>
      <div class="stat-body">
        <div class="slabel">월 예상 노출 수</div>
        <div class="sval" id="paViews">-</div>
        <div class="schange" id="paViewsTrend">-</div>
      </div>
    </div>
  </div>

  <div class="grid-col-3-2" style="margin-bottom:20px;">
    <!-- 항목별 최적화 점수 -->
    <div class="card">
      <div class="card-header"><div class="card-title">📊 항목별 최적화 현황</div></div>
      <div id="paScoreItems" style="display:flex;flex-direction:column;gap:14px;padding-top:4px;"></div>
    </div>

    <!-- 등급 및 액션 -->
    <div class="card">
      <div class="card-header"><div class="card-title">🎯 개선 우선순위</div></div>
      <div id="paPriorityList" style="display:flex;flex-direction:column;gap:10px;"></div>
    </div>
  </div>

  <div class="grid-col-3-2" style="margin-bottom:20px;">
    <!-- 리뷰 분석 -->
    <div class="card">
      <div class="card-header"><div class="card-title">💬 리뷰 감성 분석</div></div>
      <div style="display:flex;gap:12px;margin-bottom:16px;" id="paSentimentBar"></div>
      <div class="chart-wrap" style="height:200px;"><canvas id="paReviewChart"></canvas></div>
      <div id="paKeyReviews" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;"></div>
    </div>

    <!-- 키워드 노출 -->
    <div class="card">
      <div class="card-header"><div class="card-title">🔑 키워드 노출 현황</div></div>
      <div id="paKeywords" style="display:flex;flex-direction:column;gap:10px;"></div>
    </div>
  </div>

  <!-- 경쟁사 비교 -->
  <div class="card" style="margin-bottom:20px;">
    <div class="card-header">
      <div class="card-title">🥊 경쟁사 비교 분석</div>
      <span style="font-size:12px;color:#888;">동일 카테고리 상위 업체 기준</span>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>순위</th><th>업체명</th><th>평점</th><th>리뷰</th>
            <th>사진</th><th>최적화점수</th><th>강점</th>
          </tr>
        </thead>
        <tbody id="paCompetitorTable"></tbody>
      </table>
    </div>
  </div>

  <!-- 액션 플랜 -->
  <div class="card">
    <div class="card-header"><div class="card-title">📋 맞춤 액션 플랜</div></div>
    <div id="paActionPlan" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;"></div>
  </div>

</div><!-- /result-section -->

<script>
let paReviewChart;

async function analyzePlaceNaver() {
  const placeName = document.getElementById('paPlaceName').value.trim();
  const keyword   = document.getElementById('paKeyword').value.trim();
  const url       = document.getElementById('paUrl').value.trim();
  const category  = document.getElementById('paCategory').value;

  if (!placeName || !keyword) {
    alert('업체명과 대표 키워드를 입력해주세요.');
    return;
  }

  const btn = document.getElementById('analyzeBtn');
  btn.disabled = true;
  btn.textContent = '분석 중...';
  showLoading('네이버 플레이스 분석 중...');

  try {
    const res  = await fetch('index.php?route=api/place-analyze/analyze', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ place_name: placeName, keyword, url, category })
    });
    const json = await res.json();
    if (!json.data) { alert(json.error || '분석 실패'); return; }
    const d = json.data;

    renderPlaceAnalysis(d);
    showResult('paResult');
  } catch(e) {
    alert('분석 중 오류가 발생했습니다.');
  } finally {
    hideLoading();
    btn.disabled = false;
    btn.textContent = '🚀 플레이스 분석 시작';
  }
}

function renderPlaceAnalysis(d) {
  // 종합 점수
  document.getElementById('paTotalScore').textContent = d.totalScore + '점';
  const grade = d.totalScore >= 80 ? '🟢 우수' : d.totalScore >= 60 ? '🟡 보통' : '🔴 개선 필요';
  document.getElementById('paTotalGrade').textContent = grade;
  document.getElementById('paTotalGrade').className   = 'schange ' + (d.totalScore >= 80 ? 'up' : d.totalScore >= 60 ? '' : 'down');

  // 순위
  document.getElementById('paRank').textContent       = d.rank + '위';
  document.getElementById('paRankChange').textContent = d.rankChange >= 0 ? '▲ ' + d.rankChange + ' 상승' : '▼ ' + Math.abs(d.rankChange) + ' 하락';
  document.getElementById('paRankChange').className   = 'schange ' + (d.rankChange >= 0 ? 'up' : 'down');

  // 평점/리뷰
  document.getElementById('paRating').textContent      = '⭐ ' + d.rating;
  document.getElementById('paReviewCount').textContent = fmtNum(d.reviewCount) + '개 리뷰';

  // 노출 수
  document.getElementById('paViews').textContent      = fmt(d.monthlyViews);
  document.getElementById('paViewsTrend').textContent = d.viewsTrend >= 0 ? '▲ ' + d.viewsTrend + '%' : '▼ ' + Math.abs(d.viewsTrend) + '%';
  document.getElementById('paViewsTrend').className   = 'schange ' + (d.viewsTrend >= 0 ? 'up' : 'down');

  // 항목별 점수
  const scoreItems = [
    { label: '업체 정보 완성도', val: d.scores.info,     icon: '📋', desc: '영업시간, 전화번호, 주소 등' },
    { label: '사진 최적화',      val: d.scores.photos,   icon: '📸', desc: '사진 수 및 품질' },
    { label: '리뷰 관리',        val: d.scores.reviews,  icon: '💬', desc: '리뷰 수·평점·답변율' },
    { label: '키워드 최적화',    val: d.scores.keywords, icon: '🔑', desc: '설명·태그 키워드 포함도' },
    { label: '활동 지수',        val: d.scores.activity, icon: '📈', desc: '업데이트 빈도, 포스트 수' },
    { label: '메뉴/서비스 정보', val: d.scores.menu,     icon: '🍽️', desc: '메뉴판·가격 정보 등록 여부' },
  ];
  document.getElementById('paScoreItems').innerHTML = scoreItems.map(s => `
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
        <span style="font-size:13px;font-weight:600;">${s.icon} ${s.label}</span>
        <span style="font-size:13px;font-weight:700;color:${s.val>=80?'#00b894':s.val>=60?'#f5a623':'#e94560'};">${s.val}점</span>
      </div>
      <div class="progress-wrap">
        <div class="progress-fill ${s.val>=80?'pf-green':s.val>=60?'pf-blue':'pf-red'}" style="width:${s.val}%;"></div>
      </div>
      <div style="font-size:11px;color:#aaa;margin-top:3px;">${s.desc}</div>
    </div>
  `).join('');

  // 개선 우선순위
  const sorted = [...scoreItems].sort((a,b) => a.val - b.val).slice(0, 5);
  document.getElementById('paPriorityList').innerHTML = sorted.map((s,i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:10px;background:${i===0?'#fff0f3':'#f9f9fc'};border-radius:10px;border-left:3px solid ${i===0?'#e94560':'#ddd'};">
      <div style="width:22px;height:22px;background:${i===0?'#e94560':'#ccc'};border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0;">${i+1}</div>
      <div>
        <div style="font-size:13px;font-weight:700;">${s.icon} ${s.label}</div>
        <div style="font-size:11px;color:#888;">${s.desc} · 현재 ${s.val}점</div>
      </div>
    </div>
  `).join('');

  // 리뷰 감성 분석 바
  const { positive, neutral, negative } = d.sentiment;
  document.getElementById('paSentimentBar').innerHTML = `
    <div style="flex:1;text-align:center;background:#e0f7f0;border-radius:10px;padding:12px;">
      <div style="font-size:22px;">😊</div>
      <div style="font-size:18px;font-weight:800;color:#00b894;">${positive}%</div>
      <div style="font-size:11px;color:#888;">긍정</div>
    </div>
    <div style="flex:1;text-align:center;background:#f5f5f5;border-radius:10px;padding:12px;">
      <div style="font-size:22px;">😐</div>
      <div style="font-size:18px;font-weight:800;color:#888;">${neutral}%</div>
      <div style="font-size:11px;color:#888;">중립</div>
    </div>
    <div style="flex:1;text-align:center;background:#fff0f3;border-radius:10px;padding:12px;">
      <div style="font-size:22px;">😞</div>
      <div style="font-size:18px;font-weight:800;color:#e94560;">${negative}%</div>
      <div style="font-size:11px;color:#888;">부정</div>
    </div>
  `;

  // 리뷰 월별 차트
  if (paReviewChart) paReviewChart.destroy();
  paReviewChart = new Chart(document.getElementById('paReviewChart'), {
    type: 'bar',
    data: {
      labels: d.reviewHistory.map(r => r.month),
      datasets: [{
        label: '리뷰 수',
        data: d.reviewHistory.map(r => r.count),
        backgroundColor: 'rgba(233,69,96,0.15)',
        borderColor: '#e94560',
        borderWidth: 2,
        borderRadius: 6,
      }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{color:'#f0f0f5'}},x:{grid:{display:false}}} }
  });

  // 주요 키워드 리뷰
  document.getElementById('paKeyReviews').innerHTML = (d.keyReviews || []).map(r => `
    <div style="padding:10px;background:#f9f9fc;border-radius:8px;font-size:12.5px;line-height:1.6;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span style="color:#f5a623;">⭐ ${r.rating}</span>
        <span style="color:#aaa;">${r.date}</span>
      </div>
      <div style="color:#444;">${r.text}</div>
    </div>
  `).join('');

  // 키워드 노출 현황
  document.getElementById('paKeywords').innerHTML = (d.keywordRanks || []).map(k => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#f9f9fc;border-radius:10px;">
      <div>
        <div style="font-size:13px;font-weight:600;">${k.keyword}</div>
        <div style="font-size:11px;color:#aaa;">월 ${fmtNum(k.searchVolume)}회 검색</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:18px;font-weight:800;color:${k.rank<=3?'#e94560':k.rank<=10?'#f5a623':'#888'};">${k.rank}위</div>
        <div style="font-size:11px;color:${k.change>0?'#00b894':k.change<0?'#e94560':'#888'};">${k.change>0?'▲'+k.change:k.change<0?'▼'+Math.abs(k.change):'-'}</div>
      </div>
    </div>
  `).join('');

  // 경쟁사 테이블
  document.getElementById('paCompetitorTable').innerHTML = (d.competitors || []).map(c => `
    <tr style="${c.isTarget?'background:#fff8f0;font-weight:700;border-left:3px solid #e94560;':''}">
      <td><span class="rank-badge ${c.rank<=3?'rb-'+c.rank:''}">${c.rank}</span></td>
      <td>${c.isTarget?'⭐ ':''}${c.name}</td>
      <td>⭐ ${c.rating}</td>
      <td>${fmtNum(c.reviewCount)}</td>
      <td>${c.photoCount}장</td>
      <td>
        <div style="display:flex;align-items:center;gap:6px;">
          <div class="progress-wrap" style="width:50px;display:inline-block;">
            <div class="progress-fill pf-green" style="width:${c.score}%;"></div>
          </div>
          <span style="font-size:12px;">${c.score}점</span>
        </div>
      </td>
      <td style="font-size:12px;color:#666;">${(c.strengths||[]).join(', ')}</td>
    </tr>
  `).join('');

  // 액션 플랜
  const plans = [
    { step:'1단계', period:'이번 주', color:'#e94560', icon:'🔥', title:'즉시 개선', items: d.actionPlan.immediate },
    { step:'2단계', period:'이번 달', color:'#f5a623', icon:'📅', title:'단기 개선', items: d.actionPlan.shortTerm },
    { step:'3단계', period:'3개월',  color:'#00b894', icon:'📈', title:'장기 전략', items: d.actionPlan.longTerm },
  ];
  document.getElementById('paActionPlan').innerHTML = plans.map(p => `
    <div style="border:2px solid ${p.color}22;border-radius:14px;padding:18px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
        <span style="font-size:20px;">${p.icon}</span>
        <div>
          <div style="font-size:13px;font-weight:800;color:${p.color};">${p.step} — ${p.title}</div>
          <div style="font-size:11px;color:#aaa;">${p.period} 내 완료 목표</div>
        </div>
      </div>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:7px;">
        ${(p.items||[]).map(item=>`<li style="display:flex;align-items:flex-start;gap:7px;font-size:12.5px;"><span style="color:${p.color};margin-top:1px;">✓</span><span>${item}</span></li>`).join('')}
      </ul>
    </div>
  `).join('');
}
</script>
