<?php
// ── 잔액 및 거래내역 조회 ──────────────────────────────────────
$userId  = $_SESSION['user_id'] ?? 1;
$balance = 0;
$totalCharged = 0;
$totalUsed    = 0;
$transactions = [];
$pendingRequests = [];

try {
    $bal = DB::fetchOne("SELECT * FROM credit_balance WHERE user_id = ?", [$userId]);
    if ($bal) {
        $balance      = (int)$bal['balance'];
        $totalCharged = (int)$bal['total_charged'];
        $totalUsed    = (int)$bal['total_used'];
    }
    $transactions = DB::fetchAll(
        "SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 30",
        [$userId]
    );
    $pendingRequests = DB::fetchAll(
        "SELECT * FROM charge_requests WHERE user_id = ? AND status='pending' ORDER BY created_at DESC",
        [$userId]
    );
} catch (Exception $e) {
    // DB 없을 때 데모 데이터
    $balance      = 47500;
    $totalCharged = 100000;
    $totalUsed    = 52500;
    $transactions = [
        ['id'=>5,'type'=>'use',   'amount'=>-3000,'balance_after'=>47500, 'description'=>'플레이스 순위상승 - 강남 맛집 (100회)','created_at'=>date('Y-m-d H:i:s',strtotime('-2 hours')),'status'=>'completed'],
        ['id'=>4,'type'=>'use',   'amount'=>-5500,'balance_after'=>50500, 'description'=>'키워드 광고 자동화 집행','created_at'=>date('Y-m-d H:i:s',strtotime('-1 day')),'status'=>'completed'],
        ['id'=>3,'type'=>'charge','amount'=>30000,'balance_after'=>56000, 'description'=>'무통장 입금 충전','created_at'=>date('Y-m-d H:i:s',strtotime('-3 days')),'status'=>'completed'],
        ['id'=>2,'type'=>'use',   'amount'=>-44000,'balance_after'=>26000,'description'=>'블로그 자동화 포스팅 20건','created_at'=>date('Y-m-d H:i:s',strtotime('-5 days')),'status'=>'completed'],
        ['id'=>1,'type'=>'charge','amount'=>70000,'balance_after'=>70000, 'description'=>'카카오페이 충전','created_at'=>date('Y-m-d H:i:s',strtotime('-7 days')),'status'=>'completed'],
    ];
    $pendingRequests = [];
}

// 충전 패키지 정의
$packages = [
    ['amount'=>10000,  'label'=>'1만원',  'bonus'=>0,    'tag'=>''],
    ['amount'=>30000,  'label'=>'3만원',  'bonus'=>1500, 'tag'=>''],
    ['amount'=>50000,  'label'=>'5만원',  'bonus'=>3000, 'tag'=>'인기'],
    ['amount'=>100000, 'label'=>'10만원', 'bonus'=>8000, 'tag'=>'추천'],
    ['amount'=>200000, 'label'=>'20만원', 'bonus'=>20000,'tag'=>'베스트'],
    ['amount'=>500000, 'label'=>'50만원', 'bonus'=>75000,'tag'=>'VIP'],
];
?>

<!-- ── 잔액 요약 카드 ─────────────────────────────────────── -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card" style="background:linear-gradient(135deg,#0f3460,#16213e);color:#fff;border:none;">
    <div class="stat-icon" style="background:rgba(255,255,255,0.15);font-size:22px;">💰</div>
    <div class="stat-body">
      <div class="slabel" style="color:rgba(255,255,255,0.6);">현재 광고비 잔액</div>
      <div class="sval" style="color:#f5a623;font-size:26px;">
        <?= number_format($balance) ?>원
      </div>
      <div class="schange" style="color:rgba(255,255,255,0.5);">광고 집행 가능 금액</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">📥</div>
    <div class="stat-body">
      <div class="slabel">누적 충전액</div>
      <div class="sval"><?= number_format($totalCharged) ?>원</div>
      <div class="schange up">총 충전 금액</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-red">📤</div>
    <div class="stat-body">
      <div class="slabel">누적 사용액</div>
      <div class="sval"><?= number_format($totalUsed) ?>원</div>
      <div class="schange down">총 광고비 사용</div>
    </div>
  </div>
</div>

<?php if (!empty($pendingRequests)): ?>
<div class="alert alert-warning">
  ⏳ <strong>입금 확인 대기 중인 요청이 <?= count($pendingRequests) ?>건 있습니다.</strong>
  무통장 입금 후 1~2시간 내 확인됩니다.
  <a href="#history" style="color:#8a6d00;font-weight:700;text-decoration:underline;">내역 보기</a>
</div>
<?php endif; ?>

<!-- ── 충전 패키지 선택 ───────────────────────────────────── -->
<div class="card">
  <div class="card-header">
    <div class="card-title">💳 광고비 충전</div>
    <span class="badge badge-green">보너스 크레딧 지급</span>
  </div>

  <div class="alert alert-info">
    ℹ️ 충전한 금액은 <strong>플레이스 순위상승, 키워드 광고, 자동 포스팅</strong> 등 모든 유료 기능에 사용됩니다.
    5만원 이상 충전 시 <strong>보너스 크레딧</strong>이 추가 지급됩니다.
  </div>

  <!-- 패키지 그리드 -->
  <div class="grid-3" style="margin-bottom:20px;">
    <?php foreach ($packages as $pkg): ?>
    <div class="pkg-card <?= $pkg['tag']==='추천'?'pkg-recommended':'' ?>"
         onclick="selectPackage(<?= $pkg['amount'] ?>, <?= $pkg['bonus'] ?>)"
         id="pkg_<?= $pkg['amount'] ?>">
      <?php if ($pkg['tag']): ?>
        <div class="pkg-tag <?= $pkg['tag']==='VIP'?'pkg-tag-gold':($pkg['tag']==='추천'?'pkg-tag-red':'pkg-tag-blue') ?>">
          <?= $pkg['tag'] ?>
        </div>
      <?php endif; ?>
      <div class="pkg-amount"><?= $pkg['label'] ?></div>
      <div class="pkg-total">
        <?php if ($pkg['bonus'] > 0): ?>
          <span style="color:#00b894;font-size:12px;font-weight:700;">
            +<?= number_format($pkg['bonus']) ?>원 보너스
          </span><br>
          <span style="font-size:15px;font-weight:800;color:#e94560;">
            실 <?= number_format($pkg['amount'] + $pkg['bonus']) ?>원 사용
          </span>
        <?php else: ?>
          <span style="font-size:13px;color:#888;">보너스 없음</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- 직접 입력 -->
  <div class="card" style="background:#f9f9fc;border:2px dashed #e0e0f0;box-shadow:none;margin-bottom:20px;">
    <div style="font-size:13px;font-weight:700;color:#555;margin-bottom:10px;">✏️ 직접 금액 입력</div>
    <div style="display:flex;gap:10px;align-items:center;">
      <input type="number" class="form-control" id="customAmount"
             placeholder="충전 금액 입력 (최소 5,000원)"
             min="5000" step="1000"
             style="max-width:260px;"
             oninput="selectCustom(this.value)">
      <span style="font-size:14px;font-weight:700;color:#333;">원</span>
      <button class="btn btn-secondary" onclick="selectCustom(document.getElementById('customAmount').value)">
        선택
      </button>
    </div>
  </div>

  <!-- 결제수단 선택 -->
  <div class="form-group">
    <label class="form-label">결제수단 선택</label>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
      <div class="pay-method active" onclick="selectMethod('bank',this)" id="method_bank">
        <span style="font-size:20px;">🏦</span>
        <div style="font-size:12px;font-weight:700;margin-top:4px;">무통장 입금</div>
      </div>
      <div class="pay-method" onclick="selectMethod('kakao',this)" id="method_kakao">
        <span style="font-size:20px;">💛</span>
        <div style="font-size:12px;font-weight:700;margin-top:4px;">카카오페이</div>
      </div>
      <div class="pay-method" onclick="selectMethod('naver',this)" id="method_naver">
        <span style="font-size:20px;">🟢</span>
        <div style="font-size:12px;font-weight:700;margin-top:4px;">네이버페이</div>
      </div>
      <div class="pay-method" onclick="selectMethod('card',this)" id="method_card">
        <span style="font-size:20px;">💳</span>
        <div style="font-size:12px;font-weight:700;margin-top:4px;">신용카드</div>
      </div>
    </div>
  </div>

  <!-- 무통장 입금 정보 (기본 표시) -->
  <div id="bankInfo" class="card" style="background:#fff8e1;border:1px solid #ffe082;box-shadow:none;margin-bottom:16px;">
    <div style="font-size:13px;font-weight:700;color:#8a6d00;margin-bottom:10px;">🏦 무통장 입금 계좌 정보</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;">
      <div><span style="color:#888;">은행</span> <strong>국민은행</strong></div>
      <div><span style="color:#888;">계좌번호</span> <strong>123-456-789012</strong></div>
      <div><span style="color:#888;">예금주</span> <strong>(주)셀프마케팅</strong></div>
      <div><span style="color:#888;">입금 확인</span> <strong>1~2시간 내</strong></div>
    </div>
    <div class="form-group" style="margin-top:12px;margin-bottom:0;">
      <label class="form-label">입금자명 (통장에 표시되는 이름)</label>
      <input type="text" class="form-control" id="depositorName"
             placeholder="입금자명을 정확히 입력하세요" style="max-width:300px;">
    </div>
  </div>

  <!-- 카카오/네이버/카드 안내 -->
  <div id="onlineInfo" style="display:none;" class="card" style="background:#e0f7f0;border:1px solid #b2dfdb;box-shadow:none;margin-bottom:16px;">
    <div id="onlineMsg" style="font-size:13px;color:#007a5e;font-weight:600;"></div>
  </div>

  <!-- 충전 금액 요약 -->
  <div id="chargeSummary" style="background:linear-gradient(135deg,#f0f4ff,#e8eeff);border-radius:12px;padding:16px;margin-bottom:16px;display:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:13px;color:#555;">선택 금액</span>
      <span style="font-size:15px;font-weight:700;" id="summaryAmount">-</span>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
      <span style="font-size:13px;color:#00b894;">보너스 크레딧</span>
      <span style="font-size:14px;font-weight:700;color:#00b894;" id="summaryBonus">+0원</span>
    </div>
    <div style="height:1px;background:#d0d8ff;margin:8px 0;"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <span style="font-size:14px;font-weight:700;color:#333;">충전 후 잔액</span>
      <span style="font-size:18px;font-weight:800;color:#e94560;" id="summaryAfter">-</span>
    </div>
  </div>

  <button class="btn btn-primary" style="font-size:15px;padding:14px;" onclick="requestCharge()">
    💰 충전 요청하기
  </button>
</div>

<!-- ── 이용 요금 안내 ─────────────────────────────────────── -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 서비스별 광고비 요금</div>
  </div>
  <div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:#f5f5fa;">
          <th style="padding:10px 14px;text-align:left;font-weight:700;border-bottom:2px solid #e8e8f0;">서비스</th>
          <th style="padding:10px 14px;text-align:center;font-weight:700;border-bottom:2px solid #e8e8f0;">단가</th>
          <th style="padding:10px 14px;text-align:center;font-weight:700;border-bottom:2px solid #e8e8f0;">단위</th>
          <th style="padding:10px 14px;text-align:left;font-weight:700;border-bottom:2px solid #e8e8f0;">설명</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $pricingTable = [
          ['⚡ 플레이스 순위상승 (조회수)', '30원', '1회', '유저 1명이 플레이스 조회'],
          ['⚡ 플레이스 순위상승 (키워드)', '50원', '1회', '키워드 검색 후 유입'],
          ['⚡ 스마트 부스팅 (자동 최적화)', '80원', '1회', 'AI 기반 자동 최적화 유입'],
          ['📝 블로그 자동 포스팅',          '500원', '1건', '네이버 블로그 1건 발행'],
          ['🤖 AI 블로그 글쓰기',            '200원', '1건', 'ChatGPT로 글 자동 생성'],
          ['📸 인스타그램 예약 포스팅',       '300원', '1건', '인스타 1건 자동 발행'],
          ['📍 플레이스 광고 자동화',         '1,000원','1일', '네이버 플레이스 광고 1일'],
          ['🔍 SEO/순위 분석',               '100원', '1회', '실시간 순위 조회'],
        ];
        foreach ($pricingTable as $row): ?>
        <tr style="border-bottom:1px solid #f0f0f5;">
          <td style="padding:10px 14px;"><?= $row[0] ?></td>
          <td style="padding:10px 14px;text-align:center;font-weight:700;color:#e94560;"><?= $row[1] ?></td>
          <td style="padding:10px 14px;text-align:center;"><span class="badge badge-gray"><?= $row[2] ?></span></td>
          <td style="padding:10px 14px;color:#888;"><?= $row[3] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── 충전/사용 내역 ─────────────────────────────────────── -->
<div class="card" id="history">
  <div class="card-header">
    <div class="card-title">📊 충전/사용 내역</div>
    <span style="font-size:12px;color:#888;">최근 30건</span>
  </div>
  <?php if (empty($transactions)): ?>
    <div style="text-align:center;padding:40px;color:#aaa;font-size:14px;">
      아직 거래 내역이 없습니다.
    </div>
  <?php else: ?>
  <div class="table-wrapper">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
      <thead>
        <tr style="background:#f5f5fa;">
          <th style="padding:10px 14px;text-align:left;font-weight:700;border-bottom:2px solid #e8e8f0;">날짜</th>
          <th style="padding:10px 14px;text-align:left;font-weight:700;border-bottom:2px solid #e8e8f0;">내역</th>
          <th style="padding:10px 14px;text-align:right;font-weight:700;border-bottom:2px solid #e8e8f0;">금액</th>
          <th style="padding:10px 14px;text-align:right;font-weight:700;border-bottom:2px solid #e8e8f0;">잔액</th>
          <th style="padding:10px 14px;text-align:center;font-weight:700;border-bottom:2px solid #e8e8f0;">상태</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $tx):
          $isPlus  = $tx['amount'] > 0;
          $amtText = ($isPlus ? '+' : '') . number_format($tx['amount']) . '원';
          $amtColor= $isPlus ? '#00b894' : '#e94560';
          $typeIcon= [
            'charge'=>'📥','use'=>'📤','refund'=>'↩️','admin'=>'🔧'
          ][$tx['type']] ?? '💸';
          $statusBadge = [
            'completed'=>'<span class="badge badge-green">완료</span>',
            'pending'  =>'<span class="badge badge-orange">대기</span>',
            'failed'   =>'<span class="badge badge-red">실패</span>',
            'cancelled'=>'<span class="badge badge-gray">취소</span>',
          ][$tx['status']] ?? '<span class="badge badge-gray">'.$tx['status'].'</span>';
        ?>
        <tr style="border-bottom:1px solid #f0f0f5;">
          <td style="padding:10px 14px;color:#888;white-space:nowrap;font-size:12px;">
            <?= date('m.d H:i', strtotime($tx['created_at'])) ?>
          </td>
          <td style="padding:10px 14px;">
            <?= $typeIcon ?> <?= htmlspecialchars($tx['description']) ?>
          </td>
          <td style="padding:10px 14px;text-align:right;font-weight:700;color:<?= $amtColor ?>;">
            <?= $amtText ?>
          </td>
          <td style="padding:10px 14px;text-align:right;color:#333;font-weight:600;">
            <?= number_format($tx['balance_after']) ?>원
          </td>
          <td style="padding:10px 14px;text-align:center;"><?= $statusBadge ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ── CSS ─────────────────────────────────────────────────── -->
<style>
.pkg-card{
  border:2px solid #e8e8f0;border-radius:14px;padding:18px 14px;
  text-align:center;cursor:pointer;transition:all .2s;position:relative;
  background:#fff;
}
.pkg-card:hover{border-color:#e94560;transform:translateY(-2px);box-shadow:0 6px 18px rgba(233,69,96,0.12);}
.pkg-card.selected{border-color:#e94560;background:linear-gradient(135deg,#fff0f3,#fff8f0);box-shadow:0 4px 16px rgba(233,69,96,0.2);}
.pkg-recommended{border-color:#f5a623;background:linear-gradient(135deg,#fffdf0,#fffaf0);}
.pkg-amount{font-size:22px;font-weight:800;color:#1a1a2e;margin-bottom:6px;}
.pkg-total{font-size:12px;color:#888;min-height:34px;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:2px;}
.pkg-tag{position:absolute;top:-10px;left:50%;transform:translateX(-50%);font-size:10px;font-weight:800;padding:2px 10px;border-radius:20px;white-space:nowrap;}
.pkg-tag-red{background:#e94560;color:#fff;}
.pkg-tag-blue{background:#0066ff;color:#fff;}
.pkg-tag-gold{background:linear-gradient(135deg,#f5a623,#e6920a);color:#fff;}
.pay-method{
  border:2px solid #e8e8f0;border-radius:12px;padding:14px 10px;
  text-align:center;cursor:pointer;transition:all .2s;
}
.pay-method:hover{border-color:#aaa;background:#f9f9fc;}
.pay-method.active{border-color:#e94560;background:#fff0f3;}
</style>

<!-- ── JS ──────────────────────────────────────────────────── -->
<script>
let selectedAmount = 0;
let selectedBonus  = 0;
let selectedMethod = 'bank';
const currentBalance = <?= $balance ?>;

function selectPackage(amount, bonus) {
  selectedAmount = amount;
  selectedBonus  = bonus;
  document.querySelectorAll('.pkg-card').forEach(el => el.classList.remove('selected'));
  const el = document.getElementById('pkg_' + amount);
  if (el) el.classList.add('selected');
  document.getElementById('customAmount').value = '';
  updateSummary();
}

function selectCustom(val) {
  const amt = parseInt(val) || 0;
  if (amt < 5000) return;
  selectedAmount = amt;
  selectedBonus  = 0;
  document.querySelectorAll('.pkg-card').forEach(el => el.classList.remove('selected'));
  updateSummary();
}

function selectMethod(method, el) {
  selectedMethod = method;
  document.querySelectorAll('.pay-method').forEach(e => e.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('bankInfo').style.display   = method === 'bank'   ? 'block' : 'none';
  document.getElementById('onlineInfo').style.display = method !== 'bank'   ? 'block' : 'none';
  const msgs = {
    kakao: '💛 카카오페이 결제는 충전 요청 후 카카오페이 앱에서 결제 링크를 보내드립니다.',
    naver: '🟢 네이버페이 결제는 충전 요청 후 네이버페이 결제 페이지로 안내해드립니다.',
    card:  '💳 신용카드 결제는 충전 요청 후 결제 페이지 링크를 이메일로 보내드립니다.',
  };
  const msgEl = document.getElementById('onlineMsg');
  if (msgEl) msgEl.textContent = msgs[method] || '';
}

function updateSummary() {
  const summary = document.getElementById('chargeSummary');
  if (!selectedAmount) { summary.style.display='none'; return; }
  summary.style.display = 'block';
  document.getElementById('summaryAmount').textContent = number_format(selectedAmount) + '원';
  document.getElementById('summaryBonus').textContent  = '+' + number_format(selectedBonus) + '원';
  document.getElementById('summaryAfter').textContent  = number_format(currentBalance + selectedAmount + selectedBonus) + '원';
}

function number_format(n) {
  return Number(n).toLocaleString('ko-KR');
}

async function requestCharge() {
  if (!selectedAmount || selectedAmount < 5000) {
    alert('충전 금액을 선택하거나 입력해주세요. (최소 5,000원)');
    return;
  }
  const depositor = document.getElementById('depositorName').value.trim();
  if (selectedMethod === 'bank' && !depositor) {
    alert('무통장 입금 시 입금자명을 입력해주세요.');
    document.getElementById('depositorName').focus();
    return;
  }

  const btn = document.querySelector('.btn-primary');
  btn.disabled = true;
  btn.textContent = '요청 중...';

  try {
    const res  = await fetch('index.php?route=api/credits/charge', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        amount:         selectedAmount,
        bonus:          selectedBonus,
        payment_method: selectedMethod,
        depositor_name: depositor,
      })
    });
    const data = await res.json();
    if (data.success) {
      alert('✅ 충전 요청이 완료되었습니다!\n\n' +
        (selectedMethod === 'bank'
          ? '무통장 입금 확인 후 1~2시간 내 잔액이 반영됩니다.\n입금자명: ' + depositor
          : '안내 메시지를 확인해주세요.'));
      location.reload();
    } else {
      alert('오류: ' + (data.error || '요청 실패'));
    }
  } catch(e) {
    alert('네트워크 오류가 발생했습니다.');
  } finally {
    btn.disabled = false;
    btn.textContent = '💰 충전 요청하기';
  }
}

// 기본 선택
selectPackage(50000, 3000);
</script>
