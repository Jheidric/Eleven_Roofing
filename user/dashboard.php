<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireLogin('Customer','Administrator','Staff','System Admin','Owner');
$tab=$_GET['tab']??'overview';
$inqStmt=db()->prepare("SELECT * FROM inquiries WHERE user_id=? OR email=? ORDER BY created_at DESC");
$inqStmt->execute([$user['id'],$user['email']]);
$inqs=$inqStmt->fetchAll();
$total=count($inqs); $pending=count(array_filter($inqs,fn($i)=>$i['status']==='pending'));
$resolved=count(array_filter($inqs,fn($i)=>$i['status']==='resolved'));
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>My Dashboard — ERD</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/user.css">
</head><body class="dash-body">
<aside class="dash-sidebar">
  <div class="sidebar-head"><a href="../public/index.php" class="nav-logo" style="font-size:1.15rem">ERDasma</a><div class="sidebar-badge">Customer Portal</div></div>
  <nav class="sidebar-nav">
    <div class="nav-sec">My Account</div>
    <a href="dashboard.php" class="nav-lnk <?=$tab==='overview'?'active':''?>"><span class="ni">📊</span> Overview</a>
    <a href="dashboard.php?tab=inquiries" class="nav-lnk <?=$tab==='inquiries'?'active':''?>"><span class="ni">💬</span> My Inquiries</a>
    <a href="chat.php" class="nav-lnk"><span class="ni">🤖</span> Chat Support</a>
    <div class="nav-sec">Explore</div>
    <a href="../public/services.php" class="nav-lnk"><span class="ni">🏗️</span> Services</a>
    <a href="../public/products.php" class="nav-lnk"><span class="ni">📦</span> Products</a>
    <a href="../public/inquiry.php" class="nav-lnk"><span class="ni">✉️</span> New Inquiry</a>
  </nav>
  <div class="sidebar-foot">
    <div class="sidebar-user">
      <div class="user-av"><?=strtoupper(substr($user['name'],0,2))?></div>
      <div><div class="user-nm"><?=h($user['name'])?></div><div class="user-rl">Customer</div></div>
    </div>
    <a href="../auth/logout.php" class="logout-lnk">← Sign Out</a>
  </div>
</aside>
<main class="dash-main">
  <?php if($tab==='overview'): ?>
  <div class="topbar"><div class="topbar-left"><h1>Welcome, <?=h(explode(' ',$user['name'])[0])?> 👋</h1><p>Your account activity summary</p></div><a href="../public/inquiry.php" class="btn btn-primary btn-sm">+ New Inquiry</a></div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem">
    <div class="stat-crd c-accent"><div class="stat-lbl">Total Inquiries</div><div class="stat-num"><?=$total?></div><div class="stat-icon">💬</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">Pending</div><div class="stat-num"><?=$pending?></div><div class="stat-icon">⏳</div></div>
    <div class="stat-crd c-success"><div class="stat-lbl">Resolved</div><div class="stat-num"><?=$resolved?></div><div class="stat-icon">✅</div></div>
  </div>
  <div class="panel mb-3">
    <div class="panel-header"><div class="panel-title">Recent Inquiries</div><a href="?tab=inquiries" class="panel-action">View all →</a></div>
    <div class="panel-body">
      <?php foreach(array_slice($inqs,0,3) as $i): ?>
      <div class="inq-item"><div class="inq-head"><div><div class="inq-meta">#<?=$i['inquiry_id']?> · <?=fmtDate($i['created_at'])?> · <?=h($i['service_type'])?></div><div class="inq-subj"><?=h($i['subject'])?></div></div><span class="badge badge-<?=$i['status']==='in_progress'?'progress':$i['status']?>"><?=fmtStatus($i['status'])?></span></div>
        <p class="inq-msg"><?=h(mb_strimwidth($i['message'],0,100,'...'))?></p>
        <?php if($i['response']):?><div class="inq-reply"><div class="inq-reply-lbl">✅ Admin Response</div><p><?=nl2br(h($i['response']))?></p></div><?php endif;?>
      </div>
      <?php endforeach;?>
      <?php if(!$inqs):?><div style="text-align:center;padding:2rem;color:var(--muted)">No inquiries yet. <a href="../public/inquiry.php" style="color:var(--accent)">Send your first one →</a></div><?php endif;?>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
    <a href="chat.php" class="quick-link"><span>🤖</span><div><h4>Chat Support</h4><p>AI + live agents</p></div></a>
    <a href="../public/services.php" class="quick-link"><span>🏗️</span><div><h4>Services</h4><p>Browse roofing services</p></div></a>
    <a href="../public/products.php" class="quick-link"><span>📦</span><div><h4>Products</h4><p>Browse materials</p></div></a>
  </div>
  <?php elseif($tab==='inquiries'): ?>
  <div class="topbar"><div class="topbar-left"><h1>My Inquiries</h1><p>All your service requests and admin replies</p></div><a href="../public/inquiry.php" class="btn btn-primary btn-sm">+ New Inquiry</a></div>
  <?php foreach($inqs as $i): ?>
  <div class="inq-item mb-2">
    <div class="inq-head"><div><div class="inq-meta">#<?=$i['inquiry_id']?> · <?=fmtDate($i['created_at'])?> · <?=h($i['service_type'])?></div><div class="inq-subj"><?=h($i['subject'])?></div></div><span class="badge badge-<?=$i['status']==='in_progress'?'progress':$i['status']?>"><?=fmtStatus($i['status'])?></span></div>
    <p class="inq-msg"><?=nl2br(h($i['message']))?></p>
    <?php if($i['response']):?><div class="inq-reply"><div class="inq-reply-lbl">✅ Admin Response — <?=fmtDate($i['updated_at'])?></div><p><?=nl2br(h($i['response']))?></p></div>
    <?php else:?><div style="font-size:.78rem;color:var(--muted);margin-top:.65rem;padding-top:.65rem;border-top:1px solid var(--border)">Awaiting admin response · <a href="chat.php" style="color:var(--accent)">Follow up via Chat →</a></div><?php endif;?>
  </div>
  <?php endforeach;?>
  <?php if(!$inqs):?><div style="text-align:center;padding:3rem;color:var(--muted)">No inquiries yet. <a href="../public/inquiry.php" style="color:var(--accent)">Send one</a>.</div><?php endif;?>
  <?php endif;?>
</main></body></html>
