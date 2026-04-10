<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> - 셀프마케팅 관리자</title>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--primary:#e94560;--success:#00b894;--warning:#f5a623;--info:#0066ff;--bg:#f0f2f5;--sidebar:248px;}
body{font-family:'Pretendard',sans-serif;background:var(--bg);display:flex;min-height:100vh;font-size:14px;}

/* 사이드바 */
.sidebar{width:var(--sidebar);background:linear-gradient(180deg,#1a1a2e,#16213e);position:fixed;top:0;left:0;height:100vh;display:flex;flex-direction:column;z-index:100;overflow-y:auto;}
.sb-logo{padding:24px 20px 20px;border-bottom:1px solid rgba(255,255,255,.08);}
.sb-logo .icon{width:38px;height:38px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:8px;}
.sb-logo h2{font-size:15px;font-weight:800;color:#fff;}
.sb-logo p{font-size:11px;color:rgba(255,255,255,.4);margin-top:2px;}
.sb-badge{display:inline-block;background:#e94560;color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:4px;}
.sb-section{padding:16px 12px 4px;font-size:10px;font-weight:700;color:rgba(255,255,255,.3);letter-spacing:.8px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 16px;margin:2px 8px;border-radius:10px;color:rgba(255,255,255,.65);text-decoration:none;font-size:13px;font-weight:500;transition:all .2s;position:relative;}
.nav-item:hover{background:rgba(255,255,255,.08);color:#fff;}
.nav-item.active{background:linear-gradient(135deg,rgba(233,69,96,.35),rgba(245,166,35,.2));color:#fff;font-weight:700;}
.nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:#e94560;border-radius:0 3px 3px 0;}
.nav-count{margin-left:auto;background:#e94560;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;min-width:20px;text-align:center;}
.nav-count.green{background:#00b894;}
.sb-footer{margin-top:auto;padding:16px;border-top:1px solid rgba(255,255,255,.08);}
.sb-footer a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.4);font-size:12px;text-decoration:none;padding:8px 0;}
.sb-footer a:hover{color:#fff;}

/* 메인 */
.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh;}
.topbar{background:#fff;padding:16px 28px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;position:sticky;top:0;z-index:50;}
.topbar-title{font-size:18px;font-weight:800;color:#1a1a2e;}
.topbar-right{display:flex;align-items:center;gap:12px;}
.topbar-right a{color:#888;font-size:13px;text-decoration:none;}
.topbar-right a:hover{color:#e94560;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;font-family:inherit;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,#e94560,#c0392b);color:#fff;}
.btn-success{background:linear-gradient(135deg,#00b894,#00a381);color:#fff;}
.btn-warning{background:linear-gradient(135deg,#f5a623,#e6920a);color:#fff;}
.btn-danger{background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;}
.btn-sm{padding:6px 12px;font-size:12px;}
.btn-outline{background:#fff;border:1.5px solid #ddd;color:#555;}
.btn-outline:hover{border-color:#e94560;color:#e94560;}
.content{padding:28px;flex:1;}

/* 카드 */
.card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.05);margin-bottom:20px;}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;}
.card-title{font-size:15px;font-weight:700;color:#1a1a2e;}

/* 통계 그리드 */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.stat-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.05);display:flex;align-items:center;gap:14px;}
.stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.si-red{background:linear-gradient(135deg,#ffeef1,#ffd5db);}
.si-green{background:linear-gradient(135deg,#e0f7f0,#b2f0e0);}
.si-blue{background:linear-gradient(135deg,#e8f0ff,#c5d8ff);}
.si-orange{background:linear-gradient(135deg,#fff4e0,#ffe0b2);}
.si-purple{background:linear-gradient(135deg,#f3e8ff,#e0c8ff);}
.stat-body .slabel{font-size:11px;color:#aaa;margin-bottom:3px;}
.stat-body .sval{font-size:22px;font-weight:800;color:#1a1a2e;line-height:1.2;}
.stat-body .schange{font-size:11px;margin-top:2px;}
.schange.up{color:#00b894;}
.schange.down{color:#e94560;}

/* 테이블 */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:13px;}
thead th{background:#f8f8fc;padding:11px 14px;font-weight:700;color:#555;text-align:left;border-bottom:2px solid #eee;white-space:nowrap;}
tbody td{padding:12px 14px;border-bottom:1px solid #f0f0f5;vertical-align:middle;}
tbody tr:hover{background:#fafafe;}
tbody tr:last-child td{border-bottom:none;}

/* 배지 */
.badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.badge-pending{background:#fff4e0;color:#e6920a;}
.badge-confirmed{background:#e0f7f0;color:#007a5e;}
.badge-cancelled{background:#ffe4e8;color:#c0001f;}
.badge-free{background:#f0f0f5;color:#888;}
.badge-basic{background:#e8f0ff;color:#0052cc;}
.badge-premium{background:linear-gradient(135deg,#ffeef1,#fff0e0);color:#e94560;}
.badge-enterprise{background:linear-gradient(135deg,#f3e8ff,#e8f0ff);color:#7c4dff;}

/* 모달 */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;padding:32px;width:90%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:17px;font-weight:800;margin-bottom:20px;}
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:12px;font-weight:700;color:#444;margin-bottom:5px;}
.form-control{width:100%;padding:10px 13px;border:2px solid #e8e8e8;border-radius:9px;font-size:13px;font-family:inherit;outline:none;transition:border-color .2s;}
.form-control:focus{border-color:#e94560;}
textarea.form-control{resize:vertical;min-height:80px;}

/* 알림 */
.alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;}
.alert-success{background:#e0f7f0;color:#007a5e;border:1px solid #a3e6d5;}
.alert-warning{background:#fff8e1;color:#8a6d00;border:1px solid #ffe082;}
.alert-danger{background:#ffe4e8;color:#c0001f;border:1px solid #ffb3be;}
.alert-info{background:#e8f0ff;color:#0052cc;border:1px solid #b3ccff;}
</style>
</head>
<body>

<!-- 사이드바 -->
<nav class="sidebar">
  <div class="sb-logo">
    <div class="icon">⚙️</div>
    <h2>관리자 패널</h2>
    <p>셀프마케팅 Pro</p>
  </div>

  <div class="sb-section">메인</div>
  <a href="index.php?p=dashboard" class="nav-item <?= $p==='dashboard'?'active':'' ?>">📊 대시보드</a>

  <div class="sb-section">충전 관리</div>
  <a href="index.php?p=charges" class="nav-item <?= $p==='charges'?'active':'' ?>">
    💰 충전 요청 관리
    <span class="nav-count" id="pendingCount">-</span>
  </a>
  <a href="index.php?p=credits" class="nav-item <?= $p==='credits'?'active':'' ?>">📋 크레딧 내역</a>

  <div class="sb-section">회원 관리</div>
  <a href="index.php?p=users" class="nav-item <?= $p==='users'?'active':'' ?>">👥 회원 목록</a>
  <a href="index.php?p=stats" class="nav-item <?= $p==='stats'?'active':'' ?>">📈 통계</a>

  <div class="sb-footer">
    <a href="<?= MAIN_SITE_URL ?>" target="_blank">🔗 메인 사이트 열기</a>
    <a href="index.php?p=logout">🚪 로그아웃</a>
  </div>
</nav>

<!-- 메인 -->
<div class="main">
  <div class="topbar">
    <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?></div>
    <div class="topbar-right">
      <span style="font-size:12px;color:#bbb;">관리자: <?= ADMIN_ID ?></span>
      <a href="index.php?p=charges" class="btn btn-sm btn-primary">💰 충전 대기 <span id="topPendingCount" style="margin-left:4px;"></span></a>
    </div>
  </div>
  <div class="content">
<?php
// 대기 충전 수 업데이트
try {
    $pendingCount = DB::fetchOne("SELECT COUNT(*) as cnt FROM charge_requests WHERE status='pending'")['cnt'] ?? 0;
} catch(Exception $e) { $pendingCount = 0; }
?>
<script>
const _pending = <?= (int)$pendingCount ?>;
document.addEventListener('DOMContentLoaded', () => {
  const el1 = document.getElementById('pendingCount');
  const el2 = document.getElementById('topPendingCount');
  if(el1) el1.textContent = _pending > 0 ? _pending : '';
  if(el1 && _pending > 0) el1.classList.add('');
  if(el2) el2.textContent = _pending > 0 ? '('+_pending+')' : '';
});
</script>
