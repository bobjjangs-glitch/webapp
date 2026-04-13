<?php // pages/place-boost.php ?>

<div class="alert alert-warning">
  ⚠️ <strong>안내:</strong> 네이버 플레이스 API 연동 시 실제 순위 확인 및 자동화 기능이 활성화됩니다. 현재는 시뮬레이션 모드로 작동합니다.
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon si-red">🏪</div>
    <div class="stat-body"><div class="slabel">등록된 플레이스</div><div class="sval" id="totalPlaces">-</div><div class="schange up">관리 중</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green">⚡</div>
    <div class="stat-body"><div class="slabel">진행 중 작업</div><div class="sval" id="runningTasks">-</div><div class="schange up">부스팅 중</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue">✅</div>
    <div class="stat-body"><div class="slabel">완료된 작업</div><div class="sval" id="completedTasks">-</div><div class="schange up">이번 달</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-orange">🏆</div>
    <div class="stat-body"><div class="slabel">평균 순위</div><div class="sval" id="avgRank">-</div><div class="schange up">↑ 개선 중</div></div>
  </div>
</div>

<div class="grid-col-2-1">
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏪 내 플레이스 관리</div>
      <button class="btn btn-primary" onclick="showAddPlaceModal()">+ 플레이스 추가</button>
    </div>
    <div id="placeList"></div>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title">🔍 즉시 순위 확인</div></div>
    <div class="form-group">
      <label class="form-label">검색 키워드</label>
      <input type="text" class="form-control" id="rankCheckKeyword" placeholder="예) 강남 맛집">
    </div>
    <div class="form-group">
      <label class="form-label">업체명</label>
      <input type="text" class="form-control" id="rankCheckPlace" placeholder="예) 맛있는 식당">
    </div>
    <button class="btn btn-primary" style="width:100%;" onclick="checkRank()">🔍 순위 확인</button>
    <div id="rankResult" style="margin-top:16px;display:none;"></div>
  </div>
</div>

<!-- 부스팅 설정 패널 -->
<div class="card" id="boostPanel" style="display:none;">
  <div class="card-header">
    <div class="card-title">⚡ 부스팅 작업 설정</div>
    <button class="btn btn-secondary" onclick="document.getElementById('boostPanel').style.display='none'">닫기</button>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">선택된 플레이스</label>
      <input type="text" class="form-control" id="boostPlaceName" readonly>
      <input type="hidden" id="boostPlaceId">
    </div>
    <div class="form-group">
      <label class="form-label">부스팅 유형</label>
      <select class="form-control" id="boostType" onchange="updateBoostUI()">
        <option value="view_boost">👁️ 플레이스 조회수 부스팅</option>
        <option value="keyword_search">🔍 키워드 검색 유입</option>
        <option value="review_request">⭐ 리뷰 유도 캠페인</option>
        <option value="photo_update">📷 사진 업데이트 알림</option>
        <option value="smart_boost">🤖 스마트 자동 부스팅</option>
      </select>
    </div>
  </div>
  <div id="boostTypeDetail"></div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">목표 수량</label>
      <input type="number" class="form-control" id="boostCount" value="100" min="10" max="10000">
    </div>
    <div class="form-group">
      <label class="form-label">일 최대 실행 수</label>
      <input type="number" class="form-control" id="boostDailyMax" value="50" min="5" max="500">
    </div>
  </div>
  <div class="card" style="background:#f9f9fc;border:1px solid #e8e8f0;margin-bottom:16px;">
    <div class="card-title" style="font-size:14px;margin-bottom:14px;">⚙️ 고급 설정</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">실행 시간대</label>
        <select class="form-control" id="boostTimeRange">
          <option value="all">24시간</option>
          <option value="morning">오전 9시~12시</option>
          <option value="lunch" selected>점심 11시~14시</option>
          <option value="evening">저녁 17시~21시</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">실행 속도</label>
        <select class="form-control" id="boostSpeed">
          <option value="slow">느리게 (자연스럽게)</option>
          <option value="normal" selected>보통</option>
          <option value="fast">빠르게</option>
        </select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">지역 타겟</label>
        <input type="text" class="form-control" id="boostRegion" placeholder="예) 서울, 경기도">
      </div>
      <div class="form-group">
        <label class="form-label">디바이스 믹스</label>
        <select class="form-control" id="boostDevice">
          <option value="mixed" selected>모바일+PC 혼합</option>
          <option value="mobile">모바일 위주</option>
          <option value="desktop">PC 위주</option>
        </select>
      </div>
    </div>
  </div>
  <button class="btn btn-primary" style="width:100%;" onclick="startBoostTask()">⚡ 부스팅 시작</button>
</div>

<!-- 부스팅 작업 현황 -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 부스팅 작업 현황</div>
    <div style="display:flex;gap:8px;">
      <button class="btn btn-danger btn-sm" id="deleteSelectedBtn"
              style="display:none;" onclick="deleteSelectedTasks()">
        🗑️ 선택 삭제
      </button>
      <button class="btn btn-secondary" onclick="loadTasks()">🔄 새로고침</button>
    </div>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th style="width:36px;">
            <!-- 전체 선택 체크박스 -->
            <input type="checkbox" id="selectAllTasks"
                   style="width:16px;height:16px;cursor:pointer;"
                   onchange="toggleSelectAll(this.checked)">
          </th>
          <th>업체</th>
          <th>작업유형</th>
          <th>키워드</th>
          <th>진행률</th>
          <th>상태</th>
          <th>시작일</th>
          <th>제어</th>
        </tr>
      </thead>
      <tbody id="taskTable"></tbody>
    </table>
  </div>
</div>

<!-- 플레이스 추가 모달 -->
<div id="addPlaceModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;padding:32px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;">
    <div class="card-header" style="margin-bottom:20px;">
      <div class="card-title">🏪 플레이스 추가</div>
      <button class="btn btn-secondary" onclick="closeModal()" style="padding:6px 12px;">✕</button>
    </div>
    <div class="form-group">
      <label class="form-label">업체명 *</label>
      <input type="text" class="form-control" id="newPlaceName" placeholder="예) 강남 맛있는 식당">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">카테고리</label>
        <select class="form-control" id="newPlaceCategory">
          <option>음식점</option><option>카페</option><option>뷰티/미용</option>
          <option>의료/건강</option><option>쇼핑</option><option>서비스</option><option>교육</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">주소</label>
        <input type="text" class="form-control" id="newPlaceAddress" placeholder="서울 강남구...">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">네이버 플레이스 URL</label>
      <input type="text" class="form-control" id="newPlaceUrl" placeholder="https://naver.me/xxxxx">
    </div>
    <div class="form-group">
      <label class="form-label">타겟 키워드 (콤마로 구분)</label>
      <input type="text" class="form-control" id="newPlaceKeywords" placeholder="강남 맛집, 강남 점심, 역삼 맛집">
    </div>
    <button class="btn btn-primary" style="width:100%;" onclick="addPlace()">✅ 플레이스 등록</button>
  </div>
</div>

<!-- 삭제 확인 모달 -->
<div id="deleteConfirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:2000;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;padding:32px;width:400px;max-width:92vw;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="font-size:48px;margin-bottom:12px;">🗑️</div>
    <div style="font-size:18px;font-weight:700;margin-bottom:8px;">작업을 삭제할까요?</div>
    <div id="deleteConfirmMsg" style="font-size:14px;color:#666;margin-bottom:24px;"></div>
    <div style="display:flex;gap:12px;justify-content:center;">
      <button class="btn btn-secondary" onclick="closeDeleteModal()" style="min-width:100px;">취소</button>
      <button class="btn btn-danger" id="deleteConfirmBtn" style="min-width:100px;">삭제</button>
    </div>
  </div>
</div>

<script>
const taskTypeNames = {
  view_boost:     '👁️ 조회수 부스팅',
  keyword_search: '🔍 키워드 검색 유입',
  review_request: '⭐ 리뷰 유도',
  photo_update:   '📷 사진 업데이트',
  smart_boost:    '🤖 스마트 자동'
};
const statusLabels = {
  running:   '<span class="badge badge-green">▶ 실행 중</span>',
  paused:    '<span class="badge badge-orange">⏸ 일시정지</span>',
  completed: '<span class="badge badge-blue">✓ 완료</span>',
  failed:    '<span class="badge badge-red">✗ 실패</span>',
  pending:   '<span class="badge badge-gray">⏳ 대기</span>',
};

let places = [];

function $(id) { return document.getElementById(id); }

/* ═══════════════════════════════════════════
   플레이스 로드
═══════════════════════════════════════════ */
async function loadPlaces() {
  const res  = await fetch('index.php?route=api/place-boost/places');
  const data = await res.json();
  places = data.data || [];
  $('totalPlaces').textContent = places.length;

  $('placeList').innerHTML = places.length === 0
    ? '<div style="text-align:center;padding:40px;color:#888;">등록된 플레이스가 없습니다.<br>위 버튼을 눌러 추가하세요.</div>'
    : places.map(p => {
        let kws = [];
        try { kws = JSON.parse(p.target_keywords || '[]'); } catch(e) {}
        return `
          <div style="padding:16px;border:1px solid #f0f0f5;border-radius:12px;margin-bottom:10px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
              <div>
                <div style="font-size:15px;font-weight:700;">${p.place_name}</div>
                <div style="font-size:12px;color:#888;margin-top:2px;">📍 ${p.address||'-'} · ${p.category||'-'}</div>
                <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px;">
                  ${kws.map(k=>`<span class="badge badge-blue">${k}</span>`).join('')}
                </div>
              </div>
              <div style="display:flex;gap:8px;">
                <button class="btn btn-secondary btn-sm"
                        onclick="startBoost(${p.id},'${p.place_name.replace(/'/g,"\\'")}')">⚡ 부스팅</button>
                <button class="btn btn-primary btn-sm"
                        onclick="checkPlaceRank(${p.id},'${p.place_name.replace(/'/g,"\\'")}',${JSON.stringify(kws)})">📊 분석</button>
              </div>
            </div>
            <div id="rankInfo_${p.id}" style="margin-top:10px;"></div>
          </div>`;
      }).join('');
}

/* ═══════════════════════════════════════════
   작업 로드 (삭제 버튼 포함)
═══════════════════════════════════════════ */
async function loadTasks() {
  const res   = await fetch('index.php?route=api/place-boost/tasks');
  const tasks = (await res.json()).data || [];

  $('runningTasks').textContent   = tasks.filter(t => t.status === 'running').length;
  $('completedTasks').textContent = tasks.filter(t => t.status === 'completed').length;
  $('avgRank').textContent        = '—';

  // 전체선택 체크박스 초기화
  $('selectAllTasks').checked = false;
  $('deleteSelectedBtn').style.display = 'none';

  if (tasks.length === 0) {
    $('taskTable').innerHTML =
      '<tr><td colspan="8" style="text-align:center;color:#888;padding:20px;">작업 내역이 없습니다.</td></tr>';
    return;
  }

  $('taskTable').innerHTML = tasks.map(t => {
    const pct    = t.target_count > 0 ? Math.round(t.completed_count / t.target_count * 100) : 0;
    const fillCls = t.status === 'running' ? 'pf-green' : t.status === 'completed' ? 'pf-blue' : 'pf-red';

    /* 제어 버튼 영역 */
    let ctrl = '';
    if (t.status === 'running') {
      ctrl += `<button class="btn btn-secondary btn-sm" onclick="controlTask(${t.id},'pause')" style="margin-right:4px;">⏸ 정지</button>`;
    }
    if (t.status === 'paused') {
      ctrl += `<button class="btn btn-success btn-sm" onclick="controlTask(${t.id},'resume')" style="margin-right:4px;">▶ 재개</button>`;
    }
    /* ★ 삭제 버튼: 모든 상태에서 표시 */
    ctrl += `<button class="btn btn-danger btn-sm" onclick="confirmDeleteTask(${t.id},'${(t.place_name||'').replace(/'/g,"\\'")}','${t.task_type}')">🗑️ 삭제</button>`;

    return `
      <tr id="taskRow_${t.id}">
        <td style="text-align:center;">
          <input type="checkbox" class="task-checkbox"
                 data-id="${t.id}"
                 style="width:16px;height:16px;cursor:pointer;"
                 onchange="onCheckboxChange()">
        </td>
        <td style="font-weight:600;">${t.place_name}</td>
        <td>${taskTypeNames[t.task_type] || t.task_type}</td>
        <td>${t.keyword || '-'}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <div class="progress-wrap" style="width:80px;">
              <div class="progress-fill ${fillCls}" style="width:${pct}%"></div>
            </div>
            <span style="font-size:12px;font-weight:700;">${t.completed_count}/${t.target_count}</span>
          </div>
        </td>
        <td>${statusLabels[t.status] || t.status}</td>
        <td style="font-size:12px;color:#888;">${new Date(t.created_at).toLocaleDateString('ko-KR')}</td>
        <td style="white-space:nowrap;">${ctrl}</td>
      </tr>`;
  }).join('');
}

/* ═══════════════════════════════════════════
   체크박스 관련
═══════════════════════════════════════════ */
function toggleSelectAll(checked) {
  document.querySelectorAll('.task-checkbox').forEach(cb => cb.checked = checked);
  updateDeleteSelectedBtn();
}

function onCheckboxChange() {
  const all  = document.querySelectorAll('.task-checkbox');
  const chkd = document.querySelectorAll('.task-checkbox:checked');
  $('selectAllTasks').checked = all.length > 0 && all.length === chkd.length;
  updateDeleteSelectedBtn();
}

function updateDeleteSelectedBtn() {
  const count = document.querySelectorAll('.task-checkbox:checked').length;
  const btn   = $('deleteSelectedBtn');
  if (count > 0) {
    btn.style.display = 'inline-flex';
    btn.textContent   = `🗑️ 선택 삭제 (${count})`;
  } else {
    btn.style.display = 'none';
  }
}

/* ═══════════════════════════════════════════
   단건 삭제 확인 모달
═══════════════════════════════════════════ */
function confirmDeleteTask(taskId, placeName, taskType) {
  const typeName = taskTypeNames[taskType] || taskType;
  $('deleteConfirmMsg').textContent =
    `"${placeName}"의 ${typeName} 작업을 삭제합니다.\n이 작업은 되돌릴 수 없습니다.`;
  $('deleteConfirmBtn').onclick = () => deleteTask([taskId]);
  $('deleteConfirmModal').style.display = 'flex';
}

function closeDeleteModal() {
  $('deleteConfirmModal').style.display = 'none';
}

/* ═══════════════════════════════════════════
   선택 삭제
═══════════════════════════════════════════ */
function deleteSelectedTasks() {
  const ids = [...document.querySelectorAll('.task-checkbox:checked')]
                .map(cb => parseInt(cb.dataset.id));
  if (ids.length === 0) return;

  $('deleteConfirmMsg').textContent =
    `선택된 ${ids.length}개 작업을 삭제합니다.\n이 작업은 되돌릴 수 없습니다.`;
  $('deleteConfirmBtn').onclick = () => deleteTask(ids);
  $('deleteConfirmModal').style.display = 'flex';
}

/* ═══════════════════════════════════════════
   실제 삭제 API 호출
═══════════════════════════════════════════ */
async function deleteTask(ids) {
  closeDeleteModal();
  showLoading('삭제 중...');
  try {
    const res  = await fetch('index.php?route=api/place-boost/tasks/delete', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ task_ids: ids })
    });
    const data = await res.json();

    if (data.success) {
      /* 삭제된 행 애니메이션으로 제거 */
      ids.forEach(id => {
        const row = $('taskRow_' + id);
        if (row) {
          row.style.transition = 'opacity .3s, transform .3s';
          row.style.opacity    = '0';
          row.style.transform  = 'translateX(30px)';
          setTimeout(() => row.remove(), 300);
        }
      });
      showSuccessToast(`✅ ${ids.length}개 작업이 삭제되었습니다.`);
      setTimeout(loadTasks, 500);
    } else {
      alert('❌ 삭제 실패: ' + (data.error || '알 수 없는 오류'));
    }
  } catch(e) {
    alert('❌ 오류: ' + e.message);
  } finally {
    hideLoading();
  }
}

/* ═══════════════════════════════════════════
   작업 제어 (정지 / 재개)
═══════════════════════════════════════════ */
async function controlTask(id, action) {
  await fetch('index.php?route=api/place-boost/tasks/' + id, {
    method:  'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ action })
  });
  loadTasks();
}

/* ═══════════════════════════════════════════
   부스팅 시작
═══════════════════════════════════════════ */
function startBoost(id, name) {
  $('boostPlaceId').value   = id;
  $('boostPlaceName').value = name;
  $('boostPanel').style.display = 'block';
  updateBoostUI();
  $('boostPanel').scrollIntoView({ behavior: 'smooth' });
}

function updateBoostUI() {
  const type    = $('boostType').value;
  const details = {
    view_boost:     `<div class="alert alert-info">👁️ <strong>조회수 부스팅:</strong> 플레이스 조회수를 자연스럽게 증가시켜 상위 노출을 유도합니다.</div><div class="form-group"><label class="form-label">연관 키워드</label><input type="text" class="form-control" id="boostKeyword" placeholder="강남 맛집, 강남 점심"></div>`,
    keyword_search: `<div class="alert alert-success">🔍 <strong>키워드 검색 유입:</strong> 특정 키워드로 검색 후 클릭하는 패턴을 시뮬레이션합니다.</div><div class="form-group"><label class="form-label">타겟 키워드 *</label><input type="text" class="form-control" id="boostKeyword" placeholder="순위를 올리고 싶은 키워드"></div>`,
    review_request: `<div class="alert alert-warning">⭐ <strong>리뷰 유도:</strong> 방문 고객에게 자동으로 리뷰 요청 메시지를 발송합니다.</div><div class="form-group"><label class="form-label">리뷰 요청 메시지</label><textarea class="form-control" id="reviewMsg">안녕하세요! 방문해주셔서 감사합니다. 리뷰를 남겨주시면 큰 힘이 됩니다 😊</textarea></div>`,
    photo_update:   `<div class="alert alert-info">📷 <strong>사진 업데이트:</strong> 플레이스에 최신 사진을 업데이트하여 노출을 높입니다.</div>`,
    smart_boost:    `<div class="alert alert-success">🤖 <strong>스마트 자동 부스팅:</strong> AI가 최적 시간대와 방법을 선택합니다.</div><div class="form-group"><label class="form-label">목표 순위</label><input type="number" class="form-control" id="targetRank" value="3" min="1" max="10"></div>`,
  };
  $('boostTypeDetail').innerHTML = details[type] || '';
}

async function startBoostTask() {
  const placeId = $('boostPlaceId').value;
  if (!placeId) { alert('플레이스를 선택해주세요.'); return; }
  showLoading('부스팅 시작 중...');
  try {
    const res  = await fetch('index.php?route=api/place-boost/start', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        place_id:     parseInt(placeId),
        task_type:    $('boostType').value,
        keyword:      document.getElementById('boostKeyword')?.value || '',
        target_count: parseInt($('boostCount').value) || 100,
        config: {
          time_range: $('boostTimeRange').value,
          speed:      $('boostSpeed').value,
          region:     $('boostRegion').value,
          device:     $('boostDevice').value,
        }
      })
    });
    const data = await res.json();
    if (data.success) {
      alert('✅ ' + data.message);
      $('boostPanel').style.display = 'none';
      loadTasks();
    } else {
      alert('❌ ' + (data.error || '오류 발생'));
    }
  } finally { hideLoading(); }
}

/* ═══════════════════════════════════════════
   즉시 순위 확인
═══════════════════════════════════════════ */
async function checkRank() {
  const keyword = $('rankCheckKeyword').value.trim();
  const place   = $('rankCheckPlace').value.trim();
  if (!keyword || !place) { alert('키워드와 업체명을 입력하세요.'); return; }
  showLoading('순위 확인 중...');
  try {
    const res  = await fetch('index.php?route=api/place-boost/check-rank', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ keyword, place_name: place })
    });
    const data = await res.json();
    const d    = data.data;
    const color = d.rank <= 3 ? '#00b894' : d.rank <= 10 ? '#f5a623' : '#e94560';
    const bg    = d.rank <= 3 ? '#e0f7f0' : d.rank <= 10 ? '#fff3e0' : '#ffe4e8';
    const el    = $('rankResult');
    el.style.display = 'block';
    el.innerHTML = `
      <div style="text-align:center;padding:16px;background:${bg};border-radius:12px;margin-bottom:12px;">
        <div style="font-size:36px;font-weight:800;color:${color}">${d.rank}위</div>
        <div style="font-size:13px;color:#666;">"${keyword}" 검색 결과 · 전체 ${d.total}개 중</div>
      </div>
      <div style="font-size:12px;color:#888;text-align:center;">확인 시각: ${new Date(d.checked_at).toLocaleString('ko-KR')}</div>`;
  } catch(e) { alert('순위 확인 중 오류가 발생했습니다.'); }
  finally { hideLoading(); }
}

async function checkPlaceRank(placeId, placeName, keywords) {
  const el = $('rankInfo_' + placeId);
  el.innerHTML = '<div style="color:#888;font-size:12px;">순위 확인 중...</div>';
  const results = [];
  for (const kw of keywords.slice(0, 3)) {
    const res  = await fetch('index.php?route=api/place-boost/check-rank', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ keyword: kw, place_name: placeName })
    });
    const data = await res.json();
    if (data.success) results.push({ keyword: kw, rank: data.data.rank });
  }
  el.innerHTML = results.map(r =>
    `<span style="display:inline-flex;align-items:center;gap:4px;
      background:${r.rank<=3?'#e0f7f0':r.rank<=10?'#fff3e0':'#ffe4e8'};
      padding:3px 8px;border-radius:6px;font-size:12px;margin-right:6px;">
      ${r.keyword} <strong>${r.rank}위</strong></span>`
  ).join('');
}

/* ═══════════════════════════════════════════
   모달
═══════════════════════════════════════════ */
function showAddPlaceModal() { $('addPlaceModal').style.display = 'flex'; }
function closeModal()        { $('addPlaceModal').style.display = 'none'; }

async function addPlace() {
  const name = $('newPlaceName').value.trim();
  if (!name) { alert('업체명을 입력하세요.'); return; }
  const kws = $('newPlaceKeywords').value.split(',').map(k => k.trim()).filter(k => k);
  const res  = await fetch('index.php?route=api/place-boost/places', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      place_name:      name,
      category:        $('newPlaceCategory').value,
      address:         $('newPlaceAddress').value,
      naver_place_url: $('newPlaceUrl').value,
      target_keywords: kws
    })
  });
  const data = await res.json();
  if (data.success) { closeModal(); loadPlaces(); alert('✅ 플레이스가 등록되었습니다!'); }
}

/* ═══════════════════════════════════════════
   토스트 / 로딩 헬퍼
═══════════════════════════════════════════ */
function showSuccessToast(message) {
  const t = document.createElement('div');
  t.style.cssText = `
    position:fixed;bottom:30px;right:30px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;padding:14px 22px;border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:99999;
    font-size:14px;font-weight:500;
    animation:slideUp .4s cubic-bezier(.68,-.55,.265,1.55);`;
  t.textContent = message;
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .3s'; setTimeout(()=>t.remove(),300); }, 3000);
}

function showLoading(msg = '처리 중...') {
  let el = $('globalLoader');
  if (!el) {
    el = document.createElement('div');
    el.id = 'globalLoader';
    el.style.cssText = `
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,.65);display:flex;align-items:center;
      justify-content:center;z-index:9999;backdrop-filter:blur(4px);`;
    el.innerHTML = `<div style="background:#fff;padding:36px 48px;border-radius:16px;text-align:center;">
      <div style="font-size:44px;animation:spin 1s linear infinite;">⏳</div>
      <div id="loaderMsg" style="margin-top:14px;font-size:15px;font-weight:500;color:#333;">${msg}</div>
    </div>`;
    document.body.appendChild(el);
  } else {
    $('loaderMsg').textContent = msg;
    el.style.display = 'flex';
  }
}
function hideLoading() { const el=$('globalLoader'); if(el) el.style.display='none'; }

/* 초기 로드 */
loadPlaces();
loadTasks();
setInterval(loadTasks, 30000);
</script>

<style>
@keyframes slideUp { from{transform:translateY(40px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes spin    { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }

.btn-danger {
  background: linear-gradient(135deg, #ff4757, #e84560);
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: opacity .2s, transform .1s;
}
.btn-danger:hover  { opacity: .88; transform: translateY(-1px); }
.btn-danger:active { transform: translateY(0); }

.btn-success {
  background: linear-gradient(135deg, #00b894, #00cec9);
  color: #fff;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: opacity .2s;
}
.btn-success:hover { opacity: .88; }

/* 체크박스 열 */
th:first-child, td:first-child { text-align: center; }

/* 삭제 확인 모달 메시지 줄바꿈 */
#deleteConfirmMsg { white-space: pre-line; }
</style>
