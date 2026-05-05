<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../includes/sidebar.php';

$pendingInvReqs = (int) db()->query("
    SELECT COUNT(*) 
    FROM inventory_requests 
    WHERE status='pending'
")->fetchColumn();

$links = [
    ['section'=>'Main'],
    ['file'=>'index.php','icon'=>'📊','label'=>'Dashboard'],

    ['section'=>'Manage'],
    ['file'=>'inquiries.php','icon'=>'💬','label'=>'Inquiries','badge'=>'pending'],
    ['file'=>'services.php','icon'=>'🏗️','label'=>'Services'],
    ['file'=>'products.php','icon'=>'📦','label'=>'Products'],
    ['file'=>'inventory.php','icon'=>'🗂️','label'=>'Inventory Approval','badge'=>$pendingInvReqs > 0 ? 'inv_req' : ''],
    ['file'=>'tools_admin.php','icon'=>'🔧','label'=>'Borrowed Tools'],

    ['section'=>'Chat'],
    ['file'=>'livechat.php','icon'=>'🟢','label'=>'Live Chats','badge'=>'live'],
    ['file'=>'chatbot.php','icon'=>'🤖','label'=>'Chatbot Q&A'],

    ['section'=>'Content'],
    ['file'=>'about_edit.php','icon'=>'📖','label'=>'Edit About Us'],
    ['file'=>'contact_edit.php','icon'=>'📍','label'=>'Edit Contact Us'],

    ['section'=>'Reports'],
    ['file'=>'reports.php','icon'=>'📄','label'=>'Reports'],
    ['file'=>'contact_msgs.php','icon'=>'✉️','label'=>'Contact Messages'],
];

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

echo "<aside class='dash-sidebar'>";

echo "<div class='sidebar-head'>
        <div class='nav-logo' style='font-size:1.1rem'>
            Eleven<span style='color:var(--text)'>Roofing</span><br>
            <small style='font-size:.55rem;letter-spacing:1px;color:var(--muted)'>
                DASMA
            </small>
        </div>
        <div class='sidebar-badge'>Admin Portal</div>
      </div>";

echo "<nav class='sidebar-nav'>";

foreach ($links as $item) {

    if (isset($item['section'])) {
        echo "<div class='nav-sec'>{$item['section']}</div>";
        continue;
    }

    $active = (
        $current === $item['file'] ||
        basename($item['file']) === $current
    ) ? 'active' : '';

    $badge = '';

    if (($item['badge'] ?? '') === 'pending' && $pending > 0) {
        $badge = "<span class='nav-badge'>$pending</span>";
    }

    if (($item['badge'] ?? '') === 'live' && $liveChats > 0) {
        $badge = "<span class='nav-badge'>$liveChats</span>";
    }

    if (($item['badge'] ?? '') === 'inv_req') {
        $badge = "<span class='nav-badge'>$pendingInvReqs</span>";
    }

    echo "<a href='{$item['file']}' class='nav-lnk $active'>
            <span class='ni'>{$item['icon']}</span>
            {$item['label']}
            $badge
          </a>";
}

echo "</nav>";

echo "<div class='sidebar-foot'>
        <div class='sidebar-user'>
            <div class='user-av'>$initials</div>
            <div>
                <div class='user-nm'>" . htmlspecialchars($user['name']) . "</div>
                <div class='user-rl'>" . htmlspecialchars($user['role']) . "</div>
            </div>
        </div>

        <a href='/Elevenroofingdasma/auth/logout.php' class='logout-lnk'>
            ← Sign Out
        </a>
      </div>";

echo "</aside>";
?>