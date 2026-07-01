-- ============================================================
-- TIRETOP - 초기 시드 데이터
-- ============================================================

-- 기본 설정
INSERT OR IGNORE INTO settings (key, value) VALUES
  ('site_name', 'TIRETOP'),
  ('site_tagline', '차량관리의 새로운 기준'),
  ('phone', '080-000-1234'),
  ('email', 'support@tiretop.co.kr'),
  ('free_shipping_amount', '0'),
  ('point_rate', '1'),
  ('kakao_channel', '');

-- 기본 매장
INSERT OR IGNORE INTO stores (name, address, phone, open_hours, is_active) VALUES
  ('TIRETOP 강남점', '서울특별시 강남구 테헤란로 123', '02-1234-5678', '평일 09:00~18:00 / 토요일 09:00~15:00', 1),
  ('TIRETOP 송파점', '서울특별시 송파구 올림픽로 456', '02-2345-6789', '평일 09:00~18:00 / 토요일 09:00~15:00', 1),
  ('TIRETOP 마포점', '서울특별시 마포구 마포대로 789', '02-3456-7890', '평일 09:00~18:00', 1);

-- 기본 쿠폰
INSERT OR IGNORE INTO coupons (code, name, type, value, min_order, is_active) VALUES
  ('WELCOME10', '신규회원 10% 할인', 'percent', 10, 50000, 1),
  ('FIRST5000', '첫 구매 5000원 할인', 'amount', 5000, 30000, 1);

-- 기본 FAQ
INSERT OR IGNORE INTO faqs (category, question, answer, sort_order) VALUES
  ('delivery', '배송은 얼마나 걸리나요?', '주문 확인 후 1-2 영업일 내 출고되며, 택배 배송은 2-3일 소요됩니다.', 1),
  ('install', '타이어 장착은 어떻게 하나요?', '가까운 TIRETOP 매장을 방문하시거나 장착 예약 후 방문하시면 전문 기사가 장착해드립니다.', 2),
  ('return', '교환/환불은 어떻게 하나요?', '미장착 타이어는 수령 후 7일 이내 교환/환불이 가능합니다. 고객센터로 연락 주세요.', 3),
  ('warranty', '품질보증은 어떻게 되나요?', '모든 타이어는 정품 품질보증서가 제공되며, 제조사 품질보증 정책을 따릅니다.', 4);

-- 관리자 계정 (비밀번호: admin1234 → bcrypt 대신 sha256으로 처리)
INSERT OR IGNORE INTO users (email, phone, name, password, grade, status, role, points) VALUES
  ('admin@tiretop.co.kr', '010-0000-0000', '관리자', 'admin_hashed_password_placeholder', 'vip', 'active', 'admin', 0);

-- 초기 공지사항
INSERT OR IGNORE INTO notices (title, content, category, is_pinned) VALUES
  ('TIRETOP 서비스 오픈 안내', 'TIRETOP 타이어 전문몰이 오픈하였습니다. 다양한 타이어 브랜드와 합리적인 가격으로 찾아뵙겠습니다.', 'general', 1),
  ('장착 예약 서비스 안내', '온라인으로 간편하게 장착 예약이 가능합니다. 원하는 날짜와 시간을 선택하여 편리하게 이용하세요.', 'service', 0);

-- ── 상품 데이터 (products.js에서 주요 제품 삽입) ──────────────

-- 금호 타이어
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-001', '금호', 'kumho', '솔루스 TA21', '245/45R18', 95060, 198000, 52, 4.8, 199, NULL, '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 0, 0),
('tire-002', '금호', 'kumho', '솔루스 TA21', '225/55R17', 88270, 166500, 47, 4.8, 199, NULL, '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 0, 0),
('tire-003', '금호', 'kumho', '솔루스 TA21', '215/55R17', 71780, 149500, 52, 4.8, 199, NULL, '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 0, 0),
('tire-004', '금호', 'kumho', '솔루스 TA21', '225/45R17', 89240, 168300, 47, 4.8, 199, NULL, '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 0, 0),
('tire-005', '금호', 'kumho', '솔루스 TA21', '205/60R16', 82450, 164900, 50, 4.8, 199, 'BEST', '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 1, 1),
('tire-006', '금호', 'kumho', '솔루스 TA21', '165/60R14', 44620, 92900, 52, 4.8, 199, NULL, '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/solus-ta21.png', '사계절', '승용차', '고급형', 1, 0, 0),
('tire-009', '금호', 'kumho', '크루젠 KL33', '235/55R19', 116400, 215500, 46, 4.7, 1691, NULL, '["SUV","사계절용","가성비"]', 'tire', 'https://images.kumhousa.com/products/crugen-kl33.png', '사계절', 'SUV', '가성비', 1, 0, 0),
('tire-010', '금호', 'kumho', '크루젠 HP71', '225/55R18', 119310, 198850, 40, 4.7, 3088, 'BEST', '["SUV","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/crugen-hp71.png', '사계절', 'SUV', '고급형', 1, 1, 1),
('tire-012', '금호', 'kumho', '마제스티9 솔루스 TA91', '215/55R17', 107670, 203100, 47, 4.8, 2555, 'HOT', '["승용차","사계절용","고급형"]', 'tire', 'https://images.kumhousa.com/products/majesty9-ta91.png', '사계절', '승용차', '고급형', 1, 0, 1);

-- 한국타이어
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-hankook-01', '한국타이어', 'hankook', '벤투스 S2 AS', '225/45R18', 121000, 220000, 45, 4.7, 3120, 'BEST', '["승용차","사계절용","고급형"]', 'tire', 'https://www.hankooktire.com/global/content/dam/hankook/shared/products/ventus-s2-as.png', '사계절', '승용차', '고급형', 1, 1, 1),
('tire-hankook-02', '한국타이어', 'hankook', '다이나프로 HT2', '235/65R17', 98000, 178000, 45, 4.6, 2340, 'BEST', '["SUV","사계절용","고급형"]', 'tire', 'https://www.hankooktire.com/global/content/dam/hankook/shared/products/dynapro-ht2.png', '사계절', 'SUV', '고급형', 1, 1, 1),
('tire-hankook-03', '한국타이어', 'hankook', '키너지4S2', '205/55R16', 89000, 159000, 44, 4.7, 1870, 'HOT', '["승용차","사계절용","고급형"]', 'tire', 'https://www.hankooktire.com/global/content/dam/hankook/shared/products/kinergy-4s2.png', '사계절', '승용차', '고급형', 1, 0, 1),
('tire-hankook-04', '한국타이어', 'hankook', '아이온GT', '255/45R20', 198000, 340000, 42, 4.8, 892, 'NEW', '["승용차","사계절용","프리미엄"]', 'tire', 'https://www.hankooktire.com/global/content/dam/hankook/shared/products/iON-GT.png', '사계절', '승용차', '프리미엄', 1, 0, 1);

-- 넥센타이어
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-nexen-01', '넥센', 'nexen', '엔페라 AU7', '225/45R17', 71500, 132000, 46, 4.5, 1203, 'BEST', '["승용차","사계절용","가성비"]', 'tire', 'https://www.nexentire.com/upload/product/nfera-au7.png', '사계절', '승용차', '가성비', 1, 1, 0),
('tire-nexen-02', '넥센', 'nexen', '로디안 ATX', '265/70R16', 82000, 148000, 45, 4.5, 987, NULL, '["SUV","사계절용","가성비"]', 'tire', 'https://www.nexentire.com/upload/product/roadian-atx.png', '사계절', 'SUV', '가성비', 1, 0, 0);

-- 브리지스톤
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-bridgestone-01', '브리지스톤', 'bridgestone', '투란자 T005', '225/55R17', 168000, 248000, 32, 4.8, 4532, 'BEST', '["승용차","사계절용","프리미엄"]', 'tire', 'https://www.bridgestonetire.com/content/dam/bridgestone/products/turanza-t005.png', '사계절', '승용차', '프리미엄', 1, 1, 1),
('tire-bridgestone-02', '브리지스톤', 'bridgestone', '블리작 VRX2', '215/60R16', 142000, 210000, 32, 4.7, 2103, 'HOT', '["승용차","겨울용","프리미엄"]', 'tire', 'https://www.bridgestonetire.com/content/dam/bridgestone/products/blizzak-vrx2.png', '겨울', '승용차', '프리미엄', 1, 0, 1);

-- 미쉐린
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-michelin-01', '미쉐린', 'michelin', '파일럿 스포츠 4', '245/40R18', 242000, 340000, 29, 4.9, 5812, 'BEST', '["승용차","여름용","프리미엄"]', 'tire', 'https://www.michelin.com/content/dam/michelin/products/pilot-sport-4.png', '여름', '승용차', '프리미엄', 1, 1, 1),
('tire-michelin-02', '미쉐린', 'michelin', '크로스클라이밋 2', '205/55R16', 198000, 268000, 26, 4.8, 3241, 'HOT', '["승용차","사계절용","프리미엄"]', 'tire', 'https://www.michelin.com/content/dam/michelin/products/crossclimate-2.png', '사계절', '승용차', '프리미엄', 1, 0, 1);

-- 콘티넨탈
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-continental-01', '콘티넨탈', 'continental', '컨티프리미엄컨택7', '215/55R17', 178000, 258000, 31, 4.8, 2987, 'BEST', '["승용차","사계절용","프리미엄"]', 'tire', 'https://www.continental-tires.com/content/dam/continental/tires/premiumcontact-7.png', '사계절', '승용차', '프리미엄', 1, 1, 1);

-- 피렐리
INSERT OR IGNORE INTO products (product_id, brand, brand_key, name, size, price, original_price, discount, rating, review_count, badge, tags, category, image, season, vehicle_type, grade, is_active, is_best, is_featured) VALUES
('tire-pirelli-01', '피렐리', 'pirelli', 'P ZERO', '245/35R20', 312000, 450000, 31, 4.8, 1823, 'HOT', '["승용차","여름용","프리미엄"]', 'tire', 'https://www.pirelli.com/tyres/en-us/auto/tyre-detail/p-zero.html', '여름', '승용차', '프리미엄', 1, 0, 1),
('tire-pirelli-02', '피렐리', 'pirelli', '신투라토 P7', '225/50R17', 198000, 288000, 31, 4.7, 1456, 'BEST', '["승용차","사계절용","프리미엄"]', 'tire', 'https://www.pirelli.com/tyres/en-us/auto/tyre-detail/cinturato-p7.html', '사계절', '승용차', '프리미엄', 1, 1, 0);

-- 샘플 리뷰
INSERT OR IGNORE INTO reviews (product_id, author_name, rating, content, ride_comfort, noise, wet_grip, durability, is_verified, status) VALUES
(1, '김*현', 5, '정말 만족스럽습니다. 승차감이 부드럽고 소음도 적어요. 재구매 의사 100%입니다.', 5, 4, 5, 5, 1, 'active'),
(1, '이*준', 4, '가격 대비 성능이 훌륭합니다. 빗길에서도 안정적이에요.', 4, 4, 5, 4, 1, 'active'),
(1, '박*영', 5, '한국타이어 쓰다가 갈아탔는데 금호가 더 마음에 들어요.', 5, 5, 4, 5, 1, 'active'),
(2, '최*수', 4, '연비도 좋아지고 승차감도 좋습니다. 추천합니다.', 4, 4, 4, 4, 1, 'active');
