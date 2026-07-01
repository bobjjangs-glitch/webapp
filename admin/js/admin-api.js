/**
 * TIRETOP Admin - 공통 API 모듈
 * JWT 토큰 관리 + REST API 호출 헬퍼
 * 모든 어드민 페이지에서 공유
 */

const AdminAPI = (() => {
  const BASE = '/api';
  const TOKEN_KEY = 'tt_admin_token';
  const USER_KEY  = 'tt_admin_user';

  /* ─── 토큰 관리 ─────────────────────────────── */
  function getToken() {
    return localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY) || null;
  }
  function setToken(token, persist = true) {
    if (persist) {
      localStorage.setItem(TOKEN_KEY, token);
    } else {
      sessionStorage.setItem(TOKEN_KEY, token);
    }
  }
  function getUser() {
    try {
      const raw = localStorage.getItem(USER_KEY) || sessionStorage.getItem(USER_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch { return null; }
  }
  function setUser(user, persist = true) {
    const json = JSON.stringify(user);
    if (persist) {
      localStorage.setItem(USER_KEY, json);
    } else {
      sessionStorage.setItem(USER_KEY, json);
    }
  }
  function isLoggedIn() {
    return !!getToken() && !!getUser();
  }
  function logout() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    sessionStorage.removeItem(TOKEN_KEY);
    sessionStorage.removeItem(USER_KEY);
    // 구식 키도 제거
    localStorage.removeItem('ttAdmin');
    sessionStorage.removeItem('ttAdmin');
    location.href = '/admin/login.html';
  }

  /* ─── 인증 체크 (페이지 로드 시 호출) ──────── */
  function requireAdmin() {
    if (!isLoggedIn()) {
      const redirect = encodeURIComponent(location.pathname + location.search);
      location.href = `/admin/login.html?redirect=${redirect}`;
      return false;
    }
    return true;
  }

  /* ─── 공통 fetch 래퍼 ──────────────────────── */
  async function request(path, options = {}) {
    const token = getToken();
    const headers = {
      'Content-Type': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers || {}),
    };
    const url = path.startsWith('http') ? path : `${BASE}${path}`;
    try {
      const res = await fetch(url, { ...options, headers });
      // 401 → 로그아웃
      if (res.status === 401) {
        logout();
        return null;
      }
      const data = await res.json();
      return data;
    } catch (e) {
      console.error('[AdminAPI] 요청 실패:', path, e);
      return { success: false, message: '서버 통신 오류가 발생했습니다.' };
    }
  }

  async function get(path, params = {}) {
    const qs = Object.keys(params).length
      ? '?' + new URLSearchParams(params).toString()
      : '';
    return request(path + qs, { method: 'GET' });
  }

  async function post(path, body) {
    return request(path, { method: 'POST', body: JSON.stringify(body) });
  }

  async function put(path, body) {
    return request(path, { method: 'PUT', body: JSON.stringify(body) });
  }

  async function del(path) {
    return request(path, { method: 'DELETE' });
  }

  /* ─── 인증 API ──────────────────────────────── */
  async function login(email, password, persist = true) {
    const res = await post('/auth/login', { email, password });
    if (res?.success) {
      setToken(res.data.token, persist);
      setUser(res.data.user, persist);
    }
    return res;
  }

  /* ─── 대시보드 ──────────────────────────────── */
  async function getDashboard() {
    return get('/stats/dashboard');
  }

  /* ─── 상품 ──────────────────────────────────── */
  async function getProducts(params = {}) {
    return get('/products', { limit: 100, ...params });
  }
  async function getProduct(id) {
    return get(`/products/${id}`);
  }
  async function createProduct(data) {
    return post('/products', data);
  }
  async function updateProduct(id, data) {
    return put(`/products/${id}`, data);
  }
  async function deleteProduct(id) {
    return del(`/products/${id}`);
  }
  async function updateStock(id, stock) {
    return put(`/products/${id}/stock`, { stock });
  }

  /* ─── 회원 ──────────────────────────────────── */
  async function getUsers(params = {}) {
    return get('/users', { limit: 100, ...params });
  }
  async function getUser(id) {
    return get(`/users/${id}`);
  }
  async function updateUser(id, data) {
    return put(`/users/${id}`, data);
  }
  async function deleteUser(id) {
    return del(`/users/${id}`);
  }

  /* ─── 주문 ──────────────────────────────────── */
  async function getOrders(params = {}) {
    return get('/orders', { limit: 100, ...params });
  }
  async function updateOrderStatus(id, status) {
    return put(`/orders/${id}/status`, { status });
  }

  /* ─── 예약 ──────────────────────────────────── */
  async function getReservations(params = {}) {
    return get('/reservations', { limit: 100, ...params });
  }
  async function updateReservationStatus(id, status, memo) {
    return put(`/reservations/${id}/status`, { status, memo });
  }

  /* ─── 리뷰 ──────────────────────────────────── */
  async function getReviews(params = {}) {
    return get('/reviews', { limit: 100, ...params });
  }
  async function deleteReview(id) {
    return del(`/reviews/${id}`);
  }
  async function updateReview(id, data) {
    return put(`/reviews/${id}`, data);
  }

  /* ─── 쿠폰 ──────────────────────────────────── */
  async function getCoupons() {
    return get('/coupons/all');
  }
  async function createCoupon(data) {
    return post('/coupons', data);
  }

  /* ─── 이벤트 ────────────────────────────────── */
  async function getEvents(params = {}) {
    return get('/events', params);
  }
  async function createEvent(data) {
    return post('/events', data);
  }
  async function updateEvent(id, data) {
    return put(`/events/${id}`, data);
  }
  async function deleteEvent(id) {
    return del(`/events/${id}`);
  }

  /* ─── 공지/FAQ ──────────────────────────────── */
  async function getNotices(params = {}) {
    return get('/notices', params);
  }
  async function createNotice(data) {
    return post('/notices', data);
  }
  async function updateNotice(id, data) {
    return put(`/notices/${id}`, data);
  }
  async function deleteNotice(id) {
    return del(`/notices/${id}`);
  }
  async function getFaqs() {
    return get('/faqs');
  }
  async function createFaq(data) {
    return post('/faqs', data);
  }
  async function deleteFaq(id) {
    return del(`/faqs/${id}`);
  }

  /* ─── 매장 ──────────────────────────────────── */
  async function getStores() {
    return get('/stores');
  }
  async function createStore(data) {
    return post('/stores', data);
  }
  async function updateStore(id, data) {
    return put(`/stores/${id}`, data);
  }

  /* ─── 설정 ──────────────────────────────────── */
  async function getSettings() {
    return get('/settings');
  }
  async function saveSettings(data) {
    return post('/settings', data);
  }

  /* ─── 유틸 ──────────────────────────────────── */
  function fmtMoney(n) {
    return (n || 0).toLocaleString('ko-KR') + '원';
  }
  function fmtDate(s) {
    if (!s) return '-';
    const d = new Date(s);
    return d.toLocaleDateString('ko-KR', { year:'numeric', month:'2-digit', day:'2-digit' })
      .replace(/\./g, '.').trim();
  }
  function fmtDateTime(s) {
    if (!s) return '-';
    const d = new Date(s);
    return d.toLocaleString('ko-KR', {
      year:'numeric', month:'2-digit', day:'2-digit',
      hour:'2-digit', minute:'2-digit'
    });
  }

  /* ─── 토스트 알림 ───────────────────────────── */
  function toast(msg, type = 'success', duration = 2800) {
    let container = document.getElementById('tt-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'tt-toast-container';
      container.style.cssText = `
        position:fixed; bottom:28px; right:28px; z-index:99999;
        display:flex; flex-direction:column; gap:8px; pointer-events:none;
      `;
      document.body.appendChild(container);
    }
    const colors = {
      success: { bg: '#10b981', icon: '✅' },
      error:   { bg: '#ef4444', icon: '❌' },
      warn:    { bg: '#f59e0b', icon: '⚠️' },
      info:    { bg: '#3b82f6', icon: 'ℹ️' },
    };
    const c = colors[type] || colors.info;
    const el = document.createElement('div');
    el.style.cssText = `
      background:${c.bg}; color:#fff; padding:12px 18px; border-radius:10px;
      font-size:13px; font-weight:600; box-shadow:0 4px 20px rgba(0,0,0,.2);
      display:flex; align-items:center; gap:8px; pointer-events:auto;
      opacity:0; transform:translateX(40px); transition:all 0.3s ease;
      max-width:320px; line-height:1.4;
    `;
    el.innerHTML = `<span>${c.icon}</span><span>${msg}</span>`;
    container.appendChild(el);
    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateX(0)';
    });
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(40px)';
      setTimeout(() => el.remove(), 300);
    }, duration);
  }

  /* ─── 공개 인터페이스 ───────────────────────── */
  return {
    // 인증
    getToken, setToken, getUser, setUser,
    isLoggedIn, logout, requireAdmin, login,
    // CRUD
    request, get, post, put, del,
    // 도메인별
    getDashboard,
    getProducts, getProduct, createProduct, updateProduct, deleteProduct, updateStock,
    getUsers, getUser, updateUser, deleteUser,
    getOrders, updateOrderStatus,
    getReservations, updateReservationStatus,
    getReviews, deleteReview, updateReview,
    getCoupons, createCoupon,
    getEvents, createEvent, updateEvent, deleteEvent,
    getNotices, createNotice, updateNotice, deleteNotice,
    getFaqs, createFaq, deleteFaq,
    getStores, createStore, updateStore,
    getSettings, saveSettings,
    // 유틸
    fmtMoney, fmtDate, fmtDateTime, toast,
  };
})();
