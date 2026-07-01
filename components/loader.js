// components/loader.js — TIRETOP 헤더/푸터 완전판
(function () {
  "use strict";

  /* ══════════════════════════════════════════
     1. 공통 스타일 주입
  ══════════════════════════════════════════ */
  function injectStyles() {
    if (document.getElementById("tt-loader-style")) return;
    const s = document.createElement("style");
    s.id = "tt-loader-style";
    s.textContent = `
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700;900&display=swap');
:root{
  /* index.html 기준으로 통일된 디자인 토큰 */
  --primary:#e02020;--primary-dark:#c01010;--primary-light:#fff0f0;
  --dark:#111827;
  --gray1:#374151;--gray2:#4b5563;--gray3:#6b7280;--gray4:#9ca3af;
  --gray5:#e5e7eb;--gray6:#f3f4f6;--gray7:#f9fafb;
  --accent:#f59e0b;--green:#22c55e;--white:#fff;--bg:#f3f4f6;
  --shadow-sm:0 1px 4px rgba(0,0,0,.08);
  --shadow:0 2px 12px rgba(0,0,0,.07);
  --shadow-md:0 4px 20px rgba(0,0,0,.12);
  --radius-sm:6px;--radius-md:12px;--radius-lg:16px;
  --header-h:64px;
  --max-w:1280px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Noto Sans KR',sans-serif;color:var(--dark);background:var(--bg);}
a{text-decoration:none;color:inherit;}

/* 앱 배너 (index.html top-announce 스타일과 통일) */
.tt-app-banner{
  background:var(--primary);color:#fff;
  text-align:center;padding:9px 48px;
  font-size:13px;font-weight:700;
  position:relative;display:block;
}
.tt-app-banner-badge{
  display:inline;background:rgba(0,0,0,.2);color:#fff;font-size:11px;font-weight:900;
  padding:2px 8px;border-radius:3px;margin-right:6px;
}
.tt-app-banner-text{color:#fff;}
.tt-app-banner-text b{color:#fff;font-weight:800;}
.tt-app-banner-close{
  position:absolute;right:16px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:rgba(255,255,255,.8);font-size:18px;cursor:pointer;
  padding:4px 8px;line-height:1;transition:color .15s;
}
.tt-app-banner-close:hover{color:#fff;}

/* 헤더 sticky — index.html hd-sticky와 통일 */
.tt-header-sticky{
  position:sticky;top:0;z-index:9000;background:var(--white);
  box-shadow:0 2px 8px rgba(0,0,0,.08);
}

/* 유틸 바 — index.html top-util과 통일 */
.tt-util-bar{
  border-bottom:1px solid var(--gray5);
  height:34px;display:flex;align-items:center;
  background:var(--white);
}
.tt-util-inner{
  max-width:var(--max-w);margin:0 auto;padding:0 24px;width:100%;
  display:flex;align-items:center;justify-content:flex-end;gap:0;
}
.tt-util-link{
  font-size:12px;color:var(--gray3);
  padding:0 12px;border-left:1px solid var(--gray5);
  line-height:34px;
  transition:color .15s;white-space:nowrap;text-decoration:none;
}
.tt-util-link:first-child{border-left:none;}
.tt-util-link:hover{color:var(--primary);}
.tt-util-sep{width:1px;height:10px;background:var(--gray5);flex-shrink:0;}

/* 헤더 메인 행 */
.tt-header-row{
  max-width:1280px;margin:0 auto;padding:0 24px;
  height:var(--header-h);display:flex;align-items:center;gap:0;
}

/* 로고 — index.html hd-logo와 통일 */
.tt-logo{
  display:flex;align-items:center;gap:8px;
  font-size:22px;font-weight:900;color:var(--dark);
  text-decoration:none;flex-shrink:0;margin-right:40px;
}
.tt-logo-icon{
  width:32px;height:32px;background:var(--dark);
  border-radius:50%;display:flex;align-items:center;justify-content:center;
  font-size:16px;line-height:1;
}
.tt-logo-wordmark{
  font-size:22px;font-weight:900;color:var(--dark);
  letter-spacing:-0.5px;line-height:1;
}
.tt-logo-wordmark span{color:var(--primary);}

/* 네비 — index.html hd-gnb와 통일 */
.tt-nav{display:flex;align-items:center;gap:0;flex:0 0 auto;margin-right:auto;}
.tt-nav-item{
  display:block;padding:0 18px;height:var(--header-h);line-height:var(--header-h);
  font-size:15px;font-weight:700;color:var(--gray3);
  border-bottom:3px solid transparent;
  text-decoration:none;cursor:pointer;
  border-top:none;border-left:none;border-right:none;
  background:none;white-space:nowrap;
  transition:all .15s;
}
.tt-nav-item:hover,.tt-nav-item.active{color:var(--dark);border-bottom-color:var(--primary);}
.tt-nav-hot{
  position:absolute;top:12px;right:2px;
  background:var(--primary);color:#fff;
  font-size:7px;font-weight:900;
  padding:1px 4px;border-radius:3px;line-height:1.4;
}

/* 타이어 드롭다운 — index.html gnb-tire-wrap과 통일 */
.tt-nav-tire-wrap{position:relative;display:flex;align-items:center;}
.tt-nav-tire-wrap > .tt-nav-item{
  color:var(--dark);border-bottom-color:var(--primary);
}
.tt-nav-tire-drop{
  display:none;position:absolute;top:100%;left:50%;transform:translateX(-50%);
  background:var(--white);border-radius:12px;
  box-shadow:0 8px 32px rgba(0,0,0,.14);border:1px solid var(--gray5);
  padding:8px 0;min-width:180px;z-index:9500;
}
.tt-nav-tire-wrap:hover .tt-nav-tire-drop{display:block;}
.tt-nav-tire-drop a{
  display:flex;align-items:center;gap:10px;
  padding:11px 20px;font-size:14px;font-weight:600;color:var(--gray1);
  text-decoration:none;transition:background .12s,color .12s;
  white-space:nowrap;border-bottom:none !important;
  line-height:1.2;height:auto;
}
.tt-nav-tire-drop a:hover{background:var(--gray7);color:var(--primary);}
.tt-nav-tire-drop .drop-icon{font-size:17px;width:22px;text-align:center;flex-shrink:0;}
.tt-nav-tire-divider{height:1px;background:var(--gray5);margin:6px 10px;}

/* 검색창 — index.html hd-search와 통일 */
.tt-header-search{
  flex:1;display:flex;align-items:center;margin:0 24px;max-width:400px;
}
.tt-header-search-inner{
  display:flex;align-items:center;width:100%;
  background:var(--bg);border:1.5px solid var(--gray5);
  border-radius:28px;padding:0 6px 0 18px;transition:all .2s;
}
.tt-header-search-inner:focus-within{
  border-color:var(--primary);background:var(--white);
  box-shadow:0 0 0 3px rgba(224,32,32,.1);
}
.tt-header-search input{
  flex:1;border:none;outline:none;background:transparent;
  font-size:13px;color:var(--dark);padding:10px 0;min-width:0;
  font-family:inherit;
}
.tt-header-search input::placeholder{color:var(--gray4);}
.tt-search-submit{
  width:34px;height:34px;border-radius:50%;
  background:var(--primary);color:#fff;border:none;cursor:pointer;
  font-size:14px;display:flex;align-items:center;justify-content:center;
  flex-shrink:0;transition:background .2s;
}
.tt-search-submit:hover{background:var(--primary-dark);}

/* 헤더 우측 버튼들 — index.html hd-icons와 통일 */
.tt-header-right{
  display:flex;align-items:center;gap:2px;flex-shrink:0;margin-left:auto;
}
.tt-hdr-btn{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:2px;padding:6px 10px;border-radius:var(--radius-md);
  cursor:pointer;transition:background .15s;min-width:52px;
  border:none;background:none;position:relative;color:var(--gray3);
  text-decoration:none;
}
.tt-hdr-btn:hover{background:var(--bg);}
.tt-hdr-btn-icon{font-size:22px;line-height:1;}
.tt-hdr-btn-label{font-size:10px;font-weight:600;color:var(--gray3);white-space:nowrap;}
.tt-cart-cnt{
  position:absolute;top:3px;right:6px;min-width:16px;height:16px;
  padding:0 4px;
  border-radius:8px;background:var(--primary);color:#fff;
  font-size:9px;font-weight:900;
  display:flex;align-items:center;justify-content:center;
  border:2px solid var(--white);
}

/* 로그인 드롭다운 */
.tt-user-wrap{position:relative;}
.tt-user-trigger{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  padding:7px 10px;border:none;background:none;cursor:pointer;
  border-radius:var(--radius-sm);transition:background .15s;
}
.tt-user-trigger:hover,.tt-user-trigger.open{background:var(--gray6);}
.tt-user-av{
  width:26px;height:26px;border-radius:50%;
  background:linear-gradient(135deg,var(--primary),#ff6b6b);
  color:#fff;font-size:13px;font-weight:700;
  display:flex;align-items:center;justify-content:center;
}
.tt-user-lbl{font-size:10px;color:#999;}
.tt-user-drop{
  position:absolute;top:calc(100% + 6px);right:0;min-width:200px;
  background:#fff;border-radius:var(--radius-lg);
  box-shadow:var(--shadow-md);border:1px solid #eee;
  z-index:9999;display:none;overflow:hidden;
}
.tt-user-drop.open{
  display:block;
  animation:ttDropIn .15s ease;
}
@keyframes ttDropIn{
  from{opacity:0;transform:translateY(-8px);}
  to{opacity:1;transform:translateY(0);}
}
.tt-udrop-head{
  padding:14px 16px;
  background:linear-gradient(135deg,var(--primary),#ff6b6b);
  color:#fff;
}
.tt-udrop-name{font-size:14px;font-weight:700;}
.tt-udrop-email{font-size:11px;color:rgba(255,255,255,.75);margin-top:2px;}
.tt-udrop-links{padding:6px 0;}
.tt-udrop-link{
  display:flex;align-items:center;gap:10px;
  padding:10px 16px;font-size:13px;color:#333;
  text-decoration:none;transition:background .15s;
  cursor:pointer;border:none;background:none;
  width:100%;text-align:left;
}
.tt-udrop-link:hover{background:var(--gray7);}
.tt-udrop-ic{font-size:14px;width:18px;text-align:center;}
.tt-udrop-divider{height:1px;background:#f0f0f0;margin:3px 0;}
.tt-udrop-logout{color:#aaa!important;}
.tt-udrop-logout:hover{background:#fff5f5!important;color:var(--primary)!important;}

/* 푸터 */
.tt-footer{
  background:#1a1a1a;color:#aaa;
  padding:40px 0 24px;margin-top:80px;
}
.tt-footer-inner{
  max-width:1280px;margin:0 auto;padding:0 24px;
}
.tt-footer-top{
  display:grid;grid-template-columns:2fr 1fr 1fr 1fr;
  gap:40px;padding-bottom:32px;
  border-bottom:1px solid #333;margin-bottom:24px;
}
.tt-footer-brand .tt-logo-wordmark{color:#fff;}
.tt-footer-brand .tt-logo-wordmark span{color:var(--primary);}
.tt-footer-desc{font-size:13px;color:#666;line-height:1.7;margin-top:10px;}
.tt-footer-col-title{font-size:13px;font-weight:700;color:#fff;margin-bottom:14px;}
.tt-footer-link{
  display:block;font-size:12px;color:#888;
  margin-bottom:8px;text-decoration:none;transition:color .15s;
}
.tt-footer-link:hover{color:var(--primary);}
.tt-footer-bottom{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:8px;
}
.tt-footer-copy{font-size:12px;color:#555;}
.tt-footer-policy{display:flex;gap:16px;}
.tt-footer-policy a{font-size:12px;color:#666;text-decoration:none;}
.tt-footer-policy a:hover{color:#fff;}

/* 모바일 메뉴 */
.tt-mobile-menu-btn{
  display:none;border:none;background:none;
  font-size:24px;cursor:pointer;padding:4px;color:#333;
}
@media(max-width:768px){
  .tt-nav{display:none;}
  .tt-header-search{width:160px;}
  .tt-mobile-menu-btn{display:flex;}
  .tt-footer-top{grid-template-columns:1fr 1fr;}
  .tt-app-banner{font-size:11px;}
}
    `;
    document.head.appendChild(s);
  }

  /* ══════════════════════════════════════════
     2. 헤더 HTML 생성
  ══════════════════════════════════════════ */
  function buildHeader() {
    // 현재 페이지 경로로 active 탭 결정
    const path = window.location.pathname;
    const isAdmin = path.includes('/admin/');
    if (isAdmin) return; // 어드민 페이지는 헤더 삽입 안 함

    const pages = [
      { href: 'index.html',                       label: '홈' },
      { href: 'product-list.html?cat=tire',       label: '타이어', tire: true },
      { href: 'store.html',                        label: '매장찾기' },
      { href: 'event.html',                        label: '기획전' },
      { href: 'cs.html',                           label: '고객센터' },
    ];

    // base path 계산 (admin 폴더 안이면 ../)
    const base = isAdmin ? '../' : '';

    const navItems = pages.map(p => {
      const active = path.includes(p.href.split('?')[0]) ? 'active' : '';
      const hot = p.hot ? `<span class="tt-nav-hot">${p.hot}</span>` : '';
      if (p.tire) {
        return `<div class="tt-nav-tire-wrap">
          <a class="tt-nav-item ${active}" href="${base}${p.href}">${p.label} ▾${hot}</a>
          <div class="tt-nav-tire-drop">
            <a href="${base}product-list.html?cat=tire"><span class="drop-icon">🛞</span>전체 타이어</a>
            <a href="${base}product-list.html?cat=tire&season=사계절"><span class="drop-icon">☀️</span>사계절</a>
            <a href="${base}product-list.html?cat=tire&season=겨울"><span class="drop-icon">❄️</span>겨울용</a>
            <div class="tt-nav-tire-divider"></div>
            <a href="${base}product-list.html?cat=tire&vehicle=고성능"><span class="drop-icon">🏎️</span>고성능</a>
            <a href="${base}product-list.html?cat=tire&vehicle=SUV"><span class="drop-icon">🚙</span>SUV</a>
            <a href="${base}product-list.html?cat=tire&vehicle=트럭"><span class="drop-icon">🚚</span>화물</a>
          </div>
        </div>`;
      }
      return `<a class="tt-nav-item ${active}" href="${base}${p.href}">${p.label}${hot}</a>`;
    }).join('');

    const html = `
<!-- 앱 다운로드 배너 -->
<div class="tt-app-banner" id="ttAppBanner">
  <span class="tt-app-banner-badge">앱 전용</span>
  <span class="tt-app-banner-text"><b>앱으로 추가 5% 할인</b> · 타이어탑 앱 다운로드</span>
  <button class="tt-app-banner-close" onclick="document.getElementById('ttAppBanner').style.display='none'">×</button>
</div>

<!-- 헤더 고정 영역 -->
<header class="tt-header-sticky" id="ttHeader">
  <!-- 유틸 바 -->
  <div class="tt-util-bar">
    <div class="tt-util-inner">
      <a class="tt-util-link" href="${base}cs.html">고객센터</a>
      <div class="tt-util-sep"></div>
      <a class="tt-util-link" href="${base}cs.html#faq">FAQ</a>
      <div class="tt-util-sep"></div>
      <a class="tt-util-link" href="${base}cs.html#notice">공지사항</a>
      <div class="tt-util-sep"></div>
      <a class="tt-util-link" href="${base}mypage.html">주문조회</a>
    </div>
  </div>

  <!-- 메인 헤더 행 -->
  <div class="tt-header-row">
    <!-- 로고 -->
    <a class="tt-logo" href="${base}index.html">
      <span class="tt-logo-icon">🔴</span>
      <span class="tt-logo-wordmark">TIRE<span>TOP</span></span>
    </a>

    <!-- 네비게이션 -->
    <nav class="tt-nav">${navItems}</nav>

    <!-- 검색창 -->
    <form class="tt-header-search" onsubmit="return ttHeaderSearch(event)">
      <div class="tt-header-search-inner">
        <input type="text" id="ttHeaderSearchInput"
               placeholder="타이어 브랜드, 규격 검색...">
        <button type="submit" class="tt-search-submit">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
      </div>
    </form>

    <!-- 우측 버튼들 -->
    <div class="tt-header-right">
      <!-- 위시리스트 -->
      <a class="tt-hdr-btn" href="${base}mypage.html#wish">
        <span class="tt-hdr-btn-icon">🤍</span>
        <span class="tt-hdr-btn-label">찜</span>
      </a>

      <!-- 장바구니 -->
      <a class="tt-hdr-btn" href="${base}cart.html">
        <span class="tt-hdr-btn-icon">🛒</span>
        <span class="tt-hdr-btn-label">장바구니</span>
        <span class="tt-cart-cnt" id="ttCartCnt">0</span>
      </a>

      <!-- 내 계정 드롭다운 -->
      <div class="tt-user-wrap">
        <button class="tt-user-trigger" id="ttUserTrigger"
                onclick="ttToggleUserDrop()">
          <div class="tt-user-av" id="ttUserAv">👤</div>
          <span class="tt-user-lbl" id="ttUserLbl">내 계정</span>
        </button>
        <div class="tt-user-drop" id="ttUserDrop">
          <!-- 비로그인 상태 (기본) -->
          <div id="ttDropGuest">
            <div style="padding:20px 16px;text-align:center;border-bottom:1px solid #f0f0f0;">
              <div style="font-size:32px;margin-bottom:8px;">👤</div>
              <div style="font-size:14px;font-weight:700;color:#333;">로그인이 필요합니다</div>
              <div style="font-size:12px;color:#999;margin-top:4px;">회원 혜택을 누려보세요</div>
            </div>
            <div class="tt-udrop-links">
              <a class="tt-udrop-link" href="${base}login.html">
                <span class="tt-udrop-ic">🔑</span> 로그인
              </a>
              <a class="tt-udrop-link" href="${base}signup.html">
                <span class="tt-udrop-ic">✏️</span> 회원가입
              </a>
            </div>
          </div>
          <!-- 로그인 상태 -->
          <div id="ttDropUser" style="display:none;">
            <div class="tt-udrop-head">
              <div class="tt-udrop-name" id="ttDropName">회원님</div>
              <div class="tt-udrop-email" id="ttDropEmail"></div>
            </div>
            <div class="tt-udrop-links">
              <a class="tt-udrop-link" href="${base}mypage.html">
                <span class="tt-udrop-ic">👤</span> 마이페이지
              </a>
              <a class="tt-udrop-link" href="${base}mypage.html#orders">
                <span class="tt-udrop-ic">📦</span> 주문내역
              </a>
              <a class="tt-udrop-link" href="${base}mypage.html#wish">
                <span class="tt-udrop-ic">🤍</span> 찜 목록
              </a>
              <div class="tt-udrop-divider"></div>
              <button class="tt-udrop-link tt-udrop-logout" onclick="ttLogout()">
                <span class="tt-udrop-ic">🚪</span> 로그아웃
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 모바일 메뉴 버튼 -->
      <button class="tt-mobile-menu-btn" onclick="ttToggleMobileMenu()">☰</button>
    </div>
  </div>
</header>`;

    const wrap = document.createElement('div');
    wrap.id = 'tt-header-wrap';
    wrap.innerHTML = html;
    document.body.insertBefore(wrap, document.body.firstChild);
  }

  /* ══════════════════════════════════════════
     3. 푸터 HTML 생성
  ══════════════════════════════════════════ */
  function buildFooter() {
    const path    = window.location.pathname;
    const isAdmin = path.includes('/admin/');
    if (isAdmin) return;
    const base = isAdmin ? '../' : '';

    const html = `
<footer class="tt-footer">
  <div class="tt-footer-inner">
    <div class="tt-footer-top">
      <div class="tt-footer-brand">
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
          <span style="font-size:22px;">🔴</span>
          <span class="tt-logo-wordmark" style="color:#fff;font-size:20px;font-weight:900;">
            TIRE<span style="color:var(--primary)">TOP</span>
          </span>
        </div>
        <p class="tt-footer-desc">현장 추가금 0원 보장<br>전국 2,800개 제휴 매장 네트워크</p>
        <div style="margin-top:16px;display:flex;gap:10px;">
          <a href="#" style="width:36px;height:36px;border-radius:50%;background:#333;
             display:flex;align-items:center;justify-content:center;font-size:16px;">📘</a>
          <a href="#" style="width:36px;height:36px;border-radius:50%;background:#333;
             display:flex;align-items:center;justify-content:center;font-size:16px;">📸</a>
          <a href="#" style="width:36px;height:36px;border-radius:50%;background:#333;
             display:flex;align-items:center;justify-content:center;font-size:16px;">▶️</a>
        </div>
      </div>
      <div>
        <div class="tt-footer-col-title">쇼핑</div>
        <a class="tt-footer-link" href="${base}product-list.html?cat=tire">전체 타이어</a>
        <a class="tt-footer-link" href="${base}product-list.html?cat=tire&season=사계절">사계절 타이어</a>
        <a class="tt-footer-link" href="${base}product-list.html?cat=tire&season=겨울">겨울용 타이어</a>
        <a class="tt-footer-link" href="${base}product-list.html?cat=tire&vehicle=SUV">SUV 타이어</a>
        <a class="tt-footer-link" href="${base}event.html">기획전</a>
      </div>
      <div>
        <div class="tt-footer-col-title">고객지원</div>
        <a class="tt-footer-link" href="${base}cs.html">고객센터</a>
        <a class="tt-footer-link" href="${base}cs.html#faq">자주 묻는 질문</a>
        <a class="tt-footer-link" href="${base}cs.html#notice">공지사항</a>
        <a class="tt-footer-link" href="${base}mypage.html">주문조회</a>
      </div>
      <div>
        <div class="tt-footer-col-title">회사</div>
        <a class="tt-footer-link" href="#">회사소개</a>
        <a class="tt-footer-link" href="#">제휴문의</a>
        <a class="tt-footer-link" href="#">입점안내</a>
        <a class="tt-footer-link" href="#">채용정보</a>
      </div>
    </div>
    <div class="tt-footer-bottom">
      <div class="tt-footer-copy">
        (주)타이어탑 | 대표 홍길동 | 사업자 123-45-67890<br>
        서울특별시 강남구 테헤란로 123 | Tel. 1588-0000<br>
        © 2025 TIRETOP Corp. All rights reserved.
      </div>
      <div class="tt-footer-policy">
        <a href="#">이용약관</a>
        <a href="#" style="font-weight:700;color:#fff;">개인정보처리방침</a>
      </div>
    </div>
  </div>
</footer>`;

    document.body.insertAdjacentHTML('beforeend', html);
  }

  /* ══════════════════════════════════════════
     4. 장바구니 뱃지 업데이트
  ══════════════════════════════════════════ */
  function updateCartBadge() {
    const cnt = document.getElementById('ttCartCnt');
    if (!cnt) return;
    try {
      const cart  = JSON.parse(localStorage.getItem('tt_cart') || '[]');
      const total = cart.reduce((s, i) => s + (i.qty || 1), 0);
      cnt.textContent = total;
      cnt.style.display = total > 0 ? 'flex' : 'none';
    } catch {
      cnt.textContent = '0';
    }
  }

  /* ══════════════════════════════════════════
     5. 로그인 상태 반영
  ══════════════════════════════════════════ */
  function checkLoginState() {
    try {
      const user = JSON.parse(localStorage.getItem('tt_user') || 'null');
      const guestEl = document.getElementById('ttDropGuest');
      const userEl  = document.getElementById('ttDropUser');
      const avEl    = document.getElementById('ttUserAv');
      const lblEl   = document.getElementById('ttUserLbl');
      if (!guestEl || !userEl) return;
      if (user && user.name) {
        guestEl.style.display = 'none';
        userEl.style.display  = 'block';
        const nameEl  = document.getElementById('ttDropName');
        const emailEl = document.getElementById('ttDropEmail');
        if (nameEl)  nameEl.textContent  = user.name + '님';
        if (emailEl) emailEl.textContent = user.email || '';
        if (avEl)    avEl.textContent    = user.name[0].toUpperCase();
        if (lblEl)   lblEl.textContent   = user.name;
      } else {
        guestEl.style.display = 'block';
        userEl.style.display  = 'none';
        if (avEl)  avEl.textContent  = '👤';
        if (lblEl) lblEl.textContent = '내 계정';
      }
    } catch (e) {
      console.warn('[loader] 로그인 상태 확인 실패:', e);
    }
  }

  /* ══════════════════════════════════════════
     6. 전역 함수 등록
  ══════════════════════════════════════════ */
  function registerGlobals() {
    // 유저 드롭다운 토글
    window.ttToggleUserDrop = function () {
      const drop    = document.getElementById('ttUserDrop');
      const trigger = document.getElementById('ttUserTrigger');
      if (!drop) return;
      const isOpen = drop.classList.contains('open');
      drop.classList.toggle('open', !isOpen);
      trigger?.classList.toggle('open', !isOpen);
    };

    // 드롭다운 외부 클릭 닫기
    document.addEventListener('click', function (e) {
      const wrap = document.querySelector('.tt-user-wrap');
      if (wrap && !wrap.contains(e.target)) {
        document.getElementById('ttUserDrop')?.classList.remove('open');
        document.getElementById('ttUserTrigger')?.classList.remove('open');
      }
    });

    // 로그아웃
    window.ttLogout = function () {
      localStorage.removeItem('tt_user');
      localStorage.removeItem('tt_token');
      sessionStorage.removeItem('tt_user');
      sessionStorage.removeItem('tt_token');
      location.href = 'login.html';
    };

    // 헤더 검색
    window.ttHeaderSearch = function (e) {
      e.preventDefault();
      const kw = document.getElementById('ttHeaderSearchInput')?.value?.trim();
      if (!kw) return false;
      const base = window.location.pathname.includes('/admin/') ? '../' : '';
      location.href = base + 'product-list.html?search=' + encodeURIComponent(kw);
      return false;
    };

    // 모바일 메뉴 (간단 토글)
    window.ttToggleMobileMenu = function () {
      let nav = document.querySelector('.tt-nav');
      if (!nav) return;
      const isOpen = nav.style.display === 'flex';
      nav.style.cssText = isOpen
        ? 'display:none;'
        : 'display:flex;flex-direction:column;position:fixed;top:102px;left:0;right:0;background:#fff;box-shadow:var(--shadow-md);padding:12px 0;z-index:8999;';
    };
  }

  /* ══════════════════════════════════════════
     7. 바디 패딩 조정 — sticky 헤더이므로 제거
        sticky position은 normal flow 안에 유지되므로
        paddingTop 추가 시 상단 이중 공백 발생 → 완전 제거
  ══════════════════════════════════════════ */
  function adjustBodyPadding() {
    // 아무것도 하지 않음: sticky 헤더는 body paddingTop 불필요
  }

  /* ══════════════════════════════════════════
     8. 초기화 실행
  ══════════════════════════════════════════ */
  function init() {
    injectStyles();
    buildHeader();
    buildFooter();
    registerGlobals();

    // DOM 완료 후 실행
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        updateCartBadge();
        checkLoginState();
        adjustBodyPadding();
      });
    } else {
      updateCartBadge();
      checkLoginState();
      adjustBodyPadding();
    }

    // localStorage 변경 감지 (장바구니 뱃지 실시간 갱신)
    window.addEventListener('storage', function (e) {
      if (e.key === 'tt_cart' || e.key === 'tt_user') {
        updateCartBadge();
        checkLoginState();
      }
    });

    // tt_store_update 이벤트 감지
    window.addEventListener('tt_store_update', function (e) {
      if (e.detail?.key === 'tt_cart') updateCartBadge();
    });
  }

  init();
})();
