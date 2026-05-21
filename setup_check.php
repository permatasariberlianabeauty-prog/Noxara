<?php
/**
 * NOXARA Server Setup Checker
 * Protected by token - access via ?token=YOUR_SETUP_TOKEN
 */

// Load config for token and database
$configPath = __DIR__ . '/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Token protection
$setupToken = defined('SETUP_TOKEN') ? SETUP_TOKEN : 'noxara_setup_2024';
if (!isset($_GET['token']) || $_GET['token'] !== $setupToken) {
    http_response_code(403);
    die('Access denied. Invalid or missing token.');
}

header('Content-Type: text/html; charset=utf-8');

$checks = [];

// Check PHP version
$phpVersion = phpversion();
$checks[] = [
    'name' => 'PHP Version >= 8.2',
    'status' => version_compare($phpVersion, '8.2.0', '>='),
    'detail' => 'Current: ' . $phpVersion
];

// Check MySQLi extension
$checks[] = [
    'name' => 'MySQLi Extension',
    'status' => extension_loaded('mysqli'),
    'detail' => extension_loaded('mysqli') ? 'Loaded' : 'Not loaded'
];

// Check cURL extension
$checks[] = [
    'name' => 'cURL Extension',
    'status' => extension_loaded('curl'),
    'detail' => extension_loaded('curl') ? 'Loaded' : 'Not loaded'
];

// Check JSON extension
$checks[] = [
    'name' => 'JSON Extension',
    'status' => extension_loaded('json'),
    'detail' => extension_loaded('json') ? 'Loaded' : 'Not loaded'
];

// Check mbstring extension
$checks[] = [
    'name' => 'mbstring Extension',
    'status' => extension_loaded('mbstring'),
    'detail' => extension_loaded('mbstring') ? 'Loaded' : 'Not loaded'
];

// Check database connection
$dbStatus = false;
$dbDetail = 'Not configured';
if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        $dbDetail = 'Connection failed: ' . $conn->connect_error;
    } else {
        $dbStatus = true;
        $dbDetail = 'Connected to ' . DB_NAME;
        $conn->close();
    }
}
$checks[] = [
    'name' => 'Database Connection',
    'status' => $dbStatus,
    'detail' => $dbDetail
];

// Check writable directories
$writableDirs = ['uploads', 'logs', 'backups'];
foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    $checks[] = [
        'name' => "Writable: /$dir/",
        'status' => $writable,
        'detail' => !$exists ? 'Directory does not exist' : ($writable ? 'Writable' : 'Not writable')
    ];
}

// Check required database tables
$requiredTables = [
    'users', 'packages', 'transactions', 'mining_sessions',
    'daily_bonus', 'referrals', 'withdrawals', 'notifications',
    'settings', 'vip_levels'
];

if ($dbStatus) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $result = $conn->query("SHOW TABLES");
    $existingTables = [];
    while ($row = $result->fetch_row()) {
        $existingTables[] = $row[0];
    }
    foreach ($requiredTables as $table) {
        $exists = in_array($table, $existingTables);
        $checks[] = [
            'name' => "Table: $table",
            'status' => $exists,
            'detail' => $exists ? 'Exists' : 'Missing'
        ];
    }
    $conn->close();
} else {
    foreach ($requiredTables as $table) {
        $checks[] = [
            'name' => "Table: $table",
            'status' => false,
            'detail' => 'Cannot check - no DB connection'
        ];
    }
}

$totalChecks = count($checks);
$passed = count(array_filter($checks, fn($c) => $c['status']));
$failed = $totalChecks - $passed;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOXARA - Setup Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0A0E1A;
            color: #E0E0E0;
            padding: 40px 20px;
            min-height: 100vh;
        }
        .container { max-width: 800px; margin: 0 auto; }
        h1 {
            font-size: 28px;
            background: linear-gradient(135deg, #00D4FF, #7B2FFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin: 20px 0 30px;
        }
        .summary-box {
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 18px;
        }
        .summary-pass {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid #00D4FF;
            color: #00D4FF;
        }
        .summary-fail {
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid #FF4757;
            color: #FF4757;
        }
        .check-list { list-style: none; }
        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .check-item:hover { background: rgba(255,255,255,0.02); }
        .check-name { font-weight: 500; }
        .check-detail { color: #888; font-size: 13px; }
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-pass { background: rgba(0,212,255,0.15); color: #00D4FF; }
        .badge-fail { background: rgba(255,71,87,0.15); color: #FF4757; }
        .warning {
            margin-top: 30px;
            padding: 15px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid #FFD700;
            border-radius: 8px;
            color: #FFD700;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>NOXARA Setup Check</h1>
        <p style="color:#888;">Server environment verification</p>

        <div class="summary">
            <div class="summary-box summary-pass">PASS: <?= $passed ?></div>
            <div class="summary-box summary-fail">FAIL: <?= $failed ?></div>
        </div>

        <ul class="check-list">
            <?php foreach ($checks as $check): ?>
            <li class="check-item">
                <div>
                    <div class="check-name"><?= htmlspecialchars($check['name']) ?></div>
                    <div class="check-detail"><?= htmlspecialchars($check['detail']) ?></div>
                </div>
                <span class="badge <?= $check['status'] ? 'badge-pass' : 'badge-fail' ?>">
                    <?= $check['status'] ? 'PASS' : 'FAIL' ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="warning">
            Delete this file after setup is complete. Do not leave it accessible in production.
        </div>
    </div>
</body>
</html>
