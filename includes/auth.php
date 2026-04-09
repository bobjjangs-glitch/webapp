<?php
// ================================================================
// includes/auth.php - 인증 헬퍼
// ================================================================

/**
 * 로그인 확인
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * 로그인 요구
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        // 닷홈 서버 호환: 상대경로 사용
        header('Location: index.php?route=login');
        header('Cache-Control: no-cache, no-store');
        exit;
    }
}

/**
 * 현재 사용자 정보 가져오기
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = $_SESSION['user_id'];
    
    // 먼저 세션에서 가져오기 (빠름)
    if (isset($_SESSION['user_data_cached']) && 
        isset($_SESSION['user_data_cache_time']) && 
        (time() - $_SESSION['user_data_cache_time']) < 300) { // 5분 캐시
        return $_SESSION['user_data_cached'];
    }
    
    // DB에서 최신 정보 가져오기
    try {
        $user = DB::fetchOne(
            "SELECT id, username as name, email, plan, business_name, credits_balance, created_at, last_login_at
             FROM users 
             WHERE id = ? 
             LIMIT 1",
            [$userId]
        );
        
        if ($user) {
            // 세션에 캐시
            $_SESSION['user_data_cached'] = $user;
            $_SESSION['user_data_cache_time'] = time();
            
            // 세션 변수도 업데이트
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_plan'] = $user['plan'];
            $_SESSION['business_name'] = $user['business_name'] ?? '';
            
            return $user;
        }
    } catch (Exception $e) {
        error_log("getCurrentUser DB 오류: " . $e->getMessage());
    }
    
    // DB 조회 실패 시 세션에서 기본 정보 반환
    return [
        'id'             => $_SESSION['user_id'],
        'name'           => $_SESSION['user_name'] ?? '사용자',
        'email'          => $_SESSION['user_email'] ?? '',
        'plan'           => $_SESSION['user_plan'] ?? 'free',
        'business_name'  => $_SESSION['business_name'] ?? '',
        'credits_balance'=> 0,
        'created_at'     => null,
        'last_login_at'  => null
    ];
}

/**
 * 사용자 로그인 처리
 */
function loginUser(array $user): void {
    // 세션 재생성 (세션 고정 공격 방지)
    session_regenerate_id(true);
    
    // 세션 변수 설정
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_name']     = $user['name'] ?? $user['username'] ?? '사용자';
    $_SESSION['user_email']    = $user['email'] ?? '';
    $_SESSION['user_plan']     = $user['plan'] ?? 'free';
    $_SESSION['business_name'] = $user['business_name'] ?? '';
    $_SESSION['logged_in_at']  = time();
    
    // 사용자 데이터 캐시
    $_SESSION['user_data_cached'] = $user;
    $_SESSION['user_data_cache_time'] = time();

    // DB에 세션 기록 및 로그인 시간 업데이트
    try {
        $token = bin2hex(random_bytes(32));
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 세션 테이블이 있다면 기록
        DB::execute(
            "INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at, created_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW())",
            [$user['id'], $token, $ipAddress, $userAgent]
        );
    } catch (Exception $e) {
        // 세션 테이블이 없을 수 있으므로 무시
        error_log("세션 기록 실패 (무시됨): " . $e->getMessage());
    }
    
    // 마지막 로그인 시간 업데이트
    try {
        DB::execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?", 
            [$user['id']]
        );
    } catch (Exception $e) {
        error_log("로그인 시간 업데이트 실패: " . $e->getMessage());
    }
}

/**
 * 사용자 로그아웃
 */
function logoutUser(): void {
    $userId = $_SESSION['user_id'] ?? null;
    
    // DB에서 세션 삭제
    if ($userId) {
        try {
            DB::execute(
                "DELETE FROM sessions WHERE user_id = ?",
                [$userId]
            );
        } catch (Exception $e) {
            // 세션 테이블이 없을 수 있으므로 무시
            error_log("세션 삭제 실패 (무시됨): " . $e->getMessage());
        }
    }
    
    // 세션 변수 전체 삭제
    $_SESSION = [];
    
    // 세션 쿠키 삭제
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // 세션 파괴
    session_destroy();
}

/**
 * 관리자 확인
 */
function isAdmin(): bool {
    if (!isLoggedIn()) {
        return false;
    }
    
    // 세션에 관리자 플래그가 있는지 확인
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }
    
    // DB에서 확인
    try {
        $user = DB::fetchOne(
            "SELECT is_admin FROM users WHERE id = ? LIMIT 1",
            [$_SESSION['user_id']]
        );
        
        if ($user && isset($user['is_admin']) && $user['is_admin'] == 1) {
            $_SESSION['is_admin'] = true;
            return true;
        }
    } catch (Exception $e) {
        error_log("isAdmin 확인 오류: " . $e->getMessage());
    }
    
    return false;
}

/**
 * 관리자 권한 요구
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html lang="ko">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>접근 거부</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .error-box {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 400px;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    font-size: 24px;
                    margin: 0 0 10px 0;
                    color: #333;
                }
                p {
                    color: #666;
                    margin: 0 0 20px 0;
                }
                a {
                    display: inline-block;
                    padding: 10px 24px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: 500;
                }
                a:hover {
                    background: #5568d3;
                }
            </style>
        </head>
        <body>
            <div class="error-box">
                <div class="error-icon">🚫</div>
                <h1>접근 권한이 없습니다</h1>
                <p>관리자만 접근할 수 있는 페이지입니다.</p>
                <a href="index.php?route=dashboard">대시보드로 돌아가기</a>
            </div>
        </body>
        </html>';
        exit;
    }
}

/**
 * 사용자 인증 (이메일/비밀번호)
 */
function authenticateUser(string $email, string $password): array {
    try {
        // 이메일로 사용자 찾기
        $user = DB::fetchOne(
            "SELECT id, username, email, password, plan, business_name, status, is_admin
             FROM users 
             WHERE email = ?
             LIMIT 1",
            [$email]
        );
        
        if (!$user) {
            return [
                'success' => false, 
                'error' => '이메일 또는 비밀번호가 올바르지 않습니다.'
            ];
        }
        
        // 비밀번호 확인
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false, 
                'error' => '이메일 또는 비밀번호가 올바르지 않습니다.'
            ];
        }
        
        // 계정 상태 확인
        if ($user['status'] !== 'active') {
            return [
                'success' => false, 
                'error' => '계정이 비활성화되었습니다. 관리자에게 문의하세요.'
            ];
        }
        
        // 비밀번호 제거
        unset($user['password']);
        
        // 사용자 이름 설정
        $user['name'] = $user['username'];
        
        return [
            'success' => true, 
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("authenticateUser 오류: " . $e->getMessage());
        return [
            'success' => false, 
            'error' => '로그인 처리 중 오류가 발생했습니다.'
        ];
    }
}

/**
 * 사용자 등록
 */
function registerUser(string $username, string $email, string $password): array {
    try {
        // 이메일 중복 확인
        $exists = DB::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ?",
            [$email]
        );
        
        if ($exists > 0) {
            return [
                'success' => false, 
                'error' => '이미 사용 중인 이메일입니다.'
            ];
        }
        
        // 사용자명 중복 확인
        $exists = DB::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE username = ?",
            [$username]
        );
        
        if ($exists > 0) {
            return [
                'success' => false, 
                'error' => '이미 사용 중인 사용자명입니다.'
            ];
        }
        
        // 비밀번호 해시
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // 사용자 생성
        DB::execute(
            "INSERT INTO users (username, email, password, plan, status, created_at)
             VALUES (?, ?, ?, 'free', 'active', NOW())",
            [$username, $email, $hashedPassword]
        );
        
        $userId = DB::lastInsertId();
        
        return [
            'success' => true,
            'user_id' => $userId,
            'message' => '회원가입이 완료되었습니다.'
        ];
        
    } catch (Exception $e) {
        error_log("registerUser 오류: " . $e->getMessage());
        return [
            'success' => false, 
            'error' => '회원가입 처리 중 오류가 발생했습니다.'
        ];
    }
}

/**
 * 비밀번호 재설정 토큰 생성
 */
function createPasswordResetToken(string $email): array {
    try {
        // 사용자 확인
        $user = DB::fetchOne(
            "SELECT id, username, email FROM users WHERE email = ? AND status = 'active'",
            [$email]
        );
        
        if (!$user) {
            return [
                'success' => false, 
                'error' => '해당 이메일로 등록된 계정을 찾을 수 없습니다.'
            ];
        }
        
        // 토큰 생성
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // 토큰 저장
        DB::execute(
            "INSERT INTO password_resets (user_id, email, token, expires_at, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$user['id'], $email, $token, $expiresAt]
        );
        
        return [
            'success' => true,
            'token' => $token,
            'user' => $user
        ];
        
    } catch (Exception $e) {
        error_log("createPasswordResetToken 오류: " . $e->getMessage());
        return [
            'success' => false, 
            'error' => '비밀번호 재설정 요청 처리 중 오류가 발생했습니다.'
        ];
    }
}

/**
 * 세션 유효성 검사
 */
function validateSession(): bool {
    if (!isLoggedIn()) {
        return false;
    }
    
    // 세션 타임아웃 체크 (선택사항)
    if (isset($_SESSION['logged_in_at'])) {
        $sessionLifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400; // 24시간
        
        if (time() - $_SESSION['logged_in_at'] > $sessionLifetime) {
            logoutUser();
            return false;
        }
    }
    
    // 세션 활동 시간 업데이트
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * 세션 갱신
 */
function refreshSession(): void {
    if (isLoggedIn()) {
        // 세션 캐시 무효화
        unset($_SESSION['user_data_cached']);
        unset($_SESSION['user_data_cache_time']);
        
        // 새로운 데이터 로드
        getCurrentUser();
    }
}

/**
 * 사용자 세션 정보 업데이트
 */
function updateSessionUser(array $data): void {
    if (!isLoggedIn()) {
        return;
    }
    
    if (isset($data['name'])) {
        $_SESSION['user_name'] = $data['name'];
    }
    
    if (isset($data['email'])) {
        $_SESSION['user_email'] = $data['email'];
    }
    
    if (isset($data['plan'])) {
        $_SESSION['user_plan'] = $data['plan'];
    }
    
    if (isset($data['business_name'])) {
        $_SESSION['business_name'] = $data['business_name'];
    }
    
    // 캐시 무효화
    refreshSession();
}
