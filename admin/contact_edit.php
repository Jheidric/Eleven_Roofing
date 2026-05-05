<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin(); $locked = isLocked('contact');
if ($_SERVER['REQUEST_METHOD']==='POST' && !$locked) {
    foreach ($_POST as $key=>$val) {
        if ($key==='action') continue;
        $exists=db()->prepare("SELECT COUNT(*) FROM contact_content WHERE field_key=?"); $exists->execute([$key]);
        if ($exists->fetchColumn()) {
            db()->prepare("UPDATE contact_content SET field_value=?,updated_by=?,updated_at=NOW() WHERE field_key=?")->execute([trim($val),$user['id'],$key]);
        } else {
            db()->prepare("INSERT INTO contact_content (field_key,field_label,field_value,updated_by) VALUES (?,?,?,?)")->execute([$key,$key,trim($val),$user['id']]);
        }
    }
    logActivity($user['id'],'Updated Contact Us content','Content');
    header('Location: contact_edit.php?msg=1'); exit;
}
$cc=[];
foreach(db()->query("SELECT * FROM contact_content")->fetchAll() as $row) $cc[$row['field_key']]=$row['field_value'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Edit Contact Us — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Edit Contact Us</h1><p>Update contact information visible to website visitors</p></div>
    <a href="../public/contact.php" target="_blank" class="btn btn-outline btn-sm">👁️ Preview Page</a>
  </div>
  <?php if($locked): echo renderLockBadge('contact'); endif;?>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-3">Contact content updated!</div><?php endif;?>
  <form method="POST" <?=$locked?'onsubmit="return false"':''?>>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.35rem">
      <div class="panel"><div class="panel-header"><div class="panel-title">📞 Contact Details</div></div><div class="panel-body">
        <div class="form-group"><label>Main Office Address</label><textarea name="address" class="form-control" style="min-height:70px" <?=$locked?'disabled':''?>><?=h($cc['address']??'')?></textarea></div>
        <div class="form-group"><label>Main Phone Number</label><input type="text" name="phone" class="form-control" value="<?=h($cc['phone']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Emergency Hotline</label><input type="text" name="emergency_phone" class="form-control" value="<?=h($cc['emergency_phone']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" value="<?=h($cc['email']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Weekday Hours</label><input type="text" name="hours_weekday" class="form-control" value="<?=h($cc['hours_weekday']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Saturday Hours</label><input type="text" name="hours_saturday" class="form-control" value="<?=h($cc['hours_saturday']??'')?>" <?=$locked?'disabled':''?>></div>
      </div></div>
      <div class="panel"><div class="panel-header"><div class="panel-title">📍 Branch Locations</div></div><div class="panel-body">
        <div class="form-group"><label>Main Branch</label><textarea name="branch_1" class="form-control" style="min-height:65px" <?=$locked?'disabled':''?>><?=h($cc['branch_1']??'')?></textarea></div>
        <div class="form-group"><label>Branch 2</label><textarea name="branch_2" class="form-control" style="min-height:65px" <?=$locked?'disabled':''?>><?=h($cc['branch_2']??'')?></textarea></div>
        <div class="form-group"><label>Branch 3</label><textarea name="branch_3" class="form-control" style="min-height:65px" <?=$locked?'disabled':''?>><?=h($cc['branch_3']??'')?></textarea></div>
      </div></div>
    </div>
    <?php if(!$locked):?><div class="mt-3"><button type="submit" class="btn btn-primary">💾 Save Contact Content</button></div><?php endif;?>
  </form>
</main></body></html>
