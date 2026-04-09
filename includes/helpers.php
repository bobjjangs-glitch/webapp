<?php
// ================================================================
// includes/helpers.php - 헬퍼 함수 모음
// ================================================================

/**
 * 페이지 제목 반환
 */
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

/**
 * 플랜 색상 반환
 */
function planColor($plan) {
    if (empty($plan)) return '#888888';
    
    $colors = [
        'free'       => '#888888',
        'basic'      => '#3498db',
        'pro'        => '#9b59b6',
        'premium'    => '#f39c12',
        'enterprise' => '#e74c3c',
    ];
    
    return $colors[strtolower($plan)] ?? '#888888';
}

/**
 * 플랜 이름 반환
 */
function planName($plan) {
    if (empty($plan)) return '무료';
    
    $names = [
        'free'       => '무료',
        'basic'      => '베이직',
        'pro'        => '프로',
        'premium'    => '프리미엄',
        'enterprise' => '엔터프라이즈',
    ];
    
    return $names[strtolower($plan)] ?? '무료';
}

/**
 * 플랜 아이콘 반환
 */
function planIcon($plan) {
    if (empty($plan)) return '🆓';
    
    $icons = [
        'free'       => '🆓',
        'basic'      => '⭐',
        'pro'        => '💎',
        'premium'    => '👑',
        'enterprise' => '🏆',
    ];
    
    return $icons[strtolower($plan)] ?? '🆓';
}

/**
 * 플랜 라벨 반환 (아이콘 + 이름)
 */
function planLabel($plan) {
    if (empty($plan)) return '🆓 무료';
    return planIcon($plan) . ' ' . planName($plan);
}

/**
 * 플랜 배지 HTML 반환
 */
function planBadge($plan) {
    if (empty($plan)) $plan = 'free';
    
    $color = planColor($plan);
    $label = planLabel($plan);
    
    return sprintf(
        '<span class="plan-badge" style="background: %s; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block;">%s</span>',
        $color,
        htmlspecialchars($label)
    );
}

/**
 * 플랜 설명 반환
 */
function planDescription($plan) {
    if (empty($plan)) return '무료 플랜';
    
    $descriptions = [
        'free'       => '무료 플랜 - 기본 기능 제공',
        'basic'      => '베이직 플랜 - 소규모 비즈니스에 적합',
        'pro'        => '프로 플랜 - 중소기업에 최적화',
        'premium'    => '프리미엄 플랜 - 대기업용 고급 기능',
        'enterprise' => '엔터프라이즈 플랜 - 무제한 기능',
    ];
    
    return $descriptions[strtolower($plan)] ?? '무료 플랜';
}

/**
 * 플랜 가격 반환
 */
function planPrice($plan) {
    if (empty($plan)) return 0;
    
    $prices = [
        'free'       => 0,
        'basic'      => 29000,
        'pro'        => 99000,
        'premium'    => 299000,
        'enterprise' => 999000,
    ];
    
    return $prices[strtolower($plan)] ?? 0;
}

/**
 * 플랜 가격 포맷팅
 */
function planPriceFormatted($plan) {
    $price = planPrice($plan);
    
    if ($price === 0) {
        return '무료';
    }
    
    return number_format($price) . '원/월';
}

/**
 * 플랜 권한 확인
 */
function hasPlanFeature($feature, $userPlan = null) {
    if ($userPlan === null) {
        $user = getCurrentUser();
        $userPlan = $user['plan'] ?? 'free';
    }
    
    $planHierarchy = ['free' => 0, 'basic' => 1, 'pro' => 2, 'premium' => 3, 'enterprise' => 4];
    
    $featureRequirements = [
        'api_keys'          => 'free',
        'analytics'         => 'free',
        'place_boost'       => 'basic',
        'auto_post'         => 'basic',
        'advanced_analytics'=> 'pro',
        'white_label'       => 'premium',
        'api_access'        => 'premium',
        'priority_support'  => 'premium',
        'custom_domain'     => 'enterprise',
    ];
    
    $requiredPlan = $featureRequirements[$feature] ?? 'enterprise';
    
    $userLevel = $planHierarchy[strtolower($userPlan)] ?? 0;
    $requiredLevel = $planHierarchy[strtolower($requiredPlan)] ?? 4;
    
    return $userLevel >= $requiredLevel;
}

// ================================================================
// API 관련 함수
// ================================================================

/**
 * 사용자의 API 키 가져오기
 */
function getApiKey($service, $userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    
    if (!$userId) {
        return null;
    }
    
    try {
        $row = DB::fetchOne(
            "SELECT api_key, api_secret, access_token, extra_data, status 
             FROM api_keys 
             WHERE user_id = ? AND service = ? AND status = 'active'
             LIMIT 1",
            [$userId, $service]
        );
        
        return $row;
    } catch (Exception $e) {
        error_log("getApiKey 오류: " . $e->getMessage());
        return null;
    }
}

/**
 * 사용자의 모든 API 키 상태 가져오기
 */
function getAllApiKeyStatuses($userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    
    if (!$userId) {
        return [];
    }
    
    try {
        $rows = DB::fetchAll(
            "SELECT service, status, updated_at 
             FROM api_keys 
             WHERE user_id = ? AND status = 'active'",
            [$userId]
        );
        
        $statuses = [];
        foreach ($rows as $row) {
            $statuses[$row['service']] = [
                'status' => $row['status'],
                'updated_at' => $row['updated_at']
            ];
        }
        
        return $statuses;
    } catch (Exception $e) {
        error_log("getAllApiKeyStatuses 오류: " . $e->getMessage());
        return [];
    }
}

/**
 * API 키가 연동되어 있는지 확인
 */
function isApiConnected($service, $userId = null) {
    $apiKey = getApiKey($service, $userId);
    return !empty($apiKey) && !empty($apiKey['api_key']);
}

// ================================================================
// 포맷팅 함수
// ================================================================

/**
 * 숫자 포맷팅
 */
function formatNumber($number) {
    if (!is_numeric($number)) return '0';
    
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

/**
 * 짧은 숫자 포맷 (JavaScript용)
 */
function fmtNum($num) {
    return formatNumber($num);
}

/**
 * 날짜 포맷팅
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) return '-';
    
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * 상대적 시간 표시 (예: 3분 전)
 */
function timeAgo($datetime) {
    if (empty($datetime)) return '-';
    
    try {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 0) return '방금 전';
        if ($diff < 60) return '방금 전';
        if ($diff < 3600) return floor($diff / 60) . '분 전';
        if ($diff < 86400) return floor($diff / 3600) . '시간 전';
        if ($diff < 604800) return floor($diff / 86400) . '일 전';
        if ($diff < 2592000) return floor($diff / 604800) . '주 전';
        if ($diff < 31536000) return floor($diff / 2592000) . '개월 전';
        
        return floor($diff / 31536000) . '년 전';
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * 파일 크기 포맷팅
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * 가격 포맷팅
 */
function formatPrice($price) {
    return number_format($price) . '원';
}

/**
 * 한국 전화번호 포맷팅
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 10) {
        return preg_replace('/([0-9]{2,3})([0-9]{3,4})([0-9]{4})/', '$1-$2-$3', $phone);
    } elseif (strlen($phone) === 11) {
        return preg_replace('/([0-9]{3})([0-9]{4})([0-9]{4})/', '$1-$2-$3', $phone);
    }
    
    return $phone;
}

/**
 * 사업자등록번호 포맷팅
 */
function formatBusinessNumber($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    
    if (strlen($number) === 10) {
        return preg_replace('/([0-9]{3})([0-9]{2})([0-9]{5})/', '$1-$2-$3', $number);
    }
    
    return $number;
}

/**
 * 문자열 자르기 (한글 지원)
 */
function str_limit($string, $limit = 100, $end = '...') {
    if (mb_strlen($string) <= $limit) {
        return $string;
    }
    
    return mb_substr($string, 0, $limit) . $end;
}

// ================================================================
// 보안 함수
// ================================================================

/**
 * XSS 방지 문자열 이스케이프
 */
function e($string) {
    if (is_null($string)) return '';
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 비밀번호 강도 체크
 */
function checkPasswordStrength($password) {
    $strength = 0;
    
    if (strlen($password) >= 8) $strength++;
    if (strlen($password) >= 12) $strength++;
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
    
    if ($strength <= 2) return 'weak';
    if ($strength <= 4) return 'medium';
    return 'strong';
}

/**
 * 이메일 유효성 검사
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * URL 유효성 검사
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * 랜덤 문자열 생성
 */
function generateRandomString($length = 32) {
    try {
        return bin2hex(random_bytes($length / 2));
    } catch (Exception $e) {
        // random_bytes 실패 시 fallback
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

// ================================================================
// JSON 및 응답 함수
// ================================================================

/**
 * JSON 응답 반환
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 배열을 JSON 문자열로 변환
 */
function toJson($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * JSON 문자열을 배열로 변환
 */
function fromJson($json) {
    return json_decode($json, true);
}

// ================================================================
// 크레딧 관련 함수
// ================================================================

/**
 * 크레딧 잔액 가져오기
 */
function getUserCredits($userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $credits = DB::fetchColumn(
            "SELECT credits_balance FROM users WHERE id = ?",
            [$userId]
        );
        
        return (int)($credits ?? 0);
    } catch (Exception $e) {
        error_log("getUserCredits 오류: " . $e->getMessage());
        return 0;
    }
}

/**
 * 크레딧 차감
 */
function deductCredits($userId, $amount, $reason = '') {
    try {
        DB::beginTransaction();
        
        // 현재 잔액 확인
        $currentBalance = getUserCredits($userId);
        
        if ($currentBalance < $amount) {
            DB::rollback();
            return false;
        }
        
        // 크레딧 차감
        DB::execute(
            "UPDATE users SET credits_balance = credits_balance - ? WHERE id = ?",
            [$amount, $userId]
        );
        
        // 거래 내역 기록
        DB::execute(
            "INSERT INTO credit_transactions (user_id, amount, type, reason, created_at) 
             VALUES (?, ?, 'deduct', ?, NOW())",
            [$userId, $amount, $reason]
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
function addCredits($userId, $amount, $reason = '') {
    try {
        DB::beginTransaction();
        
        // 크레딧 추가
        DB::execute(
            "UPDATE users SET credits_balance = credits_balance + ? WHERE id = ?",
            [$amount, $userId]
        );
        
        // 거래 내역 기록
        DB::execute(
            "INSERT INTO credit_transactions (user_id, amount, type, reason, created_at) 
             VALUES (?, ?, 'add', ?, NOW())",
            [$userId, $amount, $reason]
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
// 알림 관련 함수
// ================================================================

/**
 * 알림 생성
 */
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    try {
        DB::execute(
            "INSERT INTO notifications (user_id, title, message, type, link, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $title, $message, $type, $link]
        );
        
        return true;
    } catch (Exception $e) {
        error_log("createNotification 오류: " . $e->getMessage());
        return false;
    }
}

/**
 * 읽지 않은 알림 개수
 */
function getUnreadNotificationCount($userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }
    
    if (!$userId) {
        return 0;
    }
    
    try {
        $count = DB::fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        
        return (int)($count ?? 0);
    } catch (Exception $e) {
        error_log("getUnreadNotificationCount 오류: " . $e->getMessage());
        return 0;
    }
}

// ================================================================
// 세션 및 메시지 함수
// ================================================================

/**
 * 성공 메시지 세션 설정
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * 에러 메시지 세션 설정
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * 성공 메시지 가져오기 (한 번만)
 */
function getSuccessMessage() {
    $message = $_SESSION['success_message'] ?? null;
    unset($_SESSION['success_message']);
    return $message;
}

/**
 * 에러 메시지 가져오기 (한 번만)
 */
function getErrorMessage() {
    $message = $_SESSION['error_message'] ?? null;
    unset($_SESSION['error_message']);
    return $message;
}

// ================================================================
// 유틸리티 함수
// ================================================================

/**
 * 메뉴 활성화 체크
 */
function isActiveMenu($currentRoute, $menuRoute) {
    if (is_array($menuRoute)) {
        return in_array($currentRoute, $menuRoute);
    }
    return $currentRoute === $menuRoute;
}

/**
 * 배열을 CSV로 다운로드
 */
function downloadCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM 추가 (엑셀에서 한글 깨짐 방지)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if (!empty($data)) {
        // 헤더 출력
        fputcsv($output, array_keys($data[0]));
        
        // 데이터 출력
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

/**
 * 디버그 로그 (개발 중에만 사용)
 */
function debugLog($message, $data = null) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return;
    }
    
    $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    
    if ($data !== null) {
        $logMessage .= ' | Data: ' . print_r($data, true);
    }
    
    error_log($logMessage);
}

/**
 * 요청 메서드 가져오기
 */
function getRequestMethod() {
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * 현재 URL 가져오기
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    return $protocol . '://' . $host . $uri;
}

/**
 * 리다이렉트
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * 안전한 POST 값 가져오기
 */
function getPost($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * 안전한 GET 값 가져오기
 */
function getGet($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * 배열이 비어있는지 확인
 */
function isEmpty($value) {
    if (is_null($value)) return true;
    if (is_string($value) && trim($value) === '') return true;
    if (is_array($value) && empty($value)) return true;
    return false;
}

/**
 * 배열에서 특정 키만 추출
 */
function array_only($array, $keys) {
    return array_intersect_key($array, array_flip((array) $keys));
}

/**
 * 배열에서 특정 키 제외
 */
function array_except($array, $keys) {
    return array_diff_key($array, array_flip((array) $keys));
}

/**
 * 퍼센트 계산
 */
function calculatePercentage($part, $total) {
    if ($total == 0) return 0;
    return round(($part / $total) * 100, 2);
}

/**
 * 평균 계산
 */
function calculateAverage($numbers) {
    if (empty($numbers)) return 0;
    return array_sum($numbers) / count($numbers);
}

/**
 * 슬러그 생성 (URL용)
 */
function createSlug($string) {
    $string = mb_strtolower($string);
    $string = preg_replace('/[^a-z0-9가-힣\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * IP 주소 가져오기
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

/**
 * User Agent 가져오기
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * 모바일 디바이스 체크
 */
function isMobile() {
    $userAgent = getUserAgent();
    return preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
}

/**
 * 브라우저 감지
 */
function getBrowser() {
    $userAgent = getUserAgent();
    
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'MSIE') !== false) return 'Internet Explorer';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    
    return 'Unknown';
}

/**
 * 현재 시간 (한국 시간)
 */
function now() {
    return date('Y-m-d H:i:s');
}

/**
 * 오늘 날짜
 */
function today() {
    return date('Y-m-d');
}
