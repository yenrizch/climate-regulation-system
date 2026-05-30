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

/* ── Optimal Lettuce Growing Conditions Panel ── */
.optimal-panel {
    background: var(--card);
    border-radius: 16px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 20px;
    transition: 0.3s;
}
.optimal-panel-header {
    padding: 16px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
}
.optimal-panel-header .header-icon {
    width: 36px; height: 36px;
    background: rgba(255,255,255,0.15);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
}
.optimal-panel-header h3 {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
}
.optimal-panel-header p {
    font-size: 11px;
    color: rgba(255,255,255,0.65);
    margin-top: 1px;
}
.optimal-stages {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
}
@media(max-width:900px){
    .optimal-stages { grid-template-columns: repeat(2, 1fr); }
}
@media(max-width:500px){
    .optimal-stages { grid-template-columns: 1fr; }
}
.stage-card {
    padding: 20px 22px;
    border-right: 1px solid var(--border);
    position: relative;
    transition: background 0.2s;
    cursor: default;
}
.stage-card:last-child { border-right: none; }
.stage-card:hover { background: var(--bg3); }
.stage-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0;
}
.stage-card.seedling::before   { background: linear-gradient(90deg, #a8edea, #4facfe); }
.stage-card.vegetative::before { background: linear-gradient(90deg, #43e97b, #38f9d7); }
.stage-card.mature::before     { background: linear-gradient(90deg, #f9d423, #f83600); }
.stage-card.harvest::before    { background: linear-gradient(90deg, #a18cd1, #fbc2eb); }
.stage-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.stage-label.seedling   { color: #4facfe; }
.stage-label.vegetative { color: #43e97b; }
.stage-label.mature     { color: #f9a825; }
.stage-label.harvest    { color: #a18cd1; }
.stage-label .stage-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.stage-label.seedling   .stage-dot { background: #4facfe; }
.stage-label.vegetative .stage-dot { background: #43e97b; }
.stage-label.mature     .stage-dot { background: #f9a825; }
.stage-label.harvest    .stage-dot { background: #a18cd1; }
.stage-metric {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
}
.stage-metric:last-child { margin-bottom: 0; }
.stage-metric-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.stage-metric-icon.t { background: rgba(255,107,53,0.12); }
.stage-metric-icon.h { background: rgba(79,172,254,0.12); }
.stage-metric-info {}
.stage-metric-info .metric-title {
    font-size: 10px;
    color: var(--text3);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.stage-metric-info .metric-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}
.stage-status {
    margin-top: 14px;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}
.stage-status.active {
    background: #e8f5e9;
    color: #2e7d32;
}
.stage-status.inactive {
    background: var(--bg3);
    color: var(--text3);
}
body.dark .stage-status.active {
    background: #0a2416;
    color: #81c784;
}
.stage-status .status-pip {
    width: 6px; height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}
.stage-status.active .status-pip  { background: #2e7d32; animation: pulse 2s infinite; }
.stage-status.inactive .status-pip { background: var(--text3); }
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🥬</div>
        <h2>Lettuce Greenhouse</h2>
        <p>Climate Monitoring System</p>
    </div>
    <nav class="sidebar-n
