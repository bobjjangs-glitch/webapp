<?php
// ================================================================
// db.php - MySQL 데이터베이스 연결 설정
// 닷홈 MySQL 정보를 아래에 입력하세요
// ================================================================

define('DB_HOST',    'localhost');          // 닷홈은 보통 localhost
define('DB_NAME',    'bobjjangs1231');       // 닷홈 DB명 (아이디와 동일한 경우 많음)
define('DB_USER',    'bobjjangs1231');       // 닷홈 DB 사용자명
define('DB_PASS',    'ssy201029@');   // 닷홈 DB 비밀번호
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT',    3306);

// ── PDO 연결 싱글톤 ──────────────────────────────────────────
class DB {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
                );
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    die('DB 연결 실패: ' . $e->getMessage());
                } else {
                    die('데이터베이스 연결에 실패했습니다. 설정을 확인해주세요.');
                }
            }
        }
        return self::$instance;
    }

    // 편의 메서드: SELECT 여러 행
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // 편의 메서드: SELECT 한 행
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // 편의 메서드: INSERT/UPDATE/DELETE
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return (int) self::connect()->lastInsertId() ?: $stmt->rowCount();
    }

    // 편의 메서드: lastInsertId
    public static function lastId(): string {
        return self::connect()->lastInsertId();
    }
}
