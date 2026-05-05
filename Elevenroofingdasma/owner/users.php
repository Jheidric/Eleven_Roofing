<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireOwner();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act=$_POST['action']??''; $uid=(int)$_POST['user_id'];
    if ($uid===$user['id']) { header('Location: users.php?err=self'); exit; }
    if ($act==='activate')   { db()->prepare("UPDATE users SET status='active'   WHERE user_id=?")->execute([$uid]); logActivity($user['id'],"Activated user #$uid",'Users'); }
    elseif ($act==='deactivate') { db()->prepare("UPDATE users SET status='inactive' WHERE user_id=?")->execute([$uid]); logActivity($user['id'],"Deactivated user #$uid",'Users'); }
    elseif ($act==='lock')   { db()->prepare("UPDATE users SET status='locked'   WHERE user_id=?")->execute([$uid]); logActivity($user['id'],"Locked user #$uid",'Users'); }
    elseif ($act==='role')   {
        $rid=(int)$_POST['role_id'];
        db()->prepare("UPDATE users SET role_id=? WHERE user_id=?")->execute([$rid,$uid]);
        logActivity($user['id'],"Changed role for user #$uid to role_id=$rid",'Users');
    } elseif ($act==='delete') {
        db()->prepare("DELETE FROM users WHERE user_id=?")->execute([$uid]);
        logActivity($user['id'],"Deleted user #$uid",'Users');
    }
    header('Location: users.php?msg=1'); exit;
}
$users=db()->query("SELECT u.*,r.role_name,r.level FROM users u JOIN roles r ON u.role_id=r.role_id ORDER BY r.level DESC,u.full_name")->fetchAll();
$roles=db()->query("SELECT * FROM roles ORDER BY level DESC")->fetchAll();
$roleColors=['Owner'=>'#9b59b6','System Admin'=>'#4a9fd4','Administrator'=>'#c8a96e','Staff'=>'#4caf72','Customer'=>'#8a897f'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>User Management — Owner</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/owner.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>User Management</h1><p>Full owner access — activate, deactivate, lock, and change roles for any user</p></div></div>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-3">User updated successfully.</div><?php endif;?>
  <?php if(isset($_GET['err'])):?><div class="alert alert-error show mb-3">You cannot modify your own account.</div><?php endif;?>

  <!-- ROLE FILTER -->
  <div style="display:flex;gap:.65rem;margin-bottom:1.25rem;flex-wrap:wrap">
    <a href="users.php" class="btn <?=!isset($_GET['role'])?'btn-primary':'btn-outline'?> btn-sm">All (<?=count($users)?>)</a>
    <?php foreach($roles as $r):
      $cnt=count(array_filter($users,fn($u)=>$u['role_name']===$r['role_name']));?>
    <a href="?role=<?=urlencode($r['role_name'])?>" class="btn <?=(($_GET['role']??'')===$r['role_name'])?'btn-primary':'btn-outline'?> btn-sm"><?=h($r['role_name'])?> (<?=$cnt?>)</a>
    <?php endforeach;?>
  </div>

  <div class="panel"><div class="panel-body">
    <?php $filterRole=$_GET['role']??'';
    foreach($users as $u):
      if($filterRole && $u['role_name']!==$filterRole) continue;
      $c=$roleColors[$u['role_name']]??'#8a897f';
      $isSelf=$u['user_id']===$user['id'];
    ?>
    <div class="user-row">
      <div class="user-info">
        <div class="user-row-av" style="background:<?=$c?>22;color:<?=$c?>"><?=strtoupper(substr($u['full_name'],0,2))?></div>
        <div>
          <div class="user-row-name"><?=h($u['full_name'])?> <?=$isSelf?'<span style="color:var(--muted);font-size:.75rem">(You)</span>':''?></div>
          <div class="user-row-email"><?=h($u['email'])?><?=$u['contact_number']?" · ".h($u['contact_number']):''?></div>
          <div style="margin-top:.25rem;display:flex;gap:.4rem">
            <span class="badge badge-<?=$u['role_name']==='Owner'?'owner':($u['role_name']==='System Admin'?'progress':($u['role_name']==='Administrator'?'accent':($u['role_name']==='Staff'?'resolved':'inactive')))?>"><?=h($u['role_name'])?></span>
            <span class="badge badge-<?=$u['status']==='active'?'resolved':($u['status']==='locked'?'critical':'inactive')?>"><?=ucfirst($u['status'])?></span>
          </div>
        </div>
      </div>
      <?php if(!$isSelf):?>
      <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap">
        <!-- Change Role -->
        <form method="POST" style="display:inline">
          <input type="hidden" name="action" value="role"><input type="hidden" name="user_id" value="<?=$u['user_id']?>">
          <select name="role_id" class="form-control" style="padding:.38rem .7rem;font-size:.79rem;width:auto" onchange="this.form.submit()">
            <?php foreach($roles as $r):?><option value="<?=$r['role_id']?>" <?=$u['role_id']==$r['role_id']?'selected':''?>><?=h($r['role_name'])?></option><?php endforeach;?>
          </select>
        </form>
        <!-- Status Actions -->
        <?php if($u['status']==='active'):?>
        <form method="POST" style="display:inline"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-warning btn-sm">Deactivate</button></form>
        <form method="POST" style="display:inline" onsubmit="return confirm('Lock this account?')"><input type="hidden" name="action" value="lock"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-danger btn-sm">🔒 Lock</button></form>
        <?php elseif($u['status']==='inactive'):?>
        <form method="POST" style="display:inline"><input type="hidden" name="action" value="activate"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-success btn-sm">Activate</button></form>
        <?php elseif($u['status']==='locked'):?>
        <form method="POST" style="display:inline"><input type="hidden" name="action" value="activate"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-success btn-sm">Unlock</button></form>
        <?php endif;?>
        <?php if($u['role_name']==='Customer'):?>
        <form method="POST" style="display:inline" onsubmit="return confirm('PERMANENTLY delete this user?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?=$u['user_id']?>"><button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
        <?php endif;?>
      </div>
      <?php else:?><span style="color:var(--muted);font-size:.8rem">Cannot modify your own account</span><?php endif;?>
    </div>
    <?php endforeach;?>
  </div></div>
</main></body></html>
