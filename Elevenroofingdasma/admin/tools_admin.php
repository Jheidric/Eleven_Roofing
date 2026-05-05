<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'borrow') {
        $tid     = (int)$_POST['tool_id'];
        $by      = trim($_POST['borrowed_by'] ?? '');
        $qty     = (int)$_POST['quantity'];
        $ret     = $_POST['expected_return'] ?? '';
        $condOut = trim($_POST['condition_out'] ?? 'Good');
        if ($by && $qty > 0) {
            db()->prepare("INSERT INTO borrowed_tools (tool_id,borrowed_by,quantity,borrow_date,expected_return,condition_out,recorded_by) VALUES (?,?,?,CURDATE(),?,?,?)")
               ->execute([$tid, $by, $qty, $ret ?: null, $condOut, $user['id']]);
            db()->prepare("UPDATE tools SET available=GREATEST(0,available-?) WHERE tool_id=?")->execute([$qty,$tid]);
            logActivity($user['id'], "Recorded borrow: $by took $qty × tool #$tid", 'Tools');
        }
        header('Location: tools_admin.php?msg=borrowed'); exit;
    } elseif ($act === 'return') {
        $bid     = (int)$_POST['borrow_id'];
        $condIn  = trim($_POST['condition_in'] ?? 'Good');
        $r = db()->prepare("SELECT tool_id,quantity FROM borrowed_tools WHERE borrow_id=?"); $r->execute([$bid]); $br = $r->fetch();
        db()->prepare("UPDATE borrowed_tools SET status='returned',return_date=CURDATE(),condition_in=? WHERE borrow_id=?")->execute([$condIn,$bid]);
        if ($br) db()->prepare("UPDATE tools SET available=available+? WHERE tool_id=?")->execute([$br['quantity'],$br['tool_id']]);
        logActivity($user['id'], "Marked returned: borrow #$bid", 'Tools');
        header('Location: tools_admin.php?msg=returned'); exit;
    } elseif ($act === 'add_tool') {
        $name = trim($_POST['tool_name'] ?? '');
        $qty  = (int)$_POST['quantity'];
        if ($name && $qty > 0) {
            db()->prepare("INSERT INTO tools (tool_name,quantity,available) VALUES (?,?,?)")->execute([$name,$qty,$qty]);
            logActivity($user['id'], "Added tool: $name (qty $qty)", 'Tools');
        }
        header('Location: tools_admin.php?msg=added'); exit;
    } elseif ($act === 'mark_overdue') {
        db()->exec("UPDATE borrowed_tools SET status='overdue' WHERE status='borrowed' AND expected_return < CURDATE()");
        header('Location: tools_admin.php?msg=overdue_updated'); exit;
    }
}

$tools   = db()->query("SELECT * FROM tools ORDER BY tool_name")->fetchAll();
$records = db()->query(
    "SELECT bt.*,t.tool_name FROM borrowed_tools bt JOIN tools t ON bt.tool_id=t.tool_id ORDER BY bt.created_at DESC"
)->fetchAll();
$overdue = array_filter($records, fn($r) => $r['status']==='borrowed' && $r['expected_return'] && strtotime($r['expected_return']) < time());
$borrowed = array_filter($records, fn($r) => $r['status']==='borrowed');
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Borrowed Tools — ELeven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body">
<?php include __DIR__.'/partials/sidebar.php'; ?>
<main class="dash-main">
  <div class="topbar">
    <div class="topbar-left"><h1>Borrowed Tools</h1><p>Track equipment borrowing and returns</p></div>
    <div class="topbar-right">
      <?php if ($overdue): ?>
      <form method="POST" style="display:inline"><input type="hidden" name="action" value="mark_overdue"><button type="submit" class="btn btn-warning btn-sm">⚠️ Mark Overdue</button></form>
      <?php endif; ?>
      <button class="btn btn-primary btn-sm" onclick="toggle('borrow-form')">+ Record Borrow</button>
      <button class="btn btn-outline btn-sm" onclick="toggle('add-tool-form')">+ Add Tool</button>
    </div>
  </div>

  <?php if (isset($_GET['msg'])): ?>
  <?php $msgs=['borrowed'=>'Borrow recorded!','returned'=>'Marked as returned!','added'=>'Tool added!','overdue_updated'=>'Overdue items updated.']; ?>
  <div class="alert alert-success show mb-3"><?= $msgs[$_GET['msg']] ?? '' ?></div>
  <?php endif; ?>

  <?php if ($overdue): ?>
  <div class="alert alert-error show mb-3">⚠️ <strong><?= count($overdue) ?> borrow<?= count($overdue)>1?'s':'' ?> are overdue</strong> — return dates have passed.</div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-grid mb-3">
    <div class="stat-crd c-info"><div class="stat-lbl">Total Tools</div><div class="stat-num"><?= count($tools) ?></div><div class="stat-sub">Types tracked</div></div>
    <div class="stat-crd c-warning"><div class="stat-lbl">Currently Borrowed</div><div class="stat-num"><?= count($borrowed) ?></div><div class="stat-sub">Out with staff</div></div>
    <div class="stat-crd c-<?= $overdue?'danger':'success' ?>"><div class="stat-lbl">Overdue</div><div class="stat-num"><?= count($overdue) ?></div><div class="stat-sub">Past return date</div></div>
    <div class="stat-crd c-success"><div class="stat-lbl">Total Records</div><div class="stat-num"><?= count($records) ?></div><div class="stat-sub">All time borrows</div></div>
  </div>

  <!-- ADD TOOL FORM -->
  <div class="panel mb-3" id="add-tool-form" style="display:none">
    <div class="panel-header"><div class="panel-title">Add New Tool</div><span class="panel-action" onclick="toggle('add-tool-form')">✕</span></div>
    <div class="panel-body">
      <form method="POST">
        <input type="hidden" name="action" value="add_tool">
        <div class="form-row">
          <div class="form-group"><label>Tool Name <span class="req">*</span></label><input type="text" name="tool_name" class="form-control" placeholder="e.g. Power Drill" required></div>
          <div class="form-group"><label>Quantity <span class="req">*</span></label><input type="number" name="quantity" class="form-control" min="1" value="1" required></div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Add Tool</button>
      </form>
    </div>
  </div>

  <!-- BORROW FORM -->
  <div class="panel mb-3" id="borrow-form" style="display:none">
    <div class="panel-header"><div class="panel-title">Record Tool Borrow</div><span class="panel-action" onclick="toggle('borrow-form')">✕</span></div>
    <div class="panel-body">
      <form method="POST">
        <input type="hidden" name="action" value="borrow">
        <div class="form-row">
          <div class="form-group"><label>Tool <span class="req">*</span></label>
            <select name="tool_id" class="form-control">
              <?php foreach ($tools as $t): ?>
              <option value="<?= $t['tool_id'] ?>"><?= h($t['tool_name']) ?> (<?= $t['available'] ?>/<?= $t['quantity'] ?> available)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Borrowed By <span class="req">*</span></label><input type="text" name="borrowed_by" class="form-control" placeholder="Staff name" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Quantity</label><input type="number" name="quantity" class="form-control" value="1" min="1"></div>
          <div class="form-group"><label>Expected Return</label><input type="date" name="expected_return" class="form-control" value="<?= date('Y-m-d',strtotime('+3 days')) ?>"></div>
        </div>
        <div class="form-group"><label>Condition Out</label>
          <select name="condition_out" class="form-control">
            <?php foreach (['Good','Fair','Needs Attention'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Record Borrow</button>
      </form>
    </div>
  </div>

  <!-- TOOL AVAILABILITY TABLE -->
  <div class="panel mb-3">
    <div class="panel-header"><div class="panel-title">Tool Availability</div></div>
    <div class="panel-body"><div class="table-wrap"><table class="data-table">
      <thead><tr><th>Tool Name</th><th>Total</th><th>Available</th><th>In Use</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($tools as $t): ?>
      <tr>
        <td style="font-weight:500"><?= h($t['tool_name']) ?></td>
        <td><?= $t['quantity'] ?></td>
        <td style="color:<?= $t['available']>0?'var(--success)':'var(--danger)' ?>;font-weight:500"><?= $t['available'] ?></td>
        <td style="color:var(--muted)"><?= $t['quantity'] - $t['available'] ?></td>
        <td><span class="badge <?= $t['available']>0?'badge-resolved':'badge-critical' ?>"><?= $t['available']>0?'Available':'All Out' ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div></div>
  </div>

  <!-- ALL BORROW RECORDS -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">Borrow Records</div></div>
    <div class="panel-body"><div class="table-wrap"><table class="data-table">
      <thead><tr><th>#</th><th>Tool</th><th>Borrowed By</th><th>Qty</th><th>Borrow Date</th><th>Expected Return</th><th>Cond. Out</th><th>Status</th><th>Cond. In</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($records as $r):
        $isOverdue = $r['status']==='borrowed' && $r['expected_return'] && strtotime($r['expected_return']) < time();
      ?>
      <tr>
        <td style="color:var(--muted)">#<?= $r['borrow_id'] ?></td>
        <td><?= h($r['tool_name']) ?></td>
        <td><?= h($r['borrowed_by']) ?></td>
        <td><?= $r['quantity'] ?></td>
        <td style="color:var(--muted);font-size:.78rem"><?= $r['borrow_date'] ?></td>
        <td style="color:<?= $isOverdue?'var(--danger)':'var(--muted)' ?>;font-size:.78rem"><?= $r['expected_return']??'—' ?><?= $isOverdue?' ⚠️':'' ?></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= h($r['condition_out']??'Good') ?></td>
        <td><span class="badge badge-<?= $isOverdue?'critical':($r['status']==='returned'?'returned':'borrowed') ?>"><?= $isOverdue?'Overdue':ucfirst($r['status']) ?></span></td>
        <td style="font-size:.78rem;color:var(--muted)"><?= $r['condition_in'] ? h($r['condition_in']) : '—' ?></td>
        <td>
          <?php if ($r['status'] !== 'returned'): ?>
          <form method="POST" style="display:flex;gap:.35rem;align-items:center">
            <input type="hidden" name="action" value="return">
            <input type="hidden" name="borrow_id" value="<?= $r['borrow_id'] ?>">
            <select name="condition_in" class="form-control" style="padding:.32rem .6rem;font-size:.77rem;width:auto">
              <?php foreach (['Good','Fair','Damaged','Needs Repair'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-success btn-sm" style="white-space:nowrap">Return</button>
          </form>
          <?php else: ?>
          <span style="color:var(--muted);font-size:.78rem">✅ Returned <?= $r['return_date']??'' ?></span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$records): ?><tr><td colspan="10" class="text-center" style="color:var(--muted);padding:2rem">No records yet</td></tr><?php endif; ?>
      </tbody>
    </table></div></div>
  </div>
</main>
<script>function toggle(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'':'none';}</script>
</body></html>
