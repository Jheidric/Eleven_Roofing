<?php
// Shared sidebar renderer
function renderSidebar(array $user, array $links, string $portalName, string $avClass = ''): void {
    $pending   = db()->query("SELECT COUNT(*) FROM inquiries WHERE status='pending'")->fetchColumn();
    $liveChats = db()->query("SELECT COUNT(*) FROM chat_sessions WHERE status IN('waiting','active')")->fetchColumn();
    $current   = basename($_SERVER['PHP_SELF']);
    $initials  = strtoupper(substr($user['name'],0,2));
    echo "<aside class='dash-sidebar'>";
    echo "<div class='sidebar-head'><div class='nav-logo' style='font-size:1.15rem'>ElevenRoofing<span style='color:var(--text)'>Dasma</span></div><div class='sidebar-badge'>$portalName</div></div>";
    echo "<nav class='sidebar-nav'>";
    foreach ($links as $item) {
        if (isset($item['section'])) {
            echo "<div class='nav-sec'>{$item['section']}</div>";
        } else {
            $active = ($current === $item['file']) ? 'active' : '';
            $badge  = '';
            if (($item['badge'] ?? '') === 'pending' && $pending > 0) $badge = "<span class='nav-badge'>$pending</span>";
            if (($item['badge'] ?? '') === 'live' && $liveChats > 0) $badge = "<span class='nav-badge'>$liveChats</span>";
            echo "<a href='{$item['file']}' class='nav-lnk $active'><span class='ni'>{$item['icon']}</span>{$item['label']}$badge</a>";
        }
    }
    echo "</nav>";
echo "<div class='sidebar-foot'>
        <div class='sidebar-user'>
            <div class='user-av $avClass'>$initials</div>
            <div>
                <div class='user-nm'>".htmlspecialchars($user['name'])."</div>
                <div class='user-rl'>".htmlspecialchars($user['role'])."</div>
            </div>
        </div>
        <a href='../auth/logout.php' class='logout-lnk'>← Sign Out</a>
      </div>";
          echo "</aside>";
}
