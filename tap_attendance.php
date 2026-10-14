<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ID Tap Attendance Station — Southern Baptist College</title>
  <link rel="icon" type="image/png" href="web_admin/assets/images/sbc_logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy-950: #040d1a;
      --navy-900: #071b30;
      --navy-850: #0c243f;
      --navy-800: #102e52;
      --navy-700: #194373;
      --gold-500: #f59e0b;
      --gold-400: #fbbf24;
      --gold-600: #d97706;
      --emerald-500: #10b981;
      --emerald-400: #34d399;
      --emerald-900: #064e3b;
      --rose-500: #f43f5e;
      --rose-400: #fb7185;
      --rose-950: #4c0519;
      --sky-400: #38bdf8;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-subtle: rgba(255, 255, 255, 0.08);
      --border-glow: rgba(245, 158, 11, 0.35);
      --card-bg: rgba(12, 36, 63, 0.7);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: radial-gradient(circle at top center, #0f2c4e 0%, var(--navy-950) 65%);
      color: var(--text-main);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* Top Navigation Bar */
    .kiosk-topbar {
      background: rgba(7, 27, 48, 0.85);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border-subtle);
      padding: 0.85rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .brand-cluster {
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      color: inherit;
    }

    .brand-logo {
      width: 48px;
      height: 48px;
      object-fit: contain;
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
    }

    .brand-text h1 {
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: -0.01em;
      color: #ffffff;
      line-height: 1.2;
    }

    .brand-text p {
      font-size: 0.78rem;
      color: var(--gold-400);
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }

    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 1.25rem;
    }

    .clock-pill {
      background: rgba(4, 13, 26, 0.75);
      border: 1px solid var(--border-subtle);
      padding: 0.45rem 1rem;
      border-radius: 9999px;
      display: flex;
      align-items: center;
      gap: 0.65rem;
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.92rem;
      font-weight: 700;
      color: var(--gold-400);
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }

    .live-dot {
      width: 8px;
      height: 8px;
      background: var(--emerald-400);
      border-radius: 50%;
      box-shadow: 0 0 10px var(--emerald-400);
      animation: pulse-dot 1.8s infinite;
    }

    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.85); }
    }

    .btn-action-pill {
      background: rgba(255, 255, 255, 0.06);
      border: 1px solid var(--border-subtle);
      color: var(--text-main);
      padding: 0.5rem 0.9rem;
      border-radius: 9999px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.45rem;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .btn-action-pill:hover {
      background: rgba(255, 255, 255, 0.14);
      border-color: rgba(255, 255, 255, 0.25);
      transform: translateY(-1px);
    }

    /* Main Container */
    .kiosk-main {
      flex: 1;
      max-width: 1350px;
      width: 100%;
      margin: 0 auto;
      padding: 2rem 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 1.75rem;
    }

    /* Informational Dual-Mode Banner */
    .workflow-banner {
      background: linear-gradient(90deg, rgba(16, 46, 82, 0.6) 0%, rgba(7, 27, 48, 0.7) 100%);
      border: 1px solid rgba(56, 189, 248, 0.25);
      border-radius: 14px;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1.5rem;
      flex-wrap: wrap;
    }

    .workflow-item {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      flex: 1;
      min-width: 280px;
    }

    .workflow-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      background: rgba(245, 158, 11, 0.15);
      border: 1px solid rgba(245, 158, 11, 0.3);
      color: var(--gold-400);
      flex-shrink: 0;
    }

    .workflow-icon.mobile {
      background: rgba(56, 189, 248, 0.15);
      border-color: rgba(56, 189, 248, 0.3);
      color: var(--sky-400);
    }

    .workflow-text h4 {
      font-size: 0.9rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.2rem;
    }

    .workflow-text p {
      font-size: 0.78rem;
      color: var(--text-muted);
      line-height: 1.35;
    }

    .workflow-divider {
      width: 1px;
      height: 38px;
      background: var(--border-subtle);
    }

    /* Shift Selector Toolbar */
    .shift-toolbar {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.65rem;
      flex-wrap: wrap;
      background: rgba(7, 27, 48, 0.55);
      padding: 0.65rem 1rem;
      border-radius: 16px;
      border: 1px solid var(--border-subtle);
    }

    .shift-btn {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-subtle);
      color: var(--text-muted);
      font-family: inherit;
      font-size: 0.82rem;
      font-weight: 600;
      padding: 0.6rem 1.15rem;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .shift-btn:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
    }

    .shift-btn.active {
      background: linear-gradient(135deg, var(--gold-500) 0%, var(--gold-600) 100%);
      color: #040d1a;
      border-color: var(--gold-400);
      font-weight: 800;
      box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
      transform: translateY(-1px);
    }

    /* Kiosk Grid */
    .kiosk-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 1.75rem;
      align-items: start;
    }

    @media (max-width: 992px) {
      .kiosk-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Tap Action Panel */
    .tap-panel {
      background: var(--card-bg);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.45);
      display: flex;
      flex-direction: column;
      gap: 1.75rem;
      position: relative;
      overflow: hidden;
    }

    .tap-panel::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--gold-500), var(--sky-400), var(--emerald-400));
    }

    .panel-header {
      text-align: center;
    }

    .panel-header .eyebrow {
      font-size: 0.75rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--gold-400);
      margin-bottom: 0.35rem;
    }

    .panel-header h2 {
      font-size: 1.65rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      color: #ffffff;
    }

    .panel-header p {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-top: 0.25rem;
    }

    /* Scanner Input Box */
    .scanner-form {
      position: relative;
    }

    .scanner-input-wrap {
      position: relative;
      border-radius: 14px;
      overflow: hidden;
      border: 2px solid rgba(245, 158, 11, 0.3);
      box-shadow: 0 0 20px rgba(245, 158, 11, 0.15);
      transition: all 0.3s ease;
    }

    .scanner-input-wrap:focus-within {
      border-color: var(--gold-400);
      box-shadow: 0 0 30px rgba(245, 158, 11, 0.35);
    }

    .scanner-input {
      width: 100%;
      background: #020914;
      border: none;
      padding: 1.25rem 3.5rem 1.25rem 1.25rem;
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.2rem;
      font-weight: 700;
      color: #ffffff;
      text-align: center;
      letter-spacing: 0.05em;
      outline: none;
    }

    .scanner-input::placeholder {
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.95rem;
      font-weight: 500;
      color: #64748b;
      letter-spacing: 0;
    }

    .scanner-icon-badge {
      position: absolute;
      right: 1.15rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gold-400);
      font-size: 1.35rem;
      pointer-events: none;
    }

    .scanner-helper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 0.5rem;
      font-size: 0.76rem;
      color: var(--text-muted);
      padding: 0 0.25rem;
    }

    .focus-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      color: var(--emerald-400);
      font-weight: 600;
    }

    .focus-indicator.unfocused {
      color: var(--rose-400);
    }

    /* Live Tap Card Display */
    .tap-display-box {
      background: rgba(4, 13, 26, 0.7);
      border: 1px solid var(--border-subtle);
      border-radius: 16px;
      padding: 1.75rem;
      min-height: 230px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    .tap-display-box.idle {
      border-color: rgba(255, 255, 255, 0.08);
    }

    .tap-display-box.success {
      background: radial-gradient(circle at center, rgba(16, 185, 129, 0.15) 0%, rgba(4, 13, 26, 0.9) 80%);
      border-color: var(--emerald-500);
      box-shadow: 0 0 35px rgba(16, 185, 129, 0.25);
    }

    .tap-display-box.error {
      background: radial-gradient(circle at center, rgba(244, 63, 94, 0.15) 0%, rgba(4, 13, 26, 0.9) 80%);
      border-color: var(--rose-500);
      box-shadow: 0 0 35px rgba(244, 63, 94, 0.25);
    }

    /* Idle State Elements */
    .idle-icon-wrap {
      width: 76px;
      height: 76px;
      border-radius: 50%;
      background: rgba(245, 158, 11, 0.1);
      border: 1px dashed rgba(245, 158, 11, 0.4);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      margin-bottom: 1rem;
      animation: float-pulse 3s ease-in-out infinite;
    }

    @keyframes float-pulse {
      0%, 100% { transform: translateY(0); box-shadow: 0 0 0 rgba(245, 158, 11, 0); }
      50% { transform: translateY(-4px); box-shadow: 0 0 20px rgba(245, 158, 11, 0.25); }
    }

    .idle-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 0.35rem;
    }

    .idle-desc {
      font-size: 0.85rem;
      color: var(--text-muted);
      max-width: 380px;
      line-height: 1.4;
    }

    /* Success Card Content */
    .success-content {
      width: 100%;
      animation: pop-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes pop-in {
      from { opacity: 0; transform: scale(0.92); }
      to { opacity: 1; transform: scale(1); }
    }

    .student-badge-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 0.85rem;
    }

    .student-avatar {
      width: 60px;
      height: 60px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--gold-500), #f97316);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      font-weight: 800;
      color: #040d1a;
      box-shadow: 0 6px 16px rgba(0,0,0,0.4);
    }

    .student-info h3 {
      font-size: 1.35rem;
      font-weight: 800;
      color: #ffffff;
      text-align: left;
    }

    .student-subtags {
      display: flex;
      gap: 0.5rem;
      margin-top: 0.2rem;
      flex-wrap: wrap;
    }

    .tag-pill {
      font-size: 0.72rem;
      font-weight: 700;
      padding: 0.2rem 0.55rem;
      border-radius: 6px;
      letter-spacing: 0.02em;
    }

    .tag-program {
      background: rgba(56, 189, 248, 0.2);
      color: var(--sky-400);
      border: 1px solid rgba(56, 189, 248, 0.35);
    }

    .tag-id {
      background: rgba(245, 158, 11, 0.2);
      color: var(--gold-400);
      border: 1px solid rgba(245, 158, 11, 0.35);
      font-family: 'JetBrains Mono', monospace;
    }

    .punch-result-banner {
      background: rgba(16, 185, 129, 0.16);
      border: 1px solid rgba(16, 185, 129, 0.35);
      padding: 0.85rem 1.25rem;
      border-radius: 12px;
      margin: 1rem 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .punch-shift {
      font-size: 1rem;
      font-weight: 800;
      color: var(--emerald-400);
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    .punch-time {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.05rem;
      font-weight: 700;
      color: #ffffff;
    }

    .progress-strip {
      background: rgba(255, 255, 255, 0.06);
      border-radius: 9999px;
      height: 8px;
      overflow: hidden;
      margin: 0.65rem 0 0.35rem 0;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--gold-500), var(--emerald-400));
      border-radius: 9999px;
      transition: width 0.6s ease;
    }

    .progress-labels {
      display: flex;
      justify-content: space-between;
      font-size: 0.74rem;
      color: var(--text-muted);
      font-weight: 600;
    }

    .cheer-quote {
      font-size: 0.82rem;
      color: var(--gold-400);
      font-style: italic;
      margin-top: 0.85rem;
      padding-top: 0.75rem;
      border-top: 1px dashed rgba(255, 255, 255, 0.1);
    }

    /* Error Card Content */
    .error-content {
      width: 100%;
      animation: pop-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .error-icon-wrap {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      background: rgba(244, 63, 94, 0.15);
      border: 1px solid var(--rose-500);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: var(--rose-400);
      margin: 0 auto 0.75rem auto;
    }

    .error-title {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--rose-400);
      margin-bottom: 0.35rem;
    }

    .error-msg {
      font-size: 0.88rem;
      color: #cbd5e1;
      line-height: 1.45;
      max-width: 420px;
      margin: 0 auto;
    }

    /* Right Column: Live Stream & Metrics */
    .side-panel {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    /* Summary Stats Grid */
    .stats-card {
      background: var(--card-bg);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      padding: 1.25rem 1.5rem;
    }

    .stats-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .stats-card-header h3 {
      font-size: 0.95rem;
      font-weight: 700;
      color: #ffffff;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.75rem;
    }

    .stat-pill {
      background: rgba(4, 13, 26, 0.6);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 0.85rem;
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }

    .stat-pill .label {
      font-size: 0.72rem;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }

    .stat-pill .val {
      font-family: 'JetBrains Mono', monospace;
      font-size: 1.35rem;
      font-weight: 800;
      color: #ffffff;
    }

    .stat-pill.m-in .val { color: var(--gold-400); }
    .stat-pill.m-out .val { color: var(--sky-400); }
    .stat-pill.a-in .val { color: var(--emerald-400); }
    .stat-pill.a-out .val { color: #a855f7; }

    /* Recent Taps Feed Table */
    .feed-card {
      background: var(--card-bg);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      padding: 1.25rem 1.5rem;
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .feed-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .feed-header h3 {
      font-size: 0.95rem;
      font-weight: 700;
      color: #ffffff;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .feed-list {
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
      max-height: 380px;
      overflow-y: auto;
      padding-right: 0.35rem;
    }

    .feed-list::-webkit-scrollbar {
      width: 5px;
    }

    .feed-list::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 10px;
    }

    .feed-item {
      background: rgba(4, 13, 26, 0.55);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      transition: all 0.2s ease;
    }

    .feed-item:hover {
      background: rgba(4, 13, 26, 0.85);
      border-color: rgba(255, 255, 255, 0.18);
    }

    .feed-student {
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
    }

    .feed-student-name {
      font-size: 0.86rem;
      font-weight: 700;
      color: #ffffff;
    }

    .feed-student-sub {
      font-size: 0.72rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 0.45rem;
    }

    .feed-punch {
      text-align: right;
      display: flex;
      flex-direction: column;
      gap: 0.15rem;
      align-items: flex-end;
    }

    .punch-tag {
      font-size: 0.7rem;
      font-weight: 700;
      padding: 0.15rem 0.45rem;
      border-radius: 5px;
    }

    .punch-tag.in {
      background: rgba(16, 185, 129, 0.2);
      color: var(--emerald-400);
      border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .punch-tag.out {
      background: rgba(56, 189, 248, 0.2);
      color: var(--sky-400);
      border: 1px solid rgba(56, 189, 248, 0.3);
    }

    .punch-timestamp {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.76rem;
      color: #cbd5e1;
    }

    .method-badge {
      font-size: 0.65rem;
      font-weight: 600;
      padding: 0.1rem 0.35rem;
      border-radius: 4px;
      background: rgba(245, 158, 11, 0.15);
      color: var(--gold-400);
    }

    .method-badge.selfie {
      background: rgba(56, 189, 248, 0.15);
      color: var(--sky-400);
    }

    /* Kiosk Footer */
    .kiosk-footer {
      text-align: center;
      padding: 1.25rem 2rem;
      border-top: 1px solid var(--border-subtle);
      font-size: 0.76rem;
      color: var(--text-muted);
      background: rgba(4, 13, 26, 0.7);
    }

    .kiosk-footer a {
      color: var(--gold-400);
      text-decoration: none;
    }

    .kiosk-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Top Navigation Header -->
  <header class="kiosk-topbar">
    <a href="#" class="brand-cluster" onclick="refocusScanner()">
      <img src="web_admin/assets/images/sbc_logo.png" alt="SBC Logo" class="brand-logo" onerror="this.src='mobile_app/assets/images/sbc_logo.png'">
      <div class="brand-text">
        <h1>Southern Baptist College</h1>
        <p>OJT & Internship ID Tap Terminal</p>
      </div>
    </a>

    <div class="topbar-actions">
      <!-- Live PST Digital Clock -->
      <div class="clock-pill">
        <span class="live-dot"></span>
        <span id="livePstClock">--:--:-- --</span>
      </div>

      <!-- Sound Toggle -->
      <button class="btn-action-pill" id="soundToggleBtn" onclick="toggleAudio()" title="Toggle Chime Sound">
        <span id="soundIcon">🔔</span>
        <span id="soundLabel">Sound On</span>
      </button>

      <!-- Fullscreen Toggle -->
      <button class="btn-action-pill" onclick="toggleFullScreen()" title="Toggle Fullscreen">
        <span>⛶</span>
        <span>Fullscreen</span>
      </button>

      <!-- Dean Suite Link -->
      <a href="web_admin/index.html" class="btn-action-pill" target="_blank" title="Open Dean Administrative Suite">
        <span>🔒</span>
        <span>Dean Suite</span>
      </a>

      <!-- CIT Department System Link -->
      <a href="http://localhost:5000" class="btn-action-pill" target="_blank" style="color: #38bdf8;" title="Open CIT Department System">
        <span>🏢</span>
        <span>CIT System</span>
      </a>
    </div>
  </header>

  <!-- Main Kiosk Layout -->
  <main class="kiosk-main">

    <!-- Informational Workflow Explanation Banner -->
    <section class="workflow-banner">
      <div class="workflow-item">
        <div class="workflow-icon">💳</div>
        <div class="workflow-text">
          <h4>Standing in Line for Attendance?</h4>
          <p>Tap your School ID card or scan your RFID badge right here on this terminal for instant verification.</p>
        </div>
      </div>

      <div class="workflow-divider"></div>

      <div class="workflow-item">
        <div class="workflow-icon mobile">📱</div>
        <div class="workflow-text">
          <h4>Don't Want to Line Up or Off-Site?</h4>
          <p>Use the SBC Mobile App to clock in/out with instant facial selfie verification directly from your phone.</p>
        </div>
      </div>
    </section>

    <!-- Shift Mode Selector Toolbar -->
    <div class="shift-toolbar">
      <button class="shift-btn active" data-mode="auto" onclick="setShiftMode('auto', this)">
        <span>⚡ Auto-Detect Shift</span>
      </button>
      <button class="shift-btn" data-mode="time_in_morning" onclick="setShiftMode('time_in_morning', this)">
        <span>🌅 Morning Time-In</span>
      </button>
      <button class="shift-btn" data-mode="time_out_morning" onclick="setShiftMode('time_out_morning', this)">
        <span>☀️ Morning Time-Out</span>
      </button>
      <button class="shift-btn" data-mode="time_in_afternoon" onclick="setShiftMode('time_in_afternoon', this)">
        <span>🌤️ Afternoon Time-In</span>
      </button>
      <button class="shift-btn" data-mode="time_out_afternoon" onclick="setShiftMode('time_out_afternoon', this)">
        <span>🌙 Afternoon Time-Out</span>
      </button>
    </div>

    <!-- Central Kiosk Interaction Grid -->
    <div class="kiosk-grid">

      <!-- Left Column: Tap Input & Confirmation Box -->
      <div class="tap-panel">
        <div class="panel-header">
          <p class="eyebrow">ATTENDANCE TAP STATION</p>
          <h2 id="activeWindowHeader">Ready for Student ID Tap</h2>
          <p id="activeWindowDesc">Morning Shift: 5:00 AM – 12:30 PM | Afternoon Shift: 12:30 PM – 7:00 PM</p>
        </div>

        <!-- Scanner Input Box -->
        <div class="scanner-form">
          <div class="scanner-input-wrap">
            <input 
              type="text" 
              id="rfidScannerInput" 
              class="scanner-input" 
              placeholder="TAP ID CARD OR SCAN BARCODE HERE" 
              autocomplete="off" 
              autocorrect="off" 
              autocapitalize="off" 
              spellcheck="false" 
              autofocus
            />
            <span class="scanner-icon-badge">📡</span>
          </div>
          <div class="scanner-helper">
            <span id="focusIndicator" class="focus-indicator">
              <span class="live-dot" style="width:6px; height:6px;"></span> Scanner Ready & Focused
            </span>
            <span>Supports 125kHz / 13.56MHz RFID cards & barcodes</span>
          </div>
        </div>

        <!-- Interactive Tap Status Display Box -->
        <div id="tapDisplayBox" class="tap-display-box idle">
          <!-- Idle State Content -->
          <div id="idleState" class="idle-content">
            <div class="idle-icon-wrap">💳</div>
            <h3 class="idle-title">Waiting for Student Tap...</h3>
            <p class="idle-desc">Students standing in line, please hold your School ID or RFID card against the reader.</p>
          </div>

          <!-- Dynamic Success State Content -->
          <div id="successState" class="success-content" style="display:none;"></div>

          <!-- Dynamic Error State Content -->
          <div id="errorState" class="error-content" style="display:none;"></div>
        </div>
      </div>

      <!-- Right Column: Live Feed & Today's Metric Counters -->
      <div class="side-panel">

        <!-- Today's Counters -->
        <div class="stats-card">
          <div class="stats-card-header">
            <h3>Today's Attendance Station Metrics</h3>
            <span class="tag-pill tag-id" id="todayDatePill">Oct 14, 2026</span>
          </div>
          <div class="stats-grid">
            <div class="stat-pill m-in">
              <span class="label">Morning In</span>
              <span class="val" id="statMIn">0</span>
            </div>
            <div class="stat-pill m-out">
              <span class="label">Morning Out</span>
              <span class="val" id="statMOut">0</span>
            </div>
            <div class="stat-pill a-in">
              <span class="label">Afternoon In</span>
              <span class="val" id="statAIn">0</span>
            </div>
            <div class="stat-pill a-out">
              <span class="label">Afternoon Out</span>
              <span class="val" id="statAOut">0</span>
            </div>
          </div>
        </div>

        <!-- Real-Time Recent Taps Feed -->
        <div class="feed-card">
          <div class="feed-header">
            <h3><span>⚡</span> Live Attendance Feed</h3>
            <span class="tag-pill tag-program" id="feedCountPill">0 recorded today</span>
          </div>
          <div id="recentTapsList" class="feed-list">
            <div style="text-align:center; padding: 2.5rem 1rem; color: #64748b; font-size: 0.85rem;">
              No attendance logs recorded today yet.
            </div>
          </div>
        </div>

      </div>

    </div>

  </main>

  <!-- Kiosk Footer -->
  <footer class="kiosk-footer">
    Southern Baptist College Internship Attendance & Verification System &bull; ID Tap Station Terminal &bull;
    <a href="web_admin/index.html">Dean Administration Portal</a>
  </footer>

  <script>
    // Global State
    let activeShiftMode = 'auto';
    let audioEnabled = localStorage.getItem('sbc_kiosk_sound') !== 'false';
    let lastSubmitAt = 0;
    const SUBMIT_COOLDOWN_MS = 750; // Prevent rapid duplicate scanner bursts
    let scannerBuffer = '';
    let scannerTimer = null;
    const SCANNER_TIMEOUT_MS = 160;

    // Elements
    const scannerInput = document.getElementById('rfidScannerInput');
    const tapDisplayBox = document.getElementById('tapDisplayBox');
    const idleState = document.getElementById('idleState');
    const successState = document.getElementById('successState');
    const errorState = document.getElementById('errorState');
    const focusIndicator = document.getElementById('focusIndicator');
    const soundToggleBtn = document.getElementById('soundToggleBtn');
    const soundIcon = document.getElementById('soundIcon');
    const soundLabel = document.getElementById('soundLabel');
    const liveClock = document.getElementById('livePstClock');

    // Update Sound Button State
    function updateSoundUi() {
      if (audioEnabled) {
        soundIcon.textContent = '🔔';
        soundLabel.textContent = 'Sound On';
        soundToggleBtn.style.color = '#f8fafc';
      } else {
        soundIcon.textContent = '🔕';
        soundLabel.textContent = 'Muted';
        soundToggleBtn.style.color = '#94a3b8';
      }
    }
    updateSoundUi();

    function toggleAudio() {
      audioEnabled = !audioEnabled;
      localStorage.setItem('sbc_kiosk_sound', audioEnabled ? 'true' : 'false');
      updateSoundUi();
      if (audioEnabled) playTone(659.25, 0.12); // pleasant confirmation chime
    }

    // Web Audio API Synthesizer (No MP3 dependencies)
    let audioCtx = null;
    function getAudioContext() {
      if (!audioCtx) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        audioCtx = new AudioContext();
      }
      if (audioCtx.state === 'suspended') {
        audioCtx.resume();
      }
      return audioCtx;
    }

    function playTone(freq, duration, type = 'sine') {
      if (!audioEnabled) return;
      try {
        const ctx = getAudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(0.18, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + duration);
      } catch (e) {
        console.warn('Audio play error:', e);
      }
    }

    function playSuccessChime() {
      if (!audioEnabled) return;
      // High bright pleasant two-tone chime (E5 -> A5)
      playTone(659.25, 0.18, 'triangle');
      setTimeout(() => playTone(880.00, 0.28, 'triangle'), 120);
    }

    function playErrorBuzzer() {
      if (!audioEnabled) return;
      // Low warning tone
      playTone(220.0, 0.25, 'sawtooth');
      setTimeout(() => playTone(185.0, 0.35, 'sawtooth'), 120);
    }

    // Keep scanner focused automatically
    function refocusScanner() {
      if (scannerInput) {
        scannerInput.focus();
        updateFocusIndicator(true);
      }
    }

    function updateFocusIndicator(isFocused) {
      if (isFocused) {
        focusIndicator.className = 'focus-indicator';
        focusIndicator.innerHTML = '<span class="live-dot" style="width:6px; height:6px;"></span> Scanner Ready & Focused';
      } else {
        focusIndicator.className = 'focus-indicator unfocused';
        focusIndicator.innerHTML = '⚠️ Click anywhere to refocus scanner';
      }
    }

    scannerInput.addEventListener('focus', () => updateFocusIndicator(true));
    scannerInput.addEventListener('blur', () => updateFocusIndicator(false));
    document.addEventListener('click', (e) => {
      // Don't steal focus if clicking on buttons or links
      if (!e.target.closest('button, a, select, input')) {
        refocusScanner();
      }
    });

    // Shift Mode Selector
    function setShiftMode(mode, btn) {
      activeShiftMode = mode;
      document.querySelectorAll('.shift-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const descMap = {
        'auto': 'Morning Shift: 5:00 AM – 12:30 PM | Afternoon Shift: 12:30 PM – 7:00 PM',
        'time_in_morning': 'Manual Shift Locked: Morning Time-In (05:00 AM – 12:30 PM)',
        'time_out_morning': 'Manual Shift Locked: Morning Time-Out (05:00 AM – 12:30 PM)',
        'time_in_afternoon': 'Manual Shift Locked: Afternoon Time-In (12:30 PM – 07:00 PM)',
        'time_out_afternoon': 'Manual Shift Locked: Afternoon Time-Out (12:30 PM – 07:00 PM)'
      };

      document.getElementById('activeWindowDesc').textContent = descMap[mode] || descMap['auto'];
      refocusScanner();
    }

    // Live Digital Clock
    function updateClock() {
      const now = new Date();
      const options = {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      };
      liveClock.textContent = now.toLocaleTimeString('en-US', options) + ' PST';
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Fullscreen Toggle
    function toggleFullScreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => console.error(err));
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        }
      }
      refocusScanner();
    }

    // Submit RFID / Tap ID
    async function submitTap(tapValue) {
      const trimmed = tapValue.trim();
      if (!trimmed) return;

      const now = Date.now();
      if (now - lastSubmitAt < SUBMIT_COOLDOWN_MS) return;
      lastSubmitAt = now;

      try {
        const response = await fetch('backend/tap_attendance_api.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            action: 'tap',
            tap_id: trimmed,
            shift_mode: activeShiftMode
          })
        });

        const data = await response.json();

        if (data.status === 'success') {
          showSuccessCard(data);
          playSuccessChime();
          loadFeed(); // Refresh recent taps feed immediately
        } else {
          showErrorCard(data.message || 'ID Tap recording failed.', data.code);
          playErrorBuzzer();
        }
      } catch (err) {
        showErrorCard('Network or server connection error. Please try tapping again.', 'NETWORK_ERROR');
        playErrorBuzzer();
        console.error('Tap submit error:', err);
      } finally {
        scannerInput.value = '';
        scannerBuffer = '';
        refocusScanner();
      }
    }

    // Show Success Card
    let resetTimer = null;
    function showSuccessCard(data) {
      if (resetTimer) clearTimeout(resetTimer);

      const s = data.student;
      const initial = s.full_name ? s.full_name.charAt(0).toUpperCase() : 'S';

      successState.innerHTML = `
        <div class="student-badge-header">
          <div class="student-avatar">${initial}</div>
          <div class="student-info">
            <h3>${escapeHtml(s.full_name)}</h3>
            <div class="student-subtags">
              <span class="tag-pill tag-id">${escapeHtml(s.student_number || s.id_no)}</span>
              <span class="tag-pill tag-program">${escapeHtml(s.course_code)}</span>
              <span class="tag-pill" style="background:rgba(255,255,255,0.08); color:#cbd5e1;">${escapeHtml(s.site_name)}</span>
            </div>
          </div>
        </div>

        <div class="punch-result-banner">
          <div class="punch-shift">
            <span>✅</span>
            <span>${escapeHtml(data.shift_label)}</span>
          </div>
          <div class="punch-time">${escapeHtml(data.recorded_time)}</div>
        </div>

        <div>
          <div class="progress-labels">
            <span>OJT Rendered Hours: ${s.rendered_hours} hrs</span>
            <span>Required: ${s.required_hours} hrs (${s.progress_percent}%)</span>
          </div>
          <div class="progress-strip">
            <div class="progress-fill" style="width: ${Math.min(100, s.progress_percent)}%"></div>
          </div>
        </div>

        <div class="cheer-quote">&ldquo;${escapeHtml(data.motivational_message || data.message)}&rdquo;</div>
      `;

      idleState.style.display = 'none';
      errorState.style.display = 'none';
      successState.style.display = 'block';

      tapDisplayBox.className = 'tap-display-box success';

      // Reset to idle state after 6 seconds
      resetTimer = setTimeout(resetToIdle, 6000);
    }

    // Show Error Card
    function showErrorCard(message, code) {
      if (resetTimer) clearTimeout(resetTimer);

      errorState.innerHTML = `
        <div class="error-icon-wrap">⚠️</div>
        <h3 class="error-title">Attendance Notice</h3>
        <p class="error-msg">${escapeHtml(message)}</p>
      `;

      idleState.style.display = 'none';
      successState.style.display = 'none';
      errorState.style.display = 'block';

      tapDisplayBox.className = 'tap-display-box error';

      resetTimer = setTimeout(resetToIdle, 5000);
    }

    function resetToIdle() {
      successState.style.display = 'none';
      errorState.style.display = 'none';
      idleState.style.display = 'block';
      tapDisplayBox.className = 'tap-display-box idle';
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Hardware Scanner Listener (RFID readers & barcode scanners)
    function flushScannerBuffer() {
      const val = scannerInput.value.trim() || scannerBuffer.trim();
      scannerBuffer = '';
      if (val) {
        submitTap(val);
      }
    }

    scannerInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        flushScannerBuffer();
        return;
      }

      if (e.key.length === 1) {
        scannerBuffer += e.key;
      }

      if (scannerTimer) clearTimeout(scannerTimer);
      scannerTimer = setTimeout(flushScannerBuffer, SCANNER_TIMEOUT_MS);
    });

    // Load Live Recent Taps Feed & Metrics
    async function loadFeed() {
      try {
        const [feedRes, statsRes] = await Promise.all([
          fetch('backend/tap_attendance_api.php?action=recent_taps&limit=15'),
          fetch('backend/tap_attendance_api.php?action=kiosk_summary')
        ]);

        const feedData = await feedRes.json();
        const statsData = await statsRes.json();

        // Update Stats Counters
        if (statsData.status === 'success' && statsData.summary) {
          const s = statsData.summary;
          document.getElementById('statMIn').textContent = s.morning_in_count || 0;
          document.getElementById('statMOut').textContent = s.morning_out_count || 0;
          document.getElementById('statAIn').textContent = s.afternoon_in_count || 0;
          document.getElementById('statAOut').textContent = s.afternoon_out_count || 0;
          if (s.server_date) {
            document.getElementById('todayDatePill').textContent = s.server_date;
          }
        }

        // Render Recent Taps Feed
        const listEl = document.getElementById('recentTapsList');
        if (feedData.status === 'success' && feedData.taps && feedData.taps.length > 0) {
          document.getElementById('feedCountPill').textContent = feedData.taps.length + ' recorded today';
          listEl.innerHTML = feedData.taps.map(tap => {
            const isOut = tap.latest_shift.toLowerCase().includes('out');
            return `
              <div class="feed-item">
                <div class="feed-student">
                  <span class="feed-student-name">${escapeHtml(tap.full_name)}</span>
                  <span class="feed-student-sub">
                    <span class="tag-pill tag-program" style="font-size:0.65rem; padding:0.1rem 0.35rem;">${escapeHtml(tap.course_code)}</span>
                    <span>${escapeHtml(tap.student_number)}</span>
                    <span class="method-badge ${tap.method_type === 'selfie' ? 'selfie' : ''}">${escapeHtml(tap.method)}</span>
                  </span>
                </div>
                <div class="feed-punch">
                  <span class="punch-tag ${isOut ? 'out' : 'in'}">${escapeHtml(tap.latest_shift)}</span>
                  <span class="punch-timestamp">${escapeHtml(tap.latest_time)}</span>
                </div>
              </div>
            `;
          }).join('');
        } else {
          document.getElementById('feedCountPill').textContent = '0 recorded today';
          listEl.innerHTML = `
            <div style="text-align:center; padding: 2.5rem 1rem; color: #64748b; font-size: 0.85rem;">
              No attendance logs recorded today yet.
            </div>
          `;
        }
      } catch (err) {
        console.warn('Feed refresh error:', err);
      }
    }

    // Auto-refresh feed every 12 seconds
    setInterval(loadFeed, 12000);
    loadFeed();
    refocusScanner();
  </script>
</body>
</html>
