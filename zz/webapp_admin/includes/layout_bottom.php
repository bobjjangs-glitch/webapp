  </div><!-- /content -->
</div><!-- /main -->

<!-- 모달 오버레이 -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeModal()">
  <div class="modal" id="modalBox">
    <h3 id="modalTitle">확인</h3>
    <div id="modalBody"></div>
    <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
      <button class="btn btn-outline" onclick="closeModal()">취소</button>
      <button class="btn btn-primary" id="modalConfirmBtn">확인</button>
    </div>
  </div>
</div>

<script>
function openModal(title, body, confirmCallback) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalBody').innerHTML    = body;
  document.getElementById('modalConfirmBtn').onclick = () => { confirmCallback(); closeModal(); };
  document.getElementById('modalOverlay').classList.add('open');
}
function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}
async function callApi(action, data={}, method='POST') {
  const url = 'index.php?p=api&action=' + action;
  const opts = method === 'GET'
    ? { method:'GET' }
    : { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) };
  const res  = await fetch(url, opts);
  return res.json();
}
function showAlert(msg, type='success') {
  const div = document.createElement('div');
  div.className = 'alert alert-' + type;
  div.textContent = msg;
  div.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:280px;box-shadow:0 4px 20px rgba(0,0,0,.15);';
  document.body.appendChild(div);
  setTimeout(() => div.remove(), 3500);
}
</script>
</body>
</html>
