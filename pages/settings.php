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
              onclick="saveApiKeyById('naver_search', 'naver_search_api_key', 'naver_search_api_secret', null)">
        💾 저장
      </button>
    </div>
  </div>

  <!-- 네이버 광고 API -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📍 네이버 광고 API</div>
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
              onclick="saveApiKeyById('naver_ad', 'naver_ad_api_key', 'naver_ad_api_secret', 'naver_ad_customer_id')">
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
              onclick="saveApiKeyById('openai', 'openai_api_key', null, null)">
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
              onclick="saveApiKeyById('kakao', 'kakao_api_key', 'kakao_api_secret', null)">
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
              onclick="saveApiKeyById('google', 'google_api_key', 'google_api_secret', null)">
        💾 저장
      </button>
    </div>
  </div>
</div>

<!-- 프로필 탭 -->
<div id="stab-profile" style="display:none;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">👤 내 정보</div>
    </div>
    <div class="card-body">
      <p style="color:#888;">프로필 설정 기능은 준비 중입니다.</p>
    </div>
  </div>
</div>

<!-- 플랜 탭 -->
<div id="stab-plan" style="display:none;">
  <div class="card">
    <div class="card-header">
      <div class="card-title">💎 요금제</div>
    </div>
    <div class="card-body">
      <p style="color:#888;">요금제 정보는 준비 중입니다.</p>
    </div>
  </div>
</div>

<script>
// 전역 헬퍼 함수
function $(id) {
  return document.getElementById(id);
}

// 탭 전환
function switchSettingsTab(tab, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  
  document.querySelectorAll('[id^="stab-"]').forEach(t => t.style.display = 'none');
  $('stab-' + tab).style.display = 'block';
}

// API 키 저장 (ID로 직접 접근)
async function saveApiKeyById(service, keyId, secretId, customerIdId) {
  console.log('=== saveApiKeyById 시작 ===');
  console.log('Service:', service);
  
  const keyEl = $(keyId);
  if (!keyEl) {
    alert('입력 필드를 찾을 수 없습니다: ' + keyId);
    return;
  }
  
  const apiKey = keyEl.value.trim();
  if (!apiKey) {
    alert('API 키를 입력해주세요.');
    keyEl.focus();
    return;
  }
  
  const apiSecret = secretId ? ($(secretId)?.value.trim() || '') : '';
  const customerId = customerIdId ? ($(customerIdId)?.value.trim() || '') : '';
  
  if (service === 'naver_ad' && !customerId) {
    alert('네이버 광고는 고객 ID가 필수입니다.');
    $(customerIdId)?.focus();
    return;
  }
  
  await saveApiKeyToServer(service, apiKey, apiSecret, '', customerId);
}

// 인스타그램 전용
async function saveInstagramApi() {
  const apiKey = $('instagram_api_key')?.value.trim() || '';
  const accessToken = $('instagram_access_token')?.value.trim() || '';
  
  if (!apiKey || !accessToken) {
    alert('App ID와 Access Token을 모두 입력해주세요.');
    return;
  }
  
  await saveApiKeyToServer('instagram', apiKey, '', accessToken, '');
}

// 서버로 전송
async function saveApiKeyToServer(service, apiKey, apiSecret, accessToken, customerId) {
  showLoading('저장 중...');
  
  try {
    const requestData = {
      service: service,
      api_key: apiKey,
      api_secret: apiSecret || '',
      access_token: accessToken || '',
      customer_id: customerId || ''
    };
    
    console.log('=== API 요청 준비 ===');
    console.log('요청 데이터:', {
      service: requestData.service,
      api_key_length: apiKey.length,
      has_secret: !!apiSecret,
      has_token: !!accessToken,
      has_customer_id: !!customerId
    });
    
    // ✅ URL 확인
    const baseUrl = window.location.pathname; // /zz/index.php
    const apiUrl = baseUrl + '?route=api/settings/api-keys';
    
    console.log('Base URL:', baseUrl);
    console.log('API URL:', apiUrl);
    console.log('Full URL:', window.location.origin + apiUrl);
    
    const response = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(requestData)
    });
    
    console.log('=== 응답 받음 ===');
    console.log('Status:', response.status);
    console.log('Status Text:', response.statusText);
    console.log('Content-Type:', response.headers.get('Content-Type'));
    console.log('Response URL:', response.url);
    
    // ✅ 404 체크
    if (response.status === 404) {
      const errorText = await response.text();
      console.error('404 응답:', errorText);
      
      throw new Error(
        '404 오류: API를 찾을 수 없습니다.\n\n' +
        '확인 사항:\n' +
        '1. api/settings.php 파일이 존재하는지\n' +
        '2. 파일 경로: /hosting/bobjjangs1231/html/zz/api/settings.php\n' +
        '3. index.php의 handleApiRoute 함수 확인\n\n' +
        '응답: ' + errorText.substring(0, 300)
      );
    }
    
    const responseText = await response.text();
    console.log('=== 응답 본문 ===');
    console.log('길이:', responseText.length);
    console.log('처음 500자:', responseText.substring(0, 500));
    console.log('마지막 100자:', responseText.substring(Math.max(0, responseText.length - 100)));
    
    if (!responseText.trim()) {
      throw new Error('서버가 빈 응답을 반환했습니다.');
    }
    
    // ✅ HTML 응답 체크
    if (responseText.includes('<!DOCTYPE') || 
        responseText.includes('<html') || 
        responseText.includes('<div class="tabs">')) {
      console.error('=== HTML 응답 받음 ===');
      console.error('전체 응답:', responseText);
      
      throw new Error(
        'HTML 페이지를 받았습니다!\n\n' +
        '문제:\n' +
        '- API가 아닌 일반 페이지(settings)를 반환했습니다.\n' +
        '- index.php의 라우팅이 제대로 작동하지 않습니다.\n\n' +
        '해결:\n' +
        '1. index.php에서 api/settings 라우트가 최우선 처리되는지 확인\n' +
        '2. handleApiRoute() 함수가 호출되는지 확인\n' +
        '3. api/settings.php 파일이 올바른 위치에 있는지 확인'
      );
    }
    
    // ✅ JSON 파싱
    let data;
    try {
      data = JSON.parse(responseText);
    } catch (e) {
      console.error('=== JSON 파싱 실패 ===');
      console.error('파싱 오류:', e);
      console.error('응답 타입:', typeof responseText);
      console.error('응답 내용:', responseText);
      
      // PHP 에러 추출
      const phpError = responseText.match(/<b>(Fatal error|Warning|Notice|Parse error)<\/b>:\s*(.*?)(?:<br|in )/i);
      if (phpError) {
        throw new Error('PHP 오류: ' + phpError[2]);
      }
      
      throw new Error(
        'JSON 파싱 실패\n\n' +
        '응답이 올바른 JSON 형식이 아닙니다.\n\n' +
        '응답 내용:\n' + responseText.substring(0, 500)
      );
    }
    
    console.log('=== JSON 파싱 성공 ===');
    console.log('파싱된 데이터:', data);
    
    if (data.success) {
      showSuccessToast('✅ ' + (data.message || 'API 키가 저장되었습니다.'));
      updateStatusBadge(service, 'active');
      setTimeout(() => loadApiStatuses(), 1000);
    } else {
      alert('❌ 저장 실패\n\n' + (data.error || '알 수 없는 오류'));
    }
    
  } catch (error) {
    console.error('=== 오류 발생 ===');
    console.error('오류 타입:', error.name);
    console.error('오류 메시지:', error.message);
    console.error('스택:', error.stack);
    
    alert('❌ 오류\n\n' + error.message);
  } finally {
    hideLoading();
  }
}

// 상태 배지 업데이트
function updateStatusBadge(service, status) {
  const badge = $('status_' + service);
  if (badge) {
    if (status === 'active') {
      badge.textContent = '✅ 연동됨';
      badge.className = 'badge badge-green';
    } else {
      badge.textContent = '미연동';
      badge.className = 'badge badge-gray';
    }
  }
}

// API 상태 로드
async function loadApiStatuses() {
  try {
    console.log('=== loadApiStatuses 시작 ===');
    
    const baseUrl = window.location.pathname;
    const apiUrl = baseUrl + '?route=api/settings/api-keys';
    
    const response = await fetch(apiUrl, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    
    console.log('GET 응답 상태:', response.status);
    
    const responseText = await response.text();
    console.log('GET 응답 (처음 300자):', responseText.substring(0, 300));
    
    if (!responseText.trim() || 
        responseText.includes('<!DOCTYPE') || 
        responseText.includes('<html>')) {
      console.warn('GET 요청도 HTML을 반환함');
      return;
    }
    
    const data = JSON.parse(responseText);
    console.log('파싱된 데이터:', data);
    
    if (data.success && data.data) {
      Object.keys(data.data).forEach(service => {
        const info = data.data[service];
        updateStatusBadge(service, info.status || 'inactive');
      });
      console.log('상태 업데이트 완료');
    }
    
  } catch (error) {
    console.error('loadApiStatuses 오류:', error);
  }
}

// UI 헬퍼
function showSuccessToast(message) {
  const toast = document.createElement('div');
  toast.style.cssText = `
    position: fixed; top: 20px; right: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; padding: 16px 24px; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 99999;
    font-size: 15px; font-weight: 500;
    animation: slideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  `;
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function showLoading(message = '처리 중...') {
  let loader = $('globalLoader');
  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.style.cssText = `
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,0.7); display: flex;
      align-items: center; justify-content: center;
      z-index: 99998; backdrop-filter: blur(4px);
    `;
    loader.innerHTML = `
      <div style="background: white; padding: 40px 50px; border-radius: 16px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="font-size: 48px; margin-bottom: 20px; animation: spin 1s linear infinite;">⏳</div>
        <div id="loaderMessage" style="font-size: 16px; color: #333; font-weight: 500;">${message}</div>
      </div>
    `;
    document.body.appendChild(loader);
  } else {
    $('loaderMessage').textContent = message;
    loader.style.display = 'flex';
  }
}

function hideLoading() {
  const loader = $('globalLoader');
  if (loader) loader.style.display = 'none';
}

// 페이지 로드
document.addEventListener('DOMContentLoaded', function() {
  console.log('=== 설정 페이지 로드 완료 ===');
  console.log('현재 URL:', window.location.href);
  console.log('Pathname:', window.location.pathname);
  console.log('Search:', window.location.search);
  
  loadApiStatuses();
});
</script>


<style>
@keyframes slideIn {
  from { 
    transform: translateX(400px) scale(0.8); 
    opacity: 0; 
  }
  to { 
    transform: translateX(0) scale(1); 
    opacity: 1; 
  }
}

@keyframes slideOut {
  from { 
    transform: translateX(0) scale(1); 
    opacity: 1; 
  }
  to { 
    transform: translateX(400px) scale(0.8); 
    opacity: 0; 
  }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 24px;
  border-bottom: 2px solid #e0e0e0;
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  border-bottom: 3px solid transparent;
  margin-bottom: -2px;
  transition: all 0.3s;
}

.tab-btn:hover {
  color: #007bff;
  background: rgba(0,123,255,0.05);
}

.tab-btn.active {
  color: #007bff;
  border-bottom-color: #007bff;
}

.card {
  margin-bottom: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  transition: box-shadow 0.3s;
}

.card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
}

.card-title {
  font-weight: 600;
  font-size: 15px;
}

.card-body {
  padding: 20px;
}

.badge {
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-green {
  background: #d4edda;
  color: #155724;
}

.badge-gray {
  background: #e9ecef;
  color: #6c757d;
}

.alert {
  padding: 14px 18px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.alert-info {
  background: #d1ecf1;
  color: #0c5460;
  border: 1px solid #bee5eb;
}
</style>
