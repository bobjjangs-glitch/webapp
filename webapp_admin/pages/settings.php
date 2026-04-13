<?php
$apiServices = [
  ['key'=>'naver_search',    'icon'=>'🟢','name'=>'네이버 검색 API',    'desc'=>'블로그/플레이스 순위 추적에 사용',    'fields'=>[['id'=>'api_key','label'=>'Client ID'],['id'=>'api_secret','label'=>'Client Secret']]],
  ['key'=>'naver_ad',        'icon'=>'📍','name'=>'네이버 광고 API',    'desc'=>'플레이스 광고 자동화에 사용',         'fields'=>[['id'=>'api_key','label'=>'API Key'],['id'=>'api_secret','label'=>'Secret Key']]],
  ['key'=>'instagram',       'icon'=>'📸','name'=>'인스타그램 API',     'desc'=>'인스타그램 예약 포스팅에 사용',       'fields'=>[['id'=>'api_key','label'=>'App ID'],['id'=>'access_token','label'=>'Access Token']]],
  ['key'=>'openai',          'icon'=>'🤖','name'=>'OpenAI API',         'desc'=>'AI 블로그 글쓰기에 사용',            'fields'=>[['id'=>'api_key','label'=>'API Key (sk-...)']]],
  ['key'=>'kakao',           'icon'=>'💛','name'=>'카카오 API',         'desc'=>'카카오 채널 자동화에 사용',           'fields'=>[['id'=>'api_key','label'=>'REST API Key'],['id'=>'api_secret','label'=>'Admin Key']]],
  ['key'=>'google_analytics','icon'=>'📊','name'=>'Google Analytics',   'desc'=>'심화 유입 분석에 사용',              'fields'=>[['id'=>'api_key','label'=>'Measurement ID (G-...)'],['id'=>'api_secret','label'=>'API Secret']]],
];
?>

<div class="tabs">
  <button class="tab-btn active" onclick="switchSettingsTab('api',this)">🔑 API 연동</button>
  <button class="tab-btn" onclick="switchSettingsTab('profile',this)">👤 프로필</button>
  <button class="tab-btn" onclick="switchSettingsTab('plan',this)">💎 플랜</button>
</div>

<!-- API 연동 탭 -->
<div id="stab-api">
  <div class="alert alert-info">ℹ️ API 키는 암호화되어 안전하게 저장됩니다. 키를 입력하면 해당 기능이 자동으로 활성화됩니다.</div>
  <?php foreach ($apiServices as $svc): ?>
  <div class="card">
    <div class="card-header">
      <div class="card-title"><?= $svc['icon'] ?> <?= $svc['name'] ?></div>
      <span class="badge badge-gray" id="status_<?= $svc['key'] ?>">미연동</span>
    </div>
    <p style="font-size:13px;color:#888;margin-bottom:14px;"><?= $svc['desc'] ?></p>
    <?php foreach ($svc['fields'] as $field): ?>
    <div class="form-group">
      <label class="form-label"><?= $field['label'] ?></label>
      <input type="password" class="form-control"
             id="<?= $svc['key'] ?>_<?= $field['id'] ?>"
             placeholder="입력 후 저장하세요"
             onfocus="this.type='text'"
             onblur="this.type='password'">
    </div>
    <?php endforeach; ?>
    <button class="btn btn-primary btn-sm"
            onclick="saveApiKey('<?= $svc['key'] ?>', <?= json_encode(array_column($svc['fields'],'id')) ?>)">
      💾 저장
    </button>
  </div>
  <?php endforeach; ?>
</div>

<!-- 프로필 탭 -->
<div id="stab-profile" style="display:none;">
  <div class="card">
    <div class="card-header"><div class="card-title">👤 프로필 설정</div></div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">이름</label>
        <input type="text" class="form-control" id="profileName" value="<?= htmlspecialchars($_SESSION['user_name']??'') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">이메일</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['user_email']??'') ?>" readonly style="background:#f9f9fc;">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">업체명</label>
      <input type="text" class="form-control" id="profileBiz" value="<?= htmlspecialchars($_SESSION['business_name']??'') ?>" placeholder="업체명을 입력하세요">
    </div>
    <div class="divider"></div>
    <div class="card-title" style="margin-bottom:14px;">🔒 비밀번호 변경</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">현재 비밀번호</label>
        <input type="password" class="form-control" id="curPw">
      </div>
      <div class="form-group">
        <label class="form-label">새 비밀번호</label>
        <input type="password" class="form-control" id="newPw">
      </div>
    </div>
    <button class="btn btn-primary" onclick="saveProfile()">💾 저장</button>
  </div>
</div>

<!-- 플랜 탭 -->
<div id="stab-plan" style="display:none;">
  <div class="card">
    <div class="card-header"><div class="card-title">💎 현재 플랜</div></div>
    <div style="text-align:center;padding:30px;">
      <div style="font-size:40px;margin-bottom:12px;">🚀</div>
      <div style="font-size:22px;font-weight:800;color:#e94560;margin-bottom:8px;">
        <?= planLabel($_SESSION['user_plan']??'free') ?> 플랜
      </div>
      <div style="font-size:13px;color:#888;">모든 기능을 제한 없이 사용하세요</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <?php
      $features = ['플레이스 순위 추적 무제한','유입 분석 90일 데이터','AI 블로그 글쓰기','인스타그램 자동화','광고 자동화','API 연동 무제한'];
      foreach($features as $f): ?>
      <div style="display:flex;align-items:center;gap:8px;padding:10px;background:#f9f9fc;border-radius:10px;">
        <span style="color:#00b894;font-size:16px;">✅</span>
        <span style="font-size:13px;"><?= $f ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
// ✅ 탭 전환
function switchSettingsTab(tab, btn) {
  ['api', 'profile', 'plan'].forEach(t => {
    document.getElementById('stab-' + t).style.display = (t === tab) ? 'block' : 'none';
  });
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// ✅ API 키 저장 ($ 대신 document.getElementById 직접 사용)
async function saveApiKey(service, fields) {
  const body = { service };

  fields.forEach(f => {
    const el = document.getElementById(service + '_' + f);
    if (el && el.value.trim()) {
      body[f] = el.value.trim();
    }
  });

  // api_key 필드가 있는 서비스인데 비어있으면 경고
  if (fields.includes('api_key') && !body['api_key']) {
    alert('API Key를 입력해주세요.');
    return;
  }

  // 아무 값도 없으면 경고
  const hasAnyValue = fields.some(f => body[f]);
  if (!hasAnyValue) {
    alert('최소 하나의 값을 입력해주세요.');
    return;
  }

  showLoading('저장 중...');
  try {
    const res = await fetch('index.php?route=api/settings/api-keys', {
      method: 'POST',
      credentials: 'same-origin', // ✅ 세션 쿠키 포함
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const data = await res.json();

    if (data.success) {
      alert('✅ ' + data.message);
      const statusEl = document.getElementById('status_' + service);
      if (statusEl) {
        statusEl.className = 'badge badge-green';
        statusEl.textContent = '연동됨';
      }
    } else {
      alert('❌ ' + (data.error || '저장 실패'));
    }
  } catch (e) {
    alert('❌ 오류: ' + e.message);
  } finally {
    hideLoading();
  }
}

// ✅ API 상태 불러오기
async function loadApiStatuses() {
  try {
    const res = await fetch('index.php?route=api/settings/api-keys', {
      method: 'GET',
      credentials: 'same-origin', // ✅ 세션 쿠키 포함
    });
    const data = await res.json();
    const map = data.data || {};
    Object.entries(map).forEach(([svc, info]) => {
      const el = document.getElementById('status_' + svc);
      if (el && info.status === 'active') {
        el.className = 'badge badge-green';
        el.textContent = '연동됨';
      }
    });
  } catch (e) {
    console.error('API 상태 로드 실패:', e);
  }
}

// ✅ 프로필 저장
async function saveProfile() {
  alert('프로필 저장 기능은 준비 중입니다.');
}

// 페이지 로드시 API 상태 조회
loadApiStatuses();
</script>
