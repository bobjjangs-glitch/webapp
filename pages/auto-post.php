<div class="tabs">
  <button class="tab-btn active" onclick="switchTab('ig',this)">📸 인스타그램 자동화</button>
  <button class="tab-btn" onclick="switchTab('blog',this)">📝 블로그 자동화</button>
  <button class="tab-btn" onclick="switchTab('ad',this)">📣 광고 자동화</button>
</div>

<!-- 인스타그램 탭 -->
<div id="tab-ig">
  <div class="grid-col-2-1">
    <div class="card">
      <div class="card-header"><div class="card-title">📸 인스타그램 포스팅 예약</div></div>
      <div class="form-group">
        <label class="form-label">게시물 유형</label>
        <select class="form-control" id="igPostType">
          <option value="image">🖼️ 이미지</option>
          <option value="carousel">🎠 카루셀</option>
          <option value="reel">🎬 릴스</option>
          <option value="story">📱 스토리</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">캡션 (최대 2,200자)</label>
        <textarea class="form-control" id="igCaption" style="height:120px;" placeholder="게시물 내용을 입력하세요...&#10;&#10;감성 있는 문구로 팔로워의 참여를 유도하세요!"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">해시태그</label>
        <div style="display:flex;gap:8px;margin-bottom:6px;">
          <input type="text" class="form-control" id="igHashtags" placeholder="#해시태그 #마케팅 #브랜딩">
          <button class="btn btn-secondary" style="white-space:nowrap;" onclick="suggestHashtags()">💡 AI 추천</button>
        </div>
        <div id="hashtagSuggestions" style="display:none;margin-top:8px;"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">예약 날짜</label>
          <input type="date" class="form-control" id="igDate">
        </div>
        <div class="form-group">
          <label class="form-label">예약 시간</label>
          <input type="time" class="form-control" id="igTime" value="09:00">
        </div>
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-secondary" style="flex:1;" onclick="saveIgDraft()">💾 임시저장</button>
        <button class="btn btn-primary" style="flex:2;" onclick="scheduleIgPost()">📅 예약 등록</button>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">⏰ 최적 포스팅 시간</div></div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php
        $bestTimes = [
          ['time'=>'오전 9시','score'=>78,'label'=>'출근 시간'],
          ['time'=>'오후 12시','score'=>92,'label'=>'점심 시간 🔥'],
          ['time'=>'오후 6시','score'=>85,'label'=>'퇴근 시간'],
          ['time'=>'오후 9시','score'=>88,'label'=>'저녁 여가'],
        ];
        foreach($bestTimes as $t): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px;background:#f9f9fc;border-radius:10px;">
          <div style="font-size:13px;font-weight:700;min-width:70px;"><?= $t['time'] ?></div>
          <div style="flex:1;">
            <div class="progress-wrap">
              <div class="progress-fill pf-green" style="width:<?= $t['score'] ?>%"></div>
            </div>
          </div>
          <div style="font-size:12px;color:#888;min-width:80px;"><?= $t['label'] ?></div>
          <div style="font-size:13px;font-weight:700;color:#00b894;"><?= $t['score'] ?>점</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">📋 인스타그램 예약 목록</div>
      <button class="btn btn-secondary btn-sm" onclick="loadIgSchedules()">🔄 새로고침</button>
    </div>
    <div class="table-wrapper">
      <table>
        <thead><tr><th>유형</th><th>캡션</th><th>해시태그</th><th>예약시간</th><th>상태</th></tr></thead>
        <tbody id="igScheduleTable"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- 블로그 탭 -->
<div id="tab-blog" style="display:none;">
  <div class="card">
    <div class="card-header"><div class="card-title">📝 블로그 포스팅 예약</div></div>
    <div class="form-group">
      <label class="form-label">포스팅 제목 *</label>
      <input type="text" class="form-control" id="blogTitle" placeholder="예) 강남 맛집 베스트 10 - 직접 다녀온 솔직 후기">
    </div>
    <div class="form-group">
      <label class="form-label">본문 내용</label>
      <textarea class="form-control" style="height:180px;" id="blogContent" placeholder="블로그 본문을 작성하세요..."></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">SEO 키워드</label>
        <input type="text" class="form-control" id="blogKeywords" placeholder="강남 맛집, 강남 점심 (콤마 구분)">
      </div>
      <div class="form-group">
        <label class="form-label">카테고리</label>
        <input type="text" class="form-control" id="blogCategory" placeholder="맛집, 여행, 리뷰...">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">예약 날짜/시간</label>
        <input type="datetime-local" class="form-control" id="blogScheduledAt">
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end;">
        <div style="display:flex;gap:8px;width:100%;">
          <button class="btn btn-secondary" style="flex:1;" onclick="saveBlogDraft()">💾 임시저장</button>
          <button class="btn btn-primary" style="flex:2;" onclick="scheduleBlogPost()">📅 예약 등록</button>
        </div>
      </div>
    </div>
  </div>

  <!-- AI 글쓰기 -->
  <div class="card">
    <div class="card-header"><div class="card-title">🤖 AI 블로그 초안 생성</div></div>
    <div class="alert alert-info">ℹ️ OpenAI API 키 등록 후 AI 자동 글쓰기 기능이 활성화됩니다. <a href="/settings" style="color:#0066cc;font-weight:700;">API 설정 →</a></div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">키워드/주제</label>
        <input type="text" class="form-control" id="aiKeyword" placeholder="강남 맛집 추천">
      </div>
      <div class="form-group">
        <label class="form-label">글쓰기 스타일</label>
        <select class="form-control" id="aiStyle">
          <option>정보 제공형</option><option>체험 후기형</option><option>리뷰형</option><option>가이드형</option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary" onclick="generateAiBlog()">✨ AI 초안 생성</button>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">📋 블로그 예약 목록</div>
      <button class="btn btn-secondary btn-sm" onclick="loadBlogSchedules()">🔄 새로고침</button>
    </div>
    <div class="table-wrapper">
      <table>
        <thead><tr><th>제목</th><th>키워드</th><th>카테고리</th><th>예약시간</th><th>상태</th><th>AI</th></tr></thead>
        <tbody id="blogScheduleTable"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- 광고 자동화 탭 -->
<div id="tab-ad" style="display:none;">
  <div class="alert alert-warning">⚠️ 광고 자동화 기능은 각 플랫폼 API 연동이 필요합니다. <a href="/settings" style="color:#8a6d00;font-weight:700;">API 설정 →</a></div>
  <div class="grid-2">
    <?php
    $adCards = [
      ['icon'=>'📍','title'=>'네이버 플레이스 광고','color'=>'#00b894','desc'=>'플레이스 광고 예산 자동 최적화 및 입찰 관리'],
      ['icon'=>'📸','title'=>'인스타그램 광고','color'=>'#e94560','desc'=>'팔로워 증가 및 게시물 홍보 자동화'],
      ['icon'=>'🔍','title'=>'네이버 검색광고','color'=>'#0066ff','desc'=>'키워드 자동 입찰 및 예산 관리'],
      ['icon'=>'💛','title'=>'카카오 채널','color'=>'#f5a623','desc'=>'친구 추가 및 메시지 자동화'],
    ];
    foreach($adCards as $c): ?>
    <div class="card">
      <div class="card-header">
        <div class="card-title" style="font-size:15px;"><?= $c['icon'] ?> <?= $c['title'] ?></div>
        <div class="toggle" onclick="this.classList.toggle('on')"></div>
      </div>
      <p style="font-size:13px;color:#888;margin-bottom:14px;"><?= $c['desc'] ?></p>
      <div class="form-group">
        <label class="form-label">월 예산 한도 (원)</label>
        <input type="number" class="form-control" placeholder="500000" step="10000">
      </div>
      <button class="btn btn-secondary btn-block" onclick="alert('API 키를 먼저 등록해주세요.')">API 연동 필요</button>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
function switchTab(tab, btn) {
  ['ig','blog','ad'].forEach(t => { $('tab-'+t).style.display = t===tab?'block':'none'; });
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  if (tab==='ig')   loadIgSchedules();
  if (tab==='blog') loadBlogSchedules();
}

async function suggestHashtags() {
  const kw  = $('igCaption').value.trim().split(' ')[0] || '마케팅';
  const res  = await fetch('index.php?route=api/auto-post/hashtag-suggestions?keyword='+encodeURIComponent(kw));
  const data = await res.json();
  const tags = data.data || [];
  const el   = $('hashtagSuggestions');
  el.style.display = 'block';
  el.innerHTML = `<div style="display:flex;flex-wrap:wrap;gap:6px;">
    ${tags.map(t=>`<span class="badge badge-blue" style="cursor:pointer;" onclick="addHashtag('${t}')">${t}</span>`).join('')}
  </div>`;
}

function addHashtag(tag) {
  const el  = $('igHashtags');
  const cur = el.value.trim();
  el.value  = cur ? cur + ' ' + tag : tag;
}

async function scheduleIgPost() {
  const date = $('igDate').value;
  const time = $('igTime').value;
  if (!date || !time) { alert('예약 날짜와 시간을 선택하세요.'); return; }
  showLoading('예약 등록 중...');
  try {
    const res = await fetch('index.php?route=api/auto-post/instagram/schedules', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({caption:$('igCaption').value,hashtags:$('igHashtags').value,post_type:$('igPostType').value,scheduled_at:date+'T'+time+':00'})
    });
    const data = await res.json();
    if (data.success) { alert('✅ '+data.message); $('igCaption').value=''; $('igHashtags').value=''; loadIgSchedules(); }
    else alert('❌ '+(data.error||'오류 발생'));
  } finally { hideLoading(); }
}

async function saveIgDraft() {
  const res  = await fetch('index.php?route=api/auto-post/instagram/schedules',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({caption:$('igCaption').value,hashtags:$('igHashtags').value,post_type:$('igPostType').value})});
  const data = await res.json();
  if (data.success) { alert('💾 임시저장 되었습니다.'); loadIgSchedules(); }
}

async function loadIgSchedules() {
  const res   = await fetch('index.php?route=api/auto-post/instagram/schedules');
  const rows  = (await res.json()).data || [];
  const statusBadge = {draft:'<span class="badge badge-gray">임시저장</span>',scheduled:'<span class="badge badge-blue">예약됨</span>',published:'<span class="badge badge-green">발행됨</span>',failed:'<span class="badge badge-red">실패</span>'};
  $('igScheduleTable').innerHTML = rows.map(r=>`<tr>
    <td>${r.post_type||'image'}</td>
    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.caption||'-'}</td>
    <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#888;">${r.hashtags||'-'}</td>
    <td style="font-size:12px;">${r.scheduled_at?new Date(r.scheduled_at).toLocaleString('ko-KR'):'-'}</td>
    <td>${statusBadge[r.status]||r.status}</td>
  </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">예약된 포스팅이 없습니다</td></tr>';
}

async function scheduleBlogPost() {
  const title = $('blogTitle').value.trim();
  if (!title) { alert('제목을 입력하세요.'); return; }
  showLoading('블로그 예약 등록 중...');
  try {
    const res  = await fetch('index.php?route=api/auto-post/blog/schedules',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title,content:$('blogContent').value,keywords:$('blogKeywords').value,category:$('blogCategory').value,scheduled_at:$('blogScheduledAt').value||null})});
    const data = await res.json();
    if (data.success) { alert('✅ '+data.message); loadBlogSchedules(); }
    else alert('❌ '+(data.error||'오류 발생'));
  } finally { hideLoading(); }
}

async function saveBlogDraft() {
  const title = $('blogTitle').value.trim();
  if (!title) { alert('제목을 입력하세요.'); return; }
  const res  = await fetch('index.php?route=api/auto-post/blog/schedules',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({title,content:$('blogContent').value,keywords:$('blogKeywords').value,category:$('blogCategory').value})});
  const data = await res.json();
  if (data.success) { alert('💾 '+data.message); loadBlogSchedules(); }
}

async function loadBlogSchedules() {
  const res   = await fetch('index.php?route=api/auto-post/blog/schedules');
  const rows  = (await res.json()).data || [];
  const statusBadge = {draft:'<span class="badge badge-gray">임시저장</span>',scheduled:'<span class="badge badge-blue">예약됨</span>',published:'<span class="badge badge-green">발행됨</span>',failed:'<span class="badge badge-red">실패</span>'};
  $('blogScheduleTable').innerHTML = rows.map(r=>`<tr>
    <td style="font-weight:600;">${r.title}</td>
    <td style="font-size:12px;color:#888;">${r.keywords||'-'}</td>
    <td style="font-size:12px;">${r.category||'-'}</td>
    <td style="font-size:12px;">${r.scheduled_at?new Date(r.scheduled_at).toLocaleString('ko-KR'):'-'}</td>
    <td>${statusBadge[r.status]||r.status}</td>
    <td>${r.is_ai_generated?'<span class="badge badge-purple">AI</span>':'-'}</td>
  </tr>`).join('') || '<tr><td colspan="6" style="text-align:center;color:#888;padding:20px;">예약된 포스팅이 없습니다</td></tr>';
}

function generateAiBlog() {
  alert('OpenAI API 키를 등록하면 AI 자동 글쓰기 기능이 활성화됩니다.\n설정 → API 설정에서 등록하세요.');
}

loadIgSchedules();
</script>
