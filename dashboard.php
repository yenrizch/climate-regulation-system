<?php
session_start();
date_default_timezone_set('Asia/Manila');

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}
require 'config.php';
$result = $conn->query("SELECT * FROM climate_data ORDER BY id DESC LIMIT 50");
$rows = $result->fetchAll(PDO::FETCH_ASSOC);
$latest_result = $conn->query("SELECT * FROM climate_data ORDER BY id DESC LIMIT 1");
$latest = $latest_result->fetch(PDO::FETCH_ASSOC);
$total_result = $conn->query("SELECT COUNT(*) as total FROM climate_data");
$total = $total_result->fetch(PDO::FETCH_ASSOC);

$history_result = $conn->query("
    SELECT * FROM climate_data 
    WHERE time >= NOW() - INTERVAL 24 HOUR 
    ORDER BY id DESC
");
$history_rows = $history_result->fetchAll(PDO::FETCH_ASSOC);

$graph_result = $conn->query("SELECT * FROM climate_data ORDER BY id DESC LIMIT 20");
$graph_rows = array_reverse($graph_result->fetchAll(PDO::FETCH_ASSOC));

$graph_labels = [];
$graph_temps = [];
$graph_hums = [];
foreach($graph_rows as $g){
    $graph_labels[] = date('h:i A', strtotime($g['time']) + (8*3600));
    $graph_temps[] = $g['temperature'];
    $graph_hums[] = $g['humidity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="10">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lettuce Greenhouse — Climate Monitor</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
    --bg:#f0f7f0;
    --bg2:#ffffff;
    --bg3:#e8f5e9;
    --sidebar:#1b4332;
    --sidebar2:#2d6a4f;
    --accent:#40916c;
    --accent2:#52b788;
    --text:#1a1a2e;
    --text2:#4a5568;
    --text3:#718096;
    --border:#d4edda;
    --card:#ffffff;
    --shadow:0 4px 24px rgba(0,0,0,0.08);
    --on:#e65100;
    --off:#2e7d32;
    --badge-on-bg:#fff3e0;
    --badge-off-bg:#e8f5e9;
}
body.dark{
    --bg:#0a1628;
    --bg2:#0f2027;
    --bg3:#0d1b2a;
    --sidebar:#051510;
    --sidebar2:#0a2416;
    --accent:#52b788;
    --accent2:#74c69d;
    --text:#e8f5e9;
    --text2:#a5d6a7;
    --text3:#6b9e7a;
    --border:#1b4332;
    --card:#0f2027;
    --shadow:0 4px 24px rgba(0,0,0,0.4);
    --on:#ffb74d;
    --off:#81c784;
    --badge-on-bg:#3e2000;
    --badge-off-bg:#0a2416;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;transition:all 0.3s;}
.sidebar{
    width:260px;background:var(--sidebar);min-height:100vh;
    display:flex;flex-direction:column;padding:30px 0;
    position:fixed;top:0;left:0;z-index:100;transition:0.3s;
}
.sidebar-logo{padding:0 25px 30px;border-bottom:1px solid rgba(255,255,255,0.1);}
.sidebar-logo h2{color:#fff;font-size:18px;font-weight:700;line-height:1.3;}
.sidebar-logo p{color:#95d5b2;font-size:12px;margin-top:4px;}
.sidebar-logo .logo-icon{font-size:36px;margin-bottom:10px;}
.sidebar-nav{padding:20px 0;flex:1;}
.nav-item{
    display:flex;align-items:center;gap:12px;
    padding:13px 25px;color:rgba(255,255,255,0.65);
    cursor:pointer;transition:0.2s;font-size:14px;font-weight:500;
    border-left:3px solid transparent;
}
.nav-item:hover,.nav-item.active{
    background:rgba(255,255,255,0.08);color:#fff;
    border-left:3px solid var(--accent2);
}
.nav-item .icon{font-size:18px;width:22px;text-align:center;}
.sidebar-bottom{padding:20px 25px;border-top:1px solid rgba(255,255,255,0.1);}
.logout-btn{
    display:flex;align-items:center;gap:10px;
    color:rgba(255,255,255,0.65);text-decoration:none;
    font-size:14px;font-weight:500;padding:10px 0;transition:0.2s;
}
.logout-btn:hover{color:#ff6b6b;}
.main{margin-left:260px;flex:1;padding:30px;min-height:100vh;}
.topbar{
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:30px;flex-wrap:wrap;gap:15px;
}
.topbar-left h1{font-size:24px;font-weight:700;color:var(--text);}
.topbar-left p{font-size:13px;color:var(--text3);margin-top:3px;}
.topbar-right{display:flex;align-items:center;gap:12px;}
.theme-btn{
    background:var(--card);border:1px solid var(--border);
    color:var(--text);padding:10px 16px;border-radius:10px;
    cursor:pointer;font-size:13px;font-family:'Inter',sans-serif;
    font-weight:500;transition:0.2s;
}
.theme-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent);}
.notif-btn{
    position:relative;background:var(--card);border:1px solid var(--border);
    color:var(--text);padding:10px 16px;border-radius:10px;cursor:pointer;
    font-size:13px;font-family:'Inter',sans-serif;font-weight:500;transition:0.2s;
}
.notif-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent);}
.notif-badge{
    position:absolute;top:-6px;right:-6px;
    background:#e53e3e;color:#fff;border-radius:50%;
    width:18px;height:18px;font-size:10px;font-weight:700;
    display:flex;align-items:center;justify-content:center;
}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;}
.card{
    background:var(--card);border-radius:16px;padding:24px;
    box-shadow:var(--shadow);border:1px solid var(--border);
    position:relative;overflow:hidden;transition:0.3s;
}
.card:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(0,0,0,0.12);}
.card-icon{
    width:48px;height:48px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-size:22px;margin-bottom:16px;
}
.card-icon.temp{background:linear-gradient(135deg,#ff6b35,#f7c59f);}
.card-icon.hum{background:linear-gradient(135deg,#4facfe,#a8edea);}
.card-icon.fan{background:linear-gradient(135deg,#43e97b,#38f9d7);}
.card-icon.total{background:linear-gradient(135deg,#a18cd1,#fbc2eb);}
.card-label{font-size:12px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;}
.card-value{font-size:36px;font-weight:800;color:var(--text);line-height:1;}
.card-value.fan-on{color:var(--on);}
.card-value.fan-off{color:var(--off);}
.card-sub{font-size:12px;color:var(--text3);margin-top:8px;}
.card-bar{position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--accent2));}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px;}
.grid-2 .full{grid-column:1/-1;}
@media(max-width:900px){.grid-2{grid-template-columns:1fr;}}
.panel{
    background:var(--card);border-radius:16px;
    box-shadow:var(--shadow);border:1px solid var(--border);
    overflow:hidden;transition:0.3s;
}
.panel-header{
    padding:18px 24px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:10px;
}
.panel-header h3{font-size:15px;font-weight:700;color:var(--text);}
.panel-header span{font-size:12px;color:var(--text3);}
.panel-body{padding:20px 24px;}
.chart-wrap{position:relative;height:220px;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{
    padding:12px 16px;text-align:left;font-size:11px;
    font-weight:700;text-transform:uppercase;letter-spacing:0.8px;
    color:var(--text3);border-bottom:2px solid var(--border);
}
.data-table td{
    padding:13px 16px;font-size:13px;
    color:var(--text);border-bottom:1px solid var(--border);
}
.data-table tr:last-child td{border-bottom:none;}
.data-table tr:hover td{background:var(--bg3);}
.badge{padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:0.5px;}
.badge-on{background:var(--badge-on-bg);color:var(--on);}
.badge-off{background:var(--badge-off-bg);color:var(--off);}
.export-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:7px 16px;border-radius:8px;font-size:12px;
    font-weight:600;text-decoration:none;transition:0.2s;
    font-family:'Inter',sans-serif;
}
.export-btn-csv{background:#2e7d32;color:#fff;}
.export-btn-csv:hover{background:#1b5e20;color:#fff;}
.export-btn-pdf{background:#c62828;color:#fff;}
.export-btn-pdf:hover{background:#8e0000;color:#fff;}
.notif-panel{
    display:none;position:fixed;top:80px;right:30px;width:360px;
    background:var(--card);border:1px solid var(--border);
    border-radius:16px;box-shadow:0 8px 40px rgba(0,0,0,0.2);
    z-index:999;max-height:480px;overflow-y:auto;
}
.notif-panel.show{display:block;}
.notif-panel-header{
    padding:16px 20px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
}
.notif-panel-header h4{font-size:15px;font-weight:700;color:var(--text);}
.notif-close{background:none;border:none;font-size:18px;cursor:pointer;color:var(--text3);}
.notif-item{
    padding:14px 20px;border-bottom:1px solid var(--border);
    display:flex;gap:12px;align-items:flex-start;
}
.notif-item:last-child{border-bottom:none;}
.notif-dot{width:10px;height:10px;border-radius:50%;margin-top:4px;flex-shrink:0;}
.notif-dot.on{background:#e65100;}
.notif-dot.off{background:#2e7d32;}
.notif-dot.temp{background:#4facfe;}
.notif-text{font-size:13px;color:var(--text);line-height:1.5;}
.notif-time{font-size:11px;color:var(--text3);margin-top:3px;}
.notif-empty{padding:30px;text-align:center;color:var(--text3);font-size:13px;}
.page-section{display:none;}
.page-section.active{display:block;}
.status-dot{
    display:inline-block;width:8px;height:8px;border-radius:50%;
    background:#40916c;margin-right:6px;animation:pulse 2s infinite;
}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.4;}}
.footer{margin-top:30px;text-align:center;color:var(--text3);font-size:12px;padding-top:20px;border-top:1px solid var(--border);}
@media(max-width:768px){
    .sidebar{width:0;overflow:hidden;}
    .main{margin-left:0;}
    .notif-panel{right:10px;width:calc(100vw - 20px);}
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🥬</div>
        <h2>Lettuce Greenhouse</h2>
        <p>Climate Monitoring System</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-item active" onclick="showPage('dashboard')" id="nav-dashboard">
            <span class="icon">📊</span> Dashboard
        </div>
        <div class="nav-item" onclick="showPage('graph')" id="nav-graph">
            <span class="icon">📈</span> Graph
        </div>
        <div class="nav-item" onclick="showPage('history')" id="nav-history">
            <span class="icon">🕐</span> History
        </div>
    </nav>
    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-btn">
            <span>🚪</span> Logout
        </a>
    </div>
</div>

<div class="main">

    <div class="topbar">
        <div class="topbar-left">
            <h1><span class="status-dot"></span>Live Monitor</h1>
            <p>Last updated: <?= date('F d, Y h:i:s A'); ?> PHT</p>
        </div>
        <div class="topbar-right">
            <button class="notif-btn" onclick="toggleNotif()" id="notifBtn">
                🔔 Notifications
                <span class="notif-badge" id="notifCount">0</span>
            </button>
            <button class="theme-btn" onclick="toggleDark()" id="themeBtn">🌙 Dark</button>
        </div>
    </div>

    <div class="notif-panel" id="notifPanel">
        <div class="notif-panel-header">
            <h4>🔔 Notifications</h4>
            <button class="notif-close" onclick="toggleNotif()">✕</button>
        </div>
        <div id="notifList">
            <div class="notif-empty">No notifications yet.<br>Checks every 1 hour.</div>
        </div>
    </div>

    <!-- DASHBOARD PAGE -->
    <div class="page-section active" id="page-dashboard">
        <div class="cards">
            <div class="card">
                <div class="card-icon temp">🌡️</div>
                <div class="card-label">Temperature</div>
                <div class="card-value"><?= isset($latest['temperature']) ? $latest['temperature'] : 'N/A'; ?>°C</div>
                <div class="card-sub">Live greenhouse temp</div>
                <div class="card-bar"></div>
            </div>
            <div class="card">
                <div class="card-icon hum">💧</div>
                <div class="card-label">Humidity</div>
                <div class="card-value"><?= isset($latest['humidity']) ? $latest['humidity'] : 'N/A'; ?>%</div>
                <div class="card-sub">Current air moisture</div>
                <div class="card-bar"></div>
            </div>
            <div class="card">
                <div class="card-icon fan">🌀</div>
                <div class="card-label">Fan Status</div>
                <div class="card-value <?= isset($latest['fan_status']) ? 'fan-'.strtolower($latest['fan_status']) : ''; ?>">
                    <?= isset($latest['fan_status']) ? $latest['fan_status'] : 'N/A'; ?>
                </div>
                <div class="card-sub">Auto ventilation</div>
                <div class="card-bar"></div>
            </div>
            <div class="card">
                <div class="card-icon total">📦</div>
                <div class="card-label">Total Records</div>
                <div class="card-value"><?= $total['total']; ?></div>
                <div class="card-sub">Database entries</div>
                <div class="card-bar"></div>
            </div>
        </div>

        <div class="panel" style="margin-bottom:20px;">
            <div class="panel-header">
                <h3>📈 Temperature & Humidity — Last 20 Readings</h3>
                <span>Auto-refresh every 10s</span>
            </div>
            <div class="panel-body">
                <div class="chart-wrap">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <h3>📋 Live Data Feed</h3>
                <span>Latest 50 records</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Temperature</th><th>Humidity</th>
                            <th>Fan Status</th><th>Time (PHT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($rows) > 0): ?>
                            <?php foreach($rows as $row): ?>
                            <tr>
                                <td>#<?= $row['id']; ?></td>
                                <td>🌡️ <?= $row['temperature']; ?> °C</td>
                                <td>💧 <?= $row['humidity']; ?> %</td>
                                <td>
                                    <?php if($row['fan_status'] == "ON"): ?>
                                        <span class='badge badge-on'>🌀 ON</span>
                                    <?php else: ?>
                                        <span class='badge badge-off'>⏹ OFF</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y h:i:s A', strtotime($row['time']) + (8*3600)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:40px;color:var(--text3);">
                                    No data yet. Waiting for ESP32...
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- GRAPH PAGE -->
    <div class="page-section" id="page-graph">
        <div class="panel" style="margin-bottom:20px;">
            <div class="panel-header">
                <h3>🌡️ Temperature Trend</h3>
                <span>Last 20 readings</span>
            </div>
            <div class="panel-body">
                <div class="chart-wrap"><canvas id="tempChart"></canvas></div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <h3>💧 Humidity Trend</h3>
                <span>Last 20 readings</span>
            </div>
            <div class="panel-body">
                <div class="chart-wrap"><canvas id="humChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- HISTORY PAGE — CSV + PDF buttons added here -->
    <div class="page-section" id="page-history">
        <div class="panel">
            <div class="panel-header">
                <h3>🕐 24-Hour History</h3>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="font-size:12px;color:var(--text3);">
                        <?= count($history_rows); ?> records
                    </span>
                    <a href="export_csv.php" class="export-btn export-btn-csv">
                        📥 Download CSV
                    </a>
                    <a href="export_pdf.php" class="export-btn export-btn-pdf">
                        📄 Download PDF
                    </a>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Temperature</th><th>Humidity</th>
                            <th>Fan Status</th><th>Time (PHT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($history_rows) > 0): ?>
                            <?php foreach($history_rows as $row): ?>
                            <tr>
                                <td>#<?= $row['id']; ?></td>
                                <td>🌡️ <?= $row['temperature']; ?> °C</td>
                                <td>💧 <?= $row['humidity']; ?> %</td>
                                <td>
                                    <?php if($row['fan_status'] == "ON"): ?>
                                        <span class='badge badge-on'>🌀 ON</span>
                                    <?php else: ?>
                                        <span class='badge badge-off'>⏹ OFF</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y h:i:s A', strtotime($row['time']) + (8*3600)); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:40px;color:var(--text3);">
                                    No history in the last 24 hours.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer">Lettuce Greenhouse Monitoring System © 2026 — PHT</div>
</div>

<script>
const labels = <?= json_encode($graph_labels); ?>;
const temps  = <?= json_encode($graph_temps); ?>;
const hums   = <?= json_encode($graph_hums); ?>;

const chartDefaults = {
    responsive:true,
    maintainAspectRatio:false,
    plugins:{legend:{labels:{font:{family:'Inter',size:12},color:getComputedStyle(document.body).getPropertyValue('--text')}}},
    scales:{
        x:{ticks:{font:{family:'Inter',size:10},color:'#718096'},grid:{color:'rgba(0,0,0,0.05)'}},
        y:{ticks:{font:{family:'Inter',size:10},color:'#718096'},grid:{color:'rgba(0,0,0,0.05)'}}
    }
};

const mainCtx = document.getElementById('mainChart').getContext('2d');
new Chart(mainCtx, {
    type:'line',
    data:{
        labels:labels,
        datasets:[
            {label:'Temperature (°C)',data:temps,borderColor:'#ff6b35',backgroundColor:'rgba(255,107,53,0.1)',tension:0.4,fill:true,pointBackgroundColor:'#ff6b35',pointRadius:4},
            {label:'Humidity (%)',data:hums,borderColor:'#4facfe',backgroundColor:'rgba(79,172,254,0.1)',tension:0.4,fill:true,pointBackgroundColor:'#4facfe',pointRadius:4}
        ]
    },
    options:chartDefaults
});

const tempCtx = document.getElementById('tempChart').getContext('2d');
new Chart(tempCtx, {
    type:'line',
    data:{
        labels:labels,
        datasets:[{label:'Temperature (°C)',data:temps,borderColor:'#ff6b35',backgroundColor:'rgba(255,107,53,0.15)',tension:0.4,fill:true,pointBackgroundColor:'#ff6b35',pointRadius:5,borderWidth:2}]
    },
    options:chartDefaults
});

const humCtx = document.getElementById('humChart').getContext('2d');
new Chart(humCtx, {
    type:'line',
    data:{
        labels:labels,
        datasets:[{label:'Humidity (%)',data:hums,borderColor:'#4facfe',backgroundColor:'rgba(79,172,254,0.15)',tension:0.4,fill:true,pointBackgroundColor:'#4facfe',pointRadius:5,borderWidth:2}]
    },
    options:chartDefaults
});

if(localStorage.getItem('theme') === 'dark'){
    document.body.classList.add('dark');
    document.getElementById('themeBtn').textContent = '☀️ Light';
}
function toggleDark(){
    document.body.classList.toggle('dark');
    const btn = document.getElementById('themeBtn');
    if(document.body.classList.contains('dark')){
        btn.textContent = '☀️ Light';
        localStorage.setItem('theme','dark');
    } else {
        btn.textContent = '🌙 Dark';
        localStorage.setItem('theme','light');
    }
}

function showPage(page){
    document.querySelectorAll('.page-section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
    document.getElementById('page-'+page).classList.add('active');
    document.getElementById('nav-'+page).classList.add('active');
}

const notifications = JSON.parse(localStorage.getItem('notifs') || '[]');

function addNotif(dot, text){
    const time = new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',hour12:true});
    notifications.unshift({dot, text, time});
    if(notifications.length > 20) notifications.pop();
    localStorage.setItem('notifs', JSON.stringify(notifications));
    renderNotifs();
}

function renderNotifs(){
    const list = document.getElementById('notifList');
    const count = document.getElementById('notifCount');
    if(notifications.length === 0){
        list.innerHTML = '<div class="notif-empty">No notifications yet.<br>Checks every 1 hour.</div>';
        count.textContent = '0';
        return;
    }
    count.textContent = notifications.length > 9 ? '9+' : notifications.length;
    list.innerHTML = notifications.map(n=>`
        <div class="notif-item">
            <div class="notif-dot ${n.dot}"></div>
            <div>
                <div class="notif-text">${n.text}</div>
                <div class="notif-time">${n.time}</div>
            </div>
        </div>
    `).join('');
}

function checkAndNotify(){
    const temp = <?= isset($latest['temperature']) ? $latest['temperature'] : 0; ?>;
    const hum  = <?= isset($latest['humidity']) ? $latest['humidity'] : 0; ?>;
    const fan  = "<?= isset($latest['fan_status']) ? $latest['fan_status'] : 'N/A'; ?>";
    if(fan === "ON"){
        addNotif('on', `🌀 Fan is ON — Temperature reached ${temp}°C`);
    } else {
        addNotif('off', `⏹ Fan is OFF — Temperature is ${temp}°C`);
    }
    addNotif('temp', `📊 Status Update — Temp: ${temp}°C | Humidity: ${hum}%`);
}

renderNotifs();
const lastCheck = localStorage.getItem('lastNotifCheck');
const now = Date.now();
if(!lastCheck || (now - parseInt(lastCheck)) >= 3600000){
    checkAndNotify();
    localStorage.setItem('lastNotifCheck', now.toString());
}
setInterval(()=>{
    checkAndNotify();
    localStorage.setItem('lastNotifCheck', Date.now().toString());
}, 3600000);

function toggleNotif(){
    document.getElementById('notifPanel').classList.toggle('show');
}
document.addEventListener('click', function(e){
    const panel = document.getElementById('notifPanel');
    const btn = document.getElementById('notifBtn');
    if(!panel.contains(e.target) && !btn.contains(e.target)){
        panel.classList.remove('show');
    }
});
</script>
</body>
</html>
