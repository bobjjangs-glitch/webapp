<?php
$filter = $_GET['filter'] ?? 'pending';
$where  = $filter === 'all' ? '' : "WHERE cr.status = " . DB::connect()->quote($filter);

try {
    $charges = DB::fetchAll("
        SELECT cr.*, u.name, u.email, u.plan,
               cb.balance as current_balance
        FROM charge_requests cr
        JOIN users u ON u.id = cr.user_id
        LEFT JOIN credit_balance cb ON cb.user_id = cr.user_id
        {$where}
        ORDER BY cr.created_at DESC
        LIMIT 100
    ");
    $summary = DB::fetchOne("SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='confirmed' THEN amount ELSE 0 END) as confirmed_sum,
        SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) as pending_sum
        FROM charge_requests");
} catch(Exception $e) {
    $charges = [];
    $summary = ['total'=>0,'pending'=>0,'confirmed_sum'=>0,'pending_sum'=>0];
}
?>

<!-- 요약 -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-orange">⏳</div>
    <div class="stat-body"><div class="slabel">대기 중</div><div class="sval" style="color:#f5a623;"><?= number_format($summary['pending']) ?>건</div><div class="schange"><?= number_format($summary['pending_sum']) ?>원</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">✅</div>
    <div class="stat-body"><div class="slabel">총 승인 금액</div><div class="sval" style="color:#00b894;"><?= number_format($summary['confirmed_sum']) ?>원</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue">📋</div>
    <div class="stat-body"><div class="slabel">전체 요청 수</div><div class="sval"><?= number_format($summary['total']) ?>건</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-red">💰</div>
    <div class="stat-body">
      <div class="slabel">직접 크레딧 지급</div>
      <button class="btn btn-sm btn-primary" onclick="openGrantModal()" style="margin-top:4px;">➕ 수동 지급</button>
    </div>
  </div>
</div>

<!-- 필터 탭 -->
<div style="display:flex;gap:8px;margin-bottom:16px;">
  <?php foreach(['pending'=>'⏳ 대기','confirmed'=>'✅ 승인','cancelled'=>'❌ 취소','all'=>'전체'] as $k=>$v): ?>
  <a href="index.php?p=charges&filter=<?= $k ?>" class="btn btn-sm <?= $filter===$k?'btn-primary':'btn-outline' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</div>

<!-- 충전 요청 테이블 -->
<div class="card">
  <div class="card-header">
    <div class="card-title">💰 충전 요청 목록</div>
    <?php if ($filter === 'pending' && !empty($charges)): ?>
    <button class="btn btn-sm btn-success" onclick="confirmAll()">✅ 대기 전체 승인</button>
    <?php endif; ?>
  </div>

  <?php if (empty($charges)): ?>
  <div style="text-align:center;padding:60px;color:#aaa;">
    <div style="font-size:40px;margin-bottom:12px;">📭</div>
    <div><?= $filter==='pending'?'대기 중인 충전 요청이 없습니다.':'요청 내역이 없습니다.' ?></div>
  </div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>요청일시</th><th>회원</th><th>플랜</th><th>요청금액</th><th>결제수단</th><th>입금자명</th><th>현재잔액</th><th>상태</th><th>관리메모</th><th>처리</th></tr>
      </thead>
      <tbody>
      <?php foreach ($charges as $r): ?>
      <tr id="row-<?= $r['id'] ?>">
        <td style="font-size:12px;color:#888;"><?= date('Y.m.d H:i', strtotime($r['created_at'])) ?></td>
        <td>
          <div style="font-weight:700;"><?= htmlspecialchars($r['name']) ?></div>
          <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($r['email']) ?></div>
        </td>
        <td><span class="badge badge-<?= $r['plan'] ?>"><?= $r['plan'] ?></span></td>
        <td style="font-weight:800;font-size:15px;color:#e94560;"><?= number_format($r['amount']) ?>원</td>
        <td><?= ['bank'=>'🏦 무통장','card'=>'💳 카드','kakao'=>'🟡 카카오','naver'=>'🟢 네이버'][$r['payment_method']] ?? $r['payment_method'] ?></td>
        <td style="font-weight:600;"><?= htmlspecialchars($r['depositor_name'] ?? '-') ?></td>
        <td style="font-size:13px;"><?= number_format($r['current_balance'] ?? 0) ?>원</td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= ['pending'=>'⏳ 대기','confirmed'=>'✅ 승인','cancelled'=>'❌ 취소'][$r['status']] ?></span></td>
        <td style="font-size:12px;color:#888;max-width:120px;"><?= htmlspecialchars($r['admin_memo'] ?? '') ?></td>
        <td>
          <div style="display:flex;gap:6px;">
            <?php if ($r['status'] === 'pending'): ?>
            <button class="btn btn-sm btn-success" onclick="confirmCharge(<?= $r['id'] ?>,'<?= htmlspecialchars($r['name']) ?>',<?= $r['amount'] ?>)">✅ 승인</button>
            <button class="btn btn-sm btn-danger"  onclick="cancelCharge(<?= $r['id'] ?>,'<?= htmlspecialchars($r['name']) ?>')">❌ 취소</button>
            <?php elseif ($r['status'] === 'confirmed'): ?>
            <span style="font-size:12px;color:#00b894;">✅ <?= $r['confirmed_at'] ? date('m/d H:i', strtotime($r['confirmed_at'])) : '' ?></span>
            <?php else: ?>
            <span style="font-size:12px;color:#e94560;">❌ 취소됨</span>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- 수동 크레딧 지급 모달 -->
<div class="modal-overlay" id="grantModalOverlay" onclick="if(event.target===this)closeGrantModal()">
  <div class="modal">
    <h3>➕ 수동 크레딧 지급</h3>
    <div class="form-group">
      <label class="form-label">회원 이메일</label>
      <input type="email" class="form-control" id="grantEmail" placeholder="user@example.com">
    </div>
    <div class="form-group">
      <label class="form-label">지급 금액 (원)</label>
      <input type="number" class="form-control" id="grantAmount" placeholder="10000" min="100">
    </div>
    <div class="form-group">
      <label class="form-label">메모</label>
      <input type="text" class="form-control" id="grantMemo" placeholder="예) 이벤트 보너스 지급">
    </div>
    <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
      <button class="btn btn-outline" onclick="closeGrantModal()">취소</button>
      <button class="btn btn-success" onclick="doGrantCredit()">💰 지급하기</button>
    </div>
  </div>
</div>

<script>
async function confirmCharge(id, name, amount) {
  if (!confirm(`${name} 님의 ${amount.toLocaleString()}원 충전 요청을 승인하시겠습니까?\n\n잔액에 즉시 반영됩니다.`)) return;
  const res = await callApi('confirm_charge', { id });
  if (res.success) {
    showAlert('✅ ' + name + ' 님 ' + amount.toLocaleString() + '원 충전 승인 완료!');
    setTimeout(() => location.reload(), 1200);
  } else { showAlert(res.error || '오류 발생', 'danger'); }
}

async function cancelCharge(id, name) {
  const memo = prompt(name + ' 님의 충전 요청을 취소합니다.\n취소 사유를 입력하세요 (선택):');
  if (memo === null) return;
  const res = await callApi('cancel_charge', { id, memo });
  if (res.success) { showAlert('❌ 충전 요청이 취소되었습니다.', 'warning'); setTimeout(() => location.reload(), 1200); }
  else showAlert(res.error || '오류 발생', 'danger');
}

async function confirmAll() {
  const pending = document.querySelectorAll('[id^="row-"]').length;
  if (!confirm(`대기 중인 ${pending}건의 충전 요청을 전체 승인하시겠습니까?`)) return;
  const res = await callApi('confirm_all_charges');
  if (res.success) { showAlert('✅ ' + res.count + '건 전체 승인 완료!'); setTimeout(() => location.reload(), 1200); }
  else showAlert(res.error || '오류 발생', 'danger');
}

function openGrantModal()  { document.getElementById('grantModalOverlay').classList.add('open'); }
function closeGrantModal() { document.getElementById('grantModalOverlay').classList.remove('open'); }

async function doGrantCredit() {
  const email  = document.getElementById('grantEmail').value.trim();
  const amount = parseInt(document.getElementById('grantAmount').value);
  const memo   = document.getElementById('grantMemo').value.trim() || '관리자 수동 지급';
  if (!email || !amount || amount < 100) { alert('이메일과 금액을 올바르게 입력해주세요.'); return; }
  const res = await callApi('grant_credit', { email, amount, memo });
  if (res.success) { showAlert('✅ ' + email + ' 님에게 ' + amount.toLocaleString() + '원 지급 완료!'); closeGrantModal(); setTimeout(() => location.reload(), 1500); }
  else showAlert(res.error || '오류 발생', 'danger');
}
</script>
