<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireSysAdmin();

$prods = db()->query(
    "SELECT p.*,c.category_name FROM products p
     JOIN categories c ON p.category_id=c.category_id
     WHERE p.is_active=1 ORDER BY p.stock_quantity ASC"
)->fetchAll();

$pendingReqs = db()->query(
    "SELECT ir.*,p.product_name,p.stock_quantity,u.full_name AS requester
     FROM inventory_requests ir
     JOIN products p ON ir.product_id=p.product_id
     JOIN users u ON ir.requested_by=u.user_id
     WHERE ir.status='pending' ORDER BY ir.requested_at ASC"
)->fetchAll();

$recentLogs = db()->query(
    "SELECT il.*,p.product_name,u.full_name FROM inventory_logs il
     JOIN products p ON il.product_id=p.product_id
     LEFT JOIN users u ON il.logged_by=u.user_id
     ORDER BY il.logged_at DESC LIMIT 30"
)->fetchAll();

$critical = array_filter($prods, fn($p) => $p['stock_quantity'] < 20);
$low      = array_filter($prods, fn($p) => $p['stock_quantity'] < $p['min_stock'] && $p['stock_quantity'] >= 20);
$normal   = array_filter($prods, fn($p) => $p['stock_quantity'] >= $p['min_stock']);
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Inventory Monitor — Sysadmin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/sysadmin.css">
</head><body class="dash-body">
<?php include __DIR__.'/partials/sidebar.php'; ?>
<main class="dash-main">
  <div class="topbar">
    <div class="topbar-left"><h1>Inventory Monitor</h1><p>Real-time stock level monitoring — system-wide view</p></div>
    <a href="../admin/inventory.php" class="btn btn-primary btn-sm">Manage Inventory →</a>
  </div>

  <div class="stats-grid-5 mb-3">
    <div class="stat-crd c-danger"><div class="stat-lbl">🚨 Critical</div><div class="stat-num"><?= count($critical) ?></div><div class="stat-sub">Below 20 units</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">⚠️ Low Stock</div><div class="stat-num"><?= count($low) ?></div><div class="stat-sub">Below minimum</div></div>
    <div class="stat-crd c-success"><div class="stat-lbl">✅ Normal</div><div class="stat-num"><?= count($normal) ?></div><div class="stat-sub">Above minimum</div></div>
    <div class="stat-crd c-<?= count($pendingReqs)>0?'warning':'success' ?>"><div class="stat-lbl">⏳ Pending Requests</div><div class="stat-num"><?= count($pendingReqs) ?></div><div class="stat-sub">Awaiting admin approval</div></div>
    <div class="stat-crd c-info"><div class="stat-lbl">📦 Total Products</div><div class="stat-num"><?= count($prods) ?></div><div class="stat-sub">In catalog</div></div>
  </div>

  <?php if ($critical): ?>
  <div class="alert alert-error show mb-3">🚨 <strong><?= count($critical) ?> product<?= count($critical)>1?'s':'' ?> critically low.</strong> Admin should log a restock or approve pending requests.</div>
  <?php endif; ?>

  <?php if ($pendingReqs): ?>
  <div class="panel mb-3" style="border-color:rgba(232,168,64,.4)">
    <div class="panel-header" style="background:var(--warning-dim)">
      <div class="panel-title">⏳ Pending Stock Requests (<?= count($pendingReqs) ?>)</div>
      <a href="../admin/inventory.php?tab=requests" class="panel-action">Review in Admin →</a>
    </div>
    <div class="panel-body"><div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Current Stock</th><th>After Approval</th><th>Requested By</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($pendingReqs as $r):
          $after = $r['change_type']==='add' ? $r['stock_quantity']+$r['quantity'] : max(0,$r['stock_quantity']-$r['quantity']);
          $clrAfter = $after<20?'var(--danger)':($after<50?'var(--warning)':'var(--success)');
        ?>
        <tr>
          <td style="font-weight:500"><?= h($r['product_name']) ?></td>
          <td><span class="badge <?= $r['change_type']==='add'?'badge-resolved':'badge-critical' ?>"><?= $r['change_type']==='add'?'➕ Add':'➖ Remove' ?></span></td>
          <td style="font-weight:500"><?= number_format($r['quantity']) ?></td>
          <td style="font-family:var(--serif);font-size:1rem"><?= number_format($r['stock_quantity']) ?></td>
          <td><span style="font-family:var(--serif);font-size:1rem;color:<?= $clrAfter ?>"><?= number_format($after) ?></span></td>
          <td style="color:var(--muted);font-size:.82rem"><?= h($r['requester']) ?></td>
          <td style="color:var(--muted);font-size:.75rem"><?= fmtDateTime($r['requested_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.35rem">
    <div>
      <!-- VISUAL MONITOR -->
      <div class="panel mb-3">
        <div class="panel-header"><div class="panel-title">📊 Stock Overview — All Products</div></div>
        <div class="panel-body">
          <div class="inv-monitor">
          <?php foreach ($prods as $p):
            $stock = (int)$p['stock_quantity'];
            $min   = (int)$p['min_stock'];
            $pct   = $min > 0 ? min(intval($stock/$min*100), 100) : 100;
            $clr   = $stock<20?'var(--danger)':($stock<$min?'var(--warning)':'var(--success)');
          ?>
          <div class="inv-row">
            <div class="inv-name" style="flex:2"><?= h($p['product_name']) ?></div>
            <div style="font-size:.72rem;color:var(--muted);width:90px;text-align:right;flex-shrink:0"><?= h($p['category_name']) ?></div>
            <div class="inv-qty" style="color:<?= $clr ?>;font-family:var(--serif);width:90px;text-align:right">
              <?= number_format($stock) ?>
            </div>
            <div style="font-size:.7rem;color:var(--muted);width:55px;text-align:right">/ <?= number_format($min) ?></div>
            <div class="inv-bar-wrap" style="width:140px">
              <div class="inv-bar-inner" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div>
            </div>
            <span class="badge <?= $stock<20?'badge-critical':($stock<$min?'badge-pending':'badge-resolved') ?>" style="width:65px;text-align:center;flex-shrink:0">
              <?= $stock<20?'Critical':($stock<$min?'Low':'Normal') ?>
            </span>
          </div>
          <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- FULL TABLE -->
      <div class="panel">
        <div class="panel-header"><div class="panel-title">Full Stock Table</div></div>
        <div class="panel-body"><div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Min</th><th>Level</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($prods as $p):
              $stock=(int)$p['stock_quantity']; $min=(int)$p['min_stock'];
              $pct=$min>0?min(intval($stock/$min*100),100):100;
              $clr=$stock<20?'var(--danger)':($stock<$min?'var(--warning)':'var(--success)');
            ?>
            <tr>
              <td style="font-weight:500"><?= h($p['product_name']) ?></td>
              <td style="color:var(--muted)"><?= h($p['category_name']) ?></td>
              <td><span style="font-family:var(--serif);font-size:1.05rem;color:<?= $clr ?>;font-weight:700"><?= number_format($stock) ?></span> <span style="font-size:.73rem;color:var(--muted)">units</span></td>
              <td style="color:var(--muted)"><?= number_format($min) ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:.45rem">
                  <div style="width:70px;height:5px;background:var(--bg4);border-radius:3px;overflow:hidden">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $clr ?>"></div>
                  </div>
                  <span style="font-size:.75rem;color:<?= $clr ?>"><?= $pct ?>%</span>
                </div>
              </td>
              <td><span class="badge <?= $stock<20?'badge-critical':($stock<$min?'badge-pending':'badge-resolved') ?>"><?= $stock<20?'Critical':($stock<$min?'Low':'Normal') ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div></div>
      </div>
    </div>

    <!-- CHANGE LOG SIDEBAR -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📋 Recent Changes</div></div>
      <div class="panel-body" style="padding:0;max-height:900px;overflow-y:auto">
        <?php foreach ($recentLogs as $l):
          $c = $l['change_type']==='add'?'var(--success)':'var(--danger)';
        ?>
        <div style="padding:.7rem 1.1rem;border-bottom:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <span style="font-size:.83rem;font-weight:500"><?= h(mb_strimwidth($l['product_name'],0,24,'...')) ?></span>
            <span style="font-size:.7rem;color:var(--muted);white-space:nowrap"><?= date('M j H:i',strtotime($l['logged_at'])) ?></span>
          </div>
          <div style="font-size:.78rem;margin-top:.15rem">
            <span style="color:<?= $c ?>;font-weight:500"><?= $l['change_type']==='add'?'+':'-' ?><?= number_format($l['quantity']) ?></span>
            <span style="color:var(--muted)"> · <?= number_format($l['old_stock']) ?>→<?= number_format($l['new_stock']) ?></span>
          </div>
          <?php if ($l['notes']): ?><div style="font-size:.72rem;color:var(--muted);margin-top:.1rem"><?= h(mb_strimwidth($l['notes'],0,45,'...')) ?></div><?php endif; ?>
          <div style="font-size:.7rem;color:var(--muted)">by <?= h($l['full_name']??'System') ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$recentLogs): ?><div style="padding:2rem;text-align:center;color:var(--muted)">No logs yet</div><?php endif; ?>
      </div>
    </div>
  </div>
</main>
</body></html>
