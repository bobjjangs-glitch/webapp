<?php
function adminIsLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']);
}
function adminRequireLogin(): void {
    if (!adminIsLoggedIn()) {
        header('Location: index.php?p=login');
        header('Cache-Control: no-cache');
        exit;
    }
}
function adminLogin(string $id, string $pw): bool {
    if ($id === ADMIN_ID && $pw === ADMIN_PASSWORD) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $id;
        $_SESSION['admin_login_at']  = time();
        return true;
    }
    return false;
}
function adminLogout(): void {
    session_unset();
    session_destroy();
}
