<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireStaff();

$myBorrows = db()->prepare("
    SELECT bt.*, t.tool_name
    FROM borrowed_tools bt
    JOIN tools t ON bt.tool_id = t.tool_id
    WHERE bt.borrowed_by = ?
    AND bt.status = 'borrowed'
    ORDER BY bt.borrow_date DESC
");
$myBorrows->execute([$user['name']]);
$myBorrows = $myBorrows->fetchAll();

$myPendingReqs = db()->prepare("
    SELECT COUNT(*)
    FROM inventory_requests
    WHERE requested_by = ?
    AND status = 'pending'
");
$myPendingReqs->execute([$user['id']]);
$myPendingReqs = (int)$myPendingReqs->fetchColumn();

$myApprovedToday = db()->prepare("
    SELECT COUNT(*)
    FROM inventory_requests
    WHERE requested_by = ?
    AND status = 'approved'
    AND DATE(reviewed_at) = CURDATE()
");
$myApprovedToday->execute([$user['id']]);
$myApprovedToday = (int)$myApprovedToday->fetchColumn();

$pendingInq = db()->query("
    SELECT COUNT(*)
    FROM inquiries
    WHERE status = 'pending'
")->fetchColumn();

$liveChats = db()->query("
    SELECT COUNT(*)
    FROM chat_sessions
    WHERE status IN ('waiting','active')
")->fetchColumn();

$critStock = db()->query("
    SELECT COUNT(*)
    FROM products
    WHERE stock_quantity < min_stock
")->fetchColumn();

$recentInq = db()->query("
    SELECT *
    FROM inquiries
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>Staff Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="css/staff.css">
</head>

<body class="dash-body">

<?php include __DIR__.'/partials/sidebar.php'; ?>

<main class="dash-main">

    <div class="staff-banner">
        <div class="staff-icon">👷</div>

        <div>
            <h2>Staff Dashboard</h2>
            <p>
                Welcome back,
                <?= h(explode(' ', $user['name'])[0]) ?>
                —
                <?= date('l, F j, Y') ?>
            </p>
        </div>
    </div>


    <!-- STATS -->
    <div class="stats-grid mb-3">

        <div class="stat-crd c-accent">
            <div class="stat-lbl">Pending Inquiries</div>
            <div class="stat-num"><?= $pendingInq ?></div>
            <div class="stat-sub">Need attention</div>
            <div class="stat-icon">💬</div>
        </div>

        <div class="stat-crd c-<?= $liveChats > 0 ? 'warning' : 'success' ?>">
            <div class="stat-lbl">Live Chats</div>
            <div class="stat-num"><?= $liveChats ?></div>
            <div class="stat-sub">Waiting / Active</div>
            <div class="stat-icon">🟢</div>
        </div>

        <div class="stat-crd c-<?= $myPendingReqs > 0 ? 'warning' : 'success' ?>">
            <div class="stat-lbl">My Stock Requests</div>
            <div class="stat-num"><?= $myPendingReqs ?></div>
            <div class="stat-sub">Awaiting admin approval</div>
            <div class="stat-icon">📦</div>
        </div>

        <div class="stat-crd c-info">
            <div class="stat-lbl">My Borrowed Tools</div>
            <div class="stat-num"><?= count($myBorrows) ?></div>
            <div class="stat-sub">Currently out</div>
            <div class="stat-icon">🔧</div>
        </div>

    </div>


    <?php if ($liveChats > 0): ?>
        <div class="alert alert-warning show mb-3">

            🔔
            <strong>
                <?= $liveChats ?> customer<?= $liveChats > 1 ? 's' : '' ?>
                waiting for live chat support!
            </strong>

            <a href="/Elevenroofingdasma/admin/livechat.php"
               style="color:var(--warning);text-decoration:underline;margin-left:.5rem">
                Join now →
            </a>

        </div>
    <?php endif; ?>


    <?php if ($myApprovedToday > 0): ?>
        <div class="alert alert-success show mb-3">

            ✅
            <?= $myApprovedToday ?> of your stock request<?= $myApprovedToday > 1 ? 's' : '' ?>
            were approved today!

        </div>
    <?php endif; ?>


    <div class="grid-2eq">

        <!-- MY BORROWED TOOLS -->
        <div class="panel">

            <div class="panel-header">
                <div class="panel-title">🔧 My Borrowed Tools</div>
                <a href="tools.php" class="panel-action">Manage →</a>
            </div>

            <div class="panel-body">

                <?php if ($myBorrows): ?>

                    <?php foreach ($myBorrows as $b):

                        $overdue =
                            $b['expected_return'] &&
                            strtotime($b['expected_return']) < time();

                    ?>

                        <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--border)">

                            <div>

                                <div style="font-size:.875rem;font-weight:500">
                                    <?= h($b['tool_name']) ?>
                                    ×
                                    <?= $b['quantity'] ?>
                                </div>

                                <div style="font-size:.75rem;color:var(--muted)">

                                    Borrowed:
                                    <?= $b['borrow_date'] ?>

                                    · Due:

                                    <span style="color:<?= $overdue ? 'var(--danger)' : 'inherit' ?>">
                                        <?= $b['expected_return'] ?? 'N/A' ?>
                                    </span>

                                </div>

                            </div>

                            <span class="badge <?= $overdue ? 'badge-critical' : 'badge-borrowed' ?>">
                                <?= $overdue ? 'OVERDUE' : 'Borrowed' ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div style="text-align:center;padding:2rem;color:var(--muted);font-size:.845rem">

                        No tools currently borrowed.

                        <a href="tools.php" style="color:var(--accent)">
                            Borrow a tool →
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- RECENT INQUIRIES -->
        <div class="panel">

            <div class="panel-header">

                <div class="panel-title">
                    💬 Recent Inquiries
                </div>

                <a href="/Elevenroofingdasma/admin/inquiries.php"
                   class="panel-action">
                    View all →
                </a>

            </div>

            <div class="panel-body" style="padding:0">

                <?php foreach ($recentInq as $i): ?>

                    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border)">

                        <div style="display:flex;justify-content:space-between">

                            <div>

                                <div style="font-size:.845rem;font-weight:500">
                                    <?= h(mb_strimwidth($i['subject'],0,35,'...')) ?>
                                </div>

                                <div style="font-size:.75rem;color:var(--muted)">

                                    <?= h($i['first_name'].' '.$i['last_name']) ?>

                                    ·

                                    <?= fmtDate($i['created_at']) ?>

                                </div>

                            </div>

                            <span class="badge badge-<?= $i['status']==='in_progress' ? 'progress' : $i['status'] ?>">
                                <?= fmtStatus($i['status']) ?>
                            </span>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </div>


    <!-- QUICK ACTIONS -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1.5rem">

        <a href="request_stock.php">
            📦 Request Stock Change
        </a>

        <a href="tools.php">
            🔧 Borrow Tools
        </a>

        <a href="/Elevenroofingdasma/admin/livechat.php">
            🟢 Live Chat Support
        </a>

    </div>

</main>

</body>
</html>