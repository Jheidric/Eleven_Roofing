<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();
$locked = isLocked('inventory');

// Handle admin approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $act = $_POST['action'] ?? '';
    $rid = (int)$_POST['request_id'];
    $note = trim($_POST['review_note'] ?? '');

    if ($act === 'approve') {
        $req = db()->prepare("SELECT * FROM inventory_requests WHERE request_id=? AND status='pending'");
        $req->execute([$rid]); $req = $req->fetch();
        if ($req) {
            $cur = db()->prepare("SELECT stock_quantity FROM products WHERE product_id=?");
            $cur->execute([$req['product_id']]); $old = (int)$cur->fetchColumn();
            $new = $req['change_type'] === 'add' ? $old + $req['quantity'] : max(0, $old - $req['quantity']);
            db()->prepare("UPDATE products SET stock_quantity=? WHERE product_id=?")->execute([$new, $req['product_id']]);
            db()->prepare("INSERT INTO inventory_logs (product_id,change_type,quantity,old_stock,new_stock,notes,logged_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$req['product_id'],$req['change_type'],$req['quantity'],$old,$new,"Approved staff request #$rid: ".($req['reason']??''),$user['id']]);
            db()->prepare("UPDATE inventory_requests SET status='approved',reviewed_by=?,review_note=?,reviewed_at=NOW() WHERE request_id=?")
               ->execute([$user['id'], $note ?: 'Approved', $rid]);
            logActivity($user['id'], "Approved inventory request #$rid (product #{$req['product_id']}, {$req['change_type']} {$req['quantity']})", 'Inventory');
        }
        header('Location: inventory.php?msg=approved'); exit;

    } elseif ($act === 'reject') {
        if (!$note) { header('Location: inventory.php?err=note&rid='.$rid); exit; }
        db()->prepare("UPDATE inventory_requests SET status='rejected',reviewed_by=?,review_note=?,reviewed_at=NOW() WHERE request_id=?")
           ->execute([$user['id'], $note, $rid]);
        logActivity($user['id'], "Rejected inventory request #$rid", 'Inventory');
        header('Location: inventory.php?msg=rejected'); exit;

    } elseif ($act === 'direct_log') {
        // Admin can still do direct stock changes
        $pid  = (int)$_POST['product_id'];
        $type = in_array($_POST['change_type'],['add','remove','adjustment']) ? $_POST['change_type'] : 'add';
        $qty  = abs((int)$_POST['quantity']);
        $notes = trim($_POST['notes'] ?? '');
        if ($qty > 0) {
            $cur = db()->prepare("SELECT stock_quantity FROM products WHERE product_id=?"); $cur->execute([$pid]); $old=(int)$cur->fetchColumn();
            $new = $type === 'add' ? $old + $qty : max(0, $old - $qty);
            db()->prepare("UPDATE products SET stock_quantity=? WHERE product_id=?")->execute([$new,$pid]);
            db()->prepare("INSERT INTO inventory_logs (product_id,change_type,quantity,old_stock,new_stock,notes,logged_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$pid,$type,$qty,$old,$new,"Admin direct: $notes",$user['id']]);
            logActivity($user['id'], "Direct inventory $type: $qty units for product #$pid", 'Inventory');
        }
        header('Location: inventory.php?msg=updated'); exit;
    }
}

$tab = $_GET['tab'] ?? 'requests';
$pendingReqs = db()->query(
    "SELECT ir.*,p.product_name,p.stock_quantity,u.full_name AS requester
     FROM inventory_requests ir
     JOIN products p ON ir.product_id=p.product_id
     JOIN users u ON ir.requested_by=u.user_id
     WHERE ir.status='pending'
     ORDER BY ir.requested_at ASC"
)->fetchAll();

$allReqs = db()->query(
    "SELECT ir.*,p.product_name,u.full_name AS requester,rv.full_name AS reviewer
     FROM inventory_requests ir
     JOIN products p ON ir.product_id=p.product_id
     JOIN users u ON ir.requested_by=u.user_id
     LEFT JOIN users rv ON ir.reviewed_by=rv.user_id
     ORDER BY ir.requested_at DESC LIMIT 50"
)->fetchAll();

$prods = db()->query("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id ORDER BY p.stock_quantity ASC")->fetchAll();
$logs  = db()->query("SELECT il.*,p.product_name,u.full_name FROM inventory_logs il JOIN products p ON il.product_id=p.product_id LEFT JOIN users u ON il.logged_by=u.user_id ORDER BY il.logged_at DESC LIMIT 20")->fetchAll();

$critical = array_filter($prods, fn($p)=>$p['stock_quantity']<20);
$low      = array_filter($prods, fn($p)=>$p['stock_quantity']<$p['min_stock']&&$p['stock_quantity']>=20);
$normal   = array_filter($prods, fn($p)=>$p['stock_quantity']>=$p['min_stock']);
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Inventory — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/admin.css">
<style>
.req-card{background:var(--bg3);border:1px solid var(--border);border-radius:var(--r2);padding:1.25rem;margin-bottom:1rem}
.req-card.pending{border-left:3px solid var(--warning)}
.req-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem}
.req-product{font-size:.9rem;font-weight:500}
.req-meta{font-size:.78rem;color:var(--muted);margin-top:.2rem}
.req-detail{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:.85rem}
.req-chip{background:var(--bg2);border:1px solid var(--border);border-radius:var(--r);padding:.3rem .7rem;font-size:.8rem}
.req-reason{font-size:.845rem;color:var(--muted);font-style:italic;margin-bottom:.85rem;line-height:1.6}
.approve-form{display:flex;gap:.5rem;align-items:flex-start}
.approve-form input{flex:1;background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:.55rem .85rem;border-radius:var(--r);font-size:.82rem;outline:none;font-family:var(--sans)}
.approve-form input:focus{border-color:var(--accent)}
</style>
</head><body class="dash-body">
<?php include __DIR__.'/partials/sidebar.php'; ?>
<main class="dash-main">
  <div class="topbar">
    <div class="topbar-left">
      <h1>Inventory Management</h1>
      <p>Approve stock requests from staff, monitor levels, and log direct changes</p>
    </div>
    <div class="topbar-right">
      <?php if ($pendingReqs): ?>
      <span class="notif-btn" style="cursor:default">⏳ <?= count($pendingReqs) ?> pending request<?= count($pendingReqs)>1?'s':''?><span class="notif-dot"></span></span>
      <?php endif; ?>
      <?php if (!$locked): ?>
      <button class="btn btn-primary btn-sm" onclick="toggle('direct-form')">+ Direct Log</button>
      <?php endif; ?>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
  <?php $msgs=['approved'=>'✅ Request approved — stock updated!','rejected'=>'Request rejected.','updated'=>'✅ Stock updated directly.']; ?>
  <div class="alert alert-<?= $_GET['msg']==='approved'||$_GET['msg']==='updated'?'success':'warning' ?> show mb-3"><?= $msgs[$_GET['msg']] ?? '' ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['err']) && $_GET['err']==='note'): ?>
  <div class="alert alert-error show mb-3">Please provide a rejection reason before rejecting.</div>
  <?php endif; ?>
  <?php if ($locked): echo renderLockBadge('inventory'); endif; ?>

  <!-- STATS -->
  <div class="stats-grid mb-3">
    <div class="stat-crd c-warning"><div class="stat-lbl">⏳ Pending Requests</div><div class="stat-num"><?= count($pendingReqs) ?></div><div class="stat-sub">From staff, awaiting approval</div></div>
    <div class="stat-crd c-danger"><div class="stat-lbl">🚨 Critical Stock</div><div class="stat-num"><?= count($critical) ?></div><div class="stat-sub">Below 20 units</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">⚠️ Low Stock</div><div class="stat-num"><?= count($low) ?></div><div class="stat-sub">Below minimum threshold</div></div>
    <div class="stat-crd c-success"><div class="stat-lbl">✅ Normal</div><div class="stat-num"><?= count($normal) ?></div><div class="stat-sub">Above minimum</div></div>
  </div>

  <!-- DIRECT LOG FORM (admin only) -->
  <?php if (!$locked): ?>
  <div class="panel mb-3" id="direct-form" style="display:none">
    <div class="panel-header"><div class="panel-title">⚡ Admin Direct Stock Change</div><span class="panel-action" onclick="toggle('direct-form')">✕</span></div>
    <div class="panel-body">
      <p style="font-size:.835rem;color:var(--warning);margin-bottom:1rem">⚠️ This bypasses the approval workflow. Use only for corrections or emergency adjustments.</p>
      <form method="POST">
        <input type="hidden" name="action" value="direct_log">
        <div class="form-row">
          <div class="form-group"><label>Product <span class="req">*</span></label><select name="product_id" class="form-control"><?php foreach($prods as $p):?><option value="<?=$p['product_id']?>"><?=h($p['product_name'])?> (<?=$p['stock_quantity']?> units)</option><?php endforeach;?></select></div>
          <div class="form-group"><label>Change Type</label><select name="change_type" class="form-control"><option value="add">➕ Add Stock</option><option value="remove">➖ Remove Stock</option><option value="adjustment">🔄 Adjustment</option></select></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Quantity <span class="req">*</span></label><input type="number" name="quantity" class="form-control" min="1" required></div>
          <div class="form-group"><label>Notes</label><input type="text" name="notes" class="form-control" placeholder="Reason for direct change..."></div>
        </div>
        <button type="submit" class="btn btn-warning btn-sm">Apply Direct Change</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- TABS -->
  <div class="tabs">
    <a href="?tab=requests" class="tab <?= $tab==='requests'?'active':'' ?>">
      ⏳ Pending Requests <?php if($pendingReqs):?><span class="nav-badge"><?=count($pendingReqs)?></span><?php endif;?>
    </a>
    <a href="?tab=all_requests" class="tab <?= $tab==='all_requests'?'active':'' ?>">All Requests</a>
    <a href="?tab=monitor" class="tab <?= $tab==='monitor'?'active':'' ?>">📊 Stock Monitor</a>
    <a href="?tab=logs" class="tab <?= $tab==='logs'?'active':'' ?>">📋 Change Log</a>
  </div>

  <!-- PENDING REQUESTS TAB -->
  <?php if ($tab === 'requests'): ?>
  <?php if ($pendingReqs): ?>
  <?php foreach ($pendingReqs as $r):
    $changeClr = $r['change_type']==='add' ? 'var(--success)' : 'var(--danger)';
    $stockAfter = $r['change_type']==='add' ? $r['stock_quantity']+$r['quantity'] : max(0,$r['stock_quantity']-$r['quantity']);
  ?>
  <div class="req-card pending">
    <div class="req-head">
      <div>
        <div class="req-product"><?= h($r['product_name']) ?></div>
        <div class="req-meta">Requested by <strong><?= h($r['requester']) ?></strong> · <?= fmtDateTime($r['requested_at']) ?></div>
      </div>
      <span class="badge badge-pending">Pending Approval</span>
    </div>
    <div class="req-detail">
      <div class="req-chip" style="border-color:<?=$changeClr?>;color:<?=$changeClr?>"><strong><?= $r['change_type']==='add'?'➕ ADD':'➖ REMOVE' ?></strong> <?= $r['quantity'] ?> units</div>
      <div class="req-chip">Current Stock: <strong><?= $r['stock_quantity'] ?></strong></div>
      <div class="req-chip">Stock After: <strong style="color:<?= $stockAfter<20?'var(--danger)':($stockAfter<50?'var(--warning)':'var(--success)') ?>"><?= $stockAfter ?></strong></div>
    </div>
    <?php if ($r['reason']): ?>
    <div class="req-reason">"<?= h($r['reason']) ?>"</div>
    <?php endif; ?>
    <?php if (!$locked): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
      <!-- APPROVE -->
      <form method="POST" class="approve-form" onsubmit="return confirm('Approve this stock change?')">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
        <input type="text" name="review_note" placeholder="Approval note (optional)...">
        <button type="submit" class="btn btn-success btn-sm" style="white-space:nowrap">✅ Approve</button>
      </form>
      <!-- REJECT -->
      <form method="POST" class="approve-form" onsubmit="return confirm('Reject this request?')">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
        <input type="text" name="review_note" placeholder="Rejection reason (required)..." required>
        <button type="submit" class="btn btn-danger btn-sm" style="white-space:nowrap">❌ Reject</button>
      </form>
    </div>
    <?php else: ?>
    <div class="lock-notice">🔒 Feature is locked. Unlock to approve or reject requests.</div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <?php else: ?>
  <div style="text-align:center;padding:3rem;color:var(--muted)">
    <div style="font-size:2.5rem;margin-bottom:1rem">✅</div>
    <p style="font-size:.9rem">No pending stock requests — all clear!</p>
  </div>
  <?php endif; ?>

  <!-- ALL REQUESTS TAB -->
  <?php elseif ($tab === 'all_requests'): ?>
  <div class="panel"><div class="panel-body"><div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>#</th><th>Product</th><th>Type</th><th>Qty</th><th>Requested By</th><th>Reason</th><th>Status</th><th>Reviewed By</th><th>Admin Note</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($allReqs as $r): ?>
      <tr>
        <td style="color:var(--muted)">#<?= $r['request_id'] ?></td>
        <td style="font-size:.845rem"><?= h($r['product_name']) ?></td>
        <td><span class="badge <?= $r['change_type']==='add'?'badge-resolved':'badge-critical' ?>"><?= $r['change_type']==='add'?'➕ Add':'➖ Remove' ?></span></td>
        <td style="font-weight:500"><?= $r['quantity'] ?></td>
        <td style="color:var(--muted);font-size:.8rem"><?= h($r['requester']) ?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h(mb_strimwidth($r['reason'],0,35,'...')) ?></td>
        <td><span class="badge badge-<?= $r['status']==='approved'?'resolved':($r['status']==='rejected'?'critical':'pending') ?>"><?= ucfirst($r['status']) ?></span></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h($r['reviewer']??'—') ?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h(mb_strimwidth($r['review_note']??'—',0,30,'...')) ?></td>
        <td style="color:var(--muted);font-size:.75rem;white-space:nowrap"><?= fmtDate($r['requested_at']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$allReqs): ?><tr><td colspan="10" class="text-center" style="color:var(--muted);padding:2rem">No requests yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div></div>

  <!-- STOCK MONITOR TAB -->
  <?php elseif ($tab === 'monitor'): ?>
  <div class="panel"><div class="panel-header"><div class="panel-title">📊 Live Stock Monitor</div></div>
    <div class="panel-body">
      <div class="inv-monitor">
        <?php foreach ($prods as $p):
          $pct = min(intval($p['stock_quantity']/max($p['min_stock'],1)*100),100);
          $clr = $p['stock_quantity']<20?'var(--danger)':($p['stock_quantity']<$p['min_stock']?'var(--warning)':'var(--success)');
        ?>
        <div class="inv-row">
          <div class="inv-name"><?= h($p['product_name']) ?></div>
          <div style="font-size:.75rem;color:var(--muted);width:100px;text-align:right"><?= h($p['category_name']) ?></div>
          <div class="inv-qty" style="color:<?= $clr ?>"><?= $p['stock_quantity'] ?> / <?= $p['min_stock'] ?></div>
          <div class="inv-bar-wrap" style="width:160px"><div class="inv-bar-inner" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div></div>
          <span class="badge <?= $p['stock_quantity']<20?'badge-critical':($p['stock_quantity']<$p['min_stock']?'badge-pending':'badge-resolved') ?>" style="width:70px;text-align:center"><?= $p['stock_quantity']<20?'Critical':($p['stock_quantity']<$p['min_stock']?'Low':'Normal') ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- CHANGE LOG TAB -->
  <?php elseif ($tab === 'logs'): ?>
  <div class="panel"><div class="panel-body"><div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Time</th><th>Product</th><th>Type</th><th>Qty</th><th>Before</th><th>After</th><th>Notes</th><th>By</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): $c=$l['change_type']==='add'?'var(--success)':'var(--danger)'; ?>
      <tr>
        <td style="color:var(--muted);font-size:.75rem;white-space:nowrap"><?= fmtDateTime($l['logged_at']) ?></td>
        <td style="font-size:.845rem"><?= h($l['product_name']) ?></td>
        <td><span style="color:<?= $c ?>;font-weight:500"><?= $l['change_type']==='add'?'➕':'➖' ?> <?= ucfirst($l['change_type']) ?></span></td>
        <td style="font-weight:500;color:<?= $c ?>"><?= $l['quantity'] ?></td>
        <td style="color:var(--muted)"><?= $l['old_stock'] ?></td>
        <td style="font-weight:500"><?= $l['new_stock'] ?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h(mb_strimwidth($l['notes']??'—',0,40,'...')) ?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h($l['full_name']??'System') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$logs): ?><tr><td colspan="8" class="text-center" style="color:var(--muted);padding:2rem">No logs yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div></div>
  <?php endif; ?>
</main>
<script>function toggle(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'':'none';}</script>
</body></html>
