/* ============================================================
   js/store.js  – TIRETOP 전역 데이터 스토어
   모든 어드민 페이지와 메인 페이지가 이 파일 하나를 통해
   localStorage 를 공유합니다.
   ============================================================ */
(function (global) {
  'use strict';

  /* ── 기본 CRUD ── */
  const TTStore = {

    /* 원시 get/set */
    get(key) {
      try { return JSON.parse(localStorage.getItem(key)) || null; }
      catch(e) { return null; }
    },
    set(key, data) {
      try {
        localStorage.setItem(key, JSON.stringify(data));
        this._dispatch(key, data);
      } catch(e) { console.warn('TTStore.set 실패:', e); }
    },

    /* 이벤트 디스패치 – 같은 탭 + 다른 탭 모두 반영 */
    _dispatch(key, data) {
      window.dispatchEvent(
        new CustomEvent('tt_store_update', { detail: { key, data } })
      );
    },

    /* ── 공통 헬퍼 ── */
    _getArr(key)       { return this.get(key) || []; },
    _setArr(key, arr)  { this.set(key, arr); },

    /** 배열에서 id 기준 upsert */
    _upsert(key, item) {
      const arr = this._getArr(key);
      const idx = arr.findIndex(x => x.id === item.id);
      if (idx >= 0) arr[idx] = { ...arr[idx], ...item };
      else arr.unshift(item);
      this._setArr(key, arr);
    },

    /** 배열에서 id 기준 삭제 */
    remove(key, id) {
      const arr = this._getArr(key).filter(x => x.id !== id);
      this._setArr(key, arr);
    },

    /** 배열에서 id 기준 부분 업데이트 */
    update(key, id, patch) {
      const arr = this._getArr(key);
      const idx = arr.findIndex(x => x.id === id);
      if (idx >= 0) arr[idx] = { ...arr[idx], ...patch };
      this._setArr(key, arr);
    },

    /* ════════════════════════════════════════
       상품 (tt_products)
    ════════════════════════════════════════ */
    adminSaveProduct(data) { this._upsert('tt_products', data); },
    adminDeleteProduct(id) { this.remove('tt_products', id); },

    /** 활성 상품만 반환 */
    getActiveProducts() {
      return this._getArr('tt_products')
        .filter(p => p.active !== false);
    },

    /** id로 단건 조회 */
    getProduct(id) {
      return this._getArr('tt_products').find(p => p.id === id) || null;
    },

    /* ════════════════════════════════════════
       주문 (tt_orders)
    ════════════════════════════════════════ */
    adminUpdateOrderStatus(id, status, extra = {}) {
      this.update('tt_orders', id, { status, ...extra });
    },

    /* ════════════════════════════════════════
       예약 (tt_reservations)
    ════════════════════════════════════════ */
    adminSaveReservation(data) { this._upsert('tt_reservations', data); },

    /* ════════════════════════════════════════
       매장 (tt_stores)
    ════════════════════════════════════════ */
    adminSaveStore(data) { this._upsert('tt_stores', data); },

    /* ════════════════════════════════════════
       이벤트 (tt_events)
    ════════════════════════════════════════ */
    adminSaveEvent(data) { this._upsert('tt_events', data); },
    getActiveEvents() {
      return this._getArr('tt_events').filter(e => e.active !== false);
    },

    /* ════════════════════════════════════════
       쿠폰 (tt_coupons)
    ════════════════════════════════════════ */
    adminSaveCoupon(data) { this._upsert('tt_coupons', data); },

    /* ════════════════════════════════════════
       리뷰 (tt_reviews)
    ════════════════════════════════════════ */
    adminSaveReview(data) { this._upsert('tt_reviews', data); },
    getPublicReviews() {
      return this._getArr('tt_reviews')
        .filter(r => r.status === 'normal' || !r.status);
    },

    /* ════════════════════════════════════════
       FAQ (tt_faqs)
    ════════════════════════════════════════ */
    adminSaveFaq(data) { this._upsert('tt_faqs', data); },

    /* ════════════════════════════════════════
       공지사항 (tt_notices)
    ════════════════════════════════════════ */
    adminSaveNotice(data) { this._upsert('tt_notices', data); },
    getNotices(limit = 10) {
      return this._getArr('tt_notices')
        .sort((a, b) => {
          if (a.pinned && !b.pinned) return -1;
          if (!a.pinned && b.pinned) return 1;
          return b.date?.localeCompare(a.date) || 0;
        })
        .slice(0, limit);
    },

    /* ════════════════════════════════════════
       재고 (tt_inventory)
    ════════════════════════════════════════ */
    adminSaveInventory(data) { this._upsert('tt_inventory', data); },

    /* ════════════════════════════════════════
       장바구니 (tt_cart)
    ════════════════════════════════════════ */
    addToCart(product, qty = 1) {
      const cart = this._getArr('tt_cart');
      const idx  = cart.findIndex(i => i.id === product.id);
      if (idx >= 0) cart[idx].qty = (cart[idx].qty || 1) + qty;
      else cart.push({ ...product, qty });
      this._setArr('tt_cart', cart);
    },
    getCartCount() {
      return this._getArr('tt_cart').reduce((s, i) => s + (i.qty || 1), 0);
    },
    clearCart() { this._setArr('tt_cart', []); },

    /* ════════════════════════════════════════
       사이트 설정 (tt_settings)
    ════════════════════════════════════════ */
    adminSaveSettings(data) {
      const cur = this.get('tt_settings') || {};
      this.set('tt_settings', { ...cur, ...data });
    },
    getSettings() {
      return this.get('tt_settings') || {
        siteName:          'TIRETOP',
        siteSlogan:        '대한민국 1등 타이어 전문점',
        sitePhone:         '1588-0000',
        mainProductCount:  8,
        showHero:          true,
        showEventBanner:   true,
        showReviewSection: true,
        allowCoupon:       true,
        allowOutOfStock:   false,
        enablePoint:       true,
        pointRate:         1,
        freeDelivery:      true,
        freeDeliveryAmt:   50000,
        baseDeliveryFee:   3000,
      };
    },
  };

  /* localStorage 변경 이벤트 (다른 탭 → 현재 탭 반영) */
  window.addEventListener('storage', (e) => {
    if (e.key && e.newValue) {
      TTStore._dispatch(e.key, JSON.parse(e.newValue));
    }
  });

  global.TTStore = TTStore;
})(window);
