<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireSysAdmin();
$filter=$_GET['module']??'all'; $search=trim($_GET['q']??'');
$where="WHERE 1=1";
if($filter!=='all') $where.=" AND al.module=".db()->quote($filter);
if($search) $where.=" AND (u.full_name LIKE ".db()->quote("%$search%")." OR al.action LIKE ".db()->quote("%$search%").")";
$logs=db()->query("SELECT al.*,u.full_name,r.role_name FROM activity_logs al JOIN users u ON al.user_id=u.user_id JOIN roles r ON u.role_id=r.role_id $where ORDER BY al.logged_at DESC LIMIT 100")->fetchAll();
$modules=db()->query("SELECT DISTINCT module FROM activity_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Activity Logs — Sysadmin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/sysadmin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Activity Logs</h1><p>Full audit trail of all system actions</p></div></div>
  <div class="panel mb-3"><div class="panel-body">
    <form method="GET" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
      <input type="text" name="q" class="form-control" style="max-width:250px" placeholder="Search user or action..." value="<?=h($search)?>">
      <select name="module" class="form-control" style="width:auto;padding:.45rem .85rem" onchange="this.form.submit()">
        <option value="all" <?=$filter==='all'?'selected':''?>>All Modules</option>
        <?php foreach($modules as $m):?><option value="<?=h($m)?>" <?=$filter===$m?'selected':''?>><?=h($m)?></option><?php endforeach;?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="activity_logs.php" class="btn btn-outline btn-sm">Clear</a>
    </form>
  </div></div>
  <div class="panel"><div class="panel-body"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Time</th><th>User</th><th>Role</th><th>Action</th><th>Module</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach($logs as $l):?>
    <tr>
      <td style="color:var(--muted);font-size:.78rem;white-space:nowrap"><?=fmtDateTime($l['logged_at'])?></td>
      <td style="font-weight:500"><?=h($l['full_name'])?></td>
      <td><span class="badge badge-<?=$l['role_name']==='Owner'?'owner':($l['role_name']==='System Admin'?'progress':($l['role_name']==='Administrator'?'accent':'resolved'))?>"><?=h($l['role_name'])?></span></td>
      <td style="font-size:.835rem"><?=h($l['action'])?></td>
      <td style="color:var(--muted);font-size:.8rem"><?=h($l['module'])?></td>
      <td style="color:var(--muted);font-size:.75rem"><?=h($l['ip_address']??'')?></td>
    </tr>
    <?php endforeach;?>
    <?php if(!$logs):?><tr><td colspan="6" class="text-center" style="color:var(--muted);padding:2rem">No logs found</td></tr><?php endif;?>
    </tbody>
  </table></div></div></div>
</main></body></html>
