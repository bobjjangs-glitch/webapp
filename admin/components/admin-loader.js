/* ============================================================
   admin-loader.js – TIRETOP Admin 공통 헤더/사이드바/푸터 삽입
   ============================================================ */

(function () {
  /* ── 현재 페이지 경로에서 파일명 추출 ── */
  const path     = location.pathname;
  const fileName = path.split('/').pop().replace('.html', '') || 'index';

  /* ── 네비게이션 메뉴 정의 ── */
  const NAV_ITEMS = [
    { id: 'index',        icon: '📊', label: '대시보드',   href: 'index.html'        },
    { id: 'products',     icon: '🛞', label: '상품 관리',  href: 'products.html'     },
    { id: 'orders',       icon: '📋', label: '주문 관리',  href: 'orders.html'       },
    { id: 'reservations', icon: '📅', label: '예약 관리',  href: 'reservations.html' },
    { id: 'inventory',    icon: '📦', label: '재고 관리',  href: 'inventory.html'    },
    { id: 'users',        icon: '👥', label: '회원 관리',  href: 'users.html'        },
    { id: 'reviews',      icon: '⭐', label: '리뷰 관리',  href: 'reviews.html'      },
    { id: 'coupons',      icon: '🎟️', label: '쿠폰 관리',  href: 'coupons.html'      },
    { id: 'events',       icon: '🎉', label: '이벤트',     href: 'events.html'       },
    { id: 'stores',       icon: '🏪', label: '매장 관리',  href: 'stores.html'       },
    { id: 'cs',           icon: '💬', label: '고객센터',   href: 'cs.html'           },
    { id: 'stats',        icon: '📈', label: '통계',       href: 'stats.html'        },
    { id: 'settings',     icon: '⚙️', label: '설정',       href: 'settings.html'     },
  ];

  /* ── 공통 CSS 주입 ── */
  const style = document.createElement('style');
  style.textContent = `
    /* ── 전체 레이아웃 ── */
    html, body {
      margin: 0; padding: 0;
      font-family: 'Pretendard', 'Noto Sans KR', -apple-system, sans-serif;
      background: #f1f5f9;
      min-height: 100vh;
    }

    /* ── 어드민 래퍼 ── */
    .admin-wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ── 사이드바 ── */
    .admin-sidebar {
      width: 220px;
      min-width: 220px;
      background: #1e293b;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 200;
      overflow-y: auto;
      overflow-x: hidden;
      transition: transform 0.25s ease;
    }
    .admin-sidebar::-webkit-scrollbar { width: 4px; }
    .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
    .admin-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

    /* 사이드바 로고 */
    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 20px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      text-decoration: none;
      flex-shrink: 0;
    }
    .sidebar-logo-icon {
      width: 36px; height: 36px;
      background: linear-gradient(135deg, #e31e24, #c41920);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; flex-shrink: 0;
    }
    .sidebar-logo-text {
      font-size: 16px; font-weight: 900;
      color: #fff; letter-spacing: -0.5px;
      line-height: 1.2;
    }
    .sidebar-logo-text span {
      display: block; font-size: 10px;
      color: rgba(255,255,255,0.45); font-weight: 400;
      letter-spacing: 0;
    }

    /* 사이드바 섹션 라벨 */
    .sidebar-section-label {
      font-size: 10px; font-weight: 700;
      color: rgba(255,255,255,0.3);
      text-transform: uppercase; letter-spacing: 1px;
      padding: 16px 18px 6px;
    }

    /* 사이드바 메뉴 아이템 */
    .sidebar-nav { padding: 8px 10px; flex: 1; }
    .sidebar-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 10px;
      color: rgba(255,255,255,0.6);
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.18s ease;
      cursor: pointer;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
    }
    .sidebar-item:hover {
      background: rgba(255,255,255,0.08);
      color: rgba(255,255,255,0.95);
    }
    .sidebar-item.active {
      background: linear-gradient(135deg, rgba(227,30,36,0.85), rgba(196,25,32,0.85));
      color: #fff;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(227,30,36,0.35);
    }
    .sidebar-item-icon {
      font-size: 16px; flex-shrink: 0;
      width: 22px; text-align: center;
    }
    .sidebar-item-label { flex: 1; }

    /* 사이드바 하단 */
    .sidebar-bottom {
      padding: 12px 10px;
      border-top: 1px solid rgba(255,255,255,0.08);
      flex-shrink: 0;
    }
    .sidebar-user {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: 10px;
      color: rgba(255,255,255,0.7);
      transition: background 0.18s;
      cursor: pointer;
    }
    .sidebar-user:hover { background: rgba(255,255,255,0.08); }
    .sidebar-user-avatar {
      width: 32px; height: 32px; border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #4f46e5);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 800; color: #fff;
      flex-shrink: 0;
    }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name {
      font-size: 12px; font-weight: 700;
      color: rgba(255,255,255,0.9);
      overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .sidebar-user-role { font-size: 10px; color: rgba(255,255,255,0.4); }

    /* ── 메인 영역 ── */
    .admin-main {
      flex: 1;
      margin-left: 220px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── 상단 헤더 ── */
    .admin-topbar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid #e2e8f0;
      padding: 0 24px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .topbar-left { display: flex; align-items: center; gap: 10px; }
    .topbar-page-title {
      font-size: 15px; font-weight: 800;
      color: #0f172a; letter-spacing: -0.3px;
    }
    .topbar-breadcrumb {
      font-size: 12px; color: #94a3b8;
      display: flex; align-items: center; gap: 4px;
    }
    .topbar-right { display: flex; align-items: center; gap: 8px; }

    .topbar-btn {
      width: 36px; height: 36px;
      border-radius: 10px;
      border: 1.5px solid #e2e8f0;
      background: #fff;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px;
      transition: all 0.18s;
      position: relative;
      text-decoration: none;
    }
    .topbar-btn:hover { background: #f8fafc; border-color: #cbd5e1; }

    .topbar-badge {
      position: absolute; top: -4px; right: -4px;
      background: #e31e24; color: #fff;
      font-size: 9px; font-weight: 800;
      min-width: 16px; height: 16px;
      border-radius: 8px; padding: 0 3px;
      display: flex; align-items: center; justify-content: center;
      border: 2px solid #fff;
    }

    .topbar-site-btn {
      display: flex; align-items: center; gap: 6px;
      padding: 7px 14px;
      border-radius: 10px;
      border: 1.5px solid #e2e8f0;
      background: #fff;
      font-size: 12px; font-weight: 600;
      color: #475569;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.18s;
    }
    .topbar-site-btn:hover { background: #f1f5f9; color: #0f172a; }

    /* ── 콘텐츠 ── */
    .admin-content {
      flex: 1;
      padding: 24px;
      max-width: 1440px;
      width: 100%;
    }

    /* ── 푸터 ── */
    .admin-footer-bar {
      background: #fff;
      border-top: 1px solid #e2e8f0;
      padding: 12px 24px;
      text-align: center;
      font-size: 12px;
      color: #94a3b8;
    }

    /* ── 모바일 오버레이 ── */
    .sidebar-overlay {
      display: none;
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 199;
    }

    /* ── 모바일 햄버거 버튼 ── */
    .sidebar-toggle {
      display: none;
      width: 36px; height: 36px;
      border-radius: 10px;
      border: 1.5px solid #e2e8f0;
      background: #fff;
      cursor: pointer;
      align-items: center; justify-content: center;
      font-size: 18px;
    }

    /* ── 토스트 ── */
    #admin-toast-container {
      position: fixed;
      bottom: 24px; right: 24px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 8px;
      pointer-events: none;
    }
    .admin-toast {
      display: flex; align-items: center; gap: 10px;
      padding: 12px 18px;
      border-radius: 12px;
      background: #1e293b;
      color: #fff;
      font-size: 13px; font-weight: 500;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      pointer-events: all;
      animation: toastIn 0.3s ease forwards;
      min-width: 220px; max-width: 340px;
    }
    .admin-toast.success { background: #065f46; }
    .admin-toast.error   { background: #991b1b; }
    .admin-toast.warning { background: #92400e; }
    .admin-toast.info    { background: #1e40af; }
    .admin-toast.hide    { animation: toastOut 0.3s ease forwards; }
    @keyframes toastIn  { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    @keyframes toastOut { from { opacity:1; transform:translateY(0);    } to { opacity:0; transform:translateY(12px); } }

    /* ── 반응형 ── */
    @media (max-width: 1024px) {
      .admin-sidebar { transform: translateX(-220px); }
      .admin-sidebar.open { transform: translateX(0); }
      .admin-main { margin-left: 0; }
      .sidebar-overlay.open { display: block; }
      .sidebar-toggle { display: flex; }
      .topbar-breadcrumb { display: none; }
    }
    @media (max-width: 640px) {
      .admin-content { padding: 16px; }
      .topbar-site-btn span { display: none; }
    }
  `;
  document.head.appendChild(style);

  /* ── 페이지 타이틀 매핑 ── */
  const PAGE_TITLES = {
    index: '대시보드', products: '상품 관리', orders: '주문 관리',
    reservations: '예약 관리', inventory: '재고 관리', users: '회원 관리',
    reviews: '리뷰 관리', coupons: '쿠폰 관리', events: '이벤트',
    stores: '매장 관리', cs: '고객센터', stats: '통계', settings: '설정',
  };
  const pageTitle = PAGE_TITLES[fileName] || 'TIRETOP Admin';

  /* ── 사이드바 HTML ── */
  const sidebarHTML = `
    <aside class="admin-sidebar" id="adminSidebar">
      <a class="sidebar-logo" href="index.html">
        <div class="sidebar-logo-icon">🛞</div>
        <div class="sidebar-logo-text">
          TIRETOP
          <span>Admin Console</span>
        </div>
      </a>

      <nav class="sidebar-nav">
        <div class="sidebar-section-label">메인</div>
        ${NAV_ITEMS.slice(0, 1).map(item => `
          <a href="${item.href}" class="sidebar-item ${fileName === item.id ? 'active' : ''}">
            <span class="sidebar-item-icon">${item.icon}</span>
            <span class="sidebar-item-label">${item.label}</span>
          </a>
        `).join('')}

        <div class="sidebar-section-label">쇼핑몰</div>
        ${NAV_ITEMS.slice(1, 6).map(item => `
          <a href="${item.href}" class="sidebar-item ${fileName === item.id ? 'active' : ''}">
            <span class="sidebar-item-icon">${item.icon}</span>
            <span class="sidebar-item-label">${item.label}</span>
          </a>
        `).join('')}

        <div class="sidebar-section-label">마케팅</div>
        ${NAV_ITEMS.slice(6, 9).map(item => `
          <a href="${item.href}" class="sidebar-item ${fileName === item.id ? 'active' : ''}">
            <span class="sidebar-item-icon">${item.icon}</span>
            <span class="sidebar-item-label">${item.label}</span>
          </a>
        `).join('')}

        <div class="sidebar-section-label">운영</div>
        ${NAV_ITEMS.slice(9).map(item => `
          <a href="${item.href}" class="sidebar-item ${fileName === item.id ? 'active' : ''}">
            <span class="sidebar-item-icon">${item.icon}</span>
            <span class="sidebar-item-label">${item.label}</span>
          </a>
        `).join('')}
      </nav>

      <div class="sidebar-bottom">
        <div class="sidebar-user" onclick="location.href='settings.html'">
          <div class="sidebar-user-avatar">홍</div>
          <div class="sidebar-user-info">
            <div class="sidebar-user-name">홍길동</div>
            <div class="sidebar-user-role">최고 관리자</div>
          </div>
          <span style="font-size:13px;color:rgba(255,255,255,0.3);">⚙️</span>
        </div>
      </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  `;

  /* ── 상단바 HTML ── */
  const topbarHTML = `
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰</button>
        <div>
          <div class="topbar-page-title">${pageTitle}</div>
          <div class="topbar-breadcrumb">
            <span>TIRETOP Admin</span>
            <span>›</span>
            <span style="color:#475569;">${pageTitle}</span>
          </div>
        </div>
      </div>
      <div class="topbar-right">
        <a href="../index.html" target="_blank" class="topbar-site-btn">
          🌐 <span>사이트 보기</span>
        </a>
        <button class="topbar-btn" title="알림">
          🔔
          <span class="topbar-badge">3</span>
        </button>
        <button class="topbar-btn" title="새로고침" onclick="location.reload()">🔄</button>
        <button class="topbar-btn" title="로그아웃"
                onclick="if(confirm('로그아웃 하시겠습니까?')) location.href='../login.html'">
          🚪
        </button>
      </div>
    </header>
  `;

  /* ── 푸터 HTML ── */
  const footerHTML = `
    <footer class="admin-footer-bar">
      © 2025 TIRETOP Admin &nbsp;·&nbsp;
      버전 2.0.0 &nbsp;·&nbsp;
      <span id="footerTime"></span>
    </footer>
  `;

  /* ── DOM 조작: 기존 body 내용을 래퍼로 감싸기 ── */
  function buildLayout() {
    /* 기존 #tt-admin-header / #tt-admin-footer 제거 */
    ['tt-admin-header', 'tt-admin-footer'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.remove();
    });

    /* admin-main이 있으면 그 부모에 래퍼를 구성 */
    const adminMain = document.getElementById('adminMain');
    if (!adminMain) return;

    /* admin-main 앞에 래퍼 생성 */
    const wrapper = document.createElement('div');
    wrapper.className = 'admin-wrapper';

    /* 사이드바 삽입 */
    wrapper.insertAdjacentHTML('beforeend', sidebarHTML);

    /* 메인 섹션 구성 */
    const mainSection = document.createElement('div');
    mainSection.className = 'admin-main';
    mainSection.insertAdjacentHTML('beforeend', topbarHTML);

    /* 기존 admin-main을 admin-content로 변환 */
    adminMain.removeAttribute('id');
    adminMain.classList.remove('admin-main');
    adminMain.classList.add('admin-content');

    mainSection.appendChild(adminMain);
    mainSection.insertAdjacentHTML('beforeend', footerHTML);

    wrapper.appendChild(mainSection);

    /* body에 직접 삽입 */
    document.body.insertBefore(wrapper, document.body.firstChild);
  }

  /* ── 사이드바 토글 (모바일) ── */
  window.toggleSidebar = function () {
    const sb  = document.getElementById('adminSidebar');
    const ovl = document.getElementById('sidebarOverlay');
    if (!sb) return;
    sb.classList.toggle('open');
    ovl.classList.toggle('open');
  };
  window.closeSidebar = function () {
    const sb  = document.getElementById('adminSidebar');
    const ovl = document.getElementById('sidebarOverlay');
    if (sb)  sb.classList.remove('open');
    if (ovl) ovl.classList.remove('open');
  };

  /* ── 토스트 ── */
  window.adminToast = function (msg, type = 'success') {
    let container = document.getElementById('admin-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'admin-toast-container';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    const icons = { success:'✅', error:'❌', warning:'⚠️', info:'ℹ️' };
    toast.className = `admin-toast ${type}`;
    toast.innerHTML = `<span>${icons[type] || '✅'}</span><span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('hide');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  };

  /* ── 푸터 시계 ── */
  function updateFooterTime() {
    const el = document.getElementById('footerTime');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleDateString('ko-KR') + ' ' +
      now.toLocaleTimeString('ko-KR', { hour:'2-digit', minute:'2-digit' });
  }

  /* ── 실행 ── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      buildLayout();
      updateFooterTime();
      setInterval(updateFooterTime, 60000);
    });
  } else {
    buildLayout();
    updateFooterTime();
    setInterval(updateFooterTime, 60000);
  }

})();
