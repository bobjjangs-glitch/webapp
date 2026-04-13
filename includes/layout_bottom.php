  </div><!-- /page-content -->
</div><!-- /main-content -->

<script>
const $ = id => document.getElementById(id);
function showLoading(msg='분석 중...'){$('loadingText').textContent=msg;$('loadingOverlay').classList.add('active');}
function hideLoading(){$('loadingOverlay').classList.remove('active');}
function toggleSidebar(){$('sidebar').classList.toggle('open');$('overlay').classList.toggle('active');}
function closeSidebar(){$('sidebar').classList.remove('open');$('overlay').classList.remove('active');}
function fmt(n){return n>=10000?((n/10000).toFixed(1)+'만'):n>=1000?((n/1000).toFixed(1)+'천'):String(n);}
function fmtNum(n){return Number(n).toLocaleString('ko-KR');}
function fmtSec(s){if(s<60)return s+'초';if(s<3600)return Math.floor(s/60)+'분 '+Math.floor(s%60)+'초';return Math.floor(s/3600)+'시간 '+Math.floor((s%3600)/60)+'분';}
function showResult(id){document.querySelectorAll('.result-section').forEach(el=>el.classList.remove('visible'));const el=$(id);if(el){el.classList.add('visible');setTimeout(()=>el.scrollIntoView({behavior:'smooth',block:'start'}),100);}}
function confirmLogout(){if(confirm('로그아웃 하시겠습니까?'))window.location.href='index.php?route=logout';}

// ── 알림 ──
let notifOpen=false;
function toggleNotif(){
  notifOpen=!notifOpen;
  $('notifDropdown').classList.toggle('open',notifOpen);
  if(notifOpen)loadNotifications();
}
document.addEventListener('click',e=>{
  if(!e.target.closest('#notifBtn')&&!e.target.closest('#notifDropdown')){notifOpen=false;$('notifDropdown').classList.remove('open');}
});
async function loadNotifications(){
  try{
    const res=await fetch('index.php?route=api/notifications');
    const data=await res.json();
    const notifs=data.data||[];
    const unread=notifs.filter(n=>!n.is_read).length;
    $('notifDot').style.display=unread>0?'block':'none';
    const icons={success:'✅',info:'ℹ️',warning:'⚠️',error:'🔴'};
    $('notifList').innerHTML=notifs.length?notifs.map(n=>`
      <div class="notif-item ${n.is_read?'':'unread'}" onclick="readNotif(${n.id})">
        <div class="n-title">${icons[n.type]||'ℹ️'} ${n.title}</div>
        <div class="n-msg">${n.message}</div>
        <div class="n-time">${new Date(n.created_at).toLocaleString('ko-KR')}</div>
      </div>`).join(''):'<div style="padding:20px;text-align:center;color:#888;font-size:13px;">알림이 없습니다</div>';
  }catch(e){}
}
async function readNotif(id){
  await fetch('index.php?route=api/notifications/'+id+'/read',{method:'PATCH'});
  loadNotifications();
}
async function markAllRead(){
  const res=await fetch('index.php?route=api/notifications');
  const data=await res.json();
  await Promise.all((data.data||[]).filter(n=>!n.is_read).map(n=>fetch('index.php?route=api/notifications/'+n.id+'/read',{method:'PATCH'})));
  loadNotifications();
}

// 페이지 로드시 알림 확인
loadNotifications();
</script>
</body>
</html>
