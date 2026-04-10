<?php
$search = trim($_GET['q'] ?? '');
$planFilter = $_GET['plan'] ?? '';
$whereArr = [];
$params   = [];
if ($search) { $whereArr[] = "(u.email LIKE ? OR u.name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($planFilter) { $whereArr[] = "u.plan = ?"; $params[] = $planFilter; }
$where = $whereArr ? 'WHERE '.implode(' AND ',$whereArr) : '';
try {
    $users = DB::fetchAll("SELECT u.*, cb.balance, (SELECT COUNT(*) FROM credit_transactions ct WHERE ct.user_id=u.id) as tx_count FROM users u LEFT JOIN credit_balance cb ON cb.user_id=u.id {$where} ORDER BY u.created_at DESC LIMIT 100", $params);
    $planStats = DB::fetchAll("SELECT plan, COUNT(*) as cnt FROM users GROUP BY plan");
} catch(Exception $e) { $users=[]; $planStats=[]; }
?>

<div class="card">
  <div class="card-header">
    <div style="display:flex;gap:10px;align-items:center;flex:1;">
      <form method="GET" action="" style="display:flex;gap:8px;flex:1;">
        <input type="hidden" name="p" value="users">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="이메일 또는 이름 검색..." style="max-width:280px;">
        <select name="plan" class="form-control" style="max-width:130px;">
          <option value="">전체 플랜</option>
          <?php foreach(['free','basic','premium','enterprise'] as $pl): ?>
          <option value="<?= $pl ?>" <?= $planFilter===$pl?'selected':'' ?>><?= $pl ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">🔍 검색</button>
        <?php if($search||$planFilter): ?><a href="index.php?p=users" class="btn btn-sm btn-outline">초기화</a><?php endif; ?>
      </form>
    </div>
    <div style="font-size:13px;color:#888;">총 <?= count($users) ?>명</div>
  </div>

  <!-- 플랜 통계 뱃지 -->
  <div style="display:flex;gap:8px;margin-bottom:16px;">
    <?php foreach($planStats as $ps): ?>
    <span class="badge badge-<?= $ps['plan'] ?>" style="font-size:12px;padding:5px 12px;"><?= $ps['plan'] ?> <?= $ps['cnt'] ?>명</span>
    <?php endforeach; ?>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>회원정보</th><th>플랜</th><th>크레딧 잔액</th><th>거래 수</th><th>가입일</th><th>최근 로그인</th><th>상태</th><th>관리</th></tr>
      </thead>
      <tbody>
      <?php foreach ($users as $i => $u): ?>
      <tr id="urow-<?= $u['id'] ?>">
        <td style="color:#aaa;font-size:12px;"><?= $u['id'] ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;background:linear-gradient(135deg,#e94560,#f5a623);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;"><?= mb_substr($u['name'],0,1) ?></div>
            <div>
              <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($u['name']) ?></div>
              <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($u['email']) ?></div>
              <?php if($u['business_name']): ?><div style="font-size:11px;color:#888;">🏢 <?= htmlspecialchars($u['business_name']) ?></div><?php endif; ?>
            </div>
          </div>
        </td>
        <td><span class="badge badge-<?= $u['plan'] ?>"><?= $u['plan'] ?></span></td>
        <td style="font-weight:700;color:<?= ($u['balance']??0)>0?'#e94560':'#aaa' ?>;"><?= number_format($u['balance']??0) ?>원</td>
        <td style="color:#888;"><?= number_format($u['tx_count']??0) ?>회</td>
        <td style="font-size:12px;color:#aaa;"><?= date('Y.m.d', strtotime($u['created_at'])) ?></td>
        <td style="font-size:12px;color:#aaa;"><?= $u['last_login_at'] ? date('m.d H:i', strtotime($u['last_login_at'])) : '-' ?></td>
        <td><?= $u['is_active'] ? '<span style="color:#00b894;font-size:12px;">● 활성</span>' : '<span style="color:#e94560;font-size:12px;">● 비활성</span>' ?></td>
        <td>
          <div style="display:flex;gap:5px;">
            <button class="btn btn-sm btn-success" onclick="quickGrant(<?= $u['id'] ?>,'<?= htmlspecialchars($u['name']) ?>','<?= htmlspecialchars($u['email']) ?>')">💰 지급</button>
            <button class="btn btn-sm btn-outline" onclick="changePlan(<?= $u['id'] ?>,'<?= htmlspecialchars($u['name']) ?>','<?= $u['plan'] ?>')">🔄 플랜</button>
            <?php if($u['is_active']): ?>
            <button class="btn btn-sm btn-danger" onclick="toggleUser(<?= $u['id'] ?>,0,'<?= htmlspecialchars($u['name']) ?>')">🚫 정지</button>
            <?php else: ?>
            <button class="btn btn-sm btn-warning" onclick="toggleUser(<?= $u['id'] ?>,1,'<?= htmlspecialchars($u['name']) ?>')">✅ 활성화</button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function quickGrant(userId, name, email) {
  const amount = prompt(name + ' 님에게 크레딧을 직접 지급합니다.\n지급 금액을 입력하세요 (원):');
  if (!amount || isNaN(+amount) || +amount < 100) return;
  const memo = prompt('메모 (선택):', '관리자 수동 지급') ?? '관리자 수동 지급';
  const res = await callApi('grant_credit', { email, amount: +amount, memo });
  if (res.success) showAlert('✅ ' + name + ' 님에게 ' + (+amount).toLocaleString() + '원 지급 완료!');
  else showAlert(res.error || '오류', 'danger');
}
async function changePlan(userId, name, curPlan) {
  const plans = ['free','basic','premium','enterprise'];
  const newPlan = prompt(name + ' 님의 플랜을 변경합니다.\n현재: ' + curPlan + '\n변경할 플랜을 입력하세요:\n' + plans.join(' / '));
  if (!newPlan || !plans.includes(newPlan)) { alert('올바른 플랜명을 입력하세요.'); return; }
  const res = await callApi('change_plan', { user_id: userId, plan: newPlan });
  if (res.success) { showAlert('✅ ' + name + ' 님 플랜 변경: ' + newPlan); setTimeout(()=>location.reload(),1200); }
  else showAlert(res.error || '오류', 'danger');
}
async function toggleUser(userId, active, name) {
  const msg = active ? name+' 님을 활성화하시겠습니까?' : name+' 님의 계정을 정지하시겠습니까?';
  if (!confirm(msg)) return;
  const res = await callApi('toggle_user', { user_id: userId, is_active: active });
  if (res.success) { showAlert(active?'✅ 활성화되었습니다.':'🚫 계정이 정지되었습니다.', active?'success':'warning'); setTimeout(()=>location.reload(),1200); }
  else showAlert(res.error || '오류', 'danger');
}
</script>
