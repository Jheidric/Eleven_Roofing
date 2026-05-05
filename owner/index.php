<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireOwner();
$totalUsers=db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers=db()->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$lockedUsers=db()->query("SELECT COUNT(*) FROM users WHERE status='locked'")->fetchColumn();
$totalInq=db()->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$pending=db()->query("SELECT COUNT(*) FROM inquiries WHERE status='pending'")->fetchColumn();
$critStock=db()->query("SELECT COUNT(*) FROM products WHERE stock_quantity < min_stock")->fetchColumn();
$liveChats=db()->query("SELECT COUNT(*) FROM chat_sessions WHERE status IN('waiting','active')")->fetchColumn();
$locks=db()->query("SELECT setting_key,setting_value FROM system_settings WHERE setting_key LIKE 'lock_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
$lockedCount=count(array_filter($locks,fn($v)=>$v==='1'));
$backupCount=db()->query("SELECT COUNT(*) FROM backup_logs")->fetchColumn();
$recentLogs=db()->query("SELECT al.*,u.full_name,r.role_name FROM activity_logs al JOIN users u ON al.user_id=u.user_id JOIN roles r ON u.role_id=r.role_id ORDER BY al.logged_at DESC LIMIT 8")->fetchAll();
$usersByRole=db()->query("SELECT r.role_name,COUNT(u.user_id) as cnt FROM roles r LEFT JOIN users u ON r.role_id=u.role_id GROUP BY r.role_id ORDER BY r.level DESC")->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Owner Dashboard — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/owner.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="owner-banner">
    <div class="owner-crown">👑</div>
    <div>
      <h2>Welcome, <?=h(explode(' ',$user['name'])[0])?> — Owner Access</h2>
      <p>You have full unrestricted access to all system features, user management, and settings. <?=date('l, F j, Y')?></p>
    </div>
  </div>
  <div class="stats-grid-5">
    <div class="stat-crd c-owner"><div class="stat-lbl">Total Users</div><div class="stat-num"><?=$totalUsers?></div><div class="stat-sub"><?=$activeUsers?> active · <?=$lockedUsers?> locked</div><div class="stat-icon">👥</div></div>
    <div class="stat-crd c-accent"><div class="stat-lbl">Inquiries</div><div class="stat-num"><?=$totalInq?></div><div class="stat-sub"><?=$pending?> pending</div><div class="stat-icon">💬</div></div>
    <div class="stat-crd c-<?=$critStock>0?'danger':'success'?>"><div class="stat-lbl">Stock Alerts</div><div class="stat-num"><?=$critStock?></div><div class="stat-sub">Low/critical</div><div class="stat-icon">📦</div></div>
    <div class="stat-crd c-<?=$lockedCount>0?'warning':'success'?>"><div class="stat-lbl">Locked Features</div><div class="stat-num"><?=$lockedCount?></div><div class="stat-sub">Of 6 features</div><div class="stat-icon">🔒</div></div>
    <div class="stat-crd c-info"><div class="stat-lbl">DB Backups</div><div class="stat-num"><?=$backupCount?></div><div class="stat-sub">Total backups made</div><div class="stat-icon">💾</div></div>
  </div>
  <div class="grid-2eq">
    <!-- USERS BY ROLE -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">👥 Users by Role</div><a href="users.php" class="panel-action">Manage all →</a></div>
      <div class="panel-body" style="padding:0">
        <?php $roleColors=['Owner'=>'var(--owner)','System Admin'=>'var(--info)','Administrator'=>'var(--accent)','Staff'=>'var(--success)','Customer'=>'var(--muted)'];
        foreach($usersByRole as $r): $c=$roleColors[$r['role_name']]??'var(--muted)';?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;gap:.65rem">
            <div style="width:8px;height:8px;border-radius:50%;background:<?=$c?>"></div>
            <span style="font-size:.875rem"><?=h($r['role_name'])?></span>
          </div>
          <div style="display:flex;align-items:center;gap:.75rem">
            <span style="font-family:var(--serif);font-size:1.1rem;color:<?=$c?>"><?=$r['cnt']?></span>
          </div>
        </div>
        <?php endforeach;?>
      </div>
    </div>
    <!-- SYSTEM STATUS -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">⚙️ System Status</div></div>
      <div class="panel-body" style="padding:0">
        <?php $features=['services'=>'Services','products'=>'Products','chatbot'=>'Chatbot','about'=>'About Us','contact'=>'Contact Us','inventory'=>'Inventory'];
        foreach($features as $k=>$lbl): $locked=($locks["lock_$k"]??'0')==='1';?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.7rem 1.25rem;border-bottom:1px solid var(--border)">
          <span style="font-size:.875rem"><?=$lbl?></span>
<span class="badge <?= $locked ? 'badge-critical' : 'badge-resolved' ?>"><?= $locked ? '🔒 Locked' : '✅ Open' ?></span>        </div>
        <?php endforeach;?>
        <div style="padding:.7rem 1.25rem"><a href="../sysadmin/feature_locks.php" class="btn btn-outline btn-sm w-full" style="text-align:center;display:block">Manage Locks</a></div>
      </div>
    </div>
  </div>
  <!-- RECENT ACTIVITY -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">📋 Recent Activity</div><a href="../sysadmin/activity_logs.php" class="panel-action">Full log →</a></div>
    <div class="panel-body"><div class="table-wrap"><table class="data-table">
      <thead><tr><th>User</th><th>Role</th><th>Action</th><th>Module</th><th>Time</th></tr></thead>
      <tbody>
      <?php foreach($recentLogs as $l):?>
      <tr>
        <td><?=h($l['full_name'])?></td>
        <td><span class="badge badge-<?=$l['role_name']==='Owner'?'owner':($l['role_name']==='System Admin'?'progress':($l['role_name']==='Administrator'?'accent':'resolved'))?>"><?=h($l['role_name'])?></span></td>
        <td style="font-size:.835rem"><?=h($l['action'])?></td>
        <td style="color:var(--muted)"><?=h($l['module'])?></td>
        <td style="color:var(--muted);font-size:.78rem"><?=fmtDateTime($l['logged_at'])?></td>
      </tr>
      <?php endforeach;?>
      </tbody>
    </table></div></div>
  </div>
</main></body></html>
