<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$cat=$_GET['cat']??'all';
$cats=db()->query("SELECT DISTINCT category FROM services WHERE is_active=1 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
if($cat==='all') { $st=db()->query("SELECT * FROM services WHERE is_active=1 ORDER BY service_id"); } else { $st=db()->prepare("SELECT * FROM services WHERE is_active=1 AND category=? ORDER BY service_id"); $st->execute([$cat]); }
$services=$st->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Services — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="css/services.css">
</head><body>
<nav class="site-nav"><a href="index.php" class="nav-logo">Eleven Roofing</a><div class="nav-links"><a href="index.php">Home</a><a href="services.php" class="active">Services</a><a href="products.php">Products</a><a href="about.php">About</a><a href="contact.php">Contact</a><?php if(isLoggedIn()):?><a href="<?=getDashboardUrl()?>">Dashboard</a><?php else:?><a href="../auth/login.php">Login</a><a href="../auth/register.php" class="nav-cta">Get Started</a><?php endif;?></div></nav>
<div class="page-hero"><div class="page-hero-inner"><p class="page-label">What We Offer</p><h1 class="page-title">Our <em>Services</em></h1><p class="page-sub">From complete roof installations to 24/7 emergency repairs — expert craftsmanship for every challenge.</p></div></div>
<div style="max-width:1200px;margin:0 auto;padding:3rem 4rem">
  <div class="filter-bar">
    <a href="services.php" class="filter-btn <?=$cat==='all'?'active':''?>">All</a>
    <?php foreach($cats as $c):?><a href="?cat=<?=urlencode($c)?>" class="filter-btn <?=$cat===$c?'active':''?>"><?=h($c)?></a><?php endforeach;?>
  </div>
  <div class="svc-full-grid">
    <?php foreach($services as $s):?>
    <div class="svc-full-card fade-in">
      <div class="svc-img-wrap"><?php if($s['image_path']&&file_exists(__DIR__.'/../'.$s['image_path'])):?><img src="../<?=h($s['image_path'])?>" alt="<?=h($s['service_name'])?>"><?php else:?><span style="font-size:3rem">🏗️</span><?php endif;?>
        <span class="svc-cat-tag"><?=h($s['category'])?></span>
      </div>
      <div class="svc-full-body">
        <h3><?=h($s['service_name'])?></h3>
        <p><?=h($s['description'])?></p>
        <div class="svc-full-foot">
          <div><div class="svc-full-price">From ₱<?=fmtNum($s['price_from'])?></div><div class="svc-full-dur">⏱ <?=h($s['duration'])?></div></div>
          <a href="inquiry.php?service=<?=urlencode($s['service_name'])?>" class="btn btn-primary btn-sm">Inquire Now</a>
        </div>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(!$services):?><div style="text-align:center;padding:3rem;color:var(--muted);grid-column:1/-1">No services found.</div><?php endif;?>
  </div>
</div>
<footer class="site-footer"><div class="footer-grid"><div class="footer-brand"><a href="index.php" class="nav-logo">ERDasma</a><p>Professional roofing services since 2013.</p></div><div class="footer-col"><h5>Services</h5><a href="services.php">Installation</a><a href="services.php">Repair</a></div><div class="footer-col"><h5>Company</h5><a href="about.php">About</a><a href="contact.php">Contact</a></div><div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a></div></div><div class="footer-bottom"><p>© 2025 Eleven Roofing Dasma.</p></div></footer>
<script>const o=new IntersectionObserver(e=>{e.forEach((x,i)=>{if(x.isIntersecting)setTimeout(()=>x.target.classList.add('visible'),i*70)})},{threshold:.08});document.querySelectorAll('.fade-in').forEach(el=>o.observe(el));</script>
</body></html>
