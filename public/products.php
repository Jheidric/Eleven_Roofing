<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$catId  = $_GET['cat'] ?? 'all';
$q      = trim($_GET['q'] ?? '');
$cats   = db()->query(
    "SELECT c.*,COUNT(p.product_id) as cnt
     FROM categories c
     LEFT JOIN products p ON p.category_id=c.category_id AND p.is_active=1
     GROUP BY c.category_id ORDER BY c.category_name"
)->fetchAll();

if ($catId === 'all') {
    $st = db()->prepare(
        "SELECT p.*,c.category_name FROM products p
         JOIN categories c ON p.category_id=c.category_id
         WHERE p.is_active=1" .
        ($q ? " AND (p.product_name LIKE ? OR p.description LIKE ?)" : "") .
        " ORDER BY c.category_name,p.product_name"
    );
    $st->execute($q ? ["%$q%","%$q%"] : []);
} else {
    $st = db()->prepare(
        "SELECT p.*,c.category_name FROM products p
         JOIN categories c ON p.category_id=c.category_id
         WHERE p.is_active=1 AND p.category_id=?" .
        ($q ? " AND (p.product_name LIKE ? OR p.description LIKE ?)" : "") .
        " ORDER BY p.product_name"
    );
    $st->execute($q ? [$catId,"%$q%","%$q%"] : [$catId]);
}
$prods = $st->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Products — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="css/products.css">
</head><body>

<nav class="site-nav">
  <a href="index.php" class="nav-logo">Eleven Roofing</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="services.php">Services</a>
    <a href="products.php" class="active">Products</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
    <?php if (isLoggedIn()): ?>
      <a href="<?= getDashboardUrl() ?>">Dashboard</a>
    <?php else: ?>
      <a href="../auth/login.php">Login</a>
      <a href="../auth/register.php" class="nav-cta">Get Started</a>
    <?php endif; ?>
  </div>
</nav>

<div class="page-hero">
  <div class="page-hero-inner">
    <p class="page-label">Premium Materials</p>
    <h1 class="page-title">Our <em>Products</em></h1>
    <p class="page-sub">Browse our complete catalog of premium roofing materials and supplies — with live stock quantities.</p>
  </div>
</div>

<div class="prod-layout">
  <!-- SIDEBAR -->
  <aside class="prod-sidebar">
    <form method="GET" class="mb-2">
      <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?= h($q) ?>">
      <button type="submit" class="btn btn-primary btn-sm w-full mt-1">Search</button>
    </form>
    <h4 style="font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--accent);margin-bottom:.9rem">Categories</h4>
    <a href="products.php<?= $q?'?q='.urlencode($q):'' ?>" class="cat-lnk <?= $catId==='all'?'active':'' ?>">
      All Products <span class="cat-cnt"><?= array_sum(array_column($cats,'cnt')) ?></span>
    </a>
    <?php foreach ($cats as $c): ?>
    <a href="?cat=<?= $c['category_id'] ?><?= $q?'&q='.urlencode($q):'' ?>" class="cat-lnk <?= $catId==$c['category_id']?'active':'' ?>">
      <?= h($c['category_name']) ?> <span class="cat-cnt"><?= $c['cnt'] ?></span>
    </a>
    <?php endforeach; ?>

    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border)">
      <h4 style="font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--accent);margin-bottom:.85rem">Stock Legend</h4>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <div style="display:flex;align-items:center;gap:.55rem;font-size:.8rem;color:var(--muted)"><span style="width:10px;height:10px;background:var(--success);border-radius:50%;flex-shrink:0"></span> Plenty in stock</div>
        <div style="display:flex;align-items:center;gap:.55rem;font-size:.8rem;color:var(--muted)"><span style="width:10px;height:10px;background:var(--warning);border-radius:50%;flex-shrink:0"></span> Limited stock</div>
        <div style="display:flex;align-items:center;gap:.55rem;font-size:.8rem;color:var(--muted)"><span style="width:10px;height:10px;background:var(--danger);border-radius:50%;flex-shrink:0"></span> Very low — order soon</div>
        <div style="display:flex;align-items:center;gap:.55rem;font-size:.8rem;color:var(--muted)"><span style="width:10px;height:10px;background:var(--bg4);border-radius:50%;flex-shrink:0;border:1px solid var(--border)"></span> Out of stock</div>
      </div>
    </div>
  </aside>

  <!-- PRODUCTS GRID -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.1rem">
      <p style="font-size:.83rem;color:var(--muted)"><?= count($prods) ?> product<?= count($prods)!=1?'s':'' ?> found</p>
      <?php if ($q): ?>
      <a href="products.php<?= $catId!=='all'?'?cat='.$catId:'' ?>" style="font-size:.8rem;color:var(--accent)">Clear search ✕</a>
      <?php endif; ?>
    </div>
    <div class="prod-full-grid">
      <?php foreach ($prods as $p):
        $stock = (int)$p['stock_quantity'];
        $min   = (int)$p['min_stock'];
        // Stock colour & label
        if ($stock <= 0) {
            $stockClr = 'var(--muted)'; $stockLabel = 'Out of Stock'; $stockBg = 'rgba(138,137,127,.12)'; $stockBdr = 'rgba(138,137,127,.3)';
        } elseif ($stock < 20) {
            $stockClr = 'var(--danger)'; $stockLabel = 'Very Low'; $stockBg = 'var(--danger-dim)'; $stockBdr = 'rgba(224,90,79,.3)';
        } elseif ($stock < $min) {
            $stockClr = 'var(--warning)'; $stockLabel = 'Limited'; $stockBg = 'var(--warning-dim)'; $stockBdr = 'rgba(232,168,64,.3)';
        } else {
            $stockClr = 'var(--success)'; $stockLabel = 'In Stock'; $stockBg = 'var(--success-dim)'; $stockBdr = 'rgba(76,175,114,.3)';
        }
        $pct = $stock > 0 ? min(intval($stock/max($min,1)*100),100) : 0;
      ?>
      <div class="prod-full-card fade-in">
        <div class="prod-full-img">
          <?php if ($p['image_path'] && file_exists(__DIR__.'/../'.$p['image_path'])): ?>
            <img src="../<?= h($p['image_path']) ?>" alt="<?= h($p['product_name']) ?>">
          <?php else: ?>
            <?= $p['icon_emoji'] ?>
          <?php endif; ?>
          <!-- Stock badge overlay -->
          <?php if ($stock <= 0 || $stock < 20): ?>
          <div class="prod-stock-overlay" style="background:<?= $stockBg ?>;border-bottom:1px solid <?= $stockBdr ?>">
            <span style="color:<?= $stockClr ?>;font-size:.72rem;font-weight:500"><?= $stockLabel ?></span>
          </div>
          <?php endif; ?>
        </div>
        <div class="prod-full-info">
          <div class="prod-full-cat"><?= h($p['category_name']) ?></div>
          <div class="prod-full-name"><?= h($p['product_name']) ?></div>
          <div class="prod-full-desc"><?= h(mb_strimwidth($p['description'],0,75,'...')) ?></div>

          <!-- ACTUAL STOCK DISPLAY -->
          <div class="prod-stock-wrap">
            <div class="prod-stock-row">
              <span class="prod-stock-lbl">Available Stock:</span>
              <strong class="prod-stock-num" style="color:<?= $stockClr ?>">
                <?= $stock > 0 ? number_format($stock).' units' : 'Out of Stock' ?>
              </strong>
            </div>
            <!-- Mini stock bar -->
            <div class="prod-stock-bar">
              <div class="prod-stock-bar-fill" style="width:<?= $pct ?>%;background:<?= $stockClr ?>"></div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.3rem">
              <span style="font-size:.7rem;color:var(--muted)">Min threshold: <?= number_format($min) ?> units</span>
              <span class="prod-stk-badge" style="background:<?= $stockBg ?>;color:<?= $stockClr ?>;border:1px solid <?= $stockBdr ?>"><?= $stockLabel ?></span>
            </div>
          </div>

          <div class="prod-full-foot">
            <div class="prod-full-price">₱<?= fmtNum($p['price']) ?></div>
            <?php if ($stock > 0): ?>
            <a href="inquiry.php?service=<?= urlencode('Material Purchase: '.$p['product_name']) ?>" class="btn btn-primary btn-sm">Order / Inquire</a>
            <?php else: ?>
            <a href="contact.php" class="btn btn-outline btn-sm">Ask Availability</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if (!$prods): ?>
      <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted)">
        <div style="font-size:2.5rem;margin-bottom:1rem">🔍</div>
        <p>No products found<?= $q ? ' for "'.h($q).'"' : '' ?>.</p>
        <?php if ($q): ?><a href="products.php" style="color:var(--accent)">View all products →</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-brand"><a href="index.php" class="nav-logo">ERDasma</a><p>Professional roofing services in Dasmaríñas, Cavite since 2013.</p></div>
    <div class="footer-col"><h5>Quick Links</h5><a href="services.php">Services</a><a href="products.php">Products</a></div>
    <div class="footer-col"><h5>Company</h5><a href="about.php">About</a><a href="contact.php">Contact</a></div>
    <div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a></div>
  </div>
  <div class="footer-bottom"><p>© 2025 Eleven Roofing Dasma.</p></div>
</footer>

<script>
const o = new IntersectionObserver(entries => {
  entries.forEach((e,i) => { if (e.isIntersecting) setTimeout(() => e.target.classList.add('visible'), i*50); });
}, {threshold:.05});
document.querySelectorAll('.fade-in').forEach(el => o.observe(el));
</script>
</body></html>
