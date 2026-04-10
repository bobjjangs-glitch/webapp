<?php
// ================================================================
// includes/layout_top.php
// ================================================================
global $pdo;

$currentUser = getCurrentUser();
$userName    = $currentUser['name']          ?? '게스트';
$userPlan    = $currentUser['plan']          ?? 'free';
$userBiz     = $currentUser['business_name'] ?? '';

$planColor = function_exists('planColor') ? planColor($userPlan) : '#f5a623';
$planLbl   = function_exists('planLabel') ? planLabel($userPlan) : $userPlan;

// ── 광고비 잔액 조회 ─────────────────────────────────────────
$creditBalance = 0;
try {
    $userId = $_SESSION['user_id'] ?? ($currentUser['id'] ?? 0);
    if ($userId && $pdo) {
        try {
            $s = $pdo->prepare("SELECT balance FROM credit_balance WHERE user_id=? LIMIT 1");
            $s->execute([$userId]);
            $creditBalance = (int)($s->fetchColumn() ?: 0);
        } catch (Exception $e) {
            try {
                $s = $pdo->prepare("SELECT credits_balance FROM users WHERE id=? LIMIT 1");
                $s->execute([$userId]);
                $creditBalance = (int)($s->fetchColumn() ?: 0);
            } catch (Exception $e2) { $creditBalance = 0; }
        }
    }
} catch (Exception $e) { $creditBalance = 0; }

// ── 네비게이션 항목 ──────────────────────────────────────────
$navItems = [
    ['id'=>'dashboard',    'label'=>'대시보드',         'icon'=>'📊', 'href'=>'index.php?route=dashboard'],
    ['id'=>'d1',           'label'=>'─ 분석 도구',       'icon'=>'',   'href'=>''],
    ['id'=>'naver-blog',   'label'=>'네이버 블로그',      'icon'=>'📝', 'href'=>'index.php?route=naver-blog'],
    ['id'=>'instagram',    'label'=>'인스타그램',         'icon'=>'📸', 'href'=>'index.php?route=instagram'],
    ['id'=>'seo',          'label'=>'SEO 분석',          'icon'=>'🔍', 'href'=>'index.php?route=seo'],
    ['id'=>'d2',           'label'=>'─ 플레이스',         'icon'=>'',   'href'=>''],
    ['id'=>'place-analyze','label'=>'플레이스 종합분석',  'icon'=>'🔬', 'href'=>'index.php?route=place-analyze'],
    ['id'=>'place-boost',  'label'=>'순위 상승 프로그램', 'icon'=>'⚡', 'href'=>'index.php?route=place-boost'],
    ['id'=>'place-ads',    'label'=>'플레이스 광고',      'icon'=>'📍', 'href'=>'index.php?route=place-ads'],
    ['id'=>'place-rank',   'label'=>'플레이스 순위추적',  'icon'=>'🏆', 'href'=>'index.php?route=place-rank'],
    ['id'=>'d3',           'label'=>'─ 자동화',           'icon'=>'',   'href'=>''],
    ['id'=>'auto-post',    'label'=>'광고 자동화',        'icon'=>'🤖', 'href'=>'index.php?route=auto-post'],
    ['id'=>'blog-rank',    'label'=>'블로그 순위추적',    'icon'=>'📈', 'href'=>'index.php?route=blog-rank'],
    ['id'=>'d4',           'label'=>'─ 데이터',           'icon'=>'',   'href'=>''],
    ['id'=>'analytics',    'label'=>'유입 분석',          'icon'=>'👁️','href'=>'index.php?route=analytics'],
    ['id'=>'settings',     'label'=>'API 설정',           'icon'=>'⚙️','href'=>'index.php?route=settings'],
    ['id'=>'d5',           'label'=>'─ 결제',             'icon'=>'',   'href'=>''],
    ['id'=>'credits',      'label'=>'광고비 충전',        'icon'=>'💰', 'href'=>'index.php?route=credits'],
];

$badges = [
    'place-boost' => '<span class="nav-badge hot">HOT</span>',
    'auto-post'   => '<span class="nav-badge new">NEW</span>',
    'credits'     => '<span class="nav-badge new">충전</span>',
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? '대시보드') ?> - 셀프마케팅 Pro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
    --primary:#e94560;--primary-dark:#c0392b;
    --secondary:#0066ff;--success:#00b894;
    --warning:#f5a623;--purple:#7c4dff;
    --bg:#f0f2f5;--card:#fff;
    --text:#1a1a2e;--text-muted:#888;
    --border:#e8e8f0;--sidebar-width:248px;
}
body{font-family:'Pretendard',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}

/* ── 사이드바 ── */
.sidebar{width:var(--sidebar-width);background:linear-gradient(160deg,#0f3460 0%,#16213e 50%,#0a0a1a 100%);position:fixed;height:100vh;left:0;top:0;overflow-y:auto;z-index:100;box-shadow:4px 0 24px rgba(0,0,0,.4);transition:transform .3s ease;display:flex;flex-direction:column;}
.sidebar::-webkit-scrollbar{width:3px;}.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:2px;}
.logo-area{padding:20px 16px 14px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0;}
.logo-badge{display:inline-flex;align-items:center;gap:11px;text-decoration:none;}
.logo-icon{width:40px;height:40px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;box-shadow:0 4px 12px rgba(233,69,96,.35);}
.logo-text .brand{font-size:16px;font-weight:800;color:#fff;letter-spacing:-.3px;}
.logo-text .sub{font-size:10px;color:rgba(255,255,255,.35);margin-top:1px;letter-spacing:.5px;}
.nav-section{padding:10px;flex:1;}
.nav-section-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.25);padding:6px 12px 2px;margin-top:8px;}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:10px;text-decoration:none;color:rgba(255,255,255,.55);font-size:12.5px;font-weight:500;margin-bottom:1px;transition:all .2s;position:relative;}
.nav-item:hover{background:rgba(255,255,255,.07);color:#fff;transform:translateX(2px);}
.nav-item.active{background:linear-gradient(135deg,rgba(233,69,96,.22),rgba(245,166,35,.12));color:#fff;font-weight:700;}
.nav-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:linear-gradient(180deg,#e94560,#f5a623);border-radius:0 3px 3px 0;}
.nav-icon{font-size:15px;min-width:18px;text-align:center;}
.nav-label{flex:1;}
.nav-badge{font-size:9px;padding:1px 5px;border-radius:4px;font-weight:700;flex-shrink:0;}
.nav-badge.hot{background:#e94560;color:#fff;}
.nav-badge.new{background:#00b894;color:#fff;}
.sidebar-footer{padding:12px 14px 18px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;}
.user-card{display:flex;align-items:center;gap:10px;}
.user-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#e94560,#f5a623);display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff;flex-shrink:0;}
.user-info .uname{font-size:12px;font-weight:600;color:#fff;line-height:1.3;}
.user-info .uplan{font-size:10px;color:<?= htmlspecialchars($planColor) ?>;font-weight:700;}

/* ── 메인 콘텐츠 ── */
.main-content{margin-left:var(--sidebar-width);flex:1;min-height:100vh;display:flex;flex-direction:column;}
.topbar{background:var(--card);padding:0 26px;height:60px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 0 rgba(0,0,0,.06);position:sticky;top:0;z-index:50;}
.topbar-left{display:flex;align-items:center;gap:12px;}
.page-title{font-size:17px;font-weight:700;color:var(--text);}
.topbar-right{display:flex;align-items:center;gap:8px;}

/* ── 버튼 ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit;border:none;text-decoration:none;}
.btn-primary{background:linear-gradient(135deg,#e94560,#c0392b);color:#fff;}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(233,69,96,.4);}
.btn-secondary{background:#f5f5f5;color:#555;border:1px solid #e0e0e0;}
.btn-secondary:hover{background:#ebebeb;}
.btn-success{background:linear-gradient(135deg,#00b894,#00a381);color:#fff;}
.btn-success:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,184,148,.4);}
.btn-warning{background:linear-gradient(135deg,#f5a623,#e69200);color:#fff;}
.btn-danger{background:linear-gradient(135deg,#e94560,#c0392b);color:#fff;}
.btn-danger:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(233,69,96,.4);}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text-muted);}
.btn-outline:hover{border-color:var(--primary);color:var(--primary);}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:7px;}
.btn-lg{padding:12px 22px;font-size:15px;}
.btn-block{width:100%;justify-content:center;}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none!important;}

/* ── 알림 ── */
.notif-btn{position:relative;width:36px;height:36px;background:#f5f5f5;border:none;border-radius:9px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:15px;transition:background .2s;}
.notif-btn:hover{background:#e8e8e8;}
.notif-dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:#e94560;border-radius:50%;border:1.5px solid #fff;display:none;}
.notif-dropdown{position:absolute;top:46px;right:0;width:340px;background:#fff;border-radius:14px;box-shadow:0 10px 40px rgba(0,0,0,.15);z-index:200;display:none;border:1px solid rgba(0,0,0,.06);overflow:hidden;}
.notif-dropdown.open{display:block;}
.notif-header{padding:14px 16px;border-bottom:1px solid #f5f5f5;display:flex;justify-content:space-between;align-items:center;}
.notif-header .ntitle{font-size:14px;font-weight:700;}
.notif-header .mark-all{font-size:11px;color:var(--primary);cursor:pointer;font-weight:600;}
.notif-item{padding:12px 16px;border-bottom:1px solid #f9f9f9;cursor:pointer;transition:background .2s;}
.notif-item:hover{background:#fafafa;}
.notif-item.unread{background:#fff5f7;}
.notif-item .n-title{font-size:13px;font-weight:700;margin-bottom:2px;}
.notif-item .n-msg{font-size:12px;color:#888;}
.notif-item .n-time{font-size:11px;color:#bbb;margin-top:3px;}

/* ── 페이지 ── */
.page-content{padding:22px 26px;flex:1;}
.card{background:var(--card);border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:18px;border:1px solid rgba(0,0,0,.04);}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;}
.card-title{font-size:14.5px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:7px;}

/* ── 통계 카드 ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.stat-card{background:var(--card);border-radius:14px;padding:18px 16px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04);display:flex;align-items:flex-start;gap:13px;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.1);}
.stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.si-red{background:linear-gradient(135deg,#ff6b6b20,#e9456018);}
.si-blue{background:linear-gradient(135deg,#4ecdc420,#44b2ac18);}
.si-green{background:linear-gradient(135deg,#00b89420,#00a38118);}
.si-orange{background:linear-gradient(135deg,#f5a62320,#e6920018);}
.si-purple{background:linear-gradient(135deg,#a29bfe20,#6c5ce718);}
.si-pink{background:linear-gradient(135deg,#fd79a820,#e8499018);}
.stat-body .slabel{font-size:11px;color:#888;font-weight:500;margin-bottom:3px;}
.stat-body .sval{font-size:23px;font-weight:800;color:var(--text);line-height:1.1;}
.stat-body .schange{font-size:11px;font-weight:600;margin-top:3px;}
.schange.up{color:#00b894;}.schange.down{color:#e94560;}.schange.neutral{color:#888;}

/* ── 폼 ── */
.form-group{margin-bottom:13px;}
.form-label{display:block;font-size:12px;font-weight:600;color:#444;margin-bottom:5px;}
.form-control{width:100%;padding:10px 12px;border:2px solid #e8e8e8;border-radius:9px;font-size:13px;color:#333;transition:all .2s;font-family:inherit;outline:none;background:#fff;}
.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(233,69,96,.1);}
textarea.form-control{resize:vertical;min-height:100px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}

/* ── 배지 ── */
.badge{padding:3px 9px;border-radius:5px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:3px;}
.badge-red{background:#ffe4e8;color:#e94560;}
.badge-green{background:#e0f7f0;color:#00b894;}
.badge-blue{background:#e0f0ff;color:#0066ff;}
.badge-orange{background:#fff3e0;color:#f5a623;}
.badge-purple{background:#ede7f6;color:#7c4dff;}
.badge-gray{background:#f5f5f5;color:#666;}

/* ── 테이블 ── */
.table-wrapper{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{font-size:11px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;padding:10px 14px;text-align:left;background:#f9f9fc;border-bottom:2px solid #f0f0f5;}
td{padding:11px 14px;font-size:13px;color:#333;border-bottom:1px solid #f5f5f5;vertical-align:middle;}
tr:hover td{background:#fafafa;}tr:last-child td{border-bottom:none;}

/* ── 순위 배지 ── */
.rank-badge{width:26px;height:26px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;background:#f0f0f0;color:#666;}
.rb-1{background:linear-gradient(135deg,#FFD700,#FFA500);color:#fff;}
.rb-2{background:linear-gradient(135deg,#C0C0C0,#A0A0A0);color:#fff;}
.rb-3{background:linear-gradient(135deg,#CD7F32,#A0522D);color:#fff;}

/* ── 진행바 ── */
.progress-wrap{background:#f0f0f5;border-radius:8px;height:7px;overflow:hidden;}
.progress-fill{height:100%;border-radius:8px;transition:width 1s ease;}
.pf-red{background:linear-gradient(90deg,#e94560,#f5a623);}
.pf-green{background:linear-gradient(90deg,#00b894,#00cec9);}
.pf-blue{background:linear-gradient(90deg,#0066ff,#00b4ff);}
.pf-orange{background:linear-gradient(90deg,#f5a623,#e69200);}
.pf-purple{background:linear-gradient(90deg,#7c4dff,#a29bfe);}

/* ── 로딩 ── */
.loading-overlay{position:fixed;inset:0;background:rgba(255,255,255,.93);display:none;align-items:center;justify-content:center;z-index:9999;flex-direction:column;gap:14px;}
.loading-overlay.active{display:flex;}
.spinner{width:44px;height:44px;border:4px solid #f0f0f5;border-top-color:#e94560;border-radius:50%;animation:spin .8s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-text{font-size:13px;font-weight:600;color:#666;}

/* ── 차트 ── */
.chart-wrap{position:relative;height:260px;}
.chart-sm{position:relative;height:190px;}

/* ── 탭 ── */
.tabs{display:flex;gap:3px;background:#f5f5f5;padding:3px;border-radius:9px;margin-bottom:18px;}
.tab-btn{flex:1;padding:8px 12px;border:none;background:transparent;border-radius:7px;font-size:12px;font-weight:600;color:#888;cursor:pointer;transition:all .2s;font-family:inherit;}
.tab-btn.active{background:#fff;color:var(--primary);box-shadow:0 2px 6px rgba(0,0,0,.08);}

/* ── 알림 박스 ── */
.alert{padding:12px 14px;border-radius:9px;margin-bottom:14px;font-size:13px;line-height:1.5;}
.alert-info{background:#e0f0ff;color:#0066cc;border-left:4px solid #0066ff;}
.alert-success{background:#e0f7f0;color:#007a5e;border-left:4px solid #00b894;}
.alert-warning{background:#fff8e1;color:#8a6d00;border-left:4px solid #f5a623;}
.alert-danger{background:#ffe4e8;color:#c0001f;border-left:4px solid #e94560;}

/* ── 그리드 ── */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;}
.grid-col-3-2{display:grid;grid-template-columns:3fr 2fr;gap:18px;}
.grid-col-2-1{display:grid;grid-template-columns:2fr 1fr;gap:18px;}

/* ── 기타 ── */
.result-section{display:none;}.result-section.visible{display:block;}
.trend-up{color:#00b894;}.trend-down{color:#e94560;}.trend-neutral{color:#888;}
.divider{height:1px;background:#f0f0f5;margin:14px 0;}
.score-circle{width:90px;height:90px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:26px;font-weight:800;}
.score-circle.excellent{background:linear-gradient(135deg,#00b894,#00cec9);color:#fff;}
.score-circle.good{background:linear-gradient(135deg,#0066ff,#00b4ff);color:#fff;}
.score-circle.average{background:linear-gradient(135deg,#f5a623,#e6920a);color:#fff;}
.score-circle.poor{background:linear-gradient(135deg,#e94560,#c0001f);color:#fff;}
.toggle{width:40px;height:22px;background:#ddd;border-radius:11px;position:relative;cursor:pointer;transition:background .3s;}
.toggle.on{background:#e94560;}
.toggle::after{content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;top:3px;left:3px;transition:left .3s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.toggle.on::after{left:21px;}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f9f9f9;font-size:13px;}
.info-row:last-child{border-bottom:none;}
.info-label{color:#888;font-size:12px;}
.info-val{font-weight:600;}
.live-dot{width:8px;height:8px;background:#00b894;border-radius:50%;display:inline-block;margin-right:5px;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.4;}}
.mobile-menu-btn{display:none;background:none;border:none;font-size:20px;cursor:pointer;color:#333;padding:4px;}

/* ── 광고비 배지 ── */
.credit-badge{display:inline-flex;align-items:center;gap:5px;padding:7px 12px;background:linear-gradient(135deg,#0f3460,#16213e);border-radius:9px;text-decoration:none;transition:all .2s;border:1px solid rgba(245,166,35,.3);}
.credit-badge:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(15,52,96,.3);}
.credit-icon{font-size:14px;}
.credit-label{font-size:10px;color:rgba(255,255,255,.55);font-weight:500;}
.credit-amount{font-size:13px;font-weight:800;color:#f5a623;}
.credit-plus{font-size:10px;color:#00b894;font-weight:700;padding:1px 5px;background:rgba(0,184,148,.15);border-radius:4px;}

/* ── 오버레이 & 모달 ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
.overlay.active{display:block;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:16px;padding:24px;width:90%;max-width:520px;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.modal-title{font-size:16px;font-weight:700;}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:#888;}
.modal-close:hover{color:#e94560;}

/* ── 반응형 ── */
@media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:900px){.grid-2,.grid-3,.grid-col-3-2,.grid-col-2-1,.form-row,.form-row-3{grid-template-columns:1fr;}}
@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.open{transform:translateX(0);}
    .main-content{margin-left:0;}
    .stats-grid{grid-template-columns:1fr 1fr;}
    .page-content{padding:14px;}
    .topbar{padding:0 14px;}
    .mobile-menu-btn{display:flex;}
    .credit-label,.credit-plus{display:none;}
}
@media(max-width:480px){
    .stats-grid{grid-template-columns:1fr;}
    .topbar-right{gap:5px;}
}
</style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text" id="loadingText">분석 중...</div>
</div>
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- ══ 사이드바 ══ -->
<nav class="sidebar" id="sidebar">
    <div class="logo-area">
        <a href="index.php?route=dashboard" class="logo-badge">
            <div class="logo-icon">🚀</div>
            <div class="logo-text">
                <div class="brand">셀프마케팅 Pro</div>
                <div class="sub">Smart Marketing Platform</div>
            </div>
        </a>
    </div>
    <div class="nav-section">
        <?php foreach ($navItems as $item):
            $isSectionTitle = (bool)preg_match('/^d\d+$/', $item['id']);
        ?>
            <?php if ($isSectionTitle): ?>
                <div class="nav-section-title"><?= htmlspecialchars($item['label']) ?></div>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav-item <?= ($activeMenu === $item['id']) ? 'active' : '' ?>"
                   data-route="<?= htmlspecialchars($item['id']) ?>">
                    <span class="nav-icon"><?= $item['icon'] ?></span>
                    <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
                    <?= $badges[$item['id']] ?? '' ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">👤</div>
            <div class="user-info">
                <div class="uname">
                    <?= htmlspecialchars($userName) ?>
                    <?= $userBiz ? ' · ' . htmlspecialchars($userBiz) : '' ?>
                </div>
                <div class="uplan"><?= htmlspecialchars($planLbl) ?> 플랜</div>
            </div>
        </div>
    </div>
</nav>

<!-- ══ 메인 콘텐츠 ══ -->
<div class="main-content">
    <header class="topbar">
        <div class="topbar-left">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
            <div class="page-title"><?= htmlspecialchars($pageTitle ?? '') ?></div>
        </div>
        <div class="topbar-right" style="position:relative;">
            <div style="position:relative;">
                <button class="notif-btn" id="notifBtn" onclick="toggleNotif()">
                    🔔<span class="notif-dot" id="notifDot"></span>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <div class="ntitle">알림 센터</div>
                        <span class="mark-all" onclick="markAllRead()">모두 읽음</span>
                    </div>
                    <div id="notifList">
                        <div style="padding:20px;text-align:center;color:#888;font-size:13px;">로딩 중...</div>
                    </div>
                </div>
            </div>
            <a href="index.php?route=credits" class="credit-badge" title="광고비 충전하기">
                <span class="credit-icon">💰</span>
                <span class="credit-label">광고비</span>
                <span class="credit-amount" id="topbarBalance"><?= number_format($creditBalance) ?>원</span>
                <span class="credit-plus">+충전</span>
            </a>
            <a href="index.php?route=settings" class="btn btn-primary btn-sm">⚙️ API 설정</a>
            <button class="btn btn-secondary btn-sm" onclick="confirmLogout()">로그아웃</button>
        </div>
    </header>
    <div class="page-content">

<script>
/* ── 사이드바 토글 ── */
function toggleSidebar(){
    var s=document.getElementById('sidebar');
    var o=document.getElementById('overlay');
    if(!s)return;
    s.classList.toggle('open');
    if(o)o.classList.toggle('active');
    document.body.style.overflow=s.classList.contains('open')?'hidden':'';
}
function closeSidebar(){
    var s=document.getElementById('sidebar');
    var o=document.getElementById('overlay');
    if(s)s.classList.remove('open');
    if(o)o.classList.remove('active');
    document.body.style.overflow='';
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeSidebar();});
window.addEventListener('resize',function(){if(window.innerWidth>768)closeSidebar();});

/* ── JS 보조: URL 기반 활성 메뉴 보정 ── */
(function(){
    var cur=(new URLSearchParams(window.location.search)).get('route')||'dashboard';
    document.querySelectorAll('.nav-item[data-route]').forEach(function(el){
        el.classList.toggle('active', el.dataset.route===cur);
    });
})();

/* ── 알림 ── */
function toggleNotif(){
    var dd=document.getElementById('notifDropdown');
    if(!dd)return;
    var open=dd.classList.toggle('open');
    if(open)loadNotifications();
}
document.addEventListener('click',function(e){
    var btn=document.getElementById('notifBtn');
    var dd=document.getElementById('notifDropdown');
    if(dd&&btn&&!btn.contains(e.target)&&!dd.contains(e.target))dd.classList.remove('open');
});
function loadNotifications(){
    var list=document.getElementById('notifList');
    if(!list)return;
    fetch(apiUrl('api/notifications'))
        .then(function(r){return r.json();})
        .then(function(data){
            var items=data.notifications||data.data||[];
            if(!items.length){
                list.innerHTML='<div style="padding:20px;text-align:center;color:#888;font-size:13px;">새 알림이 없습니다 🎉</div>';
                return;
            }
            var dot=document.getElementById('notifDot');
            if(dot)dot.style.display='block';
            list.innerHTML=items.slice(0,5).map(function(n){
                return '<div class="notif-item'+(n.is_read?'':' unread')+'">'
                    +'<div class="n-title">'+(n.title||'')+'</div>'
                    +'<div class="n-msg">'+(n.message||'')+'</div>'
                    +'<div class="n-time">'+(n.created_at||'').substring(0,16)+'</div>'
                    +'</div>';
            }).join('');
        })
        .catch(function(){
            if(list)list.innerHTML='<div style="padding:20px;text-align:center;color:#888;font-size:13px;">알림을 불러올 수 없습니다.</div>';
        });
}
function markAllRead(){
    var dot=document.getElementById('notifDot');
    if(dot)dot.style.display='none';
    document.querySelectorAll('.notif-item.unread').forEach(function(el){el.classList.remove('unread');});
}

/* ── 로그아웃 ── */
function confirmLogout(){
    if(confirm('로그아웃 하시겠습니까?'))location.href='index.php?route=logout';
}

/* ── 로딩 ── */
function showLoading(msg){
    var el=document.getElementById('loadingOverlay');
    var tx=document.getElementById('loadingText');
    if(el)el.classList.add('active');
    if(tx)tx.textContent=msg||'로딩 중...';
}
function hideLoading(){
    var el=document.getElementById('loadingOverlay');
    if(el)el.classList.remove('active');
}

/* ── 토스트 ── */
(function(){
    var w=document.createElement('div');
    w.id='toastWrap';
    w.style.cssText='position:fixed;bottom:22px;right:22px;display:flex;flex-direction:column;gap:9px;z-index:9997;pointer-events:none;';
    document.body.appendChild(w);
    if(!document.getElementById('toastStyle')){
        var s=document.createElement('style');
        s.id='toastStyle';
        s.textContent='@keyframes toastIn{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:none}}';
        document.head.appendChild(s);
    }
})();
function showToast(msg,type,duration){
    type=type||'default';duration=duration||3000;
    var w=document.getElementById('toastWrap');if(!w)return;
    var colors={success:'#00b894',error:'#e94560',warning:'#f5a623',default:'#1a1a2e'};
    var t=document.createElement('div');
    t.style.cssText='background:'+(colors[type]||colors.default)+';color:#fff;padding:11px 18px;border-radius:9px;font-size:13px;font-weight:600;box-shadow:0 4px 18px rgba(0,0,0,.18);animation:toastIn .22s ease;max-width:320px;pointer-events:auto;font-family:inherit;';
    t.textContent=msg;
    w.appendChild(t);
    setTimeout(function(){
        t.style.transition='opacity .3s,transform .3s';
        t.style.opacity='0';t.style.transform='translateX(14px)';
        setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);},320);
    },duration);
}
function showSuccessToast(msg){showToast(msg,'success');}
function showErrorToast(msg){showToast(msg,'error');}
function showWarningToast(msg){showToast(msg,'warning');}

/* ── 유틸 ── */
function apiUrl(route){return 'index.php?route='+route;}
function fmtNum(n){var x=parseInt(n,10);return isNaN(x)?'0':x.toLocaleString('ko-KR');}
function fmtDate(s){return s?String(s).substring(0,10):'-';}
</script>
