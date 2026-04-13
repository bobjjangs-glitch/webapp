<?php
// ================================================================
// includes/helpers.php - 헬퍼 함수 모음
// ================================================================

function getPageTitle(string $route): string {
    $titles = [
        'dashboard'     => '대시보드',
        'place-analyze' => '플레이스 분석',
        'place-boost'   => '플레이스 부스팅',
        'place-rank'    => '플레이스 순위',
        'place-ads'     => '플레이스 광고',
        'analytics'     => '통계 분석',
        'auto-post'     => '자동 포스팅',
        'settings'      => '설정',
        'naver-blog'    => '네이버 블로그',
        'instagram'     => '인스타그램',
        'seo'           => 'SEO 분석',
        'blog-rank'     => '블로그 순위',
        'credits'       => '크레딧 관리',
    ];
    return $titles[$route] ?? '타이어마케팅';
}

// ================================================================
// 플랜 관련 함수
// ================================================================
function planColor($plan) {
    $colors = ['free'=>'#888888','basic'=>'#3498db','pro'=>'#9b59b6','premium'=>'#f39c12','enterprise'=>'#e74c3c'];
    return $colors[strtolower($plan ?? '')] ?? '#888888';
}
function planName($plan) {
    $names = ['free'=>'무료','basic'=>'베이직','pro'=>'프로','premium'=>'프리미엄','enterprise'=>'엔터프라이즈'];
    return $names[strtolower($plan ?? '')] ?? '무료';
}
function planIcon($plan) {
    $icons = ['free'=>'🆓','basic'=>'⭐','pro'=>'💎','premium'=>'👑','enterprise'=>'🏆'];
    return $icons[strtolower($plan ?? '')] ?? '🆓';
}
function planLabel($plan) {
    return planIcon($plan) . ' ' . planName($plan);
}
function planBadge($plan) {
    if (empty($plan)) $plan = 'free';
    return sprintf(
        '<span style="background:%s;color:white;padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;display:inline-block;">%s</span>',
        planColor($plan), htmlspecialchars(planLabel($plan))
    );
}
function planDescription($plan) {
    $d = ['free'=>'무료 플랜','basic'=>'베이직 플랜','pro'=>'프로 플랜','premium'=>'프리미엄 플랜','enterprise'=>'엔터프라이즈 플랜'];
    return $d[strtolower($plan ?? '')] ?? '무료 플랜';
}
function planPrice($plan) {
    $p = ['free'=>0,'basic'=>29000,'pro'=>99000,'premium'=>299000,'enterprise'=>999000];
    return $p[strtolower($plan ?? '')] ?? 0;
}
function planPriceFormatted($plan) {
    $price = planPrice($plan);
    return $price === 0 ? '무료' : number_format($price) . '원/월';
}
function hasPlanFeature($feature, $userPlan = null) {
    if ($userPlan === null) {
        $user = getCurrentUser();
        $userPlan = $user['plan'] ?? 'free';
    }
    $hierarchy = ['free'=>0,'basic'=>1,'pro'=>2,'premium'=>3,'enterprise'=>4];
    $requirements = ['api_keys'=>'free','analytics'=>'free','place_boost'=>'basic','auto_post'=>'basic','advanced_analytics'=>'pro','white_label'=>'premium','api_access'=>'premium','priority_support'=>'premium','custom_domain'=>'enterprise'];
    $required = $requirements[$feature] ?? 'enterprise';
    return ($hierarchy[strtolower($userPlan)] ?? 0) >= ($hierarchy[strtolower($required)] ?? 4);
}

// ================================================================
// API 키 관련 함수
// ================================================================
function getApiKey($service, $userId = null) {
    if ($userId === null) $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return null;
    try {
        return DB::fetchOne(
            "SELECT api_key, api_secret, access_token, status FROM api_keys 
             WHERE user_id = ? AND service = ? AND status = 'active' LIMIT 1",
            [$userId, $service]
        );
    } catch (Exception $e) {
        error_log("getApiKey 오류: " . $e->getMessage());
        return null;
    }
}
function getAllApiKeyStatuses($userId = null) {
    if ($userId === null) $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return [];
    try {
        $rows = DB::fetchAll(
            "SELECT service, status, updated_at FROM api_keys WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
        $statuses = [];
        foreach ($rows as $row) {
            $statuses[$row['service']] = ['status' => $row['status'], 'updated_at' => $row['updated_at']];
        }
        return $statuses;
    } catch (Exception $e) {
        error_log("getAllApiKeyStatuses 오류: " . $e->getMessage());
        return [];
    }
}
function isApiConnected($service, $userId = null) {
    $key = getApiKey($service, $userId);
    return !empty($key) && !empty($key['api_key']);
}

// ================================================================
// ✅ 크레딧 관련 함수 (credit_balance 테이블 사용)
// ================================================================

/**
 * 크레딧 잔액 가져오기 - credit_balance 테이블 사용
 */
function getUserCredits($userId = null) {
    if ($userId === null) $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return 0;
    try {
        // ✅ credit_balance 테이블 (install.php에서 생성됨)
        $balance = DB::fetchColumn(
            "SELECT balance FROM credit_balance WHERE user_id = ? LIMIT 1",
            [$userId]
        );
        return (int)($balance ?? 0);
    } catch (Exception $e) {
        error_log("getUserCredits 오류: " . $e->getMessage());
        return 0;
    }
}

/**
 * 크레딧 차감 - credit_balance + credit_transactions 테이블 사용
 */
function deductCredits($userId, $amount, $action = '', $description = '') {
    if (empty($description)) $description = $action;
    try {
        DB::beginTransaction();

        // 현재 잔액 확인
        $currentBalance = (int)DB::fetchColumn(
            "SELECT balance FROM credit_balance WHERE user_id = ? LIMIT 1",
            [$userId]
        );

        if ($currentBalance < $amount) {
            DB::rollback();
            return false;
        }

        $newBalance = $currentBalance - $amount;

        // ✅ credit_balance 업데이트
        DB::execute(
            "UPDATE credit_balance SET balance = ?, total_used = total_used + ?, updated_at = NOW() WHERE user_id = ?",
            [$newBalance, $amount, $userId]
        );

        // ✅ credit_transactions 기록 (install.php 컬럼명에 맞춤)
        DB::execute(
            "INSERT INTO credit_transactions 
             (user_id, type, amount, balance_after, description, status, created_at) 
             VALUES (?, 'use', ?, ?, ?, 'completed', NOW())",
            [$userId, -$amount, $newBalance, $description ?: '서비스 이용']
        );

        DB::commit();
        return true;
    } catch (Exception $e) {
        DB::rollback();
        error_log("deductCredits 오류: " . $e->getMessage());
        return false;
    }
}

/**
 * 크레딧 추가
 */
function addCredits($userId, $amount, $description = '') {
    try {
        DB::beginTransaction();

        // credit_balance 없으면 생성
        $exists = DB::fetchColumn(
            "SELECT COUNT(*) FROM credit_balance WHERE user_id = ?", [$userId]
        );
        
        if (!$exists) {
            DB::execute(
                "INSERT INTO credit_balance (user_id, balance, total_charged) VALUES (?, 0, 0)",
                [$userId]
            );
        }

        $currentBalance = (int)DB::fetchColumn(
            "SELECT balance FROM credit_balance WHERE user_id = ?", [$userId]
        );
        $newBalance = $currentBalance + $amount;

        DB::execute(
            "UPDATE credit_balance SET balance = ?, total_charged = total_charged + ?, updated_at = NOW() WHERE user_id = ?",
            [$newBalance, $amount, $userId]
        );

        DB::execute(
            "INSERT INTO credit_transactions 
             (user_id, type, amount, balance_after, description, status, created_at) 
             VALUES (?, 'charge', ?, ?, ?, 'completed', NOW())",
            [$userId, $amount, $newBalance, $description ?: '크레딧 충전']
        );

        DB::commit();
        return true;
    } catch (Exception $e) {
        DB::rollback();
        error_log("addCredits 오류: " . $e->getMessage());
        return false;
    }
}

// ================================================================
// 포맷팅 함수
// ================================================================
function formatNumber($number) {
    if (!is_numeric($number)) return '0';
    if ($number >= 1000000) return round($number / 1000000, 1) . 'M';
    if ($number >= 1000) return round($number / 1000, 1) . 'K';
    return number_format($number);
}
function fmtNum($num) { return formatNumber($num); }
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}
function timeAgo($datetime) {
    if (empty($datetime)) return '-';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return '방금 전';
    if ($diff < 3600) return floor($diff/60) . '분 전';
    if ($diff < 86400) return floor($diff/3600) . '시간 전';
    if ($diff < 604800) return floor($diff/86400) . '일 전';
    if ($diff < 2592000) return floor($diff/604800) . '주 전';
    if ($diff < 31536000) return floor($diff/2592000) . '개월 전';
    return floor($diff/31536000) . '년 전';
}
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes/1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes/1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes/1024, 2) . ' KB';
    return $bytes . ' bytes';
}
function formatPrice($price) { return number_format($price) . '원'; }
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11) return preg_replace('/([0-9]{3})([0-9]{4})([0-9]{4})/', '$1-$2-$3', $phone);
    if (strlen($phone) === 10) return preg_replace('/([0-9]{2,3})([0-9]{3,4})([0-9]{4})/', '$1-$2-$3', $phone);
    return $phone;
}
function formatBusinessNumber($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (strlen($number) === 10) return preg_replace('/([0-9]{3})([0-9]{2})([0-9]{5})/', '$1-$2-$3', $number);
    return $number;
}
function str_limit($string, $limit = 100, $end = '...') {
    if (mb_strlen($string) <= $limit) return $string;
    return mb_substr($string, 0, $limit) . $end;
}

// ================================================================
// 보안 함수
// ================================================================
function e($string) {
    if (is_null($string)) return '';
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
function checkPasswordStrength($password) {
    $s = 0;
    if (strlen($password) >= 8) $s++;
    if (strlen($password) >= 12) $s++;
    if (preg_match('/[a-z]/', $password)) $s++;
    if (preg_match('/[A-Z]/', $password)) $s++;
    if (preg_match('/[0-9]/', $password)) $s++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $s++;
    if ($s <= 2) return 'weak';
    if ($s <= 4) return 'medium';
    return 'strong';
}
function isValidEmail($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function isValidUrl($url) { return filter_var($url, FILTER_VALIDATE_URL) !== false; }
function generateRandomString($length = 32) {
    try { return bin2hex(random_bytes($length / 2)); }
    catch (Exception $e) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < $length; $i++) $str .= $chars[rand(0, strlen($chars)-1)];
        return $str;
    }
}

// ================================================================
// JSON 응답
// ================================================================
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
function toJson($data) { return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); }
function fromJson($json) { return json_decode($json, true); }

// ================================================================
// 알림 함수
// ================================================================
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    try {
        DB::execute(
            "INSERT INTO notifications (user_id, title, message, type, link, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $title, $message, $type, $link]
        );
        return true;
    } catch (Exception $e) {
        error_log("createNotification 오류: " . $e->getMessage());
        return false;
    }
}
function getUnreadNotificationCount($userId = null) {
    if ($userId === null) $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return 0;
    try {
        return (int)(DB::fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]
        ) ?? 0);
    } catch (Exception $e) { return 0; }
}

// ================================================================
// 세션 메시지
// ================================================================
function setSuccessMessage($message) { $_SESSION['success_message'] = $message; }
function setErrorMessage($message) { $_SESSION['error_message'] = $message; }
function getSuccessMessage() { $m = $_SESSION['success_message'] ?? null; unset($_SESSION['success_message']); return $m; }
function getErrorMessage() { $m = $_SESSION['error_message'] ?? null; unset($_SESSION['error_message']); return $m; }

// ================================================================
// 유틸리티
// ================================================================
function isActiveMenu($currentRoute, $menuRoute) {
    if (is_array($menuRoute)) return in_array($currentRoute, $menuRoute);
    return $currentRoute === $menuRoute;
}
function downloadCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
function debugLog($message, $data = null) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) return;
    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($data !== null) $log .= ' | ' . print_r($data, true);
    error_log($log);
}

// ✅ getRequestBody 함수 추가 (api/auth.php, api/analyze.php 등에서 사용)
function getRequestBody() {
    static $body = null;
    if ($body === null) {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];
    }
    return $body;
}

function getRequestMethod() { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
function getCurrentUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    return $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
}
function redirect($url) { header('Location: ' . $url); exit; }
function getPost($key, $default = null) { return $_POST[$key] ?? $default; }
function getGet($key, $default = null) { return $_GET[$key] ?? $default; }
function isEmpty($value) {
    if (is_null($value)) return true;
    if (is_string($value) && trim($value) === '') return true;
    if (is_array($value) && empty($value)) return true;
    return false;
}
function array_only($array, $keys) { return array_intersect_key($array, array_flip((array)$keys)); }
function array_except($array, $keys) { return array_diff_key($array, array_flip((array)$keys)); }
function calculatePercentage($part, $total) { if ($total == 0) return 0; return round(($part/$total)*100, 2); }
function calculateAverage($numbers) { if (empty($numbers)) return 0; return array_sum($numbers)/count($numbers); }
function createSlug($string) {
    $string = mb_strtolower($string);
    $string = preg_replace('/[^a-z0-9가-힣\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
function getUserAgent() { return $_SERVER['HTTP_USER_AGENT'] ?? ''; }
function isMobile() { return (bool)preg_match('/(android|iphone|ipad|mobile)/i', getUserAgent()); }
function getBrowser() {
    $ua = getUserAgent();
    if (strpos($ua, 'Chrome') !== false) return 'Chrome';
    if (strpos($ua, 'Safari') !== false) return 'Safari';
    if (strpos($ua, 'Firefox') !== false) return 'Firefox';
    if (strpos($ua, 'Edge') !== false) return 'Edge';
    return 'Unknown';
}
function now() { return date('Y-m-d H:i:s'); }
function today() { return date('Y-m-d'); }
