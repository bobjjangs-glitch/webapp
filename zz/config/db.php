<?php
// ================================================================
// config/db.php - 데이터베이스 클래스
// ================================================================

class DB {
    private static $pdo = null;
    
    /**
     * PDO 연결 가져오기 (싱글톤)
     */
    public static function connect() {
        if (self::$pdo === null) {
            try {
                // config.php에서 정의된 DB 설정 사용
                $host = defined('DB_HOST') ? DB_HOST : 'localhost';
                $dbname = defined('DB_NAME') ? DB_NAME : 'bobjjangs1231';
                $username = defined('DB_USER') ? DB_USER : 'bobjjangs1231';
                $password = defined('DB_PASS') ? DB_PASS : 'ssy201029@';
                $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
                
                if (empty($dbname)) {
                    throw new Exception('데이터베이스 이름이 설정되지 않았습니다.');
                }
                
                $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
                
                self::$pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
                ]);
                
            } catch (PDOException $e) {
                error_log("DB 연결 실패: " . $e->getMessage());
                throw new Exception('데이터베이스 연결에 실패했습니다.');
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * 여러 행 조회
     */
    public static function fetchAll($query, $params = []) {
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("DB fetchAll 오류: " . $e->getMessage());
            throw new Exception('데이터 조회에 실패했습니다.');
        }
    }
    
    /**
     * 한 행 조회
     */
    public static function fetchOne($query, $params = []) {
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("DB fetchOne 오류: " . $e->getMessage());
            throw new Exception('데이터 조회에 실패했습니다.');
        }
    }
    
    /**
     * 단일 값 조회
     */
    public static function fetchColumn($query, $params = []) {
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("DB fetchColumn 오류: " . $e->getMessage());
            throw new Exception('데이터 조회에 실패했습니다.');
        }
    }
    
    /**
     * INSERT, UPDATE, DELETE 실행
     */
    public static function execute($query, $params = []) {
        try {
            $pdo = self::connect();
            $stmt = $pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("DB execute 오류: " . $e->getMessage());
            throw new Exception('데이터 처리에 실패했습니다.');
        }
    }
    
    /**
     * 마지막 INSERT ID
     */
    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
    
    /**
     * 트랜잭션 시작
     */
    public static function beginTransaction() {
        return self::connect()->beginTransaction();
    }
    
    /**
     * 트랜잭션 커밋
     */
    public static function commit() {
        return self::connect()->commit();
    }
    
    /**
     * 트랜잭션 롤백
     */
    public static function rollback() {
        return self::connect()->rollBack();
    }
}
