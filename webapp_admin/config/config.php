<?php
// ================================================================
// config.php - 시크릿 키 및 앱 설정
// FTP 업로드 전 이 파일에 값을 입력하세요
// ================================================================

// ── 앱 기본 설정 ──────────────────────────────────────────────
define('APP_NAME',    '셀프마케팅 Pro');
define('APP_URL',     'https://bobjjangs1231.dothome.co.kr/zz/index.php');  // 실제 URL로 변경
define('APP_VERSION', '1.0.0');

// ── 세션 보안 키 (아무 랜덤 문자열로 변경하세요) ──────────────
define('SESSION_SECRET', 'asdcvxcv34f345gfdbg323gf');

// ── 네이버 API ────────────────────────────────────────────────
define('NAVER_CLIENT_ID',     '');   // 네이버 검색 API Client ID
define('NAVER_CLIENT_SECRET', '');   // 네이버 검색 API Client Secret
define('NAVER_AD_API_KEY',    '01000000009943fb34766daff43b4b2a9d8fc0bc8a8c4fd574e97552c8bba51833a82f468a');   // 네이버 광고 API Key
define('NAVER_AD_SECRET_KEY', 'AQAAAACZQ/s0dm2v9DtLKp2PwLyKoFZ2nVx++dHW6+KuJ33Egg==');   // 네이버 광고 Secret Key
define('NAVER_AD_CUSTOMER_ID','3287255');   // 네이버 광고 고객 ID

// ── 인스타그램 / 페이스북 ──────────────────────────────────────
define('INSTAGRAM_APP_ID',     '');  // Facebook App ID
define('INSTAGRAM_APP_SECRET', '');  // Facebook App Secret
define('INSTAGRAM_ACCESS_TOKEN', '');

// ── OpenAI (AI 글쓰기) ────────────────────────────────────────
define('OPENAI_API_KEY', '');        // sk-...

// ── 카카오 ────────────────────────────────────────────────────
define('KAKAO_API_KEY',    '');
define('KAKAO_API_SECRET', '');

// ── Google Analytics ──────────────────────────────────────────
define('GA_MEASUREMENT_ID', '');     // G-XXXXXXXXXX
define('GA_API_SECRET',     '');

// ── 기타 설정 ─────────────────────────────────────────────────
define('TIMEZONE',      'Asia/Seoul');
define('DEBUG_MODE',    true);        // 문제 해결 후 반드시 false로 변경!

// 디버그 모드일 때 에러 표시
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
define('MAX_LOGIN_ATTEMPTS', 5);
define('SESSION_LIFETIME',   86400);  // 24시간 (초)

date_default_timezone_set(TIMEZONE);
