<?php
$route  = $_GET['route'] ?? '';
$method = getRequestMethod();
$body   = getRequestBody();

// POST /api/auth/login
if ($route === 'api/auth/login' && $method === 'POST') {
    $email    = trim($body['email'] ?? '');
    $password = trim($body['password'] ?? '');

    if (!$email || !$password) {
        jsonResponse(['error' => '이메일과 비밀번호를 입력해주세요.'], 400);
    }

    $user = DB::fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['error' => '이메일 또는 비밀번호가 올바르지 않습니다.'], 401);
    }

    loginUser($user);
    jsonResponse([
        'success' => true,
        'user' => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'plan'  => $user['plan'],
        ],
        'redirect' => 'index.php?route=dashboard'
    ]);
}

// POST /api/auth/logout
if ($route === 'api/auth/logout' && $method === 'POST') {
    logoutUser();
    jsonResponse(['success' => true]);
}

// POST /api/auth/register
if ($route === 'api/auth/register' && $method === 'POST') {
    $email    = trim($body['email'] ?? '');
    $password = trim($body['password'] ?? '');
    $name     = trim($body['name'] ?? '사용자');

    if (!$email || !$password || strlen($password) < 6) {
        jsonResponse(['error' => '이메일과 6자 이상의 비밀번호를 입력해주세요.'], 400);
    }

    $exists = DB::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($exists) {
        jsonResponse(['error' => '이미 가입된 이메일입니다.'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    DB::execute(
        "INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)",
        [$email, $hash, $name]
    );

    $user = DB::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    loginUser($user);
    jsonResponse(['success' => true, 'redirect' => 'index.php?route=dashboard']);
}

jsonResponse(['error' => '잘못된 요청입니다.'], 400);
