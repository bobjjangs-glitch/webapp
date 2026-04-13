<?php
// config/db.php - DB 연결 (클래스 + 전역 $pdo 동시 지원)

class DB {
    private static ?PDO $connection = null;

    public static function connect(): PDO {
        if (self::$connection === null) {
            $host    = defined('DB_HOST')    ? DB_HOST    : 'localhost';
            $dbname  = defined('DB_NAME')    ? DB_NAME    : 'bobjjangs1231';
            $user    = defined('DB_USER')    ? DB_USER    : 'bobjjangs1231';
            $pass    = defined('DB_PASS')    ? DB_PASS    : 'ssy201029@';
            $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

            try {
                self::$connection = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset={$charset}",
                    $user, $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE {$charset}_unicode_ci",
                    ]
                );
            } catch (PDOException $e) {
                error_log('DB 연결 실패: ' . $e->getMessage());
                if (defined('IS_API') && IS_API) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'error' => '데이터베이스 연결 오류']);
                } else {
                    die('<div style="text-align:center;padding:50px;font-family:sans-serif;"><h2>서버 오류</h2><p>잠시 후 다시 시도해주세요.</p></div>');
                }
                exit;
            }
        }
        return self::$connection;
    }

    public static function fetchAll(string $query, array $params = []): array {
        try {
            $stmt = self::connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('DB fetchAll 오류: ' . $e->getMessage() . ' | Query: ' . $query);
            return [];
        }
    }

    public static function fetchOne(string $query, array $params = []): ?array {
        try {
            $stmt = self::connect()->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('DB fetchOne 오류: ' . $e->getMessage() . ' | Query: ' . $query);
            return null;
        }
    }

    public static function fetchColumn(string $query, array $params = []) {
        try {
            $stmt = self::connect()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('DB fetchColumn 오류: ' . $e->getMessage());
            return false;
        }
    }

    public static function execute(string $query, array $params = []): bool {
        try {
            $stmt = self::connect()->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('DB execute 오류: ' . $e->getMessage() . ' | Query: ' . $query);
            return false;
        }
    }

    public static function lastInsertId(): string {
        return self::connect()->lastInsertId();
    }

    public static function beginTransaction(): bool {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool {
        return self::connect()->commit();
    }

    public static function rollback(): bool {
        return self::connect()->rollBack();
    }
}

// ★ 전역 $pdo 동시 지원 – layout_top.php / pages / api 등에서 global $pdo; 로 사용 가능
$GLOBALS['pdo'] = DB::connect();
$pdo = $GLOBALS['pdo'];
