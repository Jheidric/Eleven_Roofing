<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();

$totalInq       = db()->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$pending        = db()->query("SELECT COUNT(*) FROM inquiries WHERE status='pending'")->fetchColumn();
$resolved       = db()->query("SELECT COUNT(*) FROM inquiries WHERE status='resolved'")->fetchColumn();
$liveChats      = db()->query("SELECT COUNT(*) FROM chat_sessions WHERE status IN('waiting','active')")->fetchColumn();
$pendingInvReqs = db()->query("SELECT COUNT(*) FROM inventory_requests WHERE status='pending'")->fetchColumn();
$recentInq      = db()->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 6")->fetchAll();
$lowStock       = db()->query("SELECT * FROM products WHERE stock_quantity < min_stock ORDER BY stock_quantity ASC LIMIT 4")->fetchAll();
$criticalStock  = db()->query("SELECT COUNT(*) FROM products WHERE stock_quantity < min_stock")->fetchColumn();
$pendingInvList = db()->query(
    "SELECT ir.*,p.product_name,p.stock_quantity,u.full_name AS requester
     FROM inventory_requests ir
     JOIN products p ON ir.product_id=p.product_id
     JOIN users u ON ir.requested_by=u.user_id
     WHERE ir.status='pending' ORDER BY ir.requested_at ASC LIMIT 4"
)->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body">
<?php include __DIR__.'/partials/sidebar.php'; ?>
<main class="dash-main">
  <div class="topbar">
    <div class="topbar-left"><h1>Admin Dashboard</h1><p>Welcome back, <?= h(explode(' ',$user['name'])[0]) ?> — <?= date('l, F j, Y') ?></p></div>
    <div class="topbar-right">
      <?php if ($pendingInvReqs > 0): ?>
      <a href="inventory.php" class="notif-btn" style="color:var(--warning)">📦 <?= $pendingInvReqs ?> Stock Request<?= $pendingInvReqs>1?'s':'' ?><span class="notif-dot"></span></a>
      <?php endif; ?>
      <?php if ($liveChats > 0): ?>
      <a href="livechat.php" class="notif-btn">🔔 <?= $liveChats ?> Live Chat<?= $liveChats>1?'s':'' ?><span class="notif-dot"></span></a>
      <?php endif; ?>
      <a href="inquiries.php" class="btn btn-primary btn-sm">View Inquiries</a>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-crd c-accent"><div class="stat-lbl">Total Inquiries</div><div class="stat-num"><?= $totalInq ?></div><div class="stat-sub">All time</div><div class="stat-icon">💬</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">Pending Inquiries</div><div class="stat-num"><?= $pending ?></div><div class="stat-sub">Awaiting response</div><div class="stat-icon">⏳</div></div>
    <div class="stat-crd c-<?= $pendingInvReqs>0?'warning':'success' ?>"><div class="stat-lbl">Stock Requests</div><div class="stat-num"><?= $pendingInvReqs ?></div><div class="stat-sub">Awaiting your approval</div><div class="stat-icon">📦</div></div>
    <div class="stat-crd c-info"><div class="stat-lbl">Live Chats</div><div class="stat-num"><?= $liveChats ?></div><div class="stat-sub">Waiting / Active</div><div class="stat-icon">💬</div></div>
  </div>

  <?php if ($pendingInvList): ?>
  <div class="panel mb-3" style="border-color:rgba(232,168,64,.4)">
    <div class="panel-header" style="background:var(--warning-dim)">
      <div class="panel-title">⏳ Pending Stock Requests — Action Required</div>
      <a href="inventory.php" class="panel-action">Review All →</a>
    </div>
    <div class="panel-body" style="padding:0">
      <?php foreach ($pendingInvList as $r):
        $after = $r['change_type']==='add' ? $r['stock_quantity']+$r['quantity'] : max(0,$r['stock_quantity']-$r['quantity']);
      ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:1px solid var(--border)">
        <div>
          <div style="font-size:.875rem;font-weight:500"><?= h($r['product_name']) ?></div>
          <div style="font-size:.77rem;color:var(--muted)">
            <?= $r['change_type']==='add'?'<span style="color:var(--success)">➕ Add':'<span style="color:var(--danger)">➖ Remove' ?> <?= $r['quantity'] ?> units</span>
            · by <?= h($r['requester']) ?>
            · <?= fmtDateTime($r['requested_at']) ?>
          </div>
          <?php if ($r['reason']): ?><div style="font-size:.77rem;color:var(--muted);font-style:italic;margin-top:.15rem">"<?= h(mb_strimwidth($r['reason'],0,60,'...')) ?>"</div><?php endif; ?>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;flex-shrink:0;margin-left:1rem">
          <div style="text-align:center;margin-right:.5rem">
            <div style="font-size:.7rem;color:var(--muted)">After</div>
            <div style="font-family:var(--serif);font-size:1.05rem;color:<?= $after<20?'var(--danger)':($after<50?'var(--warning)':'var(--success)') ?>"><?= $after ?></div>
          </div>
          <a href="inventory.php?tab=requests" class="btn btn-success btn-sm">Review</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (count($pendingInvList) < $pendingInvReqs): ?>
      <div style="padding:.75rem 1.25rem;text-align:center;font-size:.83rem;color:var(--muted)">
        + <?= $pendingInvReqs - count($pendingInvList) ?> more · <a href="inventory.php?tab=requests" style="color:var(--accent)">View all →</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="grid-2col">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">Recent Inquiries</div><a href="inquiries.php" class="panel-action">View all →</a></div>
      <div class="panel-body"><div class="table-wrap"><table class="data-table">
        <thead><tr><th>#</th><th>Customer</th><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentInq as $inq): ?>
        <tr>
          <td style="color:var(--muted)">#<?= $inq['inquiry_id'] ?></td>
          <td><?= h($inq['first_name'].' '.$inq['last_name']) ?></td>
          <td><?= h(mb_strimwidth($inq['subject'],0,32,'...')) ?></td>
          <td><span class="badge badge-<?= $inq['status']==='in_progress'?'progress':$inq['status'] ?>"><?= fmtStatus($inq['status']) ?></span></td>
          <td style="color:var(--muted);font-size:.78rem"><?= fmtDate($inq['created_at']) ?></td>
          <td><a href="inquiries.php?reply=<?= $inq['inquiry_id'] ?>" class="btn btn-outline btn-sm">Reply</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recentInq): ?><tr><td colspan="6" class="text-center" style="color:var(--muted);padding:1.5rem">No inquiries yet</td></tr><?php endif; ?>
        </tbody>
      </table></div></div>
    </div>
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="panel">
        <div class="panel-header"><div class="panel-title">📊 Inquiries (7 Days)</div></div>
        <div class="panel-body"><div class="bar-chart" id="bar-chart"></div></div>
      </div>
      <div class="panel">
        <div class="panel-header"><div class="panel-title">⚠️ Low Stock Monitor</div><a href="inventory.php?tab=monitor" class="panel-action">Full view →</a></div>
        <div class="panel-body">
          <?php if ($lowStock): ?>
          <div class="inv-monitor">
            <?php foreach ($lowStock as $p):
              $pct = min(intval($p['stock_quantity']/max($p['min_stock'],1)*100),100);
              $clr = $p['stock_quantity']<20?'var(--danger)':($p['stock_quantity']<$p['min_stock']?'var(--warning)':'var(--success)');
            ?>
            <div class="inv-row">
              <div class="inv-name"><?= h(mb_strimwidth($p['product_name'],0,26,'...')) ?></div>
              <div class="inv-qty" style="color:<?= $clr ?>"><?= $p['stock_quantity'] ?></div>
              <div class="inv-bar-wrap"><div class="inv-bar-inner" style="width:<?= $pct ?>%;background:<?= $clr ?>"></div></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p style="color:var(--muted);font-size:.845rem">All stock levels normal ✅</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
<script>
const days=['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
const vals=[4,8,11,6,9,13,<?= min($totalInq,16) ?>];
const mx=Math.max(...vals);
const ch=document.getElementById('bar-chart');
days.forEach((d,i)=>{
  const col=document.createElement('div'); col.className='bar-col';
  const b=document.createElement('div'); b.className='bar-fill';
  b.style.height=Math.round((vals[i]/mx)*80)+'px'; b.title=d+': '+vals[i];
  const l=document.createElement('div'); l.className='bar-lbl'; l.textContent=d;
  col.appendChild(b); col.appendChild(l); ch.appendChild(col);
});
</script>
</body></html>
