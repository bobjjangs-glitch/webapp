<?php // pages/naver-blog.php ?>
<style>
.nb-tabs { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
.nb-tab  { padding:10px 24px; border-radius:8px; border:2px solid var(--border,#2a2a3e);
           background:transparent; color:var(--text-muted,#888); cursor:pointer;
           font-size:14px; font-weight:600; transition:.2s; }
.nb-tab.active { background:var(--primary,#6c63ff); border-color:var(--primary,#6c63ff); color:#fff; }
.nb-mode { display:none; }
.nb-mode.active { display:block; }
.nb-stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
@media(max-width:768px){ .nb-stat-grid{ grid-template-columns:repeat(2,1fr); } }
.nb-stat-card { background:var(--card-bg,#1a1a2e); border:1px solid var(--border,#2a2a3e);
                border-radius:12px; padding:20px; text-align:center; }
.nb-stat-card .s-icon  { font-size:28px; margin-bottom:8px; }
.nb-stat-card .s-label { font-size:12px; color:var(--text-muted,#888); margin-bottom:6px; }
.nb-stat-card .s-value { font-size:22px; font-weight:700; color:var(--text,#fff); line-height:1.2; }
.nb-trend-bar { display:flex; align-items:flex-end; gap:2px; height:40px; margin-top:8px; }
.nb-trend-bar span { flex:1; background:var(--primary,#6c63ff); border-radius:2px 2px 0 0;
                     opacity:.7; min-height:2px; transition:.3s; }
.nb-blog-card { background:var(--card-bg,#1a1a2e); border:1px solid var(--border,#2a2a3e);
                border-radius:16px; padding:24px; margin-bottom:16px; }
.nb-blog-header { display:flex; align-items:center; gap:16px; margin-bottom:20px; flex-wrap:wrap; }
.nb-blog-thumb-default { width:64px; height:64px; border-radius:12px; flex-shrink:0;
                          background:linear-gradient(135deg,#6c63ff,#a855f7);
                          display:flex; align-items:center; justify-content:center; font-size:28px; }
.nb-blog-thumb { width:64px; height:64px; border-radius:12px; object-fit:cover; flex-shrink:0; }
.nb-blog-info { flex:1; min-width:0; }
.nb-blog-info h3   { font-size:18px; font-weight:700; margin-bottom:4px; }
.nb-blog-info .meta{ font-size:13px; color:var(--text-muted,#888); margin-bottom:2px; }
.nb-score-circle   { text-align:center; flex-shrink:0; }
.nb-score-num { font-size:40px; font-weight:900; line-height:1;
                background:linear-gradient(135deg,#6c63ff,#a855f7);
                -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.nb-score-label { font-size:11px; color:var(--text-muted,#888); margin-top:4px; }
.nb-detail-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
@media(max-width:600px){ .nb-detail-grid{ grid-template-columns:repeat(2,1fr); } }
.nb-detail-item { background:rgba(108,99,255,.08); border-radius:10px; padding:14px; text-align:center; }
.nb-detail-item .d-label { font-size:11px; color:var(--text-muted,#888); margin-bottom:4px; }
.nb-detail-item .d-value { font-size:16px; font-weight:700; color:var(--text,#fff); }
.nb-info-box { background:rgba(108,99,255,.08); border:1px solid rgba(108,99,255,.2);
               border-radius:10px; padding:12px 16px; font-size:12px; color:#a29bfe;
               margin-top:16px; }
.badge-green  { background:rgba(0,184,148,.15);  color:#00b894; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-orange { background:rgba(253,203,110,.15);color:#fdcb6e; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-red    { background:rgba(255,118,117,.15); color:#ff7675; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-blue   { background:rgba(108,99,255,.15);  color:#a29bfe; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.badge-gray   { background:rgba(150,150,150,.15); color:#b2bec3; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.rank-badge { display:inline-flex; align-items:center; justify-content:center;
              width:28px; height:28px; border-radius:50%; font-size:12px; font-weight:700;
              background:var(--border,#2a2a3e); color:#fff; }
.rank-top-1 { background:linear-gradient(135deg,#f9ca24,#f0932b); }
.rank-top-2 { background:linear-gradient(135deg,#b2bec3,#636e72); }
.rank-top-3 { background:linear-gradient(135deg,#e17055,#d63031); }
</style>

<div class="page-content">
  <div class="nb-tabs">
    <button class="nb-tab active" onclick="nbSwitchTab('keyword')" id="nb-tab-keyword">🔍 키워드 분석</button>
    <button class="nb-tab"       onclick="nbSwitchTab('blog')"    id="nb-tab-blog">📋 블로그 URL 분석</button>
  </div>

  <!-- ══ 키워드 분석 탭 ══ -->
  <div id="nb-mode-keyword" class="nb-mode active">
    <div class="card" style="margin-bottom:24px;">
      <div class="card-header"><h3 class="card-title">🔍 네이버 블로그 키워드 분석</h3></div>
      <div class="card-body">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
          <input id="nbKeyword" type="text" class="form-control"
                 placeholder="분석할 키워드 입력 (예: 강남 맛집, 다이어트 방법)"
                 style="flex:1;min-width:220px;"
                 onkeydown="if(event.key==='Enter') analyzeNaverBlog()"/>
          <button id="nbAnalyzeBtn" class="btn btn-primary" onclick="analyzeNaverBlog()">🔍 분석 시작</button>
        </div>
        <div style="margin-top:8px;font-size:12px;color:#888;">
          💡 네이버 블로그 검색 API + 데이터랩 트렌드 API로 실제 데이터를 분석합니다.
        </div>
      </div>
    </div>
    <div id="nbError" class="alert alert-danger" style="display:none;margin-bottom:16px;"></div>
    <div id="nbResult" style="display:none;">
      <div class="nb-stat-grid">
        <div class="nb-stat-card">
          <div class="s-icon">📊</div>
          <div class="s-label">총 검색 결과 수</div>
          <div class="s-value" id="nbTotal">-</div>
        </div>
        <div class="nb-stat-card">
          <div class="s-icon">📈</div>
          <div class="s-label">경쟁 강도</div>
          <div class="s-value" id="nbCompete">-</div>
        </div>
        <div class="nb-stat-card">
          <div class="s-icon">💡</div>
          <div class="s-label">기회 지수</div>
          <div class="s-value" id="nbOpportunity">-</div>
        </div>
        <div class="nb-stat-card">
          <div class="s-icon">🔥</div>
          <div class="s-label">30일 검색 트렌드</div>
          <div class="s-value" id="nbTrend">-</div>
          <div class="nb-trend-bar" id="nbTrendBar"></div>
        </div>
      </div>
      <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
          <h3 class="card-title" id="nbKeywordLabel">🏆 상위 노출 포스트</h3>
          <span style="font-size:13px;color:#888;">
            크레딧 잔액: <strong id="nbCreditsLeft" style="color:#00b894;">-</strong>원
          </span>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width:55px;">순위</th>
                  <th>제목</th>
                  <th style="width:110px;">블로그명</th>
                  <th style="width:90px;">작성일</th>
                  <th style="width:90px;">추정조회수</th>
                  <th style="width:65px;">점수</th>
                </tr>
              </thead>
              <tbody id="nbPosts">
                <tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">분석 결과가 여기에 표시됩니다.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="nb-info-box">
          ℹ️ <strong>실제 데이터:</strong> 제목·블로그명·작성일·검색결과수·트렌드는 네이버 API 실시간 데이터입니다.
          조회수는 네이버가 공개하지 않아 순위·최신도·트렌드를 기반으로 추정한 값입니다.
        </div>
      </div>
    </div>
  </div>

  <!-- ══ 블로그 URL 분석 탭 ══ -->
  <div id="nb-mode-blog" class="nb-mode">
    <div class="card" style="margin-bottom:24px;">
      <div class="card-header"><h3 class="card-title">📋 네이버 블로그 분석</h3></div>
      <div class="card-body">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
          <input id="nbBlogUrl" type="text" class="form-control"
                 placeholder="블로그 URL 또는 ID (예: blog.naver.com/아이디 또는 아이디만)"
                 style="flex:1;min-width:220px;"
                 onkeydown="if(event.key==='Enter') analyzeNaverBlogUrl()"/>
          <button id="nbBlogBtn" class="btn btn-primary" onclick="analyzeNaverBlogUrl()">📋 분석 시작</button>
        </div>
      </div>
    </div>
    <div id="nbBlogError" class="alert alert-danger" style="display:none;margin-bottom:16px;"></div>
    <div id="nbBlogResult" style="display:none;">
      <div class="nb-blog-card">
        <div class="nb-blog-header">
          <div class="nb-blog-thumb-default" id="nbBlogThumbWrap">📝</div>
          <div class="nb-blog-info">
            <h3 id="nbBlogName">-</h3>
            <div class="meta" id="nbBlogDesc"></div>
            <div class="meta" id="nbBlogActivity"></div>
          </div>
          <div class="nb-score-circle">
            <div class="nb-score-num" id="nbBlogScore">-</div>
            <div class="nb-score-label">블로그 점수</div>
          </div>
        </div>
        <div class="nb-detail-grid">
          <div class="nb-detail-item"><div class="d-label">📝 총 포스트</div><div class="d-value" id="nbBlogPosts">-</div></div>
          <div class="nb-detail-item"><div class="d-label">💬 이웃 수</div><div class="d-value" id="nbBlogNeighbor">-</div></div>
          <div class="nb-detail-item"><div class="d-label">📅 블로그 나이</div><div class="d-value" id="nbBlogAge">-</div></div>
          <div class="nb-detail-item"><div class="d-label">🔥 최근 30일 포스팅</div><div class="d-value" id="nbBlogRecent">-</div></div>
          <div class="nb-detail-item"><div class="d-label">⚡ 활동성</div><div class="d-value" id="nbBlogActLevel">-</div></div>
          <div class="nb-detail-item"><div class="d-label">🔗 바로가기</div>
            <div class="d-value"><a id="nbBlogLink" href="#" target="_blank" style="color:#a29bfe;font-size:13px;">방문하기 →</a></div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><h3 class="card-title">📄 최근 포스트 (RSS 실시간)</h3></div>
        <div class="card-body" style="padding:0;">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th style="width:50px;">번호</th>
                  <th>제목</th>
                  <th style="width:90px;">작성일</th>
                </tr>
              </thead>
              <tbody id="nbRecentPosts">
                <tr><td colspan="3" style="text-align:center;padding:32px;color:#888;">로딩 중...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="nb-info-box">
          ℹ️ <strong>실제 데이터:</strong> 블로그명·포스트 목록·작성일은 RSS 피드 실시간 데이터입니다.
          일평균 방문자·카테고리 랭킹은 네이버 공개 API 미제공으로 표시되지 않습니다.
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';

var COMPETE = {
  low:       {label:'낮음',    cls:'badge-green'},
  medium:    {label:'보통',    cls:'badge-orange'},
  high:      {label:'높음',    cls:'badge-red'},
  very_high: {label:'매우 높음',cls:'badge-red'},
};

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(n){
  if(typeof fmtNum==='function') return fmtNum(n);
  n=parseInt(n)||0;
  if(n>=1000000) return (n/1000000).toFixed(1)+'M';
  if(n>=1000) return (n/1000).toFixed(1)+'K';
  return n.toLocaleString();
}
function apiGet(p){ return typeof apiUrl==='function' ? apiUrl(p) : 'index.php?route='+p; }
function postApi(path, data){
  var url = apiGet(path);
  if(typeof axios!=='undefined') return axios.post(url,data).then(function(r){ return r.data; });
  return fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}).then(function(r){ return r.json(); });
}
function extractBlogId(s){
  s = s.trim();
  var m = s.match(/blog\.naver\.com\/([a-zA-Z0-9_\-]+)/);
  if(m) return m[1];
  if(/^[a-zA-Z0-9_\-]+$/.test(s)) return s;
  return '';
}

window.nbSwitchTab = function(mode){
  document.getElementById('nb-tab-keyword').classList.toggle('active', mode==='keyword');
  document.getElementById('nb-tab-blog').classList.toggle('active',    mode==='blog');
  document.getElementById('nb-mode-keyword').classList.toggle('active', mode==='keyword');
  document.getElementById('nb-mode-blog').classList.toggle('active',    mode==='blog');
};

// ── 키워드 분석 ──────────────────────────────────────────────
window.analyzeNaverBlog = function(){
  var kw = document.getElementById('nbKeyword').value.trim();
  if(!kw){ alert('키워드를 입력하세요.'); return; }

  // URL 입력 감지
  if(kw.indexOf('blog.naver.com')!==-1 || kw.indexOf('naver.com')!==-1){
    var bid = extractBlogId(kw);
    if(bid){ nbSwitchTab('blog'); document.getElementById('nbBlogUrl').value=bid; analyzeNaverBlogUrl(); return; }
  }

  var errEl=document.getElementById('nbError'), resEl=document.getElementById('nbResult');
  var btn=document.getElementById('nbAnalyzeBtn');
  errEl.style.display=resEl.style.display='none';
  btn.disabled=true; btn.textContent='⏳ 분석 중...';
  if(typeof showLoading==='function') showLoading('네이버 API 분석 중...');

  postApi('api/naver-blog/analyze',{keyword:kw})
  .then(function(json){
    if(typeof hideLoading==='function') hideLoading();
    btn.disabled=false; btn.textContent='🔍 분석 시작';

    if(!json.success && json.is_blog_url && json.blog_id){
      nbSwitchTab('blog'); document.getElementById('nbBlogUrl').value=json.blog_id; analyzeNaverBlogUrl(); return;
    }
    if(!json.success){
      var msg=json.error||'오류가 발생했습니다.';
      if(json.redirect) msg+=' <a href="'+json.redirect+'" style="color:#e94560;font-weight:700;">설정 이동 →</a>';
      errEl.innerHTML='⚠️ '+msg; errEl.style.display='block'; return;
    }

    var d=json.data||{};
    document.getElementById('nbTotal').textContent       = fmt(d.totalResults||0)+'건';
    document.getElementById('nbOpportunity').textContent = (d.opportunity||0)+'점';
    document.getElementById('nbKeywordLabel').textContent= '🏆 "'+esc(d.keyword||kw)+'" 분석 결과';

    var c=COMPETE[d.competition]||{label:d.competition||'-',cls:'badge-gray'};
    document.getElementById('nbCompete').innerHTML='<span class="badge '+c.cls+'">'+c.label+'</span>';

    // 트렌드 표시
    var tr=d.trend||{};
    var trendEl=document.getElementById('nbTrend');
    var trendBar=document.getElementById('nbTrendBar');
    if(tr.avg!==undefined){
      trendEl.textContent = (tr.avg||0).toFixed(1)+' / 100';
      if(tr.data && tr.data.length>0){
        var maxR=Math.max.apply(null, tr.data.map(function(x){ return x.ratio||0; }));
        trendBar.innerHTML = tr.data.slice(-14).map(function(x){
          var h = maxR>0 ? Math.max(4, Math.round((x.ratio/maxR)*40)) : 4;
          return '<span style="height:'+h+'px;" title="'+x.period+': '+x.ratio+'"></span>';
        }).join('');
      }
    } else {
      trendEl.textContent='N/A';
    }

    if(json.credits_remaining!==undefined){
      var cl=document.getElementById('nbCreditsLeft'); if(cl) cl.textContent=fmt(json.credits_remaining);
    }

    var posts=d.posts||[];
    document.getElementById('nbPosts').innerHTML = posts.length
      ? posts.map(function(p){
          var rc=p.rank<=3?'rank-top-'+p.rank:'';
          return '<tr>'+
            '<td><span class="rank-badge '+rc+'">'+p.rank+'</span></td>'+
            '<td style="max-width:240px;">'+(p.isTop?'🔥 ':'')+
              '<a href="'+esc(p.link)+'" target="_blank" style="color:var(--primary,#a29bfe);">'+esc(p.title)+'</a>'+
              (p.description?'<div style="font-size:11px;color:#666;margin-top:2px;">'+esc(p.description)+'</div>':'')+
            '</td>'+
            '<td style="font-size:12px;color:#888;">'+esc(p.author)+'</td>'+
            '<td style="font-size:12px;color:#888;">'+esc(p.date)+'</td>'+
            '<td style="font-size:12px;color:#aaa;">~'+fmt(p.views)+'<span style="font-size:10px;color:#666;"> (추정)</span></td>'+
            '<td><span class="badge badge-blue">'+p.score+'점</span></td>'+
          '</tr>';
        }).join('')
      : '<tr><td colspan="6" style="text-align:center;padding:32px;color:#888;">검색 결과가 없습니다.</td></tr>';

    resEl.style.display='block';
    resEl.scrollIntoView({behavior:'smooth',block:'start'});
  })
  .catch(function(e){
    if(typeof hideLoading==='function') hideLoading();
    btn.disabled=false; btn.textContent='🔍 분석 시작';
    var msg='서버 오류가 발생했습니다.';
    if(e&&e.response&&e.response.data){
      var d2=e.response.data; msg=d2.error||d2.message||msg;
      if(d2.redirect) msg+=' <a href="'+d2.redirect+'" style="color:#e94560;font-weight:700;">설정 이동 →</a>';
      if(e.response.status===402) msg='💳 '+msg+' <a href="index.php?route=credits" style="color:#00b894;">충전 →</a>';
    }
    errEl.innerHTML='⚠️ '+msg; errEl.style.display='block';
  });
};

// ── 블로그 URL 분석 ──────────────────────────────────────────
window.analyzeNaverBlogUrl = function(){
  var input=document.getElementById('nbBlogUrl').value.trim();
  if(!input){ alert('블로그 URL 또는 ID를 입력하세요.'); return; }
  var blogId=extractBlogId(input);
  if(!blogId){ alert('올바른 블로그 URL 또는 ID를 입력하세요.\n예: blog.naver.com/아이디'); return; }

  var errEl=document.getElementById('nbBlogError'), resEl=document.getElementById('nbBlogResult');
  var btn=document.getElementById('nbBlogBtn');
  errEl.style.display=resEl.style.display='none';
  btn.disabled=true; btn.textContent='⏳ 분석 중...';
  if(typeof showLoading==='function') showLoading('블로그 분석 중...');

  postApi('api/naver-blog/analyze-url',{blogId:blogId})
  .then(function(json){
    if(typeof hideLoading==='function') hideLoading();
    btn.disabled=false; btn.textContent='📋 분석 시작';
    if(!json.success){ errEl.innerHTML='⚠️ '+esc(json.error||'오류'); errEl.style.display='block'; return; }

    var d=json.data||{};
    document.getElementById('nbBlogName').textContent    = d.blogName||blogId;
    document.getElementById('nbBlogScore').textContent   = d.score||'-';
    document.getElementById('nbBlogPosts').textContent   = d.totalPosts ? fmt(d.totalPosts)+'개' : '정보 없음';
    document.getElementById('nbBlogNeighbor').textContent= d.neighbors  ? fmt(d.neighbors)+'명'  : '정보 없음';
    document.getElementById('nbBlogAge').textContent     = d.blogAge||'정보 없음';
    document.getElementById('nbBlogRecent').textContent  = (d.recentPostCount||0)+'개';
    document.getElementById('nbBlogActLevel').textContent= d.activityLevel||'-';
    document.getElementById('nbBlogLink').href           = 'https://blog.naver.com/'+blogId;

    var descEl=document.getElementById('nbBlogDesc');
    descEl.textContent=d.description||'';
    descEl.style.display=d.description?'block':'none';

    var actEl=document.getElementById('nbBlogActivity');
    actEl.textContent='분석 시각: '+(d.analyzed_at||'-');

    // 썸네일
    var wrap=document.getElementById('nbBlogThumbWrap');
    if(d.thumbnailUrl){
      wrap.outerHTML='<img src="'+esc(d.thumbnailUrl)+'" class="nb-blog-thumb" id="nbBlogThumbWrap" '+
        'onerror="this.outerHTML=\'<div class=\\\'nb-blog-thumb-default\\\' id=\\\'nbBlogThumbWrap\\\'>📝</div>\'">';
    }

    // 최근 포스트
    var posts=d.recentPosts||[];
    document.getElementById('nbRecentPosts').innerHTML = posts.length
      ? posts.map(function(p,i){
          return '<tr>'+
            '<td><span class="rank-badge">'+(i+1)+'</span></td>'+
            '<td><a href="'+esc(p.link)+'" target="_blank" style="color:var(--primary,#a29bfe);">'+esc(p.title)+'</a>'+
              (p.summary?'<div style="font-size:11px;color:#666;margin-top:2px;">'+esc(p.summary)+'</div>':'')+
            '</td>'+
            '<td style="font-size:12px;color:#888;white-space:nowrap;">'+esc(p.date||'-')+'</td>'+
          '</tr>';
        }).join('')
      : '<tr><td colspan="3" style="text-align:center;padding:32px;color:#888;">'+
        (json.data.rssStatus!==200 ? 'RSS 피드를 불러올 수 없습니다. 비공개 블로그이거나 존재하지 않는 ID입니다.' : '포스트가 없습니다.')+
        '<br><a href="https://blog.naver.com/'+blogId+'" target="_blank" style="color:#a29bfe;">블로그 직접 방문 →</a></td></tr>';

    resEl.style.display='block';
    resEl.scrollIntoView({behavior:'smooth',block:'start'});
  })
  .catch(function(e){
    if(typeof hideLoading==='function') hideLoading();
    btn.disabled=false; btn.textContent='📋 분석 시작';
    var msg='서버 오류가 발생했습니다.';
    if(e&&e.response&&e.response.data) msg=e.response.data.error||msg;
    errEl.innerHTML='⚠️ '+msg; errEl.style.display='block';
  });
};

})();
</script>
