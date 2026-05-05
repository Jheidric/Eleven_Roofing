<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireStaff();

$prods = db()->query(
    "SELECT p.*,c.category_name FROM products p
     JOIN categories c ON p.category_id=c.category_id
     WHERE p.is_active=1 ORDER BY p.stock_quantity ASC"
)->fetchAll();

$myPending = db()->prepare("SELECT COUNT(*) FROM inventory_requests WHERE requested_by=? AND status='pending'");
$myPending->execute([$user['id']]);
$myPending = (int)$myPending->fetchColumn();

$critical = array_filter($prods, fn($p) => $p['stock_quantity'] < 20);
$low      = array_filter($prods, fn($p) => $p['stock_quantity'] < $p['min_stock'] && $p['stock_quantity'] >= 20);
$normal   = array_filter($prods, fn($p) => $p['stock_quantity'] >= $p['min_stock']);
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>View Inventory — Staff Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/staff.css">
</head><body class="dash-body">
<?php include __DIR__.'/partials/sidebar.php'; ?>
<main class="dash-main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Inventory (View Only)</h1>
      <p>Current stock levels — read-only. To request a change, use <a href="request_stock.php" style="color:var(--accent)">Request Stock Change</a>.</p>
    </div>
    <a href="request_stock.php" class="btn btn-primary btn-sm">📦 Request Stock Change</a>
  </div>

  <?php if ($myPending > 0): ?>
  <div class="alert alert-warning show mb-3">
    ⏳ You have <strong><?= $myPending ?> pending stock request<?= $myPending>1?'s':'' ?></strong> awaiting admin approval.
    <a href="request_stock.php" style="color:var(--warning);text-decoration:underline;margin-left:.4rem">View →</a>
  </div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-grid mb-3">
    <div class="stat-crd c-danger"><div class="stat-lbl">🚨 Critical</div><div class="stat-num"><?= count($critical) ?></div><div class="stat-sub">Below 20 units</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">⚠️ Low</div><div class="stat-num"><?= count($low) ?></div><div class="stat-sub">Below minimum</div></div>
    <div class="stat-crd c-success"><div class="stat-lbl">✅ Normal</div><div class="stat-num"><?= count($normal) ?></div><div class="stat-sub">Above minimum</div></div>
    <div class="stat-crd c-info"><div class="stat-lbl">📦 Total</div><div class="stat-num"><?= count($prods) ?></div><div class="stat-sub">Products tracked</div></div>
  </div>

  <?php if ($critical): ?>
  <div class="alert alert-error show mb-3">
    🚨 <strong><?= count($critical) ?> product<?= count($critical)>1?'s':'' ?> critically low (below 20 units).</strong>
    Consider submitting a stock request for restocking.
  </div>
  <?php endif; ?>

  <!-- VISUAL MONITOR -->
  <div class="panel mb-3">
    <div class="panel-header"><div class="panel-title">📊 Stock Level Monitor</div></div>
    <div class="panel-body">
      <div class="inv-monitor">
        <?php foreach ($prods as $p):
          $stock = (int)$p['stock_quantity'];
          $min   = (int)$p['min_stock'];
          $pct   = $min > 0 ? min(intval($stock/$min*100), 100) : 100;
          $clr   = $stock < 20 ? 'var(--danger)' : ($stock < $min ? 'var(--warning)' : 'var(--success)');
        ?>
        <div class="inv-row">
          <div class="inv-name"><?= h($p['product_name']) ?></div>
          <div style="font-size:.72rem;color:var(--muted);width:90px;text-align:right;flex-shrink:0"><?= h($p['category_name']) ?></div>
          <div class="inv-qty" style="color:<?= $clr ?>;font-family:var(--serif)">
            <?= number_format($stock) ?> <span style="font-size:.7rem;font-family:var(--sans);color:var(--muted)">/ <?= number_format($min) ?> min</span>
          </div>
          <div class="inv-bar-wrap" style="width:150px">
            <div class="inv-bar-inner" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div>
          </div>
          <span class="badge <?= $stock<20?'badge-critical':($stock<$min?'badge-pending':'badge-resolved') ?>" style="width:68px;text-align:center;flex-shrink:0">
            <?= $stock<20?'Critical':($stock<$min?'Low':'Normal') ?>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- FULL TABLE with actual numbers -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">Full Stock Table — Actual Quantities</div></div>
    <div class="panel-body"><div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Current Stock</th>
            <th>Min Threshold</th>
            <th>% of Min</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($prods as $p):
          $stock = (int)$p['stock_quantity'];
          $min   = (int)$p['min_stock'];
          $pct   = $min > 0 ? min(intval($stock/$min*100), 100) : 100;
          $clr   = $stock < 20 ? 'var(--danger)' : ($stock < $min ? 'var(--warning)' : 'var(--success)');
        ?>
        <tr>
          <td style="font-weight:500"><?= h($p['product_name']) ?></td>
          <td><span class="badge badge-accent"><?= h($p['category_name']) ?></span></td>
          <td>
            <span style="font-family:var(--serif);font-size:1.05rem;font-weight:700;color:<?= $clr ?>">
              <?= number_format($stock) ?>
            </span>
            <span style="font-size:.75rem;color:var(--muted)"> units</span>
          </td>
          <td style="color:var(--muted)"><?= number_format($min) ?> units</td>
          <td>
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="width:80px;height:5px;background:var(--bg4);border-radius:3px;overflow:hidden">
                <div style="width:<?= $pct ?>%;height:100%;background:<?= $clr ?>;border-radius:3px"></div>
              </div>
              <span style="font-size:.78rem;color:<?= $clr ?>"><?= $pct ?>%</span>
            </div>
          </td>
          <td>
            <span class="badge <?= $stock<20?'badge-critical':($stock<$min?'badge-pending':'badge-resolved') ?>">
              <?= $stock<=0?'Out of Stock':($stock<20?'🚨 Critical':($stock<$min?'⚠️ Low':'✅ Normal')) ?>
            </span>
          </td>
          <td>
            <?php if ($stock < $min): ?>
            <a href="request_stock.php?pid=<?= $p['product_id'] ?>" class="btn btn-warning btn-sm">Request Restock</a>
            <?php else: ?>
            <span style="color:var(--muted);font-size:.78rem">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</main>
</body></html>
