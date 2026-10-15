<?php
// Automatic router and self-healing background starter for CIT Department System (Flask port 5000)
$flask_url = "http://localhost:5000/";

function check_flask_alive($host = "127.0.0.1", $port = 5000, $timeout = 0.5) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

$is_running = check_flask_alive();

// If Flask is not running, attempt silent background start
if (!$is_running) {
    $vbs_path = __DIR__ . DIRECTORY_SEPARATOR . "start_cit_background.vbs";
    if (file_exists($vbs_path)) {
        @pclose(@popen("wscript.exe \"" . $vbs_path . "\"", "r"));
    }

    // Wait up to 2 seconds for server to boot
    for ($i = 0; $i < 3; $i++) {
        usleep(650000); // 650ms
        if (check_flask_alive()) {
            $is_running = true;
            break;
        }
    }
}

// If server is responsive, redirect immediately!
if ($is_running) {
    header("Location: " . $flask_url);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to CIT Department System...</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-dark: #071b30;
            --navy-surface: #0f2c4e;
            --amber-gold: #f59e0b;
            --gold-light: #fbbf24;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: radial-gradient(circle at 50% 30%, #0c2340 0%, var(--navy-dark) 100%);
            color: var(--text-light);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .card {
            background: linear-gradient(145deg, rgba(15, 44, 78, 0.95), rgba(7, 27, 48, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(245, 158, 11, 0.1);
        }
        .spinner-ring {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border: 4px solid rgba(245, 158, 11, 0.2);
            border-top: 4px solid var(--amber-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            font-size: 1.35rem;
            font-weight: 800;
            margin-bottom: 0.6rem;
            color: #ffffff;
        }
        p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: var(--gold-light);
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--amber-gold);
            border-radius: 50%;
            animation: pulse 1.2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
        .btn-action {
            display: inline-block;
            background: linear-gradient(135deg, var(--amber-gold), #d97706);
            color: #040d1a;
            font-weight: 800;
            font-size: 0.9rem;
            padding: 0.75rem 1.8rem;
            border-radius: 9999px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
        }
        .tip-box {
            margin-top: 1.5rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 8px;
            font-size: 0.78rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner-ring"></div>
        <div class="status-badge">
            <span class="status-dot"></span>
            <span id="statusText">Starting Python Server...</span>
        </div>
        <h2>CIT Department System</h2>
        <p>Initializing server process on port 5000. You will be redirected automatically in a moment.</p>
        <a href="http://localhost:5000/" id="directLink" class="btn-action">Open Now ↗</a>
        <div class="tip-box">
            Tip: You can also keep the server running in a terminal by running <code>run_cit_system.bat</code>.
        </div>
    </div>

    <script>
        const targetUrl = 'http://localhost:5000/';
        let attempts = 0;
        const maxAttempts = 15;

        async function pollServer() {
            attempts++;
            try {
                // Try to reach port 5000
                await fetch(targetUrl, { mode: 'no-cors', cache: 'no-store' });
                // If fetch does not throw, the port is listening!
                document.getElementById('statusText').textContent = 'Ready! Redirecting...';
                window.location.replace(targetUrl);
                return;
            } catch (e) {
                // Not ready yet, retry
                if (attempts < maxAttempts) {
                    setTimeout(pollServer, 800);
                } else {
                    document.getElementById('statusText').textContent = 'Server starting... click below';
                }
            }
        }

        // Start polling immediately
        setTimeout(pollServer, 800);
    </script>
</body>
</html>
