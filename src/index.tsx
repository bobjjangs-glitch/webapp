import { Hono } from 'hono'
import { cors } from 'hono/cors'

// ============================================================
// TIRETOP - 타이어 전문몰 백엔드 API
// Cloudflare Pages + Hono + D1
// ============================================================

type Bindings = {
  DB: D1Database
}

const app = new Hono<{ Bindings: Bindings }>()

// ── CORS ────────────────────────────────────────────────────
app.use('/api/*', cors({
  origin: '*',
  allowMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowHeaders: ['Content-Type', 'Authorization'],
}))

// ── 유틸: 응답 헬퍼 ─────────────────────────────────────────
const ok  = (c: any, data: any, meta?: any) =>
  c.json({ success: true,  data, ...(meta || {}) })
const err = (c: any, msg: string, code = 400) =>
  c.json({ success: false, message: msg }, code)

// ── 유틸: JWT (HMAC-SHA256, 유니코드 안전 Base64URL) ─────────
const JWT_SECRET = 'tiretop-jwt-secret-2024-please-change-in-production'

// Base64URL 인코딩 (유니코드/한글 안전)
function b64urlEncode(str: string): string {
  const bytes = new TextEncoder().encode(str)
  let bin = ''
  bytes.forEach(b => { bin += String.fromCharCode(b) })
  return btoa(bin).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'')
}

// Base64URL 디코딩 (유니코드/한글 안전)
function b64urlDecode(str: string): string {
  const b64 = str.replace(/-/g,'+').replace(/_/g,'/')
  const bin = atob(b64.padEnd(b64.length + (4 - b64.length % 4) % 4, '='))
  const bytes = Uint8Array.from(bin, c => c.charCodeAt(0))
  return new TextDecoder().decode(bytes)
}

async function signJWT(payload: any): Promise<string> {
  const header = b64urlEncode(JSON.stringify({ alg: 'HS256', typ: 'JWT' }))
  const body   = b64urlEncode(JSON.stringify({ ...payload, iat: Date.now() }))
  const data   = `${header}.${body}`
  const key    = await crypto.subtle.importKey(
    'raw', new TextEncoder().encode(JWT_SECRET),
    { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
  )
  const sig    = await crypto.subtle.sign('HMAC', key, new TextEncoder().encode(data))
  const sigB64 = btoa(String.fromCharCode(...new Uint8Array(sig)))
    .replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'')
  return `${data}.${sigB64}`
}

async function verifyJWT(token: string): Promise<any | null> {
  try {
    const parts = token.split('.')
    if (parts.length !== 3) return null
    const [header, body, sig] = parts
    const data   = `${header}.${body}`
    const key    = await crypto.subtle.importKey(
      'raw', new TextEncoder().encode(JWT_SECRET),
      { name: 'HMAC', hash: 'SHA-256' }, false, ['verify']
    )
    const sigB64 = sig.replace(/-/g,'+').replace(/_/g,'/')
    const sigBuf = Uint8Array.from(atob(sigB64.padEnd(sigB64.length + (4 - sigB64.length % 4) % 4, '=')), c => c.charCodeAt(0))
    const valid  = await crypto.subtle.verify('HMAC', key, sigBuf, new TextEncoder().encode(data))
    if (!valid) return null
    const payload = JSON.parse(b64urlDecode(body))
    if (payload.exp && payload.exp < Date.now()) return null
    return payload
  } catch {
    return null
  }
}

// ── 유틸: 패스워드 해싱 (SHA-256 + salt) ────────────────────
async function hashPassword(password: string): Promise<string> {
  const salt   = crypto.randomUUID()
  const data   = new TextEncoder().encode(salt + password)
  const hash   = await crypto.subtle.digest('SHA-256', data)
  const hashHex = Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2,'0')).join('')
  return `${salt}:${hashHex}`
}

async function verifyPassword(password: string, stored: string): Promise<boolean> {
  try {
    const [salt, hash] = stored.split(':')
    if (!salt || !hash) return false
    const data    = new TextEncoder().encode(salt + password)
    const newHash = await crypto.subtle.digest('SHA-256', data)
    const newHex  = Array.from(new Uint8Array(newHash)).map(b => b.toString(16).padStart(2,'0')).join('')
    return hash === newHex
  } catch {
    return false
  }
}

// ── 미들웨어: 인증 체크 ──────────────────────────────────────
async function authMiddleware(c: any, next: any) {
  const auth = c.req.header('Authorization')
  if (!auth?.startsWith('Bearer ')) return err(c, '로그인이 필요합니다', 401)
  const token   = auth.slice(7)
  const payload = await verifyJWT(token)
  if (!payload) return err(c, '유효하지 않은 토큰입니다', 401)
  c.set('user', payload)
  await next()
}

async function adminMiddleware(c: any, next: any) {
  const auth = c.req.header('Authorization')
  if (!auth?.startsWith('Bearer ')) return err(c, '관리자 로그인이 필요합니다', 401)
  const token   = auth.slice(7)
  const payload = await verifyJWT(token)
  if (!payload) return err(c, '유효하지 않은 토큰입니다', 401)
  if (payload.role !== 'admin') return err(c, '관리자 권한이 필요합니다', 403)
  c.set('user', payload)
  await next()
}

// ── 유틸: 주문번호 생성 ──────────────────────────────────────
function generateOrderNumber(): string {
  const now  = new Date()
  const date = now.toISOString().slice(0,10).replace(/-/g,'')
  const rand = Math.floor(Math.random() * 10000).toString().padStart(4,'0')
  return `TT${date}${rand}`
}

function generateReservationNumber(): string {
  const now  = new Date()
  const date = now.toISOString().slice(0,10).replace(/-/g,'')
  const rand = Math.floor(Math.random() * 10000).toString().padStart(4,'0')
  return `RV${date}${rand}`
}

// ============================================================
// 헬스체크
// ============================================================
app.get('/api/health', (c) =>
  c.json({ status: 'ok', service: 'TIRETOP', version: '2.0.0', timestamp: new Date().toISOString() })
)

// ============================================================
// 인증 API
// ============================================================

// 회원가입
app.post('/api/auth/register', async (c) => {
  try {
    const { email, phone, name, password, birth_date, gender, marketing_agree } = await c.req.json()
    if (!email || !name || !password) return err(c, '필수 항목을 입력해주세요')
    if (password.length < 6) return err(c, '비밀번호는 6자 이상이어야 합니다')

    const existing = await c.env.DB.prepare('SELECT id FROM users WHERE email=?').bind(email).first()
    if (existing) return err(c, '이미 사용 중인 이메일입니다')

    if (phone) {
      const existPhone = await c.env.DB.prepare('SELECT id FROM users WHERE phone=?').bind(phone).first()
      if (existPhone) return err(c, '이미 사용 중인 휴대폰 번호입니다')
    }

    const hashedPw = await hashPassword(password)
    const result   = await c.env.DB.prepare(`
      INSERT INTO users (email, phone, name, password, birth_date, gender, marketing_agree)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).bind(email, phone||null, name, hashedPw, birth_date||null, gender||null, marketing_agree?1:0).run()

    const userId = result.meta.last_row_id
    const token  = await signJWT({ id: userId, email, name, role: 'user', exp: Date.now() + 7*24*60*60*1000 })

    return ok(c, { token, user: { id: userId, email, name, role: 'user', grade: 'normal', points: 0 } })
  } catch (e: any) {
    return err(c, e.message || '회원가입 처리 중 오류가 발생했습니다', 500)
  }
})

// 로그인
app.post('/api/auth/login', async (c) => {
  try {
    const { email, password } = await c.req.json()
    if (!email || !password) return err(c, '이메일과 비밀번호를 입력해주세요')

    const user = await c.env.DB.prepare(
      'SELECT * FROM users WHERE email=? AND status != "banned"'
    ).bind(email).first() as any

    if (!user) return err(c, '이메일 또는 비밀번호가 올바르지 않습니다', 401)

    // 관리자 기본 비번 처리
    let valid = false
    if (user.password === 'admin_hashed_password_placeholder' && password === 'admin1234') {
      valid = true
      // 실제 해시로 업데이트
      const newHash = await hashPassword(password)
      await c.env.DB.prepare('UPDATE users SET password=? WHERE id=?').bind(newHash, user.id).run()
    } else {
      valid = await verifyPassword(password, user.password)
    }

    if (!valid) return err(c, '이메일 또는 비밀번호가 올바르지 않습니다', 401)

    await c.env.DB.prepare('UPDATE users SET last_login=CURRENT_TIMESTAMP WHERE id=?').bind(user.id).run()

    const token = await signJWT({
      id: user.id, email: user.email, name: user.name,
      role: user.role, exp: Date.now() + 7*24*60*60*1000
    })

    return ok(c, {
      token,
      user: {
        id: user.id, email: user.email, name: user.name,
        phone: user.phone, grade: user.grade, role: user.role,
        points: user.points, status: user.status
      }
    })
  } catch (e: any) {
    return err(c, e.message || '로그인 처리 중 오류가 발생했습니다', 500)
  }
})

// 토큰 검증 (내 정보 조회)
app.get('/api/auth/me', authMiddleware, async (c) => {
  const me   = c.get('user')
  const user = await c.env.DB.prepare(
    'SELECT id,email,name,phone,grade,role,points,status,birth_date,gender,vehicle_number,address,created_at FROM users WHERE id=?'
  ).bind(me.id).first()
  if (!user) return err(c, '사용자를 찾을 수 없습니다', 404)
  return ok(c, user)
})

// 내 정보 수정
app.put('/api/auth/me', authMiddleware, async (c) => {
  const me   = c.get('user')
  const body = await c.req.json()
  const { name, phone, birth_date, gender, address, address_detail, zipcode, vehicle_number } = body
  await c.env.DB.prepare(`
    UPDATE users SET name=?,phone=?,birth_date=?,gender=?,address=?,
    address_detail=?,zipcode=?,vehicle_number=?,updated_at=CURRENT_TIMESTAMP
    WHERE id=?
  `).bind(name,phone||null,birth_date||null,gender||null,address||null,
    address_detail||null,zipcode||null,vehicle_number||null,me.id).run()
  return ok(c, { message: '정보가 수정되었습니다' })
})

// 비밀번호 변경
app.put('/api/auth/password', authMiddleware, async (c) => {
  const me   = c.get('user')
  const { current_password, new_password } = await c.req.json()
  if (!current_password || !new_password) return err(c, '비밀번호를 입력해주세요')
  if (new_password.length < 6) return err(c, '새 비밀번호는 6자 이상이어야 합니다')
  const user = await c.env.DB.prepare('SELECT password FROM users WHERE id=?').bind(me.id).first() as any
  const valid = await verifyPassword(current_password, user.password)
  if (!valid) return err(c, '현재 비밀번호가 올바르지 않습니다', 401)
  const newHash = await hashPassword(new_password)
  await c.env.DB.prepare('UPDATE users SET password=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').bind(newHash,me.id).run()
  return ok(c, { message: '비밀번호가 변경되었습니다' })
})

// ============================================================
// 상품 API
// ============================================================

// 상품 목록 (필터링/정렬/페이징)
app.get('/api/products', async (c) => {
  const { brand, category, season, vehicle_type, grade, sort, page, limit, search, active, featured, best } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(100, parseInt(limit||'20'))
  const off = (pg-1)*lmt

  let where: string[] = []
  let params: any[]   = []

  if (active !== undefined) { where.push('is_active=?'); params.push(active==='1'?1:0) }
  else { where.push('is_active=1') }
  if (brand)       { where.push('brand_key=?');    params.push(brand) }
  if (category)    { where.push('category=?');     params.push(category) }
  if (season)      { where.push('season=?');       params.push(season) }
  if (vehicle_type){ where.push('vehicle_type=?'); params.push(vehicle_type) }
  if (grade)       { where.push('grade=?');        params.push(grade) }
  if (featured)    { where.push('is_featured=1') }
  if (best)        { where.push('is_best=1') }
  if (search)      { where.push('(name LIKE ? OR brand LIKE ? OR size LIKE ?)'); params.push(`%${search}%`,`%${search}%`,`%${search}%`) }

  const whereStr = where.length ? 'WHERE ' + where.join(' AND ') : ''

  const sortMap: Record<string,string> = {
    'price_asc':   'price ASC',
    'price_desc':  'price DESC',
    'rating_desc': 'rating DESC',
    'review_desc': 'review_count DESC',
    'discount_desc':'discount DESC',
    'newest':      'created_at DESC',
  }
  const orderBy = sortMap[sort||''] || 'sort_order ASC, is_featured DESC, rating DESC'

  const countSQL = `SELECT COUNT(*) as cnt FROM products ${whereStr}`
  const dataSQL  = `SELECT * FROM products ${whereStr} ORDER BY ${orderBy} LIMIT ? OFFSET ?`

  const [countRes, dataRes] = await Promise.all([
    c.env.DB.prepare(countSQL).bind(...params).first() as any,
    c.env.DB.prepare(dataSQL).bind(...params, lmt, off).all()
  ])

  const items = (dataRes.results || []).map((p: any) => ({
    ...p,
    tags: p.tags ? JSON.parse(p.tags) : []
  }))

  return ok(c, items, {
    total: countRes?.cnt || 0,
    page: pg, limit: lmt,
    total_pages: Math.ceil((countRes?.cnt || 0) / lmt)
  })
})

// 상품 상세
app.get('/api/products/:id', async (c) => {
  const id      = c.req.param('id')
  const isNum   = /^\d+$/.test(id)
  const product = await c.env.DB.prepare(
    isNum ? 'SELECT * FROM products WHERE id=?' : 'SELECT * FROM products WHERE product_id=?'
  ).bind(id).first() as any
  if (!product) return err(c, '상품을 찾을 수 없습니다', 404)

  // 리뷰 통계
  const reviewStats = await c.env.DB.prepare(`
    SELECT COUNT(*) as cnt, AVG(rating) as avg,
      SUM(CASE WHEN rating=5 THEN 1 ELSE 0 END) as r5,
      SUM(CASE WHEN rating=4 THEN 1 ELSE 0 END) as r4,
      SUM(CASE WHEN rating=3 THEN 1 ELSE 0 END) as r3,
      SUM(CASE WHEN rating=2 THEN 1 ELSE 0 END) as r2,
      SUM(CASE WHEN rating=1 THEN 1 ELSE 0 END) as r1
    FROM reviews WHERE product_id=? AND status='active'
  `).bind(product.id).first() as any

  // 최신 리뷰 3개
  const reviews = await c.env.DB.prepare(
    "SELECT * FROM reviews WHERE product_id=? AND status='active' ORDER BY created_at DESC LIMIT 3"
  ).bind(product.id).all()

  return ok(c, {
    ...product,
    tags:        product.tags ? JSON.parse(product.tags) : [],
    specs:       product.specs ? JSON.parse(product.specs) : null,
    review_stats: reviewStats,
    recent_reviews: reviews.results || []
  })
})

// 상품 생성 (관리자)
app.post('/api/products', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const {
    product_id, brand, brand_key, name, size, price, original_price, discount,
    rating, review_count, badge, tags, category, image, season, vehicle_type,
    grade, description, specs, is_active, is_featured, is_best, stock, sort_order
  } = body
  if (!brand || !name || !price) return err(c, '브랜드, 상품명, 가격은 필수입니다')
  const pid = product_id || `tire-${Date.now()}`
  const result = await c.env.DB.prepare(`
    INSERT INTO products
    (product_id,brand,brand_key,name,size,price,original_price,discount,rating,review_count,
     badge,tags,category,image,season,vehicle_type,grade,description,specs,
     is_active,is_featured,is_best,stock,sort_order)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  `).bind(
    pid, brand, brand_key||brand.toLowerCase(), name, size||null,
    price, original_price||price, discount||0, rating||0, review_count||0,
    badge||null, tags?JSON.stringify(tags):null, category||'tire', image||null,
    season||null, vehicle_type||null, grade||null, description||null,
    specs?JSON.stringify(specs):null,
    is_active!==false?1:0, is_featured?1:0, is_best?1:0, stock||100, sort_order||0
  ).run()
  return ok(c, { id: result.meta.last_row_id, product_id: pid })
})

// 상품 수정 (관리자)
app.put('/api/products/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const body = await c.req.json()
  const {
    brand, brand_key, name, size, price, original_price, discount, badge,
    tags, category, image, season, vehicle_type, grade, description, specs,
    is_active, is_featured, is_best, stock, sort_order
  } = body
  await c.env.DB.prepare(`
    UPDATE products SET
    brand=?,brand_key=?,name=?,size=?,price=?,original_price=?,discount=?,
    badge=?,tags=?,category=?,image=?,season=?,vehicle_type=?,grade=?,
    description=?,specs=?,is_active=?,is_featured=?,is_best=?,stock=?,
    sort_order=?,updated_at=CURRENT_TIMESTAMP
    WHERE id=?
  `).bind(
    brand, brand_key||brand?.toLowerCase(), name, size||null,
    price, original_price||price, discount||0,
    badge||null, tags?JSON.stringify(tags):null, category||'tire', image||null,
    season||null, vehicle_type||null, grade||null, description||null,
    specs?JSON.stringify(specs):null,
    is_active!==false?1:0, is_featured?1:0, is_best?1:0, stock??100, sort_order||0,
    id
  ).run()
  return ok(c, { message: '상품이 수정되었습니다' })
})

// 상품 삭제 (관리자)
app.delete('/api/products/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM products WHERE id=?').bind(id).run()
  return ok(c, { message: '상품이 삭제되었습니다' })
})

// 재고 업데이트 (관리자)
app.put('/api/products/:id/stock', adminMiddleware, async (c) => {
  const id    = c.req.param('id')
  const { stock } = await c.req.json()
  await c.env.DB.prepare('UPDATE products SET stock=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').bind(stock,id).run()
  return ok(c, { message: '재고가 업데이트되었습니다' })
})

// ============================================================
// 리뷰 API
// ============================================================

app.get('/api/reviews', async (c) => {
  const { product_id, user_id, status, page, limit } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(50, parseInt(limit||'10'))
  const off = (pg-1)*lmt

  let where: string[] = ["status='active'"]
  let params: any[]   = []
  if (product_id) { where.push('product_id=?'); params.push(product_id) }
  if (user_id)    { where.push('user_id=?');    params.push(user_id) }
  if (status)     { where[0] = 'status=?'; params.unshift(status) }

  const whereStr = 'WHERE ' + where.join(' AND ')
  const [cnt, data] = await Promise.all([
    c.env.DB.prepare(`SELECT COUNT(*) as cnt FROM reviews ${whereStr}`).bind(...params).first() as any,
    c.env.DB.prepare(`SELECT * FROM reviews ${whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?`).bind(...params,lmt,off).all()
  ])
  return ok(c, data.results||[], { total: cnt?.cnt||0, page: pg, limit: lmt })
})

app.post('/api/reviews', authMiddleware, async (c) => {
  const me   = c.get('user')
  const body = await c.req.json()
  const { product_id, rating, content, image_url, ride_comfort, noise, wet_grip, durability } = body
  if (!product_id || !rating || !content) return err(c, '상품, 별점, 내용은 필수입니다')
  const result = await c.env.DB.prepare(`
    INSERT INTO reviews (product_id,user_id,author_name,rating,content,image_url,
    ride_comfort,noise,wet_grip,durability,is_verified)
    VALUES (?,?,?,?,?,?,?,?,?,?,0)
  `).bind(product_id,me.id,me.name,rating,content,image_url||null,
    ride_comfort||null,noise||null,wet_grip||null,durability||null).run()
  // 상품 평점 업데이트
  await c.env.DB.prepare(`
    UPDATE products SET
    rating=(SELECT AVG(rating) FROM reviews WHERE product_id=? AND status='active'),
    review_count=(SELECT COUNT(*) FROM reviews WHERE product_id=? AND status='active')
    WHERE id=?
  `).bind(product_id,product_id,product_id).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.delete('/api/reviews/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare("UPDATE reviews SET status='deleted' WHERE id=?").bind(id).run()
  return ok(c, { message: '리뷰가 삭제되었습니다' })
})

app.put('/api/reviews/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const { status } = await c.req.json()
  await c.env.DB.prepare("UPDATE reviews SET status=? WHERE id=?").bind(status,id).run()
  return ok(c, { message: '리뷰 상태가 변경되었습니다' })
})

// ============================================================
// 주문 API
// ============================================================

app.get('/api/orders', async (c) => {
  const { user_id, status, page, limit } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(50, parseInt(limit||'10'))
  const off = (pg-1)*lmt

  let where: string[] = []
  let params: any[]   = []
  if (user_id) { where.push('o.user_id=?'); params.push(user_id) }
  if (status)  { where.push('o.status=?');  params.push(status) }

  const whereStr = where.length ? 'WHERE '+where.join(' AND ') : ''
  const [cnt, data] = await Promise.all([
    c.env.DB.prepare(`SELECT COUNT(*) as cnt FROM orders o ${whereStr}`).bind(...params).first() as any,
    c.env.DB.prepare(`SELECT o.*,u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id=u.id ${whereStr} ORDER BY o.created_at DESC LIMIT ? OFFSET ?`).bind(...params,lmt,off).all()
  ])

  const orders = await Promise.all((data.results||[]).map(async (order: any) => {
    const items = await c.env.DB.prepare('SELECT * FROM order_items WHERE order_id=?').bind(order.id).all()
    return { ...order, items: items.results||[] }
  }))

  return ok(c, orders, { total: cnt?.cnt||0, page: pg, limit: lmt })
})

// 내 주문 조회
app.get('/api/orders/my', authMiddleware, async (c) => {
  const me  = c.get('user')
  const { page, limit } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(20, parseInt(limit||'10'))
  const off = (pg-1)*lmt
  const [cnt, data] = await Promise.all([
    c.env.DB.prepare('SELECT COUNT(*) as cnt FROM orders WHERE user_id=?').bind(me.id).first() as any,
    c.env.DB.prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT ? OFFSET ?').bind(me.id,lmt,off).all()
  ])
  const orders = await Promise.all((data.results||[]).map(async (order: any) => {
    const items = await c.env.DB.prepare('SELECT * FROM order_items WHERE order_id=?').bind(order.id).all()
    return { ...order, items: items.results||[] }
  }))
  return ok(c, orders, { total: cnt?.cnt||0, page: pg, limit: lmt })
})

app.post('/api/orders', async (c) => {
  const body = await c.req.json()
  const { customer_name, customer_phone, customer_email, items, payment_method,
    coupon_id, point_used, shipping_address, shipping_zipcode, memo, store_id } = body
  if (!customer_name || !customer_phone || !items?.length) return err(c, '주문자 정보와 상품을 입력해주세요')

  // 포인트 처리 (로그인 사용자)
  let userId: number | null = null
  const auth = c.req.header('Authorization')
  if (auth?.startsWith('Bearer ')) {
    const payload = await verifyJWT(auth.slice(7))
    if (payload) userId = payload.id
  }

  let subtotal = 0
  const orderItems: any[] = []
  for (const item of items) {
    const product = await c.env.DB.prepare('SELECT * FROM products WHERE id=?').bind(item.product_id).first() as any
    if (!product) return err(c, `상품(${item.product_id})을 찾을 수 없습니다`)
    if (product.stock < item.qty) return err(c, `${product.name} 재고가 부족합니다`)
    const itemTotal = product.price * item.qty
    subtotal += itemTotal
    orderItems.push({ ...item, price: product.price, product_name: product.name, product_size: product.size, brand: product.brand, subtotal: itemTotal })
  }

  let discountAmount = 0
  if (coupon_id) {
    const coupon = await c.env.DB.prepare('SELECT * FROM coupons WHERE id=? AND is_active=1').bind(coupon_id).first() as any
    if (coupon && subtotal >= coupon.min_order) {
      discountAmount = coupon.type==='percent'
        ? Math.floor(subtotal * coupon.value / 100)
        : coupon.value
      if (coupon.max_discount) discountAmount = Math.min(discountAmount, coupon.max_discount)
    }
  }

  const pointUsed    = Math.min(parseInt(point_used||'0'), subtotal - discountAmount)
  const totalAmount  = subtotal - discountAmount - pointUsed
  const orderNumber  = generateOrderNumber()

  const result = await c.env.DB.prepare(`
    INSERT INTO orders (order_number,user_id,customer_name,customer_phone,customer_email,
    payment_method,subtotal,discount_amount,point_used,total_amount,
    shipping_address,shipping_zipcode,memo,coupon_id,store_id)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  `).bind(orderNumber,userId,customer_name,customer_phone,customer_email||null,
    payment_method||'card',subtotal,discountAmount,pointUsed,totalAmount,
    shipping_address||null,shipping_zipcode||null,memo||null,
    coupon_id||null,store_id||null).run()

  const orderId = result.meta.last_row_id

  // 주문 상품 및 재고 처리
  for (const item of orderItems) {
    await c.env.DB.prepare(`
      INSERT INTO order_items (order_id,product_id,product_name,product_size,brand,price,qty,subtotal)
      VALUES (?,?,?,?,?,?,?,?)
    `).bind(orderId,item.product_id,item.product_name,item.product_size||null,item.brand||null,item.price,item.qty,item.subtotal).run()
    await c.env.DB.prepare('UPDATE products SET stock=stock-? WHERE id=?').bind(item.qty,item.product_id).run()
  }

  // 포인트 차감 & 적립
  if (userId && pointUsed > 0) {
    await c.env.DB.prepare('UPDATE users SET points=points-? WHERE id=?').bind(pointUsed,userId).run()
  }
  if (userId) {
    const earnPoints = Math.floor(totalAmount * 0.01)
    await c.env.DB.prepare('UPDATE users SET points=points+?,total_spend=total_spend+?,order_count=order_count+1 WHERE id=?')
      .bind(earnPoints,totalAmount,userId).run()
  }

  return ok(c, { order_number: orderNumber, order_id: orderId, total_amount: totalAmount })
})

app.put('/api/orders/:id/status', adminMiddleware, async (c) => {
  const id  = c.req.param('id')
  const { status } = await c.req.json()
  const cols: Record<string,string> = {
    'confirmed': ',paid_at=CURRENT_TIMESTAMP',
    'shipping':  ',shipped_at=CURRENT_TIMESTAMP',
    'delivered': ',delivered_at=CURRENT_TIMESTAMP',
    'cancelled': ',cancelled_at=CURRENT_TIMESTAMP',
  }
  const extra = cols[status]||''
  await c.env.DB.prepare(`UPDATE orders SET status=?${extra},updated_at=CURRENT_TIMESTAMP WHERE id=?`).bind(status,id).run()
  return ok(c, { message: '주문 상태가 변경되었습니다' })
})

// ============================================================
// 예약 API
// ============================================================

app.get('/api/reservations', async (c) => {
  const { status, date, page, limit } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(50, parseInt(limit||'20'))
  const off = (pg-1)*lmt

  let where: string[] = []
  let params: any[]   = []
  if (status) { where.push('status=?'); params.push(status) }
  if (date)   { where.push('reserved_date=?'); params.push(date) }

  const whereStr = where.length ? 'WHERE '+where.join(' AND ') : ''
  const [cnt, data] = await Promise.all([
    c.env.DB.prepare(`SELECT COUNT(*) as cnt FROM reservations ${whereStr}`).bind(...params).first() as any,
    c.env.DB.prepare(`SELECT * FROM reservations ${whereStr} ORDER BY reserved_date ASC,reserved_time ASC LIMIT ? OFFSET ?`).bind(...params,lmt,off).all()
  ])
  return ok(c, data.results||[], { total: cnt?.cnt||0, page: pg, limit: lmt })
})

app.get('/api/reservations/my', authMiddleware, async (c) => {
  const me  = c.get('user')
  const data = await c.env.DB.prepare('SELECT * FROM reservations WHERE user_id=? ORDER BY created_at DESC').bind(me.id).all()
  return ok(c, data.results||[])
})

app.post('/api/reservations', async (c) => {
  const body = await c.req.json()
  const { customer_name, customer_phone, vehicle_number, vehicle_model,
    product_id, qty, store_id, reserved_date, reserved_time, memo } = body
  if (!customer_name || !customer_phone || !reserved_date || !reserved_time) {
    return err(c, '예약자 정보, 날짜, 시간은 필수입니다')
  }

  let userId: number | null = null
  const auth = c.req.header('Authorization')
  if (auth?.startsWith('Bearer ')) {
    const payload = await verifyJWT(auth.slice(7))
    if (payload) userId = payload.id
  }

  let productName = null
  if (product_id) {
    const prod = await c.env.DB.prepare('SELECT name,size FROM products WHERE id=?').bind(product_id).first() as any
    if (prod) productName = `${prod.name} ${prod.size||''}`
  }

  let storeName = null
  if (store_id) {
    const store = await c.env.DB.prepare('SELECT name FROM stores WHERE id=?').bind(store_id).first() as any
    if (store) storeName = store.name
  }

  const rvNum = generateReservationNumber()
  const result = await c.env.DB.prepare(`
    INSERT INTO reservations (reservation_number,user_id,customer_name,customer_phone,
    vehicle_number,vehicle_model,product_id,product_name,qty,store_id,store_name,
    reserved_date,reserved_time,memo)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
  `).bind(rvNum,userId,customer_name,customer_phone,vehicle_number||null,vehicle_model||null,
    product_id||null,productName,qty||4,store_id||null,storeName,
    reserved_date,reserved_time,memo||null).run()

  return ok(c, { reservation_number: rvNum, id: result.meta.last_row_id })
})

app.put('/api/reservations/:id/status', adminMiddleware, async (c) => {
  const id  = c.req.param('id')
  const { status } = await c.req.json()
  await c.env.DB.prepare('UPDATE reservations SET status=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').bind(status,id).run()
  return ok(c, { message: '예약 상태가 변경되었습니다' })
})

// ============================================================
// 회원 관리 API (관리자)
// ============================================================

app.get('/api/users', adminMiddleware, async (c) => {
  const { search, grade, status, sort, page, limit } = c.req.query()
  const pg  = Math.max(1, parseInt(page||'1'))
  const lmt = Math.min(100, parseInt(limit||'20'))
  const off = (pg-1)*lmt

  let where: string[] = ["role='user'"]
  let params: any[]   = []
  if (grade)  { where.push('grade=?');  params.push(grade) }
  if (status) { where.push('status=?'); params.push(status) }
  if (search) { where.push('(name LIKE ? OR email LIKE ? OR phone LIKE ?)'); params.push(`%${search}%`,`%${search}%`,`%${search}%`) }

  const whereStr = 'WHERE ' + where.join(' AND ')
  const sortMap: Record<string,string> = {
    'join_desc':  'created_at DESC', 'join_asc': 'created_at ASC',
    'order_desc': 'order_count DESC', 'spend_desc': 'total_spend DESC',
  }
  const orderBy = sortMap[sort||''] || 'created_at DESC'

  const [cnt, data, stats] = await Promise.all([
    c.env.DB.prepare(`SELECT COUNT(*) as cnt FROM users ${whereStr}`).bind(...params).first() as any,
    c.env.DB.prepare(`SELECT id,email,name,phone,grade,status,points,total_spend,order_count,created_at,last_login FROM users ${whereStr} ORDER BY ${orderBy} LIMIT ? OFFSET ?`).bind(...params,lmt,off).all(),
    c.env.DB.prepare(`SELECT COUNT(*) as total,
      SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
      SUM(CASE WHEN grade='vip' THEN 1 ELSE 0 END) as vip,
      SUM(CASE WHEN date(created_at)=date('now') THEN 1 ELSE 0 END) as today
      FROM users WHERE role='user'`).first() as any
  ])

  return ok(c, data.results||[], {
    total: cnt?.cnt||0, page: pg, limit: lmt,
    stats: { total: stats?.total||0, active: stats?.active||0, vip: stats?.vip||0, today: stats?.today||0 }
  })
})

app.get('/api/users/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const user = await c.env.DB.prepare('SELECT id,email,name,phone,grade,status,points,total_spend,order_count,birth_date,gender,address,vehicle_number,created_at,last_login FROM users WHERE id=?').bind(id).first()
  if (!user) return err(c, '사용자를 찾을 수 없습니다', 404)
  return ok(c, user)
})

app.put('/api/users/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const { grade, status, points } = await c.req.json()
  await c.env.DB.prepare('UPDATE users SET grade=?,status=?,points=?,updated_at=CURRENT_TIMESTAMP WHERE id=?').bind(grade,status,points,id).run()
  return ok(c, { message: '회원 정보가 수정되었습니다' })
})

app.delete('/api/users/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare("UPDATE users SET status='banned',updated_at=CURRENT_TIMESTAMP WHERE id=?").bind(id).run()
  return ok(c, { message: '회원이 정지되었습니다' })
})

// ============================================================
// 쿠폰 API
// ============================================================

app.get('/api/coupons', async (c) => {
  const data = await c.env.DB.prepare("SELECT * FROM coupons WHERE is_active=1 ORDER BY created_at DESC").all()
  return ok(c, data.results||[])
})

app.get('/api/coupons/all', adminMiddleware, async (c) => {
  const data = await c.env.DB.prepare("SELECT * FROM coupons ORDER BY created_at DESC").all()
  return ok(c, data.results||[])
})

app.post('/api/coupons/validate', async (c) => {
  const { code, amount } = await c.req.json()
  const coupon = await c.env.DB.prepare(`
    SELECT * FROM coupons WHERE code=? AND is_active=1
    AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
    AND (total_count IS NULL OR used_count < total_count)
  `).bind(code).first() as any
  if (!coupon) return err(c, '유효하지 않은 쿠폰입니다')
  if (amount < coupon.min_order) return err(c, `최소 주문금액 ${coupon.min_order.toLocaleString()}원 이상이어야 합니다`)
  let discount = coupon.type==='percent' ? Math.floor(amount*coupon.value/100) : coupon.value
  if (coupon.max_discount) discount = Math.min(discount, coupon.max_discount)
  return ok(c, { coupon, discount_amount: discount })
})

app.post('/api/coupons', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const { code, name, type, value, min_order, max_discount, total_count, starts_at, expires_at } = body
  if (!code || !name || !type || !value) return err(c, '필수 항목을 입력해주세요')
  const result = await c.env.DB.prepare(`
    INSERT INTO coupons (code,name,type,value,min_order,max_discount,total_count,starts_at,expires_at)
    VALUES (?,?,?,?,?,?,?,?,?)
  `).bind(code,name,type,value,min_order||0,max_discount||null,total_count||null,starts_at||null,expires_at||null).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.put('/api/coupons/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const { is_active } = await c.req.json()
  await c.env.DB.prepare('UPDATE coupons SET is_active=? WHERE id=?').bind(is_active?1:0,id).run()
  return ok(c, { message: '쿠폰 상태가 변경되었습니다' })
})

app.delete('/api/coupons/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM coupons WHERE id=?').bind(id).run()
  return ok(c, { message: '쿠폰이 삭제되었습니다' })
})

// ============================================================
// 이벤트 API
// ============================================================

app.get('/api/events', async (c) => {
  const { active } = c.req.query()
  const where = active==='1' ? "WHERE is_active=1" : ""
  const data  = await c.env.DB.prepare(`SELECT * FROM events ${where} ORDER BY sort_order ASC, created_at DESC`).all()
  return ok(c, data.results||[])
})

app.post('/api/events', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const { title, description, image_url, link_url, starts_at, ends_at, is_active, sort_order } = body
  if (!title) return err(c, '이벤트 제목을 입력해주세요')
  const result = await c.env.DB.prepare(`
    INSERT INTO events (title,description,image_url,link_url,starts_at,ends_at,is_active,sort_order)
    VALUES (?,?,?,?,?,?,?,?)
  `).bind(title,description||null,image_url||null,link_url||null,starts_at||null,ends_at||null,is_active!==false?1:0,sort_order||0).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.put('/api/events/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const body = await c.req.json()
  const { title, description, image_url, link_url, starts_at, ends_at, is_active, sort_order } = body
  await c.env.DB.prepare(`
    UPDATE events SET title=?,description=?,image_url=?,link_url=?,starts_at=?,ends_at=?,is_active=?,sort_order=?
    WHERE id=?
  `).bind(title,description||null,image_url||null,link_url||null,starts_at||null,ends_at||null,is_active?1:0,sort_order||0,id).run()
  return ok(c, { message: '이벤트가 수정되었습니다' })
})

app.delete('/api/events/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM events WHERE id=?').bind(id).run()
  return ok(c, { message: '이벤트가 삭제되었습니다' })
})

// ============================================================
// 공지사항 API
// ============================================================

app.get('/api/notices', async (c) => {
  const { active } = c.req.query()
  const where = active==='1' ? "WHERE is_active=1" : ""
  const data  = await c.env.DB.prepare(`SELECT * FROM notices ${where} ORDER BY is_pinned DESC, created_at DESC`).all()
  return ok(c, data.results||[])
})

app.post('/api/notices', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const { title, content, category, is_pinned } = body
  if (!title || !content) return err(c, '제목과 내용을 입력해주세요')
  const result = await c.env.DB.prepare(`
    INSERT INTO notices (title,content,category,is_pinned) VALUES (?,?,?,?)
  `).bind(title,content,category||'general',is_pinned?1:0).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.put('/api/notices/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const body = await c.req.json()
  const { title, content, category, is_pinned, is_active } = body
  await c.env.DB.prepare(`
    UPDATE notices SET title=?,content=?,category=?,is_pinned=?,is_active=?,updated_at=CURRENT_TIMESTAMP
    WHERE id=?
  `).bind(title,content,category||'general',is_pinned?1:0,is_active!==false?1:0,id).run()
  return ok(c, { message: '공지사항이 수정되었습니다' })
})

app.delete('/api/notices/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM notices WHERE id=?').bind(id).run()
  return ok(c, { message: '공지사항이 삭제되었습니다' })
})

// ============================================================
// FAQ API
// ============================================================

app.get('/api/faqs', async (c) => {
  const data = await c.env.DB.prepare("SELECT * FROM faqs WHERE is_active=1 ORDER BY sort_order ASC").all()
  return ok(c, data.results||[])
})

app.post('/api/faqs', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const { category, question, answer, sort_order } = body
  if (!question || !answer) return err(c, '질문과 답변을 입력해주세요')
  const result = await c.env.DB.prepare(`
    INSERT INTO faqs (category,question,answer,sort_order) VALUES (?,?,?,?)
  `).bind(category||'general',question,answer,sort_order||0).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.delete('/api/faqs/:id', adminMiddleware, async (c) => {
  const id = c.req.param('id')
  await c.env.DB.prepare('DELETE FROM faqs WHERE id=?').bind(id).run()
  return ok(c, { message: 'FAQ가 삭제되었습니다' })
})

// ============================================================
// 매장 API
// ============================================================

app.get('/api/stores', async (c) => {
  const data = await c.env.DB.prepare("SELECT * FROM stores WHERE is_active=1 ORDER BY id ASC").all()
  return ok(c, data.results||[])
})

app.post('/api/stores', adminMiddleware, async (c) => {
  const body = await c.req.json()
  const { name, address, phone, open_hours, lat, lng } = body
  if (!name || !address) return err(c, '매장명과 주소를 입력해주세요')
  const result = await c.env.DB.prepare(`
    INSERT INTO stores (name,address,phone,open_hours,lat,lng) VALUES (?,?,?,?,?,?)
  `).bind(name,address,phone||null,open_hours||null,lat||null,lng||null).run()
  return ok(c, { id: result.meta.last_row_id })
})

app.put('/api/stores/:id', adminMiddleware, async (c) => {
  const id   = c.req.param('id')
  const body = await c.req.json()
  const { name, address, phone, open_hours, is_active } = body
  await c.env.DB.prepare(`
    UPDATE stores SET name=?,address=?,phone=?,open_hours=?,is_active=? WHERE id=?
  `).bind(name,address,phone||null,open_hours||null,is_active?1:0,id).run()
  return ok(c, { message: '매장 정보가 수정되었습니다' })
})

// ============================================================
// 설정 API
// ============================================================

app.get('/api/settings', async (c) => {
  const data = await c.env.DB.prepare('SELECT key,value FROM settings').all()
  const settings: Record<string,string> = {}
  for (const row of (data.results||[]) as any[]) {
    settings[row.key] = row.value
  }
  return ok(c, settings)
})

app.post('/api/settings', adminMiddleware, async (c) => {
  const body = await c.req.json() as Record<string,string>
  for (const [key, value] of Object.entries(body)) {
    await c.env.DB.prepare(
      'INSERT OR REPLACE INTO settings (key,value,updated_at) VALUES (?,?,CURRENT_TIMESTAMP)'
    ).bind(key, String(value)).run()
  }
  return ok(c, { message: '설정이 저장되었습니다' })
})

// ============================================================
// 통계 API (관리자 대시보드)
// ============================================================

app.get('/api/stats/dashboard', adminMiddleware, async (c) => {
  const [
    totalUsers, totalOrders, totalProducts, totalReservations,
    todayOrders, pendingReservations, recentOrders, topProducts
  ] = await Promise.all([
    c.env.DB.prepare("SELECT COUNT(*) as cnt FROM users WHERE role='user'").first() as any,
    c.env.DB.prepare("SELECT COUNT(*) as cnt, SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'").first() as any,
    c.env.DB.prepare("SELECT COUNT(*) as cnt FROM products WHERE is_active=1").first() as any,
    c.env.DB.prepare("SELECT COUNT(*) as cnt FROM reservations WHERE status='pending'").first() as any,
    c.env.DB.prepare("SELECT COUNT(*) as cnt, SUM(total_amount) as revenue FROM orders WHERE date(created_at)=date('now')").first() as any,
    c.env.DB.prepare("SELECT * FROM reservations WHERE status='pending' ORDER BY reserved_date ASC LIMIT 5").all(),
    c.env.DB.prepare("SELECT o.*,u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 10").all(),
    c.env.DB.prepare("SELECT p.name,p.brand,SUM(oi.qty) as sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY p.id ORDER BY sold DESC LIMIT 5").all(),
  ])

  return ok(c, {
    summary: {
      total_users:        totalUsers?.cnt || 0,
      total_revenue:      totalOrders?.revenue || 0,
      total_orders:       totalOrders?.cnt || 0,
      total_products:     totalProducts?.cnt || 0,
      pending_reservations: totalReservations?.cnt || 0,
      today_orders:       todayOrders?.cnt || 0,
      today_revenue:      todayOrders?.revenue || 0,
    },
    recent_orders:       recentOrders.results || [],
    pending_reservations: pendingReservations.results || [],
    top_products:        topProducts.results || [],
  })
})

export default app
