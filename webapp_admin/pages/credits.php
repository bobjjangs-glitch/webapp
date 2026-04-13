<?php
$search = trim($_GET['q'] ?? '');
$type   = $_GET['type'] ?? '';
$whereArr = [];
$params   = [];
if ($search) { $whereArr[] = "(u.email LIKE ? OR u.name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($type)   { $whereArr[] = "ct.type = ?"; $params[] = $type; }
$where = $whereArr ? 'WHERE '.implode(' AND ', $whereArr) : '';
try {
    $txList = DB::fetchAll("SELECT ct.*, u.name, u.email FROM credit_transactions ct JOIN users u ON u.id=ct.user_id {$where} ORDER BY ct.created_at DESC LIMIT 200", $params);
    $typeSummary = DB::fetchAll("SELECT type, COUNT(*) as cnt, SUM(amount) as total FROM credit_transactions GROUP BY type");
} catch(Exception $e) { $txList=[]; $typeSummary=[]; }
$typeLabels = ['charge'=>'💰 충전','use'=>'📤 사용','refund'=>'↩️ 환불','admin'=>'⚙️ 관리자'];
?>

<!-- 타입별 요약 -->
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
<?php foreach($typeSummary as $ts): ?>
<div style="background:#fff;border-radius:12px;padding:16px 20px;min-width:140px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
  <div style="font-size:11px;color:#aaa;margin-bottom:4px;"><?= $typeLabels[$ts['type']] ?? $ts['type'] ?></div>
  <div style="font-size:18px;font-weight:800;color:#1a1a2e;"><?= number_format($ts['total']) ?>원</div>
  <div style="font-size:11px;color:#888;"><?= number_format($ts['cnt']) ?>건</div>
</div>
<?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input type="hidden" name="p" value="credits">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="이메일 또는 이름..." style="max-width:220px;">
        <select name="type" class="form-control" style="max-width:140px;">
          <option value="">전체 유형</option>
          <?php foreach(['charge'=>'충전','use'=>'사용','refund'=>'환불','admin'=>'관리자'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $type===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">🔍 검색</button>
        <?php if($search||$type): ?><a href="index.php?p=credits" class="btn btn-sm btn-outline">초기화</a><?php endif; ?>
      </form>
    </div>
    <div style="font-size:13px;color:#888;">총 <?= count($txList) ?>건</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>일시</th><th>회원</th><th>유형</th><th>금액</th><th>처리 후 잔액</th><th>내용</th><th>결제수단</th><th>상태</th></tr></thead>
      <tbody>
      <?php foreach ($txList as $t): ?>
      <tr>
        <td style="font-size:12px;color:#aaa;white-space:nowrap;"><?= date('Y.m.d H:i', strtotime($t['created_at'])) ?></td>
        <td>
          <div style="font-weight:600;"><?= htmlspecialchars($t['name']) ?></div>
          <div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($t['email']) ?></div>
        </td>
        <td><span class="badge <?= ['charge'=>'badge-confirmed','use'=>'badge-cancelled','refund'=>'badge-pending','admin'=>'badge-free'][$t['type']] ?? 'badge-free' ?>"><?= $typeLabels[$t['type']] ?? $t['type'] ?></span></td>
        <td style="font-weight:700;font-size:14px;color:<?= $t['amount']>0?'#00b894':'#e94560' ?>;"><?= ($t['amount']>0?'+':'').number_format($t['amount']) ?>원</td>
        <td style="font-size:13px;"><?= number_format($t['balance_after']) ?>원</td>
        <td style="font-size:13px;max-width:180px;"><?= htmlspecialchars($t['description']) ?></td>
        <td style="font-size:12px;color:#888;"><?= $t['payment_method'] ?? '-' ?></td>
        <td><span class="badge <?= ['completed'=>'badge-confirmed','pending'=>'badge-pending','failed'=>'badge-cancelled','cancelled'=>'badge-cancelled'][$t['status']] ?? 'badge-free' ?>"><?= $t['status'] ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
