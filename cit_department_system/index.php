<?php
// Automatic router from Apache to CIT Department System (Flask port 5000)
$flask_url = "http://localhost:5000/";

// Check if Flask server is responsive
$is_running = false;
$fp = @fsockopen("127.0.0.1", 5000, $errno, $errstr, 1);
if ($fp) {
    $is_running = true;
    fclose($fp);
}

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
    <title>CIT Department System Launcher</title>
    <meta http-equiv="refresh" content="3;url=http://localhost:5000/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 1.25rem; padding: 2.5rem; text-align: center; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.6); }
        .btn-gold { background: #f59e0b; color: #1e293b; font-weight: 700; border-radius: 9999px; padding: 0.75rem 2rem; border: none; text-decoration: none; display: inline-block; margin-top: 1.25rem; transition: transform 0.2s; }
        .btn-gold:hover { background: #d97706; color: #1e293b; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="mb-3 fw-bold text-white">CIT Department System</h2>
        <p class="text-secondary mb-4">Connecting to the Python Flask application running at <code class="text-warning">http://localhost:5000</code>...</p>
        <a href="http://localhost:5000/" class="btn btn-gold">Launch CIT Department System ↗</a>
        <div class="mt-4 text-muted small">
            Tip: You can also start the system anytime by running <code class="text-info">run_cit_system.bat</code>.
        </div>
    </div>
</body>
</html>
