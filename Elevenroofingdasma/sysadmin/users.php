<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireSysAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act=$_POST['action']??''; $uid=(int)$_POST['user_id'];
    if ($act==='toggle'&&$uid!==$user['id']) {
        db()->prepare("UPDATE users SET status=IF(status='active','inactive','active') WHERE user_id=?")->execute([$uid]);
        logActivity($user['id'],"Toggled status for user #$uid",'Users');
    } elseif ($act==='lock'&&$uid!==$user['id']) {
        db()->prepare("UPDATE users SET status='locked' WHERE user_id=?")->execute([$uid]);
        logActivity($user['id'],"Locked account for user #$uid",'Users');
    }
    header('Location: users.php?msg=1'); exit;
}
$users=db()->query("SELECT u.*,r.role_name,r.level FROM users u JOIN roles r ON u.role_id=r.role_id ORDER BY r.level DESC,u.full_name")->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Users — Sysadmin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/sysadmin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>User Management</h1><p>View and manage all system user accounts</p></div></div>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-3">User status updated.</div><?php endif;?>
  <div class="panel"><div class="panel-body"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Contact</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($users as $u):?>
    <tr>
      <td style="color:var(--muted)">#<?=$u['user_id']?></td>
      <td style="font-weight:500"><?=h($u['full_name'])?></td>
      <td style="font-size:.78rem;color:var(--muted)"><?=h($u['email'])?></td>
      <td><span class="badge badge-<?=$u['role_name']==='Owner'?'owner':($u['role_name']==='System Admin'?'progress':($u['role_name']==='Administrator'?'accent':($u['role_name']==='Staff'?'resolved':'inactive')))?>"><?=h($u['role_name'])?></span></td>
      <td style="color:var(--muted);font-size:.78rem"><?=h($u['contact_number']??'—')?></td>
      <td><span class="badge badge-<?=$u['status']==='active'?'resolved':($u['status']==='locked'?'critical':'inactive')?>"><?=ucfirst($u['status'])?></span></td>
      <td style="color:var(--muted);font-size:.78rem"><?=fmtDate($u['created_at'])?></td>
      <td style="display:flex;gap:.35rem;flex-wrap:wrap">
        <?php if($u['user_id']!==$user['id']&&$u['role_name']!=='Owner'):?>
        <form method="POST" style="display:inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-outline btn-sm"><?=$u['status']==='active'?'Deactivate':'Activate'?></button></form>
        <?php if($u['status']!=='locked'):?>
        <form method="POST" style="display:inline" onsubmit="return confirm('Lock this account?')"><input type="hidden" name="action" value="lock"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-danger btn-sm">Lock</button></form>
        <?php endif;?>
        <?php else:?><span style="color:var(--muted);font-size:.78rem"><?=$u['user_id']===$user['id']?'You':'Protected'?></span><?php endif;?>
      </td>
    </tr>
    <?php endforeach;?>
    </tbody>
  </table></div></div></div>
</main></body></html>
