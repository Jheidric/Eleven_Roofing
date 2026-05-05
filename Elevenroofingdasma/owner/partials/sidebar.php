<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../includes/sidebar.php';

$pendingInvReqs = (int) db()->query("
    SELECT COUNT(*) 
    FROM inventory_requests 
    WHERE status='pending'
")->fetchColumn();

$current = basename($_SERVER['PHP_SELF']);

$pending = (int) db()->query("
    SELECT COUNT(*) 
    FROM inquiries 
    WHERE status='pending'
")->fetchColumn();

$liveChats = (int) db()->query("
    SELECT COUNT(*) 
    FROM chat_sessions 
    WHERE status IN('waiting','active')
")->fetchColumn();

$initials = strtoupper(substr($user['name'], 0, 2));

$links = [
    ['section' => 'Main'],
    ['file' => 'index.php', 'icon' => '👑', 'label' => 'Owner Dashboard'],

    ['section' => 'Full Control'],
    ['file' => 'users.php', 'icon' => '👥', 'label' => 'All Users'],
    ['file' => 'permissions.php', 'icon' => '🔐', 'label' => 'Permissions'],

    ['section' => 'System'],
    ['file' => '../sysadmin/feature_locks.php', 'icon' => '🔒', 'label' => 'Feature Locks'],
    ['file' => '../sysadmin/backup.php', 'icon' => '💾', 'label' => 'Backup & Restore'],
    ['file' => '../sysadmin/activity_logs.php', 'icon' => '📋', 'label' => 'Activity Logs'],

    ['section' => 'Content'],
    ['file' => '../admin/about_edit.php', 'icon' => '📖', 'label' => 'Edit About Us'],
    ['file' => '../admin/contact_edit.php', 'icon' => '📍', 'label' => 'Edit Contact Us'],

    ['section' => 'Operations'],
    ['file' => '../admin/inquiries.php', 'icon' => '💬', 'label' => 'Inquiries', 'badge' => 'pending'],
    ['file' => '../admin/services.php', 'icon' => '🏗️', 'label' => 'Services'],
    ['file' => '../admin/products.php', 'icon' => '📦', 'label' => 'Products'],
    ['file' => '../admin/inventory.php', 'icon' => '🗂️', 'label' => 'Inventory Approval'],
    ['file' => '../admin/tools_admin.php', 'icon' => '🔧', 'label' => 'Borrowed Tools'],
    ['file' => '../admin/reports.php', 'icon' => '📄', 'label' => 'Reports'],
];

echo "<aside class='dash-sidebar'>";

echo "<div class='sidebar-head'>
        <div class='nav-logo' style='font-size:1.1rem'>
            Eleven<span style='color:var(--text)'>Roofing</span>
        </div>
        <div class='sidebar-badge' style='color:var(--owner)'>
            Owner Portal
        </div>
      </div>";

echo "<nav class='sidebar-nav'>";

foreach ($links as $item) {

    if (isset($item['section'])) {
        echo "<div class='nav-sec'>{$item['section']}</div>";
        continue;
    }

    $fname  = $item['file'];
    $active = (basename($fname) === $current) ? 'active' : '';
    $badge  = '';

    if (($item['badge'] ?? '') === 'pending' && $pending > 0) {
        $badge = "<span class='nav-badge'>$pending</span>";
    }

    if (($item['badge'] ?? '') === 'live' && $liveChats > 0) {
        $badge = "<span class='nav-badge'>$liveChats</span>";
    }

    if ($fname === '../admin/inventory.php' && $pendingInvReqs > 0) {
        $badge = "<span class='nav-badge'>$pendingInvReqs</span>";
    }

    echo "<a href='$fname' class='nav-lnk $active'>
            <span class='ni'>{$item['icon']}</span>
            {$item['label']}
            $badge
          </a>";
}

echo "</nav>";

echo "<div class='sidebar-foot'>
        <div class='sidebar-user'>
            <div class='user-av av-owner'>$initials</div>
            <div>
                <div class='user-nm'>" . h($user['name']) . "</div>
                <div class='user-rl'>" . h($user['role']) . "</div>
            </div>
        </div>

        <a href='/Elevenroofingdasma/auth/logout.php' class='logout-lnk'>
            ← Sign Out
        </a>
      </div>";

echo "</aside>";
?>