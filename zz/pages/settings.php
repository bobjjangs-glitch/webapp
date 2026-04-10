<?php
// pages/settings.php
?>

<div class="tabs">
  <button class="tab-btn active" onclick="switchSettingsTab('api',this)">🔑 API 연동</button>
  <button class="tab-btn" onclick="switchSettingsTab('profile',this)">👤 프로필</button>
  <button class="tab-btn" onclick="switchSettingsTab('plan',this)">💎 플랜</button>
</div>

<!-- API 연동 탭 -->
<div id="stab-api">
  <div class="alert alert-info">
    ℹ️ API 키는 암호화되어 안전하게 저장됩니다. 키를 입력하면 해당 기능이 자동으로 활성화됩니다.
  </div>

  <!-- 네이버 검색 API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🟢 네이버 검색 API</div>
      <span class="badge badge-gray" id="status_naver_search">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">블로그/플레이스 순위 추적에 사용</p>
      <div class="form-group">
        <label class="form-label">Client ID</label>
        <input type="password" class="form-control"
               id="naver_search_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">Client Secret</label>
        <input type="password" class="form-control"
               id="naver_search_api_secret"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveApiKeyById('naver_search','naver_search_api_key','naver_search_api_secret',null,null)">
        💾 저장
      </button>
    </div>
  </div>

  <!-- ★ 네이버 플레이스 API (신규 추가) -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📍 네이버 플레이스 API</div>
      <span class="badge badge-gray" id="status_naver_place">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">
        네이버 플레이스 순위 조회 및 업체 정보 분석에 사용됩니다.<br>
        <a href="https://developers.naver.com/apps/#/register" target="_blank"
           style="color:#007bff;font-size:12px;">🔗 네이버 개발자센터에서 발급</a>
      </p>

      <div class="form-group">
        <label class="form-label">Client ID</label>
        <input type="password" class="form-control"
               id="naver_place_api_key"
               placeholder="네이버 앱 Client ID 입력"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>

      <div class="form-group">
        <label class="form-label">Client Secret</label>
        <input type="password" class="form-control"
               id="naver_place_api_secret"
               placeholder="네이버 앱 Client Secret 입력"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>

      <div class="form-group">
        <label class="form-label">업체 ID <span style="color:#999;font-size:12px;">(선택 · 기본 업체 지정 시 입력)</span></label>
        <input type="text" class="form-control"
               id="naver_place_business_id"
               placeholder="예: 1234567890 (네이버 플레이스 URL의 숫자)">
        <small style="color:#aaa;font-size:11px;">
          플레이스 URL: map.naver.com/v5/entry/place/<strong>업체ID</strong>
        </small>
      </div>

      <div style="display:flex;gap:10px;align-items:center;">
        <button class="btn btn-primary btn-sm" type="button"
                onclick="saveNaverPlaceApi()">
          💾 저장
        </button>
        <button class="btn btn-outline btn-sm" type="button"
                onclick="testNaverPlaceApi()">
          🔍 연결 테스트
        </button>
      </div>

      <!-- 테스트 결과 영역 -->
      <div id="naver_place_test_result" style="display:none;margin-top:14px;"></div>
    </div>
  </div>

  <!-- 네이버 광고 API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📢 네이버 광고 API</div>
      <span class="badge badge-gray" id="status_naver_ad">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">플레이스 광고 자동화에 사용</p>
      <div class="form-group">
        <label class="form-label">API Key</label>
        <input type="password" class="form-control"
               id="naver_ad_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">Secret Key</label>
        <input type="password" class="form-control"
               id="naver_ad_api_secret"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">고객 ID</label>
        <input type="text" class="form-control"
               id="naver_ad_customer_id"
               placeholder="숫자만 입력">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveApiKeyById('naver_ad','naver_ad_api_key','naver_ad_api_secret',null,'naver_ad_customer_id')">
        💾 저장
      </button>
    </div>
  </div>

  <!-- 인스타그램 API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📸 인스타그램 API</div>
      <span class="badge badge-gray" id="status_instagram">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">인스타그램 예약 포스팅에 사용</p>
      <div class="form-group">
        <label class="form-label">App ID</label>
        <input type="password" class="form-control"
               id="instagram_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">Access Token</label>
        <input type="password" class="form-control"
               id="instagram_access_token"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveInstagramApi()">
        💾 저장
      </button>
    </div>
  </div>

  <!-- OpenAI API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🤖 OpenAI API</div>
      <span class="badge badge-gray" id="status_openai">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">AI 블로그 글쓰기에 사용</p>
      <div class="form-group">
        <label class="form-label">API Key (sk-...)</label>
        <input type="password" class="form-control"
               id="openai_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveApiKeyById('openai','openai_api_key',null,null,null)">
        💾 저장
      </button>
    </div>
  </div>

  <!-- 카카오 API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">💛 카카오 API</div>
      <span class="badge badge-gray" id="status_kakao">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">카카오 채널 자동화에 사용</p>
      <div class="form-group">
        <label class="form-label">REST API Key</label>
        <input type="password" class="form-control"
               id="kakao_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">Admin Key</label>
        <input type="password" class="form-control"
               id="kakao_api_secret"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveApiKeyById('kakao','kakao_api_key','kakao_api_secret',null,null)">
        💾 저장
      </button>
    </div>
  </div>

  <!-- Google Analytics -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 Google Analytics</div>
      <span class="badge badge-gray" id="status_google">미연동</span>
    </div>
    <div class="card-body">
      <p style="font-size:13px;color:#888;margin-bottom:14px;">웹사이트 분석에 사용</p>
      <div class="form-group">
        <label class="form-label">Measurement ID (G-XXXXXXXXXX)</label>
        <input type="password" class="form-control"
               id="google_api_key"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <div class="form-group">
        <label class="form-label">API Secret</label>
        <input type="password" class="form-control"
               id="google_api_secret"
               placeholder="입력 후 저장하세요"
               onfocus="this.type='text'"
               onblur="this.type='password'">
      </div>
      <button class="btn btn-primary btn-sm" type="button"
              onclick="saveApiKeyById('google','google_api_key','google_api_secret',null,null)">
        💾 저장
      </button>
    </div>
  </div>
</div>

<!-- 프로필 탭 -->
<div id="stab-profile" style="display:none;">
  <div class="card">
    <div class="card-header"><div class="card-title">👤 내 정보</div></div>
    <div class="card-body">
      <p style="color:#888;">프로필 설정 기능은 준비 중입니다.</p>
    </div>
  </div>
</div>

<!-- 플랜 탭 -->
<div id="stab-plan" style="display:none;">
  <div class="card">
    <div class="card-header"><div class="card-title">💎 요금제</div></div>
    <div class="card-body">
      <p style="color:#888;">요금제 정보는 준비 중입니다.</p>
    </div>
  </div>
</div>

<script>
function $(id) { return document.getElementById(id); }

/* ── 탭 전환 ── */
function switchSettingsTab(tab, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('[id^="stab-"]').forEach(t => t.style.display = 'none');
  $('stab-' + tab).style.display = 'block';
}

/* ── 네이버 플레이스 저장 (전용 함수) ── */
async function saveNaverPlaceApi() {
  const apiKey    = $('naver_place_api_key')?.value.trim()    || '';
  const apiSecret = $('naver_place_api_secret')?.value.trim() || '';
  const bizId     = $('naver_place_business_id')?.value.trim()|| '';

  if (!apiKey)    { alert('Client ID를 입력해주세요.'); $('naver_place_api_key').focus(); return; }
  if (!apiSecret) { alert('Client Secret을 입력해주세요.'); $('naver_place_api_secret').focus(); return; }

  await saveApiKeyToServer('naver_place', apiKey, apiSecret, '', '', bizId);
}

/* ── 네이버 플레이스 연결 테스트 ── */
async function testNaverPlaceApi() {
  const apiKey    = $('naver_place_api_key')?.value.trim()    || '';
  const apiSecret = $('naver_place_api_secret')?.value.trim() || '';
  const resultBox = $('naver_place_test_result');

  if (!apiKey || !apiSecret) {
    alert('Client ID와 Client Secret을 먼저 입력해주세요.');
    return;
  }

  resultBox.style.display = 'block';
  resultBox.innerHTML = '<div class="test-loading">🔄 연결 테스트 중...</div>';

  try {
    const baseUrl = window.location.pathname;
    const res = await fetch(baseUrl + '?route=api/settings/test-naver-place', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ api_key: apiKey, api_secret: apiSecret })
    });
    const data = await res.json();

    if (data.success) {
      resultBox.innerHTML = `
        <div class="test-success">
          ✅ 연결 성공!
          <br><small>검색 결과 수: ${data.total ?? '-'}건 확인됨</small>
        </div>`;
    } else {
      resultBox.innerHTML = `
        <div class="test-error">
          ❌ 연결 실패: ${data.error ?? '알 수 없는 오류'}
        </div>`;
    }
  } catch (e) {
    resultBox.innerHTML = `<div class="test-error">❌ 오류: ${e.message}</div>`;
  }
}

/* ── 공통 API 키 저장 ── */
async function saveApiKeyById(service, keyId, secretId, tokenId, customerIdId) {
  const keyEl = $(keyId);
  if (!keyEl) { alert('입력 필드를 찾을 수 없습니다: ' + keyId); return; }

  const apiKey = keyEl.value.trim();
  if (!apiKey) { alert('API 키를 입력해주세요.'); keyEl.focus(); return; }

  const apiSecret  = secretId     ? ($(secretId)?.value.trim()     || '') : '';
  const accessToken= tokenId      ? ($(tokenId)?.value.trim()       || '') : '';
  const customerId = customerIdId ? ($(customerIdId)?.value.trim()  || '') : '';

  if (service === 'naver_ad' && !customerId) {
    alert('네이버 광고는 고객 ID가 필수입니다.');
    $(customerIdId)?.focus(); return;
  }

  await saveApiKeyToServer(service, apiKey, apiSecret, accessToken, customerId, '');
}

/* ── 인스타그램 전용 ── */
async function saveInstagramApi() {
  const apiKey      = $('instagram_api_key')?.value.trim()      || '';
  const accessToken = $('instagram_access_token')?.value.trim() || '';
  if (!apiKey || !accessToken) {
    alert('App ID와 Access Token을 모두 입력해주세요.'); return;
  }
  await saveApiKeyToServer('instagram', apiKey, '', accessToken, '', '');
}

/* ── 서버 전송 공통 함수 ── */
async function saveApiKeyToServer(service, apiKey, apiSecret, accessToken, customerId, businessId) {
  showLoading('저장 중...');
  try {
    const baseUrl = window.location.pathname;
    const apiUrl  = baseUrl + '?route=api/settings/api-keys';

    const payload = {
      service,
      api_key:      apiKey,
      api_secret:   apiSecret   || '',
      access_token: accessToken || '',
      customer_id:  customerId  || '',
      business_id:  businessId  || ''
    };

    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });

    const responseText = await response.text();

    if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
      throw new Error('HTML 페이지를 받았습니다. API 라우팅을 확인해주세요.');
    }

    let data;
    try { data = JSON.parse(responseText); }
    catch(e) { throw new Error('JSON 파싱 실패\n\n' + responseText.substring(0, 300)); }

    if (data.success) {
      showSuccessToast('✅ ' + (data.message || 'API 키가 저장되었습니다.'));
      updateStatusBadge(service, 'active');
      setTimeout(() => loadApiStatuses(), 800);
    } else {
      alert('❌ 저장 실패\n\n' + (data.error || '알 수 없는 오류'));
    }
  } catch (error) {
    alert('❌ 오류\n\n' + error.message);
  } finally {
    hideLoading();
  }
}

/* ── 상태 배지 업데이트 ── */
function updateStatusBadge(service, status) {
  const badge = $('status_' + service);
  if (!badge) return;
  if (status === 'active') {
    badge.textContent = '✅ 연동됨';
    badge.className = 'badge badge-green';
  } else {
    badge.textContent = '미연동';
    badge.className = 'badge badge-gray';
  }
}

/* ── API 상태 로드 ── */
async function loadApiStatuses() {
  try {
    const baseUrl = window.location.pathname;
    const res = await fetch(baseUrl + '?route=api/settings/api-keys', {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    const text = await res.text();
    if (!text.trim() || text.includes('<!DOCTYPE')) return;

    const data = JSON.parse(text);
    if (data.success && data.data) {
      Object.keys(data.data).forEach(svc => {
        updateStatusBadge(svc, data.data[svc].status || 'inactive');
      });
    }
  } catch(e) {
    console.error('loadApiStatuses 오류:', e);
  }
}

/* ── UI 헬퍼 ── */
function showSuccessToast(message) {
  const toast = document.createElement('div');
  toast.style.cssText = `
    position:fixed;top:20px;right:20px;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:white;padding:16px 24px;border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:99999;
    font-size:15px;font-weight:500;
    animation:slideIn .4s cubic-bezier(.68,-.55,.265,1.55);
  `;
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.animation='slideOut .3s ease'; setTimeout(()=>toast.remove(),300); }, 3500);
}

function showLoading(msg = '처리 중...') {
  let el = $('globalLoader');
  if (!el) {
    el = document.createElement('div');
    el.id = 'globalLoader';
    el.style.cssText = `
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,.7);display:flex;
      align-items:center;justify-content:center;
      z-index:99998;backdrop-filter:blur(4px);
    `;
    el.innerHTML = `
      <div style="background:white;padding:40px 50px;border-radius:16px;text-align:center;">
        <div style="font-size:48px;margin-bottom:20px;animation:spin 1s linear infinite;">⏳</div>
        <div id="loaderMessage" style="font-size:16px;color:#333;font-weight:500;">${msg}</div>
      </div>`;
    document.body.appendChild(el);
  } else {
    $('loaderMessage').textContent = msg;
    el.style.display = 'flex';
  }
}
function hideLoading() {
  const el = $('globalLoader');
  if (el) el.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', () => loadApiStatuses());
</script>

<style>
@keyframes slideIn  { from{transform:translateX(400px) scale(.8);opacity:0} to{transform:translateX(0) scale(1);opacity:1} }
@keyframes slideOut { from{transform:translateX(0) scale(1);opacity:1} to{transform:translateX(400px) scale(.8);opacity:0} }
@keyframes spin     { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

.tabs { display:flex;gap:10px;margin-bottom:24px;border-bottom:2px solid #e0e0e0; }
.tab-btn {
  padding:12px 24px;border:none;background:none;cursor:pointer;
  font-size:14px;font-weight:500;color:#666;
  border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .3s;
}
.tab-btn:hover  { color:#007bff;background:rgba(0,123,255,.05); }
.tab-btn.active { color:#007bff;border-bottom-color:#007bff; }

.card {
  margin-bottom:20px;border:1px solid #e0e0e0;
  border-radius:12px;overflow:hidden;transition:box-shadow .3s;
}
.card:hover { box-shadow:0 4px 12px rgba(0,0,0,.08); }
.card-header {
  display:flex;justify-content:space-between;align-items:center;
  padding:16px 20px;background:#f8f9fa;border-bottom:1px solid #e0e0e0;
}
.card-title  { font-weight:600;font-size:15px; }
.card-body   { padding:20px; }

.badge { padding:5px 14px;border-radius:20px;font-size:12px;font-weight:600; }
.badge-green { background:#d4edda;color:#155724; }
.badge-gray  { background:#e9ecef;color:#6c757d; }

.alert { padding:14px 18px;border-radius:8px;margin-bottom:20px; }
.alert-info { background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb; }

/* 테스트 결과 박스 */
.test-loading { padding:12px 16px;background:#fff8e1;border:1px solid #ffe082;border-radius:8px;color:#795548;font-size:13px; }
.test-success { padding:12px 16px;background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;color:#2e7d32;font-size:13px; }
.test-error   { padding:12px 16px;background:#ffebee;border:1px solid #ef9a9a;border-radius:8px;color:#c62828;font-size:13px; }

/* 아웃라인 버튼 */
.btn-outline {
  background:transparent;border:1px solid #007bff;color:#007bff;
  padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px;
  transition:all .2s;
}
.btn-outline:hover { background:#007bff;color:white; }
</style>
