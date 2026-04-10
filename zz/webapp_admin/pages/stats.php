<?php
try {
    $daily = DB::fetchAll("SELECT DATE(created_at) as d, SUM(CASE WHEN type='charge' THEN amount ELSE 0 END) as charged, SUM(CASE WHEN type='use' THEN ABS(amount) ELSE 0 END) as used FROM credit_transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY d");
    $planDist = DB::fetchAll("SELECT plan, COUNT(*) as cnt FROM users GROUP BY plan");
    $topUsers = DB::fetchAll("SELECT u.name,u.email,u.plan,cb.balance,cb.total_charged FROM users u JOIN credit_balance cb ON cb.user_id=u.id ORDER BY cb.total_charged DESC LIMIT 10");
    $monthlyCharge = DB::fetchAll("SELECT DATE_FORMAT(created_at,'%Y-%m') as m, SUM(amount) as total, COUNT(*) as cnt FROM charge_requests WHERE status='confirmed' GROUP BY m ORDER BY m DESC LIMIT 6");
} catch(Exception $e) { $daily=[]; $planDist=[]; $topUsers=[]; $monthlyCharge=[]; }
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
  <!-- 30일 충전/사용 차트 -->
  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-title">📈 30일 충전/사용 현황</div></div>
    <div style="height:220px;"><canvas id="dailyChart"></canvas></div>
  </div>
  <!-- 플랜 분포 -->
  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-title">👥 플랜 분포</div></div>
    <div style="height:220px;display:flex;align-items:center;justify-content:center;">
      <canvas id="planChart"></canvas>
    </div>
  </div>
</div>

<!-- 월별 매출 -->
<div class="card">
  <div class="card-header"><div class="card-title">💰 월별 충전 매출</div></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>월</th><th>승인 건수</th><th>총 충전액</th><th>일평균</th></tr></thead>
      <tbody>
      <?php foreach ($monthlyCharge as $m): ?>
      <tr>
        <td style="font-weight:700;"><?= $m['m'] ?></td>
        <td><?= number_format($m['cnt']) ?>건</td>
        <td style="font-weight:700;color:#e94560;"><?= number_format($m['total']) ?>원</td>
        <td style="color:#888;"><?= number_format($m['total']/30) ?>원</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Top 충전 회원 -->
<div class="card">
  <div class="card-header"><div class="card-title">🏆 누적 충전액 TOP 10</div></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>순위</th><th>회원</th><th>플랜</th><th>누적 충전</th><th>현재 잔액</th></tr></thead>
      <tbody>
      <?php foreach ($topUsers as $i => $u): ?>
      <tr>
        <?php $rankColor = $i===0?'#f5a623':($i===1?'#888':($i===2?'#cd7f32':'#ccc')); $rankLabel = isset(['🥇','🥈','🥉'][$i]) ? ['🥇','🥈','🥉'][$i] : ($i+1).'위'; ?>
        <td><span style="font-weight:800;color:<?= $rankColor ?>;font-size:16px;"><?= $rankLabel ?></span></td>
        <td><div style="font-weight:700;"><?= htmlspecialchars($u['name']) ?></div><div style="font-size:11px;color:#aaa;"><?= htmlspecialchars($u['email']) ?></div></td>
        <td><span class="badge badge-<?= $u['plan'] ?>"><?= $u['plan'] ?></span></td>
        <td style="font-weight:800;color:#e94560;"><?= number_format($u['total_charged']) ?>원</td>
        <td><?= number_format($u['balance']) ?>원</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// 30일 차트
const daily = <?= json_encode($daily, JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('dailyChart'), {
  type: 'bar',
  data: {
    labels: daily.map(d => d.d.slice(5)),
    datasets: [
      { label:'충전', data: daily.map(d=>d.charged), backgroundColor:'rgba(233,69,96,0.7)', borderRadius:4 },
      { label:'사용', data: daily.map(d=>d.used),    backgroundColor:'rgba(0,184,148,0.7)',  borderRadius:4 }
    ]
  },
  options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top'}}, scales:{x:{grid:{display:false}},y:{beginAtZero:true,grid:{color:'#f0f0f5'}}} }
});
// 플랜 파이 차트
const planDist = <?= json_encode($planDist, JSON_UNESCAPED_UNICODE) ?>;
new Chart(document.getElementById('planChart'), {
  type: 'doughnut',
  data: {
    labels: planDist.map(p=>p.plan),
    datasets:[{ data: planDist.map(p=>p.cnt), backgroundColor:['#e0e0e0','#b3ccff','#ffb3be','#b2f0e0'], borderWidth:0 }]
  },
  options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'right'}} }
});
</script>
