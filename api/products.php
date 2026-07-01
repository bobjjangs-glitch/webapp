<?php
/* ====================================================
   TIRETOP - products.php
   최적화: CORS 즉시처리, PDO fetch 최소화, 캐시 헤더
   ==================================================== */

// ① CORS & JSON 헤더 최우선 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// ② OPTIONS preflight 즉시 종료 (브라우저 대기 제거)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';

// ③ DB 연결 실패 즉시 응답
try {
    $pdo = getDB();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB 연결 실패: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// toProduct: DB row → 프론트엔드 camelCase 변환
// ============================================================
function toProduct(array $row): array {
    $tags = [];
    if (!empty($row['tags'])) {
        $decoded = json_decode($row['tags'], true);
        $tags = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $row['tags'])));
    }

    $factoryPrice = (int)($row['price'] ?? 0);
    $salePrice    = (int)($row['sale_price'] ?? $factoryPrice);
    $discount     = $factoryPrice > 0 ? round(($factoryPrice - $salePrice) / $factoryPrice * 100) : 0;

    return [
        'id'           => (int)$row['id'],
        'name'         => $row['name'] ?? '',
        'brand'        => $row['brand'] ?? '',
        'category'     => $row['category'] ?? '',
        'subCategory'  => $row['sub_category'] ?? '',
        'width'        => $row['width'] ?? '',
        'aspect'       => $row['aspect'] ?? '',
        'diameter'     => $row['diameter'] ?? '',
        'size'         => $row['size'] ?? '',
        'factoryPrice' => $factoryPrice,
        'salePrice'    => $salePrice,
        'price'        => $factoryPrice,
        'discount'     => $discount,
        'stock'        => (int)($row['stock'] ?? 0),
        'image'        => $row['image'] ?? '',
        'description'  => $row['description'] ?? '',
        'tags'         => $tags,
        'active'       => (bool)($row['active'] ?? true),
        'featured'     => (bool)($row['featured'] ?? false),
        'rating'       => (float)($row['rating'] ?? 0),
        'reviewCount'  => (int)($row['review_count'] ?? 0),
        'createdAt'    => $row['created_at'] ?? '',
        'updatedAt'    => $row['updated_at'] ?? '',
    ];
}

// ============================================================
// GET - 상품 목록 / 단건 조회
// ============================================================
if ($method === 'GET') {
    // 단건 조회
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode(['success' => true, 'data' => toProduct($row)], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '상품 없음'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 목록 조회 (필터)
    $where  = [];
    $params = [];

    // active 필터 (어드민은 파라미터 없이 전체, 프론트는 active=1)
    if (isset($_GET['active']) && $_GET['active'] !== '') {
        $where[]  = 'active = ?';
        $params[] = (int)$_GET['active'];
    }

    // 카테고리 필터
    if (!empty($_GET['category'])) {
        $cat = $_GET['category'];
        if ($cat === 'engineoil' || $cat === 'oil') {
            $where[]  = "category IN ('engineoil','oil')";
        } else {
            $where[]  = 'category = ?';
            $params[] = $cat;
        }
    }

    // 브랜드 필터
    if (!empty($_GET['brand'])) {
        $where[]  = 'brand = ?';
        $params[] = $_GET['brand'];
    }

    // 검색
    if (!empty($_GET['search'])) {
        $kw       = '%' . $_GET['search'] . '%';
        $where[]  = '(name LIKE ? OR brand LIKE ? OR description LIKE ?)';
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
    }

    // 정렬
    $sortMap = [
        'latest'   => 'id DESC',
        'oldest'   => 'id ASC',
        'price_asc'  => 'sale_price ASC',
        'price_desc' => 'sale_price DESC',
        'rating'   => 'rating DESC',
        'name'     => 'name ASC',
    ];
    $sort    = $sortMap[$_GET['sort'] ?? ''] ?? 'id DESC';

    // LIMIT
    $limit  = isset($_GET['limit']) ? max(1, min(500, (int)$_GET['limit'])) : 500;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $sql  = 'SELECT * FROM products';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= " ORDER BY $sort LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = array_map('toProduct', $rows);

    // 전체 건수 (페이지네이션용)
    $countSql  = 'SELECT COUNT(*) FROM products';
    if ($where) {
        $countSql .= ' WHERE ' . implode(' AND ', $where);
    }
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // 브라우저 캐시 5초 (어드민 새로고침은 자동 bypass)
    header('Cache-Control: public, max-age=5');

    echo json_encode([
        'success' => true,
        'data'    => $products,
        'total'   => $total,
        'limit'   => $limit,
        'offset'  => $offset,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// POST - 상품 등록 / 수정
// ============================================================
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    $id          = !empty($body['id']) ? (int)$body['id'] : null;
    $name        = $body['name']        ?? '';
    $brand       = $body['brand']       ?? '';
    $category    = $body['category']    ?? 'tire';
    $subCategory = $body['subCategory'] ?? $body['sub_category'] ?? '';
    $width       = $body['width']       ?? '';
    $aspect      = $body['aspect']      ?? '';
    $diameter    = $body['diameter']    ?? '';
    $size        = $body['size']        ?? ($width && $aspect && $diameter ? "{$width}/{$aspect}R{$diameter}" : '');
    $price       = (int)($body['factoryPrice'] ?? $body['price'] ?? 0);
    $salePrice   = (int)($body['salePrice']    ?? $body['sale_price'] ?? $price);
    $stock       = (int)($body['stock']        ?? 0);
    $image       = $body['image']       ?? '';
    $description = $body['description'] ?? '';
    $tags        = json_encode(is_array($body['tags'] ?? null) ? $body['tags'] : [], JSON_UNESCAPED_UNICODE);
    $active      = isset($body['active'])   ? (int)(bool)$body['active']   : 1;
    $featured    = isset($body['featured']) ? (int)(bool)$body['featured'] : 0;
    $rating      = (float)($body['rating']  ?? 0);
    $reviewCount = (int)($body['reviewCount'] ?? $body['review_count'] ?? 0);

    if (!$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '상품명 필수'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($id) {
        // 수정
        $stmt = $pdo->prepare('
            UPDATE products SET
                name=?, brand=?, category=?, sub_category=?,
                width=?, aspect=?, diameter=?, size=?,
                price=?, sale_price=?, stock=?, image=?,
                description=?, tags=?, active=?, featured=?,
                rating=?, review_count=?, updated_at=NOW()
            WHERE id=?
        ');
        $stmt->execute([
            $name, $brand, $category, $subCategory,
            $width, $aspect, $diameter, $size,
            $price, $salePrice, $stock, $image,
            $description, $tags, $active, $featured,
            $rating, $reviewCount, $id
        ]);
        $stmt2 = $pdo->prepare('SELECT * FROM products WHERE id=?');
        $stmt2->execute([$id]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => toProduct($row)], JSON_UNESCAPED_UNICODE);
    } else {
        // 등록
        $stmt = $pdo->prepare('
            INSERT INTO products
                (name, brand, category, sub_category, width, aspect, diameter, size,
                 price, sale_price, stock, image, description, tags, active, featured,
                 rating, review_count, created_at, updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
        ');
        $stmt->execute([
            $name, $brand, $category, $subCategory,
            $width, $aspect, $diameter, $size,
            $price, $salePrice, $stock, $image,
            $description, $tags, $active, $featured,
            $rating, $reviewCount
        ]);
        $newId = (int)$pdo->lastInsertId();
        $stmt2 = $pdo->prepare('SELECT * FROM products WHERE id=?');
        $stmt2->execute([$newId]);
        $row = $stmt2->fetch(PDO::FETCH_ASSOC);
        http_response_code(201);
        echo json_encode(['success' => true, 'data' => toProduct($row)], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ============================================================
// PUT - 부분 업데이트 (active 토글 등)
// ============================================================
if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id 필수'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fields = [];
    $params = [];

    $map = [
        'active'       => 'active',
        'featured'     => 'featured',
        'stock'        => 'stock',
        'salePrice'    => 'sale_price',
        'sale_price'   => 'sale_price',
        'price'        => 'price',
        'factoryPrice' => 'price',
        'rating'       => 'rating',
        'reviewCount'  => 'review_count',
    ];

    foreach ($map as $jsKey => $dbCol) {
        if (array_key_exists($jsKey, $body)) {
            $fields[] = "$dbCol = ?";
            $params[] = $body[$jsKey];
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '변경 필드 없음'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fields[]  = 'updated_at = NOW()';
    $params[]  = $id;

    $pdo->prepare('UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?')
        ->execute($params);

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id 필수'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => '허용되지 않는 메서드'], JSON_UNESCAPED_UNICODE);
