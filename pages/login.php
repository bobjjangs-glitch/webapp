<?php
if (isLoggedIn()) { header('Location: index.php?route=dashboard'); exit; }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>로그인 - 셀프마케팅 Pro</title>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Pretendard',sans-serif;background:linear-gradient(135deg,#0f3460 0%,#16213e 50%,#0a0a1a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.login-wrap{width:100%;max-width:420px;}
.login-box{background:#fff;border-radius:20px;padding:40px;box-shadow:0 24px 64px rgba(0,0,0,0.35);}
.logo{text-align:center;margin-bottom:30px;}
.logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 12px;box-shadow:0 8px 24px rgba(233,69,96,0.35);}
.logo h1{font-size:22px;font-weight:800;color:#1a1a2e;letter-spacing:-0.3px;}
.logo p{font-size:12.5px;color:#888;margin-top:5px;}
.form-group{margin-bottom:14px;}
label{display:block;font-size:12px;font-weight:700;color:#444;margin-bottom:5px;}
input{width:100%;padding:11px 13px;border:2px solid #e8e8e8;border-radius:9px;font-size:13px;color:#333;outline:none;font-family:inherit;transition:border-color .2s;}
input:focus{border-color:#e94560;box-shadow:0 0 0 3px rgba(233,69,96,0.1);}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#e94560,#c0392b);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;margin-top:4px;}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(233,69,96,0.4);}
.error-msg{background:#ffe4e8;color:#c0001f;border:1px solid #ffb3be;border-radius:9px;padding:11px 14px;font-size:13px;margin-bottom:14px;display:none;}
.error-msg.show{display:block;}
.divider{height:1px;background:#f0f0f5;margin:20px 0;}
.demo-info{background:#f9f9fc;border-radius:9px;padding:14px;font-size:12px;color:#666;text-align:center;line-height:1.7;}
.demo-info strong{color:#e94560;}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="logo">
      <div class="logo-icon">🚀</div>
      <h1>셀프마케팅 Pro</h1>
      <p>스마트 마케팅 자동화 플랫폼</p>
    </div>

    <div class="error-msg" id="errorMsg"></div>

    <form id="loginForm">
      <div class="form-group">
        <label>이메일</label>
        <input type="email" id="email" placeholder="admin@example.com" required autocomplete="username">
      </div>
      <div class="form-group">
        <label>비밀번호</label>
        <input type="password" id="password" placeholder="비밀번호를 입력하세요" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-login" id="loginBtn">🔐 로그인</button>
    </form>

    <div class="divider"></div>
    <div class="demo-info">
      처음 사용하신다면 <a href="index.php?route=install" style="color:#e94560"><strong>install</strong></a> 에서 설치를 먼저 진행하세요
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  const errEl = document.getElementById('errorMsg');
  btn.textContent = '로그인 중...'; btn.disabled = true;
  errEl.classList.remove('show');

  try {
    const res = await fetch('index.php?route=api/auth/login', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
      })
    });
    const data = await res.json();
    if (data.success) {
      window.location.href = 'index.php?route=dashboard';
    } else {
      errEl.textContent = data.error || '로그인에 실패했습니다.';
      errEl.classList.add('show');
    }
  } catch(e) {
    errEl.textContent = '서버 연결에 실패했습니다.';
    errEl.classList.add('show');
  } finally {
    btn.textContent = '🔐 로그인'; btn.disabled = false;
  }
});
</script>
</body>
</html>
