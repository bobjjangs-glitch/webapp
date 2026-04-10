<?php
// ================================================================
// includes/auth.php - 인증 헬퍼 (install.php DB 스키마 기준)
// ================================================================

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php?route=login');
        header('Cache-Control: no-cache, no-store');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;

    // 5분 캐시
    if (isset($_SESSION['user_data_cached'], $_SESSION['user_data_cache_time'])
        && (time() - $_SESSION['user_data_cache_time']) < 300) {
        return $_SESSION['user_data_cached'];
    }

    try {
        // ✅ install.php 스키마 컬럼명 사용 (name, password_hash, is_active)
        $user = DB::fetchOne(
            "SELECT id, name, email, plan, business_name, is_active, last_login_at, created_at
             FROM users WHERE id = ? LIMIT 1",
            [$_SESSION['user_id']]
        );

        if ($user) {
            // credits는 credit_balance 테이블에서 별도 조회
            try {
                $balance = DB::fetchColumn(
                    "SELECT balance FROM credit_balance WHERE user_id = ? LIMIT 1",
                    [$user['id']]
                );
                $user['credits_balance'] = (int)($balance ?? 0);
            } catch (Exception $e) {
                $user['credits_balance'] = 0;
            }

            $_SESSION['user_data_cached'] = $user;
            $_SESSION['user_data_cache_time'] = time();
            $_SESSION['user_name']   = $user['name'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_plan']   = $user['plan'];

            return $user;
        }
    } catch (Exception $e) {
        error_log("getCurrentUser DB 오류: " . $e->getMessage());
    }

    // DB 실패 시 세션 기본값 반환
    return [
        'id'              => $_SESSION['user_id'],
        'name'            => $_SESSION['user_name'] ?? '사용자',
        'email'           => $_SESSION['user_email'] ?? '',
        'plan'            => $_SESSION['user_plan'] ?? 'free',
        'business_name'   => $_SESSION['business_name'] ?? '',
        'credits_balance' => 0,
    ];
}

function loginUser(array $user): void {
    session_regenerate_id(true);

    // ✅ install.php 스키마: name 컬럼 (username 아님)
    $displayName = $user['name'] ?? $user['username'] ?? '사용자';

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $displayName;
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['user_plan']  = $user['plan'] ?? 'free';
    $_SESSION['logged_in_at'] = time();

    $_SESSION['user_data_cached'] = $user;
    $_SESSION['user_data_cache_time'] = time();

    // sessions 테이블 기록 (없어도 무시)
    try {
        $token = bin2hex(random_bytes(32));
        DB::execute(
            "INSERT INTO sessions (user_id, token, ip_address, user_agent, expires_at, created_at)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY), NOW())",
            [$user['id'], $token, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']
        );
    } catch (Exception $e) {
        error_log("세션 기록 실패 (무시됨): " . $e->getMessage());
    }

    try {
        DB::execute("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$user['id']]);
    } catch (Exception $e) {
        error_log("로그인 시간 업데이트 실패: " . $e->getMessage());
    }
}

function logoutUser(): void {
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        try { DB::execute("DELETE FROM sessions WHERE user_id = ?", [$userId]); }
        catch (Exception $e) { /* 무시 */ }
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
}

function isAdmin(): bool {
    if (!isLoggedIn()) return false;
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) return true;
    try {
        $user = DB::fetchOne("SELECT is_active FROM users WHERE id = ? LIMIT 1", [$_SESSION['user_id']]);
        // plan이 premium/enterprise이면 관리자로 취급
        $plan = $_SESSION['user_plan'] ?? 'free';
        if (in_array($plan, ['premium', 'enterprise'])) {
            $_SESSION['is_admin'] = true;
            return true;
        }
    } catch (Exception $e) { /* 무시 */ }
    return false;
}

function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>접근 거부</title></head>
        <body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#f5f5f5;">
        <div style="background:white;padding:40px;border-radius:12px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
        <div style="font-size:64px;margin-bottom:20px;">🚫</div>
        <h1>접근 권한이 없습니다</h1>
        <p style="color:#666;">관리자만 접근할 수 있습니다.</p>
        <a href="index.php?route=dashboard" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#667eea;color:white;text-decoration:none;border-radius:6px;">대시보드로 돌아가기</a>
        </div></body></html>';
        exit;
    }
}

function authenticateUser(string $email, string $password): array {
    try {
        // ✅ install.php 스키마 기준
        $user = DB::fetchOne(
            "SELECT id, name, email, password_hash, plan, business_name, is_active
             FROM users WHERE email = ? LIMIT 1",
            [$email]
        );
        if (!$user) return ['success'=>false,'error'=>'이메일 또는 비밀번호가 올바르지 않습니다.'];
        if (!password_verify($password, $user['password_hash'])) return ['success'=>false,'error'=>'이메일 또는 비밀번호가 올바르지 않습니다.'];
        if (!$user['is_active']) return ['success'=>false,'error'=>'비활성화된 계정입니다.'];
        unset($user['password_hash']);
        return ['success'=>true,'user'=>$user];
    } catch (Exception $e) {
        error_log("authenticateUser 오류: " . $e->getMessage());
        return ['success'=>false,'error'=>'로그인 처리 중 오류가 발생했습니다.'];
    }
}

function validateSession(): bool {
    if (!isLoggedIn()) return false;
    if (isset($_SESSION['logged_in_at'])) {
        $lifetime = defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400;
        if (time() - $_SESSION['logged_in_at'] > $lifetime) {
            logoutUser();
            return false;
        }
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function refreshSession(): void {
    if (isLoggedIn()) {
        unset($_SESSION['user_data_cached'], $_SESSION['user_data_cache_time']);
        getCurrentUser();
    }
}

function updateSessionUser(array $data): void {
    if (!isLoggedIn()) return;
    if (isset($data['name'])) $_SESSION['user_name'] = $data['name'];
    if (isset($data['email'])) $_SESSION['user_email'] = $data['email'];
    if (isset($data['plan'])) $_SESSION['user_plan'] = $data['plan'];
    if (isset($data['business_name'])) $_SESSION['business_name'] = $data['business_name'];
    refreshSession();
}
