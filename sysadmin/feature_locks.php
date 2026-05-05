<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireSysAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $features=['services','products','chatbot','about','contact','inventory'];
    foreach($features as $f) {
        $val=isset($_POST['lock_'.$f])?'1':'0';
        setSetting('lock_'.$f,$val,$user['id']);
    }
    logActivity($user['id'],'Updated feature locks','System');
    header('Location: feature_locks.php?msg=1'); exit;
}
$locks=[];
foreach(['services','products','chatbot','about','contact','inventory'] as $f)
    $locks[$f]=db()->prepare("SELECT setting_value FROM system_settings WHERE setting_key=?")?->execute(['lock_'.$f])&&false?:'';
$st=db()->prepare("SELECT setting_key,setting_value FROM system_settings WHERE setting_key LIKE 'lock_%'");
$st->execute(); $locks=$st->fetchAll(PDO::FETCH_KEY_PAIR);
$features=[
    'services' =>['🏗️','Services','Controls who can add, edit, or delete services'],
    'products' =>['📦','Products','Controls product catalog editing and deletion'],
    'chatbot'  =>['🤖','Chatbot Q&A','Controls chatbot question and answer editing'],
    'about'    =>['📖','About Us Page','Controls editing of the About Us page content'],
    'contact'  =>['📍','Contact Us Page','Controls editing of contact information'],
    'inventory'=>['🗂️','Inventory','Controls stock updates and inventory logging'],
];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Feature Locks — Sysadmin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/sysadmin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Feature Locks</h1><p>Lock or unlock specific features across the entire system</p></div></div>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-3">Feature lock settings saved successfully!</div><?php endif;?>
  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.35rem">
    <div>
      <div class="alert alert-warning show mb-3">⚠️ <strong>Warning:</strong> Locking a feature prevents ALL administrators and staff from editing it. Only the System Admin or Owner can unlock it.</div>
      <form method="POST">
        <div class="lock-grid">
          <?php foreach($features as $key=>[$icon,$name,$desc]):
            $isLocked=($locks["lock_$key"]??'0')==='1';?>
          <div class="lock-card">
            <div class="lock-card-head">
              <div style="display:flex;align-items:center;gap:.6rem"><span style="font-size:1.25rem"><?=$icon?></span><div class="lock-card-title"><?=$name?></div></div>
              <label class="toggle">
                <input type="checkbox" name="lock_<?=$key?>" <?=$isLocked?'checked':''?>>
                <span class="toggle-slider"></span>
              </label>
            </div>
            <div class="lock-card-desc"><?=$desc?></div>
            <div class="lock-status <?=$isLocked?'locked':'unlocked'?>"><?=$isLocked?'🔒 Currently LOCKED — Admins cannot edit':'✅ Currently UNLOCKED — Admins can edit'?></div>
          </div>
          <?php endforeach;?>
        </div>
        <div class="mt-3"><button type="submit" class="btn btn-primary">💾 Save Lock Settings</button></div>
      </form>
    </div>
    <div>
      <div class="panel mb-3"><div class="panel-header"><div class="panel-title">ℹ️ How Locks Work</div></div>
        <div class="panel-body">
          <div style="display:flex;flex-direction:column;gap:.75rem">
            <?php foreach(['🔒 Locked = Admins & Staff CANNOT edit that feature','✅ Unlocked = Normal editing access is restored','👤 Only System Admin & Owner can change locks','📋 All lock changes are logged in activity logs'] as $tip):?>
            <div style="font-size:.83rem;color:var(--muted);line-height:1.55"><?=$tip?></div>
            <?php endforeach;?>
          </div>
        </div>
      </div>
      <div class="panel"><div class="panel-header"><div class="panel-title">Lock Change History</div></div>
        <div class="panel-body" style="padding:0">
          <?php $hist=db()->query("SELECT al.*,u.full_name FROM activity_logs al JOIN users u ON al.user_id=u.user_id WHERE al.action LIKE '%lock%' OR al.module='System' ORDER BY al.logged_at DESC LIMIT 10")->fetchAll();
          foreach($hist as $l):?>
          <div style="padding:.65rem 1.1rem;border-bottom:1px solid var(--border)">
            <div style="font-size:.83rem;font-weight:500"><?=h($l['full_name'])?></div>
            <div style="font-size:.75rem;color:var(--muted)"><?=fmtDateTime($l['logged_at'])?></div>
          </div>
          <?php endforeach;?>
          <?php if(!$hist):?><div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.845rem">No history yet</div><?php endif;?>
        </div>
      </div>
    </div>
  </div>
</main></body></html>
