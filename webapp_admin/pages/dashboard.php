<?php
try {
    $totalUsers    = DB::fetchOne("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
    $totalBalance  = DB::fetchOne("SELECT COALESCE(SUM(balance),0) as s FROM credit_balance")['s'] ?? 0;
    $pendingCharge = DB::fetchOne("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as s FROM charge_requests WHERE status='pending'");
    $confirmedToday= DB::fetchOne("SELECT COALESCE(SUM(amount),0) as s FROM charge_requests WHERE status='confirmed' AND DATE(confirmed_at)=CURDATE()")['s'] ?? 0;
    $recentCharges = DB::fetchAll("SELECT cr.*,u.name,u.email FROM charge_requests cr JOIN users u ON u.id=cr.user_id ORDER BY cr.created_at DESC LIMIT 8");
    $recentUsers   = DB::fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
} catch(Exception $e) {
    $totalUsers=$totalBalance=$confirmedToday=0;
    $pendingCharge=['c'=>0,'s'=>0];
    $recentCharges=$recentUsers=[];
}
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-blue">👥</div>
    <div class="stat-body"><div class="slabel">전체 회원</div><div class="sval"><?= number_format($totalUsers) ?>명</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-orange">💰</div>
    <div class="stat-body">
      <div class="slabel">대기 충전 요청</div>
      <div class="sval" style="color:#f5a623;"><?= number_format($pendingCharge['c']) ?>건</div>
      <div class="schange"><?= number_format($pendingCharge['s']) ?>원</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">✅</div>
    <div class="stat-body"><div class="slabel">오늘 승인 금액</div><div class="sval" style="color:#00b894;"><?= number_format($confirmedToday) ?>원</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-purple">💳</div>
    <div class="stat-body"><div class="slabel">전체 보유 크레딧</div><div class="sval"><?= number_format($totalBalance) ?>원</div></div>
  </div>
</div>

<?php if ($pendingCharge['c'] > 0): ?>
<div class="alert alert-warning" style="display:flex;align-items:center;justify-content:space-between;">
  <span>⏳ <strong><?= $pendingCharge['c'] ?>건</strong>의 충전 요청이 승인을 기다리고 있습니다. (총 <?= number_format($pendingCharge['s']) ?>원)</span>
  <a href="index.php?p=charges" class="btn btn-sm btn-warning">바로 처리하기 →</a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;">
  <!-- 최근 충전 요청 -->
  <div class="card" style="margin-bottom:0;">
    <div class="card-header">
      <div class="card-title">💰 최근 충전 요청</div>
      <a href="index.php?p=charges" class="btn btn-sm btn-outline">전체 보기</a>
    </div>
    <?php if (empty($recentCharges)): ?>
      <div style="text-align:center;padding:30px;color:#aaa;font-size:13px;">충전 요청이 없습니다.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>회원</th><th>금액</th><th>방법</th><th>상태</th><th>시간</th><th>처리</th></tr></thead>
        <tbody>
        <?php foreach ($recentCharges as $r): ?>
        <tr>
          <td><div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($r['name']) ?></div><div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($r['email']) ?></div></td>
          <td style="font-weight:700;color:#e94560;"><?= number_format($r['amount']) ?>원</td>
          <td><?= $r['payment_method']==='bank'?'🏦 무통장':($r['payment_method']==='card'?'💳 카드':'📱 '.strtoupper($r['payment_method'])) ?></td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= ['pending'=>'⏳ 대기','confirmed'=>'✅ 승인','cancelled'=>'❌ 취소'][$r['status']] ?? $r['status'] ?></span></td>
          <td style="font-size:12px;color:#aaa;"><?= date('m/d H:i', strtotime($r['created_at'])) ?></td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
            <button class="btn btn-sm btn-success" onclick="confirmCharge(<?= $r['id'] ?>, '<?= htmlspecialchars($r['name']) ?>', <?= $r['amount'] ?>)">✅ 승인</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- 최근 가입 회원 -->
  <div class="card" style="margin-bottom:0;">
    <div class="card-header">
      <div class="card-title">👥 최근 가입 회원</div>
      <a href="index.php?p=users" class="btn btn-sm btn-outline">전체 보기</a>
    </div>
    <?php foreach ($recentUsers as $u): ?>
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f0f0f5;">
      <div style="width:36px;height:36px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;"><?= mb_substr($u['name'],0,1) ?></div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($u['name']) ?></div>
        <div style="font-size:11px;color:#aaa;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($u['email']) ?></div>
      </div>
      <span class="badge badge-<?= $u['plan'] ?>"><?= $u['plan'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
async function confirmCharge(id, name, amount) {
  if (!confirm(`${name} 님의 ${amount.toLocaleString()}원 충전 요청을 승인하시겠습니까?`)) return;
  const res = await callApi('confirm_charge', { id });
  if (res.success) { showAlert('✅ 충전이 승인되었습니다!'); setTimeout(()=>location.reload(), 1000); }
  else showAlert(res.error || '오류 발생', 'danger');
}
</script>
