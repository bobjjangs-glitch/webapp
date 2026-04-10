<?php
// ================================================================
// api/auth.php - 인증 API (로그인/로그아웃/회원가입)
// ================================================================

$route  = $_GET['route'] ?? '';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// php://input 읽기 (getRequestBody 대신 직접 처리)
$inputRaw = file_get_contents('php://input');
$body = json_decode($inputRaw, true) ?? [];

// ================================================================
// POST /api/auth/login
// ================================================================
if ($route === 'api/auth/login' && $method === 'POST') {
    $email    = trim($body['email'] ?? '');
    $password = trim($body['password'] ?? '');

    if (empty($email) || empty($password)) {
        jsonResponse(['success' => false, 'error' => '이메일과 비밀번호를 입력해주세요.'], 400);
    }

    try {
        // ✅ install.php에서 만든 컬럼명(password_hash, is_active) 사용
        $user = DB::fetchOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if (!$user) {
            jsonResponse(['success' => false, 'error' => '이메일 또는 비밀번호가 올바르지 않습니다.'], 401);
        }

        // ✅ password_hash 컬럼 확인
        $passwordField = isset($user['password_hash']) ? 'password_hash' : 'password';
        if (!password_verify($password, $user[$passwordField])) {
            jsonResponse(['success' => false, 'error' => '이메일 또는 비밀번호가 올바르지 않습니다.'], 401);
        }

        // ✅ 세션에 저장 (name 컬럼 우선, 없으면 username)
        $displayName = $user['name'] ?? $user['username'] ?? '사용자';

        // 세션 재생성
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $displayName;
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_plan']  = $user['plan'] ?? 'free';
        $_SESSION['logged_in_at'] = time();

        // 마지막 로그인 업데이트
        try {
            DB::execute("UPDATE users SET last_login_at = NOW() WHERE id = ?", [$user['id']]);
        } catch (Exception $e) {
            // 무시
        }

        jsonResponse([
            'success'  => true,
            'user'     => [
                'id'    => $user['id'],
                'name'  => $displayName,
                'email' => $user['email'],
                'plan'  => $user['plan'] ?? 'free',
            ],
            'redirect' => 'index.php?route=dashboard'
        ]);

    } catch (Exception $e) {
        error_log("로그인 오류: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'DB 오류: ' . $e->getMessage()], 500);
    }
}

// ================================================================
// POST /api/auth/logout
// ================================================================
if ($route === 'api/auth/logout' && $method === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;

    // DB 세션 삭제 시도 (테이블 없어도 무시)
    if ($userId) {
        try {
            DB::execute("DELETE FROM sessions WHERE user_id = ?", [$userId]);
        } catch (Exception $e) { /* 무시 */ }
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    jsonResponse(['success' => true, 'redirect' => 'index.php?route=login']);
}

// ================================================================
// POST /api/auth/register
// ================================================================
if ($route === 'api/auth/register' && $method === 'POST') {
    $email    = trim($body['email'] ?? '');
    $password = trim($body['password'] ?? '');
    $name     = trim($body['name'] ?? '사용자');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'error' => '올바른 이메일을 입력해주세요.'], 400);
    }

    if (empty($password) || strlen($password) < 6) {
        jsonResponse(['success' => false, 'error' => '비밀번호는 6자 이상이어야 합니다.'], 400);
    }

    try {
        // 이메일 중복 확인
        $exists = DB::fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
        if ($exists) {
            jsonResponse(['success' => false, 'error' => '이미 가입된 이메일입니다.'], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        DB::execute(
            "INSERT INTO users (email, password_hash, name, plan, is_active, created_at) 
             VALUES (?, ?, ?, 'free', 1, NOW())",
            [$email, $hash, $name]
        );

        $newUserId = DB::lastInsertId();

        // 크레딧 초기화 (credit_balance 테이블)
        try {
            DB::execute(
                "INSERT IGNORE INTO credit_balance (user_id, balance, total_charged) VALUES (?, 1000, 1000)",
                [$newUserId]
            );
            DB::execute(
                "INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, status) 
                 VALUES (?, 'admin', 1000, 1000, '신규 가입 무료 크레딧', 'completed')",
                [$newUserId]
            );
        } catch (Exception $e) {
            error_log("크레딧 초기화 실패 (무시됨): " . $e->getMessage());
        }

        // 세션 설정
        session_regenerate_id(true);
        $_SESSION['user_id']    = $newUserId;
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_plan']  = 'free';
        $_SESSION['logged_in_at'] = time();

        jsonResponse([
            'success'  => true,
            'redirect' => 'index.php?route=dashboard'
        ]);

    } catch (Exception $e) {
        error_log("회원가입 오류: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => '회원가입 처리 중 오류가 발생했습니다: ' . $e->getMessage()], 500);
    }
}

jsonResponse(['success' => false, 'error' => '잘못된 요청입니다.', 'route' => $route, 'method' => $method], 400);
