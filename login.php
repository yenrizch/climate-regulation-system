<?php
session_start();
require 'config.php';
$error = "";
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $sql->execute([$username, $password]);
    if($sql->rowCount() > 0){
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lettuce Greenhouse — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --forest:    #1a3a2a;
    --canopy:    #2d5a3d;
    --leaf:      #4a8c5c;
    --sprout:    #7bbf8a;
    --mist:      #d4ead8;
    --cream:     #f5f0e8;
    --soil:      #3b2a1a;
    --gold:      #c8a84b;
    --glass:     rgba(255,255,255,0.07);
    --glass-border: rgba(255,255,255,0.15);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    min-height: 100vh;
    font-family: 'DM Sans', sans-serif;
    background-color: var(--forest);
    display: flex;
    align-items: stretch;
    overflow: hidden;
  }

  /* ── Left panel: botanical illustration side ── */
  .panel-left {
    flex: 1;
    position: relative;
    background: linear-gradient(160deg, #0e2218 0%, #1a3a2a 45%, #2d5a3d 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 60px 56px;
    overflow: hidden;
  }

  /* Layered leaf SVG background */
  .panel-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      radial-gradient(ellipse 80% 60% at 30% 20%, rgba(74,140,92,0.18) 0%, transparent 70%),
      radial-gradient(ellipse 60% 80% at 80% 80%, rgba(45,90,61,0.25) 0%, transparent 60%);
    pointer-events: none;
  }

  /* Decorative large lettuce leaf SVG */
  .leaf-art {
    position: absolute;
    top: -40px;
    right: -60px;
    opacity: 0.18;
    width: 520px;
    animation: sway 8s ease-in-out infinite;
    transform-origin: 60% 10%;
  }

  .leaf-art-2 {
    position: absolute;
    bottom: 80px;
    left: -80px;
    opacity: 0.10;
    width: 380px;
    transform: rotate(140deg) scaleX(-1);
    animation: sway 11s ease-in-out infinite reverse;
    transform-origin: 50% 90%;
  }

  @keyframes sway {
    0%, 100% { transform: rotate(0deg); }
    50%       { transform: rotate(3deg); }
  }

  /* Floating pollen dots */
  .dots {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }
  .dot {
    position: absolute;
    border-radius: 50%;
    background: var(--sprout);
    opacity: 0;
    animation: float linear infinite;
  }
  @keyframes float {
    0%   { opacity: 0; transform: translateY(0) scale(0.5); }
    20%  { opacity: 0.4; }
    80%  { opacity: 0.2; }
    100% { opacity: 0; transform: translateY(-120px) scale(1.2); }
  }

  .panel-left-content {
    position: relative;
    z-index: 2;
  }

  .brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--glass);
    border: 1px solid var(--glass-border);
    border-radius: 40px;
    padding: 6px 16px 6px 10px;
    margin-bottom: 36px;
    backdrop-filter: blur(8px);
  }
  .brand-badge .dot-live {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--sprout);
    box-shadow: 0 0 8px var(--sprout);
    animation: pulse 2s ease-in-out infinite;
  }
  @keyframes pulse {
    0%,100% { box-shadow: 0 0 6px var(--sprout); }
    50%      { box-shadow: 0 0 16px var(--sprout); }
  }
  .brand-badge span {
    font-size: 12px;
    color: var(--mist);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 500;
  }

  .panel-left-content h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.4rem, 3.5vw, 3.4rem);
    font-weight: 600;
    color: var(--cream);
    line-height: 1.18;
    margin-bottom: 20px;
    letter-spacing: -0.01em;
  }
  .panel-left-content h1 em {
    font-style: italic;
    color: var(--sprout);
  }

  .panel-left-content p {
    font-size: 15px;
    color: rgba(212,234,216,0.65);
    line-height: 1.7;
    max-width: 340px;
    font-weight: 300;
  }

  .stats-row {
    display: flex;
    gap: 32px;
    margin-top: 44px;
  }
  .stat {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .stat-num {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: var(--gold);
    font-weight: 600;
    letter-spacing: -0.02em;
  }
  .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(212,234,216,0.5);
  }

  /* ── Right panel: login form ── */
  .panel-right {
    width: 460px;
    flex-shrink: 0;
    background: var(--cream);
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 64px 52px;
    position: relative;
  }

  /* Subtle texture overlay */
  .panel-right::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234a8c5c' fill-opacity='0.035'%3E%3Ccircle cx='30' cy='30' r='1.5'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
  }

  .form-wrap {
    position: relative;
    z-index: 1;
  }

  .form-logo {
    width: 52px;
    height: 52px;
    background: var(--forest);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 32px;
    box-shadow: 0 4px 20px rgba(26,58,42,0.2);
  }

  .form-eyebrow {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--leaf);
    font-weight: 500;
    margin-bottom: 10px;
  }

  .form-title {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 600;
    color: var(--soil);
    margin-bottom: 8px;
    letter-spacing: -0.02em;
  }

  .form-subtitle {
    font-size: 14px;
    color: #7a8c7d;
    margin-bottom: 36px;
    font-weight: 300;
  }

  /* Error message */
  .error-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff0f0;
    border: 1px solid #f5c6c6;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 22px;
    font-size: 13.5px;
    color: #b03030;
  }
  .error-box svg { flex-shrink: 0; }

  /* Input group */
  .field-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--soil);
    margin-bottom: 8px;
    letter-spacing: 0.02em;
  }

  .input-wrap {
    position: relative;
  }

  .input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9bb39f;
    pointer-events: none;
    transition: color 0.2s;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 13px 16px 13px 44px;
    border: 1.5px solid #dde8de;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14.5px;
    color: var(--soil);
    background: white;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    -webkit-appearance: none;
  }

  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: var(--leaf);
    box-shadow: 0 0 0 3px rgba(74,140,92,0.12);
  }

  input[type="text"]:focus + .focus-line,
  input[type="password"]:focus + .focus-line {
    width: 100%;
  }

  input::placeholder { color: #b4c8b7; }

  .input-wrap:focus-within .input-icon {
    color: var(--leaf);
  }

  /* Remember / forgot row */
  .form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 6px 0 28px;
  }

  .remember {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }

  .remember input[type="checkbox"] {
    width: 16px;
    height: 16px;
    padding: 0;
    accent-color: var(--leaf);
    border-radius: 4px;
    cursor: pointer;
  }

  .remember span {
    font-size: 13px;
    color: #5e7462;
  }

  .forgot-link {
    font-size: 13px;
    color: var(--leaf);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
  }
  .forgot-link:hover { color: var(--canopy); }

  /* Submit button */
  .btn-login {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--canopy) 0%, var(--forest) 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 500;
    letter-spacing: 0.03em;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(26,58,42,0.28);
  }

  .btn-login::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 60%);
    pointer-events: none;
  }

  .btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(26,58,42,0.36);
  }

  .btn-login:active { transform: translateY(0); }

  /* Divider */
  .divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 28px 0 20px;
  }
  .divider-line {
    flex: 1;
    height: 1px;
    background: #dde8de;
  }
  .divider span {
    font-size: 12px;
    color: #9bb39f;
    white-space: nowrap;
  }

  /* Footer */
  .form-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e4ede4;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .form-footer-brand {
    font-size: 12px;
    color: #9bb39f;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .form-footer-brand svg { opacity: 0.6; }

  .version-badge {
    font-size: 11px;
    background: #eaf3eb;
    color: var(--leaf);
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 500;
  }

  /* Responsive */
  @media (max-width: 820px) {
    .panel-left { display: none; }
    .panel-right { width: 100%; padding: 48px 32px; }
  }
</style>
</head>
<body>

<!-- Left panel -->
<div class="panel-left">

  <!-- Floating dots -->
  <div class="dots" id="dots"></div>

  <!-- Decorative leaf SVG -->
  <svg class="leaf-art" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M200 480 C200 480 20 360 10 200 C0 40 200 10 200 10 C200 10 400 40 390 200 C380 360 200 480 200 480Z" fill="#4a8c5c"/>
    <path d="M200 480 L200 10" stroke="#2d5a3d" stroke-width="3"/>
    <path d="M200 200 Q280 160 360 120" stroke="#2d5a3d" stroke-width="1.5" stroke-dasharray="4 4"/>
    <path d="M200 260 Q120 220 50 180" stroke="#2d5a3d" stroke-width="1.5" stroke-dasharray="4 4"/>
    <path d="M200 320 Q290 290 350 250" stroke="#2d5a3d" stroke-width="1.5" stroke-dasharray="4 4"/>
    <path d="M200 150 Q110 110 60 80" stroke="#2d5a3d" stroke-width="1.5" stroke-dasharray="4 4"/>
    <ellipse cx="200" cy="245" rx="110" ry="155" fill="none" stroke="#7bbf8a" stroke-width="1" stroke-dasharray="6 8" opacity="0.5"/>
  </svg>

  <svg class="leaf-art-2" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M200 480 C200 480 20 360 10 200 C0 40 200 10 200 10 C200 10 400 40 390 200 C380 360 200 480 200 480Z" fill="#4a8c5c"/>
    <path d="M200 480 L200 10" stroke="#2d5a3d" stroke-width="3"/>
  </svg>

  <div class="panel-left-content">
    <div class="brand-badge">
      <span class="dot-live"></span>
      <span>Live Monitoring Active</span>
    </div>

    <h1>Grow smarter,<br><em>harvest better</em></h1>
    <p>Your intelligent greenhouse management platform. Monitor climate, irrigation, and crop health from one unified dashboard.</p>

    <div class="stats-row">
      <div class="stat">
        <span class="stat-num">98%</span>
        <span class="stat-label">Yield Rate</span>
      </div>
      <div class="stat">
        <span class="stat-num">12</span>
        <span class="stat-label">Active Zones</span>
      </div>
      <div class="stat">
        <span class="stat-num">24°C</span>
        <span class="stat-label">Avg. Temp</span>
      </div>
    </div>
  </div>
</div>

<!-- Right panel: login form -->
<div class="panel-right">
  <div class="form-wrap">

    <div class="form-logo">
      <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 24 C14 24 4 18 4 10 C4 5.58 8.58 2 14 2 C19.42 2 24 5.58 24 10 C24 18 14 24 14 24Z" fill="#4a8c5c"/>
        <path d="M14 24 L14 2" stroke="#7bbf8a" stroke-width="1.5"/>
        <path d="M14 12 Q18 9 22 7" stroke="#7bbf8a" stroke-width="1" stroke-dasharray="2 2"/>
        <path d="M14 16 Q10 13 6 11" stroke="#7bbf8a" stroke-width="1" stroke-dasharray="2 2"/>
      </svg>
    </div>

    <p class="form-eyebrow">Greenhouse Portal</p>
    <h2 class="form-title">Welcome back</h2>
    <p class="form-subtitle">Sign in to access your greenhouse dashboard</p>

    <?php if($error != ""): ?>
    <div class="error-box">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#b03030" stroke-width="1.5"/><path d="M8 5v3.5M8 11v.5" stroke="#b03030" stroke-width="1.5" stroke-linecap="round"/></svg>
      <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <div class="field-group">
        <label for="username">Username</label>
        <div class="input-wrap">
          <svg class="input-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/><path d="M2 14c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
        </div>
      </div>

      <div class="field-group">
        <label for="password">Password</label>
        <div class="input-wrap">
          <svg class="input-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 7V5a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
        </div>
      </div>

      <div class="form-options">
        <label class="remember">
          <input type="checkbox" name="remember">
          <span>Remember me</span>
        </label>
        <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
      </div>

      <button type="submit" name="login" class="btn-login">Sign In to Dashboard</button>

    </form>

    <div class="form-footer">
      <div class="form-footer-brand">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 12C7 12 2 9 2 5C2 2.79 4.24 1 7 1C9.76 1 12 2.79 12 5C12 9 7 12 7 12Z" fill="#4a8c5c"/></svg>
        Lettuce Greenhouse System
      </div>
      <span class="version-badge">v2.4.1</span>
    </div>

  </div>
</div>

<script>
  // Generate floating pollen dots
  const container = document.getElementById('dots');
  for (let i = 0; i < 18; i++) {
    const d = document.createElement('div');
    d.className = 'dot';
    const size = Math.random() * 4 + 2;
    d.style.cssText = `
      width: ${size}px;
      height: ${size}px;
      left: ${Math.random() * 100}%;
      bottom: ${Math.random() * 40}%;
      animation-duration: ${6 + Math.random() * 10}s;
      animation-delay: ${Math.random() * 8}s;
    `;
    container.appendChild(d);
  }
</script>
</body>
</html>
