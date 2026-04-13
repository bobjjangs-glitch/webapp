<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>관리자 로그인 - 셀프마케팅 Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@400;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Pretendard',sans-serif;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);min-height:100vh;display:flex;align-items:center;justify-content:center;}
.box{background:#fff;border-radius:20px;padding:40px;width:90%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.4);}
.logo{text-align:center;margin-bottom:28px;}
.logo-icon{width:52px;height:52px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 12px;}
.logo h1{font-size:20px;font-weight:800;color:#1a1a2e;}
.logo p{font-size:12px;color:#e94560;font-weight:700;margin-top:4px;background:#fff0f3;border-radius:6px;padding:3px 10px;display:inline-block;}
label{display:block;font-size:12px;font-weight:700;color:#444;margin-bottom:5px;}
input{width:100%;padding:11px 13px;border:2px solid #e8e8e8;border-radius:9px;font-size:14px;font-family:inherit;outline:none;margin-bottom:14px;transition:border-color .2s;}
input:focus{border-color:#e94560;}
.btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a1a2e,#0f3460);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;}
.btn:hover{opacity:.9;}
.err{background:#ffe4e8;color:#c0001f;border:1px solid #ffb3be;border-radius:9px;padding:11px 14px;font-size:13px;margin-bottom:16px;}
.warn{background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:10px 14px;font-size:12px;color:#8a6d00;margin-top:16px;text-align:center;}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <div class="logo-icon">🔐</div>
    <h1>관리자 패널</h1>
    <p>ADMIN ONLY</p>
  </div>
  <?php if (!empty($loginError)): ?>
  <div class="err">❌ <?= htmlspecialchars($loginError) ?></div>
  <?php endif; ?>
  <form method="POST">
    <label>관리자 아이디</label>
    <input type="text" name="admin_id" placeholder="admin" required autocomplete="username">
    <label>비밀번호</label>
    <input type="password" name="admin_pw" placeholder="관리자 비밀번호" required autocomplete="current-password">
    <button type="submit" class="btn">🔐 로그인</button>
  </form>
  <div class="warn">⚠️ 이 페이지는 관리자 전용입니다. 무단 접근 시 법적 책임이 따를 수 있습니다.</div>
</div>
</body>
</html>
