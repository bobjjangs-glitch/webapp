<?php
// ================================================================
// install.php - 최초 설치: 테이블 생성 + 관리자 계정 생성
// 설치 완료 후 반드시 이 파일을 삭제하거나 접근 차단하세요!
// ================================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminEmail    = trim($_POST['admin_email'] ?? '');
    $adminPassword = trim($_POST['admin_password'] ?? '');
    $adminName     = trim($_POST['admin_name'] ?? '관리자');

    if (!$adminEmail || !$adminPassword) {
        $message = '❌ 이메일과 비밀번호를 입력해주세요.';
    } else {
        try {
            $pdo = DB::connect();

            // ── 테이블 생성 SQL ──────────────────────────────────
            $sqls = [
"CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `plan` ENUM('free','basic','premium','enterprise') DEFAULT 'free',
  `plan_expires_at` DATETIME DEFAULT NULL,
  `business_name` VARCHAR(100) DEFAULT NULL,
  `business_category` VARCHAR(100) DEFAULT NULL,
  `avatar_url` VARCHAR(500) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token` VARCHAR(128) UNIQUE NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_token` (`token`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `places` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `place_name` VARCHAR(200) NOT NULL,
  `place_id` VARCHAR(100) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `address` VARCHAR(300) DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `naver_place_url` VARCHAR(500) DEFAULT NULL,
  `target_keywords` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `place_rank_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `place_id` INT NOT NULL,
  `keyword` VARCHAR(200) NOT NULL,
  `rank` INT DEFAULT NULL,
  `prev_rank` INT DEFAULT NULL,
  `review_count` INT DEFAULT 0,
  `rating` DECIMAL(3,1) DEFAULT 0.0,
  `checked_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_place_id` (`place_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `place_boost_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `place_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `task_type` VARCHAR(50) NOT NULL,
  `keyword` VARCHAR(200) DEFAULT NULL,
  `target_count` INT DEFAULT 100,
  `completed_count` INT DEFAULT 0,
  `status` ENUM('pending','running','paused','completed','failed') DEFAULT 'pending',
  `scheduled_at` DATETIME DEFAULT NULL,
  `config` JSON DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_place_id` (`place_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `user_visits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) DEFAULT NULL,
  `visitor_id` VARCHAR(128) DEFAULT NULL,
  `page_url` VARCHAR(1000) DEFAULT NULL,
  `referrer` VARCHAR(1000) DEFAULT NULL,
  `utm_source` VARCHAR(200) DEFAULT NULL,
  `utm_medium` VARCHAR(200) DEFAULT NULL,
  `utm_campaign` VARCHAR(200) DEFAULT NULL,
  `channel` VARCHAR(100) DEFAULT NULL,
  `device_type` VARCHAR(50) DEFAULT NULL,
  `browser` VARCHAR(100) DEFAULT NULL,
  `os` VARCHAR(100) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `is_new_visitor` TINYINT(1) DEFAULT 0,
  `duration_seconds` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visitor_id` (`visitor_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `blog_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `platform` VARCHAR(50) NOT NULL,
  `blog_url` VARCHAR(500) DEFAULT NULL,
  `blog_name` VARCHAR(200) DEFAULT NULL,
  `api_key` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('active','inactive','error') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `blog_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `blog_account_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `title` VARCHAR(500) NOT NULL,
  `content` LONGTEXT DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `category` VARCHAR(200) DEFAULT NULL,
  `tags` TEXT DEFAULT NULL,
  `status` ENUM('draft','scheduled','published','failed') DEFAULT 'draft',
  `scheduled_at` DATETIME DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `is_ai_generated` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `instagram_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `access_token` TEXT DEFAULT NULL,
  `follower_count` INT DEFAULT 0,
  `status` ENUM('active','inactive','error') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `instagram_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT DEFAULT NULL,
  `user_id` INT NOT NULL,
  `caption` TEXT DEFAULT NULL,
  `image_urls` TEXT DEFAULT NULL,
  `hashtags` TEXT DEFAULT NULL,
  `post_type` VARCHAR(50) DEFAULT 'image',
  `status` ENUM('draft','scheduled','published','failed') DEFAULT 'draft',
  `scheduled_at` DATETIME DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `type` ENUM('success','info','warning','error') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `link` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `service` VARCHAR(100) NOT NULL,
  `api_key` TEXT DEFAULT NULL,
  `api_secret` TEXT DEFAULT NULL,
  `access_token` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive','error') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `user_service` (`user_id`, `service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `credit_balance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `balance` BIGINT DEFAULT 0 COMMENT '잔액 (원 단위)',
  `total_charged` BIGINT DEFAULT 0 COMMENT '누적 충전액',
  `total_used` BIGINT DEFAULT 0 COMMENT '누적 사용액',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `credit_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('charge','use','refund','admin') NOT NULL COMMENT '충전/사용/환불/관리자조정',
  `amount` BIGINT NOT NULL COMMENT '금액 (양수=증가, 음수=감소)',
  `balance_after` BIGINT NOT NULL COMMENT '처리 후 잔액',
  `description` VARCHAR(300) NOT NULL COMMENT '내역 설명',
  `ref_type` VARCHAR(50) DEFAULT NULL COMMENT '참조 타입 (place_boost, ad_campaign 등)',
  `ref_id` INT DEFAULT NULL COMMENT '참조 ID',
  `status` ENUM('pending','completed','failed','cancelled') DEFAULT 'completed',
  `payment_method` VARCHAR(50) DEFAULT NULL COMMENT '결제수단 (card,bank,kakao,naver)',
  `payment_key` VARCHAR(200) DEFAULT NULL COMMENT '결제 고유키',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

"CREATE TABLE IF NOT EXISTS `charge_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` BIGINT NOT NULL COMMENT '요청 충전 금액',
  `payment_method` VARCHAR(50) NOT NULL COMMENT '결제수단',
  `depositor_name` VARCHAR(100) DEFAULT NULL COMMENT '무통장 입금자명',
  `status` ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  `admin_memo` VARCHAR(500) DEFAULT NULL,
  `confirmed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            ];

            foreach ($sqls as $sql) {
                $pdo->exec($sql);
            }

            // ── 관리자 계정 생성 ─────────────────────────────────
            $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password_hash, name, plan) VALUES (?, ?, ?, 'premium')");
            $stmt->execute([$adminEmail, $hash, $adminName]);

            // ── 샘플 알림 ────────────────────────────────────────
            $userId = $pdo->lastInsertId() ?: 1;
            $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES
                (?, '설치 완료!', '셀프마케팅 Pro가 성공적으로 설치되었습니다.', 'success'),
                (?, '시작하기', '설정 메뉴에서 API 키를 등록해보세요.', 'info')")
                ->execute([$userId, $userId]);

            // ── 관리자 크레딧 잔액 초기화 (10,000원 무료 지급) ──
            $pdo->prepare("INSERT IGNORE INTO credit_balance (user_id, balance, total_charged) VALUES (?, 10000, 10000)")
                ->execute([$userId]);
            $pdo->prepare("INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, status) VALUES (?, 'admin', 10000, 10000, '신규 가입 축하 무료 크레딧', 'completed')")
                ->execute([$userId]);

            $success = true;
            $message = '✅ 설치가 완료되었습니다! 이제 <strong>install.php 파일을 FTP에서 삭제</strong>하고 로그인하세요.';

        } catch (Exception $e) {
            $message = '❌ 오류: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>설치 - 셀프마케팅 Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Pretendard',sans-serif;background:linear-gradient(135deg,#0f3460,#16213e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.box{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
.logo{text-align:center;margin-bottom:28px;}
.logo .icon{width:60px;height:60px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px;}
.logo h1{font-size:22px;font-weight:800;color:#1a1a2e;}
.logo p{font-size:13px;color:#888;margin-top:4px;}
.step{background:#f0f9ff;border:1px solid #bce0fd;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:12.5px;color:#0066aa;line-height:1.7;}
.step strong{display:block;font-size:13px;margin-bottom:4px;color:#004d99;}
label{display:block;font-size:12px;font-weight:700;color:#444;margin-bottom:5px;}
input{width:100%;padding:11px 13px;border:2px solid #e8e8e8;border-radius:9px;font-size:13px;color:#333;outline:none;font-family:inherit;margin-bottom:14px;}
input:focus{border-color:#e94560;box-shadow:0 0 0 3px rgba(233,69,96,0.1);}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#e94560,#c0392b);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;}
.btn:hover{opacity:0.9;}
.msg{padding:13px 15px;border-radius:9px;font-size:13px;margin-bottom:16px;line-height:1.5;}
.msg.error{background:#ffe4e8;color:#c0001f;border:1px solid #ffb3be;}
.msg.success{background:#e0f7f0;color:#007a5e;border:1px solid #a3e6d5;}
.warn{background:#fff8e1;border:1px solid #ffe082;border-radius:9px;padding:12px 14px;font-size:12px;color:#8a6d00;margin-top:16px;}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <div class="icon">🚀</div>
    <h1>셀프마케팅 Pro 설치</h1>
    <p>처음 한 번만 실행하세요</p>
  </div>

  <div class="step">
    <strong>📋 설치 전 체크리스트</strong>
    1. <code>config/db.php</code>에 MySQL 정보 입력 완료<br>
    2. <code>config/config.php</code>에 앱 URL 수정 완료<br>
    3. FTP로 모든 파일 업로드 완료
  </div>

  <?php if ($message): ?>
    <div class="msg <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <form method="POST">
    <label>관리자 이름</label>
    <input type="text" name="admin_name" value="관리자" required>
    <label>관리자 이메일</label>
    <input type="email" name="admin_email" placeholder="admin@example.com" required>
    <label>관리자 비밀번호</label>
    <input type="password" name="admin_password" placeholder="8자 이상 권장" required>
    <button type="submit" class="btn">🔧 설치 시작</button>
  </form>
  <?php else: ?>
    <a href="index.php?route=login" class="btn" style="display:block;text-align:center;text-decoration:none;">🔐 로그인하러 가기</a>
  <?php endif; ?>

  <div class="warn">
    ⚠️ 설치 완료 후 반드시 <strong>install.php 파일을 FTP에서 삭제</strong>하거나
    이름을 바꾸세요. 보안상 중요합니다.
  </div>
</div>
</body>
</html>
