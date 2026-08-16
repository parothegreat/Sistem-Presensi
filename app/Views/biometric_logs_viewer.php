<?php
// Get biometric logs data
$db = \Config\Database::connect();

// Get unprocessed logs
$unprocessed = $db->table('biometric_logs')
    ->where('processed', 0)
    ->orderBy('created_at', 'DESC')
    ->get()
    ->getResultArray();

// Get processed logs with errors
$errors = $db->table('biometric_logs')
    ->where('processed', 1)
    ->where('process_error IS NOT NULL', null, false)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get()
    ->getResultArray();

// Get recent processed logs
$processed = $db->table('biometric_logs')
    ->where('processed', 1)
    ->where('process_error IS NULL', null, false)
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->get()
    ->getResultArray();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Biometric Logs Checker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0 30px 0;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-box h3 {
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .stat-box .value {
            font-size: 36px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-unprocessed {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-fingerprint {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-checkin {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-checkout {
            background: #cce5ff;
            color: #004085;
        }

        .empty {
            color: #999;
            font-style: italic;
            padding: 20px;
            text-align: center;
        }

        .tips {
            margin-top: 30px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .tips h3 {
            color: #004085;
            margin-bottom: 10px;
        }

        .tips ul {
            margin-left: 20px;
            color: #004085;
        }

        .tips li {
            margin: 5px 0;
        }

        .refresh-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .refresh-btn:hover {
            background: #764ba2;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📊 Biometric Logs Checker</h1>

        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Data</button>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-box">
                <h3>Unprocessed Logs</h3>
                <div class="value"><?= count($unprocessed) ?></div>
            </div>
            <div class="stat-box">
                <h3>Processing Errors</h3>
                <div class="value"><?= count($errors) ?></div>
            </div>
            <div class="stat-box">
                <h3>Successfully Processed</h3>
                <div class="value"><?= count($processed) ?></div>
            </div>
        </div>

        <!-- Unprocessed Logs -->
        <h2>⏳ Unprocessed Logs (Ready to Process)</h2>
        <?php if (empty($unprocessed)): ?>
            <p class="empty">✓ No unprocessed logs - everything is processed!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Device</th>
                        <th>User ID (NIS)</th>
                        <th>Timestamp</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unprocessed as $log): ?>
                        <tr>
                            <td><strong><?= $log['id'] ?></strong></td>
                            <td><?= $log['device_id'] ?></td>
                            <td><strong><?= $log['user_id'] ?></strong></td>
                            <td><?= $log['timestamp'] ?></td>
                            <td><span class="badge badge-fingerprint"><?= $log['biometric_type'] ?></span></td>
                            <td><span class="badge badge-checkin"><?= $log['status'] ?></span></td>
                            <td><?= date('d M H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Processing Errors -->
        <h2>❌ Processing Errors</h2>
        <?php if (empty($errors)): ?>
            <p class="empty">✓ No processing errors!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID (NIS)</th>
                        <th>Timestamp</th>
                        <th>Error Message</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($errors as $error): ?>
                        <tr>
                            <td><strong><?= $error['id'] ?></strong></td>
                            <td><?= $error['user_id'] ?></td>
                            <td><?= $error['timestamp'] ?></td>
                            <td><span class="status-error"><?= $error['process_error'] ?></span></td>
                            <td><?= date('d M H:i', strtotime($error['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Successfully Processed -->
        <h2>✅ Successfully Processed (Last 20)</h2>
        <?php if (empty($processed)): ?>
            <p class="empty">No processed logs yet</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID (NIS)</th>
                        <th>Timestamp</th>
                        <th>Attendance ID</th>
                        <th>Type</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($processed as $log): ?>
                        <tr>
                            <td><strong><?= $log['id'] ?></strong></td>
                            <td><?= $log['user_id'] ?></td>
                            <td><?= $log['timestamp'] ?></td>
                            <td><?= $log['attendance_id'] ? '<strong>' . $log['attendance_id'] . '</strong>' : '-' ?></td>
                            <td><span class="badge badge-fingerprint"><?= $log['biometric_type'] ?></span></td>
                            <td><?= date('d M H:i', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="tips">
            <h3>💡 Quick Tips</h3>
            <ul>
                <li><strong>Run cronjob:</strong> <code>php spark attendance:process-biometric</code></li>
                <li><strong>Dry-run:</strong> <code>php spark attendance:process-biometric --dry-run</code></li>
                <li>This page auto-refreshes data, click "Refresh Data" button for manual refresh</li>
                <li>All logs are stored for audit purposes</li>
                <li>Check error messages for troubleshooting</li>
            </ul>
        </div>
    </div>
</body>

</html>