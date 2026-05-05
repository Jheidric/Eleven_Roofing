<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireSysAdmin();

$totalUsers = db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalInq   = db()->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$pending    = db()->query("SELECT COUNT(*) FROM inquiries WHERE status='pending'")->fetchColumn();

$critStock  = db()->query("
    SELECT COUNT(*) 
    FROM products 
    WHERE stock_quantity < min_stock
")->fetchColumn();

$liveChats = db()->query("
    SELECT COUNT(*) 
    FROM chat_sessions 
    WHERE status IN('waiting','active')
")->fetchColumn();

$locks = db()->query("
    SELECT setting_key, setting_value
    FROM system_settings
    WHERE setting_key LIKE 'lock_%'
")->fetchAll(PDO::FETCH_KEY_PAIR);

$lockedCount = count(
    array_filter($locks, fn($v) => $v === '1')
);

$recentLogs = db()->query("
    SELECT 
        al.action,
        al.module,
        al.logged_at,
        u.full_name,
        r.role_name
    FROM activity_logs al
    JOIN users u ON al.user_id = u.user_id
    JOIN roles r ON u.role_id = r.role_id
    ORDER BY al.logged_at DESC
    LIMIT 10
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="css/sysadmin.css">
</head>

<body class="dash-body">

<?php include __DIR__.'/partials/sidebar.php'; ?>

<main class="dash-main">

    <div class="topbar">
        <div class="topbar-left">
            <h1>System Admin Dashboard</h1>
            <p>Full system overview — <?= date('l, F j, Y') ?></p>
        </div>

        <div class="topbar-right">
            <?php if($liveChats > 0): ?>
                <a href="../admin/livechat.php" class="notif-btn">
                    🔔 <?= $liveChats ?> Live Chat<?= $liveChats > 1 ? 's' : '' ?>
                    <span class="notif-dot"></span>
                </a>
            <?php endif; ?>

            <a href="backup.php" class="btn btn-primary btn-sm">
                💾 Backup DB
            </a>
        </div>
    </div>


    <div class="stats-grid-5">

        <div class="stat-crd c-info">
            <div class="stat-lbl">Total Users</div>
            <div class="stat-num"><?= $totalUsers ?></div>
        </div>

        <div class="stat-crd c-accent">
            <div class="stat-lbl">Inquiries</div>
            <div class="stat-num"><?= $totalInq ?></div>
            <div class="stat-sub"><?= $pending ?> pending</div>
        </div>

        <div class="stat-crd c-<?= $critStock > 0 ? 'danger' : 'success' ?>">
            <div class="stat-lbl">Stock Alerts</div>
            <div class="stat-num"><?= $critStock ?></div>
        </div>

        <div class="stat-crd c-<?= $lockedCount > 0 ? 'warning' : 'success' ?>">
            <div class="stat-lbl">Locked Features</div>
            <div class="stat-num"><?= $lockedCount ?></div>
        </div>

        <div class="stat-crd c-info">
            <div class="stat-lbl">Live Chats</div>
            <div class="stat-num"><?= $liveChats ?></div>
        </div>

    </div>


    <div class="grid-2eq">

        <div class="panel">

            <div class="panel-header">
                <div class="panel-title">🔒 Feature Lock Status</div>
                <a href="feature_locks.php" class="panel-action">Manage →</a>
            </div>

            <div class="panel-body" style="padding:0">

                <?php
                $features = [
                    'services' => 'Services',
                    'products' => 'Products',
                    'chatbot'  => 'Chatbot Q&A',
                    'about'    => 'About Us',
                    'contact'  => 'Contact Us',
                    'inventory'=> 'Inventory'
                ];

                foreach($features as $k => $lbl):

                    $isLocked = ($locks["lock_$k"] ?? '0') === '1';
                ?>

                    <div style="display:flex;justify-content:space-between;padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">

                        <span><?= $lbl ?></span>

                        <span class="badge <?= $isLocked ? 'badge-critical' : 'badge-resolved' ?>">
                            <?= $isLocked ? '🔒 Locked' : '✅ Unlocked' ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>


        <div class="panel">

            <div class="panel-header">
                <div class="panel-title">📋 Recent Activity Logs</div>
                <a href="activity_logs.php" class="panel-action">View all →</a>
            </div>

            <div class="panel-body">

                <div class="table-wrap">

                    <table class="data-table">

                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Time</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach($recentLogs as $l): ?>

                            <tr>

                                <td><?= h($l['full_name']) ?></td>

                                <td><?= h($l['role_name']) ?></td>

                                <td><?= h($l['action']) ?></td>

                                <td><?= h($l['module']) ?></td>

                                <td><?= fmtDateTime($l['logged_at']) ?></td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>

</body>
</html>