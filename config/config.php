<?php
// ================================================================
// config.php - 전체 설정 파일
// ================================================================

define('APP_NAME',    '셀프마케팅 Pro');
define('APP_URL',     'https://bobjjangs1231.dothome.co.kr/zz/index.php');
define('APP_VERSION', '1.0.0');

// ── 데이터베이스 설정 ────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'bobjjangs1231');
define('DB_USER',    'bobjjangs1231');
define('DB_PASS',    'ssy201029@');
define('DB_CHARSET', 'utf8mb4');

// ── 세션 ─────────────────────────────────────────────────────
define('SESSION_SECRET',   'asdcvxcv34f345gfdbg323gf');
define('SESSION_LIFETIME', 86400);

// ── 네이버 API ───────────────────────────────────────────────
define('NAVER_CLIENT_ID',     '2dnGBgxEUpkTPzn1z367');
define('NAVER_CLIENT_SECRET', 'fmBN5wQHnO');
define('NAVER_AD_API_KEY',    '01000000009943fb34766daff43b4b2a9d8fc0bc8a8c4fd574e97552c8bba51833a82f468a');
define('NAVER_AD_SECRET_KEY', 'AQAAAACZQ/s0dm2v9DtLKp2PwLyKoFZ2nVx++dHW6+KuJ33Egg==');
define('NAVER_AD_CUSTOMER_ID','3287255');

// ── 인스타그램 / 페이스북 ────────────────────────────────────
define('INSTAGRAM_APP_ID',       '');
define('INSTAGRAM_APP_SECRET',   '');
define('INSTAGRAM_ACCESS_TOKEN', '');

// ── OpenAI ───────────────────────────────────────────────────
define('OPENAI_API_KEY', '');

// ── 카카오 ───────────────────────────────────────────────────
define('KAKAO_API_KEY',    '');
define('KAKAO_API_SECRET', '');

// ── Google Analytics ─────────────────────────────────────────
define('GA_MEASUREMENT_ID', '');
define('GA_API_SECRET',     '');

// ── 기타 ─────────────────────────────────────────────────────
define('TIMEZONE',           'Asia/Seoul');
define('DEBUG_MODE',         false); // ★ API JSON 깨짐 방지 - 반드시 false
define('MAX_LOGIN_ATTEMPTS', 5);

// ── 에러 설정 ────────────────────────────────────────────────
// DEBUG_MODE 와 무관하게 display_errors 는 항상 OFF
// (API 응답에 PHP 경고문이 섞이면 JSON 파싱 실패)
ini_set('display_errors',         0);
ini_set('display_startup_errors', 0);
ini_set('log_errors',             1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
error_reporting(E_ALL); // 로그에는 모두 기록, 화면 출력만 OFF

date_default_timezone_set(TIMEZONE);
