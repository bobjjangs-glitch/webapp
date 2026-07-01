/* ============================================================
   TIRETOP - store.js  v4.0
   Hono D1 API 기반 (PHP 완전 제거)
   ============================================================ */
(function (global) {
  'use strict';

  // ── BASE URL ─────────────────────────────────────────────
  const BASE = '/api';

  // ── 토큰 관리 ────────────────────────────────────────────
  const Auth = {
    getToken()  { return localStorage.getItem('tt_token') || ''; },
    setToken(t) { localStorage.setItem('tt_token', t); },
    removeToken(){ localStorage.removeItem('tt_token'); localStorage.removeItem('tt_user'); },
    getUser()   {
      try { return JSON.parse(localStorage.getItem('tt_user')||'null'); } catch { return null; }
    },
    setUser(u)  { localStorage.setItem('tt_user', JSON.stringify(u)); },
    isLoggedIn(){ return !!this.getToken(); },
    isAdmin()   { const u = this.getUser(); return u && u.role === 'admin'; },
  };

  // ── fetch 래퍼 ───────────────────────────────────────────
  async function api(path, method = 'GET', body = null, auth = false) {
    const url  = BASE + '/' + path;
    const opt  = { method, headers: { 'Content-Type': 'application/json' } };
    const token = Auth.getToken();
    if (auth || token) opt.headers['Authorization'] = 'Bearer ' + token;
    if (body && method !== 'GET') opt.body = JSON.stringify(body);
    try {
      const res  = await fetch(url, opt);
      const text = await res.text();
      if (!text) return { success: true, data: [] };
      const json = JSON.parse(text);
      if (res.status === 401) {
        Auth.removeToken();
        window.location.href = '/login.html?redirect=' + encodeURIComponent(window.location.pathname);
        return;
      }
      return json;
    } catch (e) {
      console.error('[TTStore] API 오류:', url, e.message);
      throw e;
    }
  }

  // ── 이벤트 발행 ──────────────────────────────────────────
  function dispatch(key, data) {
    window.dispatchEvent(new CustomEvent('tt_store_update', { detail: { key, data } }));
  }

  // ── 캐시 ─────────────────────────────────────────────────
  const _cache = {};

  // ============================================================
  const TTStore = {

    // ── 인증 ─────────────────────────────────────────────────
    Auth,

    async register(data) {
      const res = await api('auth/register', 'POST', data);
      if (res?.success) {
        Auth.setToken(res.data.token);
        Auth.setUser(res.data.user);
        dispatch('tt_auth', res.data.user);
      }
      return res;
    },

    async login(email, password) {
      const res = await api('auth/login', 'POST', { email, password });
      if (res?.success) {
        Auth.setToken(res.data.token);
        Auth.setUser(res.data.user);
        dispatch('tt_auth', res.data.user);
      }
      return res;
    },

    logout() {
      Auth.removeToken();
      dispatch('tt_auth', null);
      window.location.href = '/login.html';
    },

    async getMe() {
      const res = await api('auth/me', 'GET', null, true);
      if (res?.success) Auth.setUser(res.data);
      return res?.data;
    },

    async updateMe(data) {
      const res = await api('auth/me', 'PUT', data, true);
      return res;
    },

    async changePassword(current, next) {
      return await api('auth/password', 'PUT', { current_password: current, new_password: next }, true);
    },

    // ── 상품 ─────────────────────────────────────────────────
    async fetchProducts(params = {}) {
      const qs   = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res  = await api('products' + qs);
      if (res?.success) {
        _cache.products = res.data;
        dispatch('tt_products', res.data);
      }
      return res;
    },

    async getActiveProducts(params = {}) {
      return await this.fetchProducts({ ...params, active: 1 });
    },

    async getProduct(id) {
      const res = await api('products/' + id);
      return res?.success ? res.data : null;
    },

    async adminSaveProduct(data) {
      const res = data.id
        ? await api('products/' + data.id, 'PUT', data, true)
        : await api('products', 'POST', data, true);
      _cache.products = null;
      return res;
    },

    async adminDeleteProduct(id) {
      const res = await api('products/' + id, 'DELETE', null, true);
      _cache.products = null;
      return res;
    },

    async updateProductStock(id, stock) {
      return await api(`products/${id}/stock`, 'PUT', { stock }, true);
    },

    // ── 리뷰 ─────────────────────────────────────────────────
    async fetchReviews(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('reviews' + qs);
      if (res?.success) { _cache.reviews = res.data; dispatch('tt_reviews', res.data); }
      return res;
    },

    async addReview(data) {
      return await api('reviews', 'POST', data, true);
    },

    async adminDeleteReview(id) {
      return await api('reviews/' + id, 'DELETE', null, true);
    },

    async adminUpdateReview(id, data) {
      return await api('reviews/' + id, 'PUT', data, true);
    },

    // ── 주문 ─────────────────────────────────────────────────
    async fetchOrders(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('orders' + qs, 'GET', null, true);
      if (res?.success) { _cache.orders = res.data; dispatch('tt_orders', res.data); }
      return res;
    },

    async fetchMyOrders(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('orders/my' + qs, 'GET', null, true);
      return res?.success ? res.data : [];
    },

    async createOrder(data) {
      return await api('orders', 'POST', data);
    },

    async updateOrderStatus(id, status) {
      return await api(`orders/${id}/status`, 'PUT', { status }, true);
    },

    // ── 예약 ─────────────────────────────────────────────────
    async fetchReservations(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('reservations' + qs, 'GET', null, true);
      if (res?.success) { _cache.reservations = res.data; dispatch('tt_reservations', res.data); }
      return res;
    },

    async fetchMyReservations() {
      const res = await api('reservations/my', 'GET', null, true);
      return res?.success ? res.data : [];
    },

    async createReservation(data) {
      return await api('reservations', 'POST', data);
    },

    async updateReservationStatus(id, status) {
      return await api(`reservations/${id}/status`, 'PUT', { status }, true);
    },

    // ── 회원 관리 ─────────────────────────────────────────────
    async fetchUsers(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('users' + qs, 'GET', null, true);
      if (res?.success) { _cache.users = res.data; dispatch('tt_users', res.data); }
      return res;
    },

    async updateUser(id, data) {
      return await api('users/' + id, 'PUT', data, true);
    },

    // ── 이벤트 ───────────────────────────────────────────────
    async fetchEvents(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('events' + qs);
      if (res?.success) { _cache.events = res.data; dispatch('tt_events', res.data); }
      return res;
    },

    async getActiveEvents() {
      return await this.fetchEvents({ active: 1 });
    },

    async adminSaveEvent(data) {
      const res = data.id
        ? await api('events/' + data.id, 'PUT', data, true)
        : await api('events', 'POST', data, true);
      _cache.events = null;
      return res;
    },

    async adminDeleteEvent(id) {
      return await api('events/' + id, 'DELETE', null, true);
    },

    // ── 공지사항 ──────────────────────────────────────────────
    async fetchNotices(params = {}) {
      const qs  = Object.keys(params).length ? '?' + new URLSearchParams(params).toString() : '';
      const res = await api('notices' + qs);
      if (res?.success) { _cache.notices = res.data; dispatch('tt_notices', res.data); }
      return res;
    },

    async adminSaveNotice(data) {
      const res = data.id
        ? await api('notices/' + data.id, 'PUT', data, true)
        : await api('notices', 'POST', data, true);
      _cache.notices = null;
      return res;
    },

    async adminDeleteNotice(id) {
      return await api('notices/' + id, 'DELETE', null, true);
    },

    // ── 쿠폰 ─────────────────────────────────────────────────
    async fetchCoupons() {
      const res = await api('coupons');
      return res?.success ? res.data : [];
    },

    async fetchAllCoupons() {
      const res = await api('coupons/all', 'GET', null, true);
      return res?.success ? res.data : [];
    },

    async validateCoupon(code, amount) {
      return await api('coupons/validate', 'POST', { code, amount });
    },

    async adminSaveCoupon(data) {
      const res = data.id
        ? await api('coupons/' + data.id, 'PUT', data, true)
        : await api('coupons', 'POST', data, true);
      return res;
    },

    async adminDeleteCoupon(id) {
      return await api('coupons/' + id, 'DELETE', null, true);
    },

    // ── FAQ ───────────────────────────────────────────────────
    async fetchFaqs() {
      const res = await api('faqs');
      return res?.success ? res.data : [];
    },

    async adminSaveFaq(data) {
      return data.id
        ? await api('faqs/' + data.id, 'PUT', data, true)
        : await api('faqs', 'POST', data, true);
    },

    async adminDeleteFaq(id) {
      return await api('faqs/' + id, 'DELETE', null, true);
    },

    // ── 매장 ─────────────────────────────────────────────────
    async fetchStores() {
      const res = await api('stores');
      return res?.success ? res.data : [];
    },

    async adminSaveStore(data) {
      return data.id
        ? await api('stores/' + data.id, 'PUT', data, true)
        : await api('stores', 'POST', data, true);
    },

    // ── 설정 ─────────────────────────────────────────────────
    async fetchSettings() {
      const res = await api('settings');
      if (res?.success) _cache.settings = res.data;
      return res?.success ? res.data : {};
    },

    async adminSaveSettings(data) {
      return await api('settings', 'POST', data, true);
    },

    getSettingsCache() { return _cache.settings || {}; },

    // ── 통계 (어드민 대시보드) ───────────────────────────────
    async fetchDashboard() {
      const res = await api('stats/dashboard', 'GET', null, true);
      return res?.success ? res.data : null;
    },

    // ── 장바구니 (로컬스토리지) ──────────────────────────────
    getCart() {
      try { return JSON.parse(localStorage.getItem('tt_cart')||'[]'); } catch { return []; }
    },
    saveCart(cart) {
      localStorage.setItem('tt_cart', JSON.stringify(cart));
      dispatch('tt_cart', cart);
      this.updateCartBadge();
    },
    addToCart(product, qty = 1) {
      const cart = this.getCart();
      const idx  = cart.findIndex(i => i.id === product.id);
      if (idx !== -1) cart[idx].qty = (cart[idx].qty||1) + qty;
      else cart.push({ ...product, qty });
      this.saveCart(cart);
    },
    removeFromCart(productId) {
      this.saveCart(this.getCart().filter(i => i.id !== productId));
    },
    getCartCount() {
      return this.getCart().reduce((s,i) => s + (i.qty||1), 0);
    },
    clearCart() {
      localStorage.removeItem('tt_cart');
      dispatch('tt_cart', []);
      this.updateCartBadge();
    },
    updateCartBadge() {
      const cnt   = this.getCartCount();
      const badge = document.querySelector('.cart-count,.cart-badge,[data-cart-count]');
      if (badge) badge.textContent = cnt;
    },

    // ── 초기화 ───────────────────────────────────────────────
    async init() {
      try {
        await Promise.all([
          this.fetchProducts({ active: 1, limit: 50 }),
          this.fetchEvents({ active: 1 }),
          this.fetchNotices({ active: 1 }),
          this.fetchSettings(),
        ]);
      } catch (e) { console.warn('[TTStore] init 일부 실패:', e.message); }
      this.updateCartBadge();
      this.updateAuthUI();
    },

    async initAdmin() {
      if (!Auth.isLoggedIn()) { window.location.href = '/admin/login.html'; return; }
      if (!Auth.isAdmin()) { alert('관리자 권한이 필요합니다'); window.location.href = '/'; return; }
      try { await this.fetchProducts({ limit: 200 }); }
      catch (e) { console.warn('[TTStore] initAdmin 실패:', e.message); }
    },

    // ── 로그인 상태 UI 업데이트 ───────────────────────────────
    updateAuthUI() {
      const user   = Auth.getUser();
      const logged = !!user;
      document.querySelectorAll('[data-auth-logged]').forEach(el => {
        el.style.display = logged ? '' : 'none';
      });
      document.querySelectorAll('[data-auth-guest]').forEach(el => {
        el.style.display = logged ? 'none' : '';
      });
      document.querySelectorAll('[data-auth-name]').forEach(el => {
        el.textContent = user ? user.name : '';
      });
      const cartBadge = document.querySelector('.cart-count,.cart-badge');
      if (cartBadge) cartBadge.textContent = this.getCartCount();
    },
  };

  global.TTStore = TTStore;
  global.TTAuth  = Auth;

})(window);
