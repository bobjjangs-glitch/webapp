<?php
/* ====================================================
   TIRETOP - config.php
   ==================================================== */

define('DB_HOST',    'localhost');
define('DB_NAME',    'bobjjangs1231');   // ← 본인 DB명
define('DB_USER',    'bobjjangs1231');   // ← 본인 계정
define('DB_PASS',    'ssy201029@'); // ← 실제 비밀번호로 변경
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,   // ← 연결 타임아웃 5초
    ]);
    return $pdo;
}

// 응답 헬퍼
function ok($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function getBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
