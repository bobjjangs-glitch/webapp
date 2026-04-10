<?php
// ================================================================
// config.php - 전체 설정 파일
// ================================================================

// ── 앱 기본 설정 ──────────────────────────────────────────────
define('APP_NAME',    '타이어마케팅');
define('APP_URL',     'https://bobjjangs1231.dothome.co.kr/zz/index.php');
define('APP_VERSION', '1.0.0');

// ── 데이터베이스 설정 (보안 주의!) ─────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'bobjjangs1231');
define('DB_USER',    'bobjjangs1231');
define('DB_PASS',    'ssy201029@');  // ⚠️ 실제 운영 시 환경 변수로 관리!
define('DB_CHARSET', 'utf8mb4');

// ── 세션 보안 키 ──────────────────────────────────────────────
define('SESSION_SECRET', 'asdcvxcv34f345gfdbg323gf');

// ── 네이버 API (config.php에 저장된 값은 기본값) ───────────────
// ⚠️ 실제 사용은 api_keys 테이블에 저장된 값을 우선 사용
define('NAVER_CLIENT_ID',     '2dnGBgxEUpkTPzn1z367');
define('NAVER_CLIENT_SECRET', 'fmBN5wQHnO');
define('NAVER_AD_API_KEY',    '01000000009943fb34766daff43b4b2a9d8fc0bc8a8c4fd574e97552c8bba51833a82f468a');
define('NAVER_AD_SECRET_KEY', 'AQAAAACZQ/s0dm2v9DtLKp2PwLyKoFZ2nVx++dHW6+KuJ33Egg==');
define('NAVER_AD_CUSTOMER_ID','3287255');

// ── 인스타그램 / 페이스북 ──────────────────────────────────────
define('INSTAGRAM_APP_ID',     '');
define('INSTAGRAM_APP_SECRET', '');
define('INSTAGRAM_ACCESS_TOKEN', '');

// ── OpenAI (AI 글쓰기) ────────────────────────────────────────
define('OPENAI_API_KEY', '');

// ── 카카오 ────────────────────────────────────────────────────
define('KAKAO_API_KEY',    '');
define('KAKAO_API_SECRET', '');

// ── Google Analytics ──────────────────────────────────────────
define('GA_MEASUREMENT_ID', '');
define('GA_API_SECRET',     '');

// ── 기타 설정 ─────────────────────────────────────────────────
define('TIMEZONE',      'Asia/Seoul');
define('DEBUG_MODE',    true);  // ⚠️ 문제 해결 후 반드시 false로 변경!
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_LIFETIME',   86400);  // 24시간

// 디버그 모드 설정
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set(TIMEZONE);
