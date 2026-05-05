<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') { db()->prepare("UPDATE contact_messages SET is_read=1 WHERE contact_id=?")->execute([(int)$_POST['contact_id']]); header('Location: contact_msgs.php'); exit; }
$msgs=db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Contact Messages — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Contact Messages</h1><p>Messages submitted via the Contact Us page</p></div></div>
  <div class="panel"><div class="panel-body">
    <?php foreach($msgs as $m):?>
    <div style="background:<?=$m['is_read']?'var(--bg3)':'var(--bg2)'?>;border:1px solid <?=$m['is_read']?'var(--border)':'var(--accent)'?>;border-radius:var(--r2);padding:1.2rem;margin-bottom:.85rem">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.6rem">
        <div><?php if(!$m['is_read']):?><span class="badge badge-accent" style="margin-bottom:.35rem;display:inline-block">New</span><br><?php endif;?>
          <strong><?=h($m['first_name'].' '.$m['last_name'])?></strong>
          <span style="color:var(--muted);font-size:.8rem;margin-left:.5rem"><?=h($m['email'])?></span>
          <?php if($m['phone']):?><span style="color:var(--muted);font-size:.8rem;margin-left:.5rem">· <?=h($m['phone'])?></span><?php endif;?>
        </div>
        <span style="color:var(--muted);font-size:.75rem"><?=fmtDateTime($m['created_at'])?></span>
      </div>
      <div style="font-size:.83rem;color:var(--accent);margin-bottom:.4rem"><?=h($m['subject'])?></div>
      <p style="font-size:.845rem;color:var(--muted);font-weight:300;line-height:1.7"><?=nl2br(h($m['message']))?></p>
      <?php if(!$m['is_read']):?><form method="POST" style="margin-top:.6rem;display:inline"><input type="hidden" name="contact_id" value="<?=$m['contact_id']?>"><button type="submit" class="btn btn-outline btn-sm">Mark as Read</button></form><?php endif;?>
    </div>
    <?php endforeach;?>
    <?php if(!$msgs):?><div style="text-align:center;padding:3rem;color:var(--muted)">No contact messages yet</div><?php endif;?>
  </div></div>
</main></body></html>
