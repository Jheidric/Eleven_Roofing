<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$services = db()->query("SELECT * FROM services WHERE is_active=1 ORDER BY service_id LIMIT 3")->fetchAll();
$products = db()->query(
    "SELECT p.*,c.category_name FROM products p
     JOIN categories c ON p.category_id=c.category_id
     WHERE p.is_active=1 ORDER BY p.product_id LIMIT 4"
)->fetchAll();
$siteName = db()->prepare("SELECT setting_value FROM system_settings WHERE setting_key='site_name'");
$siteName->execute(); $siteName = $siteName->fetchColumn() ?: 'Eleven Roofing';
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= h($siteName) ?> — Professional Roofing Services</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="css/home.css">
</head><body>

<div class="notif-bar">🏠 Professional Roofing Services — Dasmaríñas, Cavite &nbsp;·&nbsp; Call: <?= h(getContact('phone') ?: '(046) 123-4567') ?></div>

<nav class="site-nav">
  <a href="index.php" class="nav-logo"><?= h($siteName) ?></a>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="services.php">Services</a>
    <a href="products.php">Products</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
    <?php if (isLoggedIn()): ?>
      <a href="<?= getDashboardUrl() ?>">My Dashboard</a>
    <?php else: ?>
      <a href="../auth/login.php">Login</a>
      <a href="../auth/register.php" class="nav-cta">Get Started</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid">
    <div>
      <p class="hero-label">Trusted Roofing Experts Since 2013 · Dasmaríñas, Cavite</p>
      <h1 class="hero-title">Building <em>Roofs</em> That Last Generations</h1>
      <p class="hero-sub">From installation to repair and maintenance — precision craftsmanship with premium materials for homes and businesses in Cavite.</p>
      <div class="hero-btns">
        <a href="services.php" class="btn btn-primary btn-lg">Explore Services</a>
        <a href="inquiry.php" class="btn btn-outline btn-lg">Send an Inquiry</a>
      </div>
    </div>
    <div class="hero-stats">
      <div class="stat-card fade-in"><div class="snum">500+</div><div class="slbl">Projects Completed</div></div>
      <div class="stat-card fade-in"><div class="snum">12+</div><div class="slbl">Years Experience</div></div>
      <div class="stat-card fade-in"><div class="snum">98%</div><div class="slbl">Client Satisfaction</div></div>
      <div class="stat-card stat-wide fade-in">
        <div class="stat-wide-icon">🏆</div>
        <div><h4>ISO Certified Quality</h4><p>Industry-standard roofing excellence</p></div>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES PREVIEW -->
<div class="section-alt"><div class="section-wrap">
  <p class="slabel fade-in">What We Offer</p>
  <h2 class="stitle fade-in">Our Core <em>Services</em></h2>
  <div class="services-grid">
    <?php foreach ($services as $s): ?>
    <div class="svc-card fade-in">
      <div class="svc-img">
        <?php if ($s['image_path'] && file_exists(__DIR__.'/../'.$s['image_path'])): ?>
          <img src="../<?= h($s['image_path']) ?>" alt="<?= h($s['service_name']) ?>">
        <?php else: ?>🏗️<?php endif; ?>
      </div>
      <div class="svc-body">
        <h3><?= h($s['service_name']) ?></h3>
        <p><?= h(mb_strimwidth($s['description'],0,100,'...')) ?></p>
        <div class="svc-foot">
          <span class="svc-price">From ₱<?= fmtNum($s['price_from']) ?></span>
          <a href="inquiry.php?service=<?= urlencode($s['service_name']) ?>">Inquire →</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-3"><a href="services.php" class="btn btn-outline">View All Services</a></div>
</div></div>

<!-- PRODUCTS PREVIEW — with real stock numbers -->
<div class="section-wrap">
  <p class="slabel fade-in">Premium Materials</p>
  <h2 class="stitle fade-in">Our <em>Products</em></h2>
  <div class="products-grid">
    <?php foreach ($products as $p):
      $stock = (int)$p['stock_quantity'];
      $min   = (int)$p['min_stock'];
      if ($stock <= 0)      { $stockClr='var(--muted)';   $stockLabel='Out of Stock'; }
      elseif ($stock < 20)  { $stockClr='var(--danger)';  $stockLabel='Very Low'; }
      elseif ($stock < $min){ $stockClr='var(--warning)'; $stockLabel='Limited'; }
      else                  { $stockClr='var(--success)'; $stockLabel='In Stock'; }
    ?>
    <a href="products.php" class="prod-card fade-in">
      <div class="prod-img">
        <?php if ($p['image_path'] && file_exists(__DIR__.'/../'.$p['image_path'])): ?>
          <img src="../<?= h($p['image_path']) ?>" alt="<?= h($p['product_name']) ?>">
        <?php else: ?><?= $p['icon_emoji'] ?><?php endif; ?>
      </div>
      <div class="prod-info">
        <div class="prod-cat"><?= h($p['category_name']) ?></div>
        <div class="prod-name"><?= h($p['product_name']) ?></div>
        <div class="prod-price">₱<?= fmtNum($p['price']) ?></div>
        <!-- Real stock shown on homepage cards -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--border)">
          <span style="font-size:.72rem;color:<?= $stockClr ?>;font-weight:500">
            <?= $stock > 0 ? number_format($stock).' units available' : 'Out of Stock' ?>
          </span>
          <span style="font-size:.68rem;background:<?= $stock>0?'rgba(76,175,114,.1)':'rgba(138,137,127,.1)' ?>;color:<?= $stockClr ?>;border:1px solid <?= $stock>0?'rgba(76,175,114,.3)':'rgba(138,137,127,.3)' ?>;padding:.15rem .45rem;border-radius:3px;font-weight:500"><?= $stockLabel ?></span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-3"><a href="products.php" class="btn btn-outline">Browse All Products</a></div>
</div>

<!-- WHY US -->
<div class="section-alt"><div class="section-wrap">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
    <div>
      <p class="slabel fade-in">Why Choose Us</p>
      <h2 class="stitle fade-in">Experience You Can <em>Trust</em></h2>
      <div style="display:flex;flex-direction:column;gap:1.35rem">
        <?php foreach ([
          ['Licensed & Certified Professionals','All technicians are fully licensed, insured, and trained to industry standards.'],
          ['Premium Grade Materials','We source only the highest quality roofing materials with manufacturer warranties.'],
          ['Transparent Pricing','No hidden fees. Full cost breakdown before every project begins.'],
          ['24/7 Emergency Service','Emergency repair services available around the clock, 7 days a week.'],
        ] as [$title,$desc]): ?>
        <div class="fade-in" style="display:flex;gap:1rem;align-items:flex-start">
          <div style="width:8px;height:8px;background:var(--accent);border-radius:50%;margin-top:5px;flex-shrink:0"></div>
          <div><h4 style="font-size:.95rem;font-weight:500;margin-bottom:.3rem"><?= $title ?></h4>
          <p style="color:var(--muted);font-size:.845rem;font-weight:300;line-height:1.6"><?= $desc ?></p></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="fade-in" style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--r2);padding:2.75rem;text-align:center">
      <div style="font-family:var(--serif);font-size:5.5rem;font-weight:900;color:var(--accent);opacity:.25;line-height:1">11</div>
      <p style="color:var(--muted);font-size:.855rem;margin-top:.5rem;font-weight:300">Years of excellence in Cavite</p>
      <div style="display:flex;justify-content:space-around;margin-top:1.75rem;padding-top:1.75rem;border-top:1px solid var(--border)">
        <?php foreach ([['500+','Roofs Built'],['50+','Staff'],['3','Locations']] as [$n,$l]): ?>
        <div style="text-align:center">
          <div style="font-family:var(--serif);font-size:1.6rem;color:var(--accent)"><?= $n ?></div>
          <div style="font-size:.73rem;color:var(--muted)"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div></div>

<!-- CTA -->
<div class="cta-wrap">
  <h2>Ready to Get Started?</h2>
  <p>Send us an inquiry and our team will respond within 24 hours with a detailed quote.</p>
  <a href="inquiry.php" class="btn btn-primary btn-lg">Send an Inquiry</a>
</div>

<footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="index.php" class="nav-logo"><?= h($siteName) ?></a>
      <p>Professional roofing services in Dasmaríñas, Cavite since 2013. Licensed and ISO certified.</p>
    </div>
    <div class="footer-col"><h5>Services</h5><a href="services.php">Installation</a><a href="services.php">Repair</a><a href="services.php">Maintenance</a></div>
    <div class="footer-col"><h5>Company</h5><a href="about.php">About Us</a><a href="contact.php">Contact</a></div>
    <div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a><a href="inquiry.php">Send Inquiry</a></div>
  </div>
  <div class="footer-bottom"><p>© 2025 <?= h($siteName) ?>. All rights reserved.</p><p>Dasmaríñas, Cavite</p></div>
</footer>

<a href="../user/chat.php" class="chat-fab" title="Chat Support">💬</a>

<script>
const o = new IntersectionObserver(entries => {
  entries.forEach((e,i) => { if (e.isIntersecting) setTimeout(() => e.target.classList.add('visible'), i*70); });
}, {threshold:.08});
document.querySelectorAll('.fade-in').forEach(el => o.observe(el));
</script>
</body></html>
