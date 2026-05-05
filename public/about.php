<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$a=[];
foreach(db()->query("SELECT section_key,content FROM about_content")->fetchAll() as $row) $a[$row['section_key']]=$row['content'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>About Us — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="css/about.css">
</head><body>
<nav class="site-nav"><a href="index.php" class="nav-logo">Eleven Roofing</a><div class="nav-links"><a href="index.php">Home</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="about.php" class="active">About</a><a href="contact.php">Contact</a><?php if(isLoggedIn()):?><a href="<?=getDashboardUrl()?>">Dashboard</a><?php else:?><a href="../auth/login.php">Login</a><a href="../auth/register.php" class="nav-cta">Get Started</a><?php endif;?></div></nav>

<div class="page-hero"><div class="page-hero-inner">
  <p class="page-label">Who We Are</p>
  <h1 class="page-title"><?=h($a['hero_title']??'About Eleven Roofing Dasma')?></h1>
  <p class="page-sub"><?=h($a['hero_subtitle']??'Professional Roofing Services in Dasmaríñas, Cavite')?></p>
</div></div>

<!-- STORY -->
<div class="about-alt"><div class="about-wrap">
  <div class="about-grid">
    <div>
      <p class="slabel fade-in">Our Story</p>
      <h2 class="fade-in" style="font-family:var(--serif);font-size:2.1rem;margin-bottom:1.25rem">Built on <em style="color:var(--accent)">Trust &amp; Craftsmanship</em></h2>
      <p class="fade-in" style="color:var(--muted);font-size:.9rem;line-height:1.85;font-weight:300;margin-bottom:1rem"><?=nl2br(h($a['story_body']??''))?></p>
      <?php if(!empty($a['mission'])):?><div class="about-quote fade-in"><strong>Mission:</strong> <?=h($a['mission'])?></div><?php endif;?>
      <?php if(!empty($a['vision'])):?><div class="about-quote fade-in" style="margin-top:.65rem"><strong>Vision:</strong> <?=h($a['vision'])?></div><?php endif;?>
      <div class="mt-3 fade-in"><a href="contact.php" class="btn btn-primary">Get In Touch</a></div>
    </div>
    <div class="about-visual fade-in">
      <div style="height:190px;background:linear-gradient(135deg,#141a17,#0d1512);display:flex;align-items:center;justify-content:center;font-size:4rem">🏗️</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--border)">
        <?php foreach([[$a['years']??'12','Years'],[$a['projects']??'500+','Projects'],['98%','Satisfaction']] as [$n,$l]):?>
        <div style="padding:1.1rem;text-align:center;border-right:1px solid var(--border)">
          <div style="font-family:var(--serif);font-size:1.55rem;color:var(--accent)"><?=h($n)?></div>
          <div style="font-size:.73rem;color:var(--muted)"><?=$l?></div>
        </div>
        <?php endforeach;?>
      </div>
    </div>
  </div>
</div></div>

<!-- VALUES -->
<div class="about-wrap">
  <p class="slabel fade-in text-center">What Drives Us</p>
  <h2 class="fade-in text-center" style="font-family:var(--serif);font-size:2.1rem;margin-bottom:2.5rem">Our Core <em style="color:var(--accent)">Values</em></h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem">
    <?php foreach([['🎯','Quality First','We never cut corners. Every job is done right or done again.'],['🤝','Honest Partnerships','Transparent pricing and clear communication from start to finish.'],['🛡️','Safety Above All','Strict safety protocols on every site, always.'],['⚡','Fast Response','24/7 emergency team responds when you need it most.'],['🌱','Sustainable Practices','Eco-conscious materials and minimal construction waste.'],['📋','Accountability','Written warranties and follow-through inspections on every project.']] as [$ico,$title,$desc]):?>
    <div class="value-card fade-in">
      <div style="font-size:1.75rem;margin-bottom:.8rem"><?=$ico?></div>
      <h4 style="font-size:.95rem;font-weight:500;margin-bottom:.45rem"><?=$title?></h4>
      <p style="color:var(--muted);font-size:.83rem;font-weight:300;line-height:1.65"><?=$desc?></p>
    </div>
    <?php endforeach;?>
  </div>
</div>

<!-- CTA -->
<div class="about-cta">
  <h2>Work With the Best</h2>
  <p>Have a roofing project in mind? Our experts are ready to help you plan, price, and build.</p>
  <a href="contact.php" class="btn btn-primary btn-lg">Contact Us Today</a>
</div>
<footer class="site-footer"><div class="footer-grid"><div class="footer-brand"><a href="index.php" class="nav-logo">ERDasma</a><p>Professional roofing services since 2013.</p></div><div class="footer-col"><h5>Services</h5><a href="services.php">Installation</a><a href="services.php">Repair</a></div><div class="footer-col"><h5>Company</h5><a href="about.php">About</a><a href="contact.php">Contact</a></div><div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a></div></div><div class="footer-bottom"><p>© 2025 Eleven Roofing Dasma.</p></div></footer>
<script>const o=new IntersectionObserver(e=>{e.forEach((x,i)=>{if(x.isIntersecting)setTimeout(()=>x.target.classList.add('visible'),i*70)})},{threshold:.08});document.querySelectorAll('.fade-in').forEach(el=>o.observe(el));</script>
</body></html>
