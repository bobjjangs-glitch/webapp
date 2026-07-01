-- ============================================================
-- TIRETOP - 초기 DB 스키마
-- ============================================================

-- 1. 회원 테이블
CREATE TABLE IF NOT EXISTS users (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  email        TEXT    UNIQUE NOT NULL,
  phone        TEXT    UNIQUE,
  name         TEXT    NOT NULL,
  password     TEXT    NOT NULL,
  grade        TEXT    NOT NULL DEFAULT 'normal',  -- normal/bronze/silver/gold/vip
  status       TEXT    NOT NULL DEFAULT 'active',  -- active/inactive/banned
  birth_date   TEXT,
  gender       TEXT,
  address      TEXT,
  address_detail TEXT,
  zipcode      TEXT,
  vehicle_number TEXT,
  points       INTEGER NOT NULL DEFAULT 0,
  total_spend  INTEGER NOT NULL DEFAULT 0,
  order_count  INTEGER NOT NULL DEFAULT 0,
  marketing_agree INTEGER NOT NULL DEFAULT 0,
  role         TEXT    NOT NULL DEFAULT 'user',    -- user/admin
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login   DATETIME
);

-- 2. 세션 테이블 (JWT 블랙리스트 / 리프레시 토큰)
CREATE TABLE IF NOT EXISTS sessions (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id      INTEGER NOT NULL,
  token        TEXT    NOT NULL UNIQUE,
  expires_at   DATETIME NOT NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. 상품 테이블
CREATE TABLE IF NOT EXISTS products (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id     TEXT    UNIQUE NOT NULL,  -- tire-001 형식
  brand          TEXT    NOT NULL,
  brand_key      TEXT    NOT NULL,
  name           TEXT    NOT NULL,
  size           TEXT,
  price          INTEGER NOT NULL,
  original_price INTEGER NOT NULL,
  discount       INTEGER NOT NULL DEFAULT 0,
  rating         REAL    NOT NULL DEFAULT 0,
  review_count   INTEGER NOT NULL DEFAULT 0,
  stock          INTEGER NOT NULL DEFAULT 100,
  badge          TEXT,
  tags           TEXT,                     -- JSON 배열
  category       TEXT    NOT NULL DEFAULT 'tire',
  image          TEXT,
  season         TEXT,
  vehicle_type   TEXT,
  grade          TEXT,
  description    TEXT,
  specs          TEXT,                     -- JSON 객체 (규격 정보)
  is_active      INTEGER NOT NULL DEFAULT 1,
  is_featured    INTEGER NOT NULL DEFAULT 0,
  is_best        INTEGER NOT NULL DEFAULT 0,
  sort_order     INTEGER NOT NULL DEFAULT 0,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. 상품 이미지 테이블
CREATE TABLE IF NOT EXISTS product_images (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id INTEGER NOT NULL,
  url        TEXT    NOT NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 5. 주문 테이블
CREATE TABLE IF NOT EXISTS orders (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  order_number   TEXT    UNIQUE NOT NULL,
  user_id        INTEGER,
  customer_name  TEXT    NOT NULL,
  customer_phone TEXT    NOT NULL,
  customer_email TEXT,
  status         TEXT    NOT NULL DEFAULT 'pending',  -- pending/confirmed/shipping/delivered/cancelled/refunded
  payment_method TEXT    NOT NULL DEFAULT 'card',
  payment_status TEXT    NOT NULL DEFAULT 'pending',  -- pending/paid/failed/refunded
  subtotal       INTEGER NOT NULL DEFAULT 0,
  discount_amount INTEGER NOT NULL DEFAULT 0,
  point_used     INTEGER NOT NULL DEFAULT 0,
  total_amount   INTEGER NOT NULL DEFAULT 0,
  shipping_address TEXT,
  shipping_zipcode TEXT,
  memo           TEXT,
  coupon_id      INTEGER,
  store_id       INTEGER,
  reservation_id INTEGER,
  paid_at        DATETIME,
  shipped_at     DATETIME,
  delivered_at   DATETIME,
  cancelled_at   DATETIME,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 6. 주문 상품 테이블
CREATE TABLE IF NOT EXISTS order_items (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id     INTEGER NOT NULL,
  product_id   INTEGER NOT NULL,
  product_name TEXT    NOT NULL,
  product_size TEXT,
  brand        TEXT,
  price        INTEGER NOT NULL,
  qty          INTEGER NOT NULL DEFAULT 1,
  subtotal     INTEGER NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- 7. 리뷰 테이블
CREATE TABLE IF NOT EXISTS reviews (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id   INTEGER NOT NULL,
  user_id      INTEGER,
  order_id     INTEGER,
  author_name  TEXT    NOT NULL,
  rating       INTEGER NOT NULL DEFAULT 5,
  content      TEXT    NOT NULL,
  image_url    TEXT,
  ride_comfort INTEGER,
  noise        INTEGER,
  wet_grip     INTEGER,
  durability   INTEGER,
  is_verified  INTEGER NOT NULL DEFAULT 0,
  status       TEXT    NOT NULL DEFAULT 'active',  -- active/hidden/deleted
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
);

-- 8. 예약 테이블 (장착 예약)
CREATE TABLE IF NOT EXISTS reservations (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  reservation_number TEXT UNIQUE NOT NULL,
  user_id        INTEGER,
  customer_name  TEXT    NOT NULL,
  customer_phone TEXT    NOT NULL,
  vehicle_number TEXT,
  vehicle_model  TEXT,
  product_id     INTEGER,
  product_name   TEXT,
  qty            INTEGER NOT NULL DEFAULT 4,
  store_id       INTEGER,
  store_name     TEXT,
  reserved_date  TEXT    NOT NULL,
  reserved_time  TEXT    NOT NULL,
  status         TEXT    NOT NULL DEFAULT 'pending',  -- pending/confirmed/completed/cancelled
  memo           TEXT,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 9. 쿠폰 테이블
CREATE TABLE IF NOT EXISTS coupons (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  code           TEXT    UNIQUE NOT NULL,
  name           TEXT    NOT NULL,
  type           TEXT    NOT NULL DEFAULT 'percent',  -- percent/amount
  value          INTEGER NOT NULL,
  min_order      INTEGER NOT NULL DEFAULT 0,
  max_discount   INTEGER,
  total_count    INTEGER,
  used_count     INTEGER NOT NULL DEFAULT 0,
  is_active      INTEGER NOT NULL DEFAULT 1,
  starts_at      DATETIME,
  expires_at     DATETIME,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 10. 쿠폰 사용 내역
CREATE TABLE IF NOT EXISTS coupon_uses (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  coupon_id INTEGER NOT NULL,
  user_id   INTEGER NOT NULL,
  order_id  INTEGER,
  used_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- 11. 매장 테이블
CREATE TABLE IF NOT EXISTS stores (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  name        TEXT    NOT NULL,
  address     TEXT    NOT NULL,
  phone       TEXT,
  open_hours  TEXT,
  lat         REAL,
  lng         REAL,
  is_active   INTEGER NOT NULL DEFAULT 1,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 12. 이벤트/기획전 테이블
CREATE TABLE IF NOT EXISTS events (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  title       TEXT    NOT NULL,
  description TEXT,
  image_url   TEXT,
  link_url    TEXT,
  starts_at   DATETIME,
  ends_at     DATETIME,
  is_active   INTEGER NOT NULL DEFAULT 1,
  sort_order  INTEGER NOT NULL DEFAULT 0,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 13. 공지사항
CREATE TABLE IF NOT EXISTS notices (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  title      TEXT    NOT NULL,
  content    TEXT    NOT NULL,
  category   TEXT    NOT NULL DEFAULT 'general',
  is_pinned  INTEGER NOT NULL DEFAULT 0,
  is_active  INTEGER NOT NULL DEFAULT 1,
  view_count INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 14. FAQ
CREATE TABLE IF NOT EXISTS faqs (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  category   TEXT    NOT NULL DEFAULT 'general',
  question   TEXT    NOT NULL,
  answer     TEXT    NOT NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active  INTEGER NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 15. 설정
CREATE TABLE IF NOT EXISTS settings (
  key        TEXT    PRIMARY KEY,
  value      TEXT    NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ── 인덱스 ──────────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_users_email   ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_phone   ON users(phone);
CREATE INDEX IF NOT EXISTS idx_products_brand ON products(brand_key);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active);
CREATE INDEX IF NOT EXISTS idx_orders_user   ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_reviews_product ON reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_reservations_date ON reservations(reserved_date);
CREATE INDEX IF NOT EXISTS idx_sessions_token ON sessions(token);
CREATE INDEX IF NOT EXISTS idx_sessions_user ON sessions(user_id);
