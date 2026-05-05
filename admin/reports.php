<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();
$type=$_GET['type']??'';
$data=[];
if ($type) {
    if ($type==='inquiries') $data=db()->query("SELECT * FROM inquiries ORDER BY created_at DESC")->fetchAll();
    elseif ($type==='inventory') $data=db()->query("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id ORDER BY p.stock_quantity ASC")->fetchAll();
    elseif ($type==='products') $data=db()->query("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id ORDER BY c.category_name")->fetchAll();
    elseif ($type==='tools') $data=db()->query("SELECT bt.*,t.tool_name FROM borrowed_tools bt JOIN tools t ON bt.tool_id=t.tool_id ORDER BY bt.created_at DESC")->fetchAll();
    if ($data) db()->prepare("INSERT INTO reports (report_type,generated_by) VALUES (?,?)")->execute([$type,$user['id']]);
}
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Reports — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
<style>@media print{.dash-sidebar,.topbar,.report-btns{display:none!important}.dash-main{margin:0!important;padding:1rem!important}}</style>
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Reports</h1><p>Generate and print system reports — <?=date('F j, Y')?></p></div>
    <?php if($type&&$data):?><button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print Report</button><?php endif;?>
  </div>
  <div class="report-btns" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
    <?php foreach([['inquiries','💬','Inquiry Report'],['inventory','🗂️','Inventory Report'],['products','📦','Product Report'],['tools','🔧','Tools Report']] as [$t,$i,$lbl]):?>
    <a href="?type=<?=$t?>" class="panel" style="cursor:pointer;text-decoration:none;transition:border-color .2s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor=''">
      <div class="panel-body" style="text-align:center;padding:1.75rem">
        <div style="font-size:2rem;margin-bottom:.75rem"><?=$i?></div>
        <div style="font-family:var(--serif);font-size:1rem;margin-bottom:.75rem"><?=$lbl?></div>
        <span class="btn btn-primary btn-sm">Generate</span>
      </div>
    </a>
    <?php endforeach;?>
  </div>
  <?php if($type&&$data):?>
  <div class="panel">
    <div class="panel-header"><div class="panel-title"><?=ucfirst($type)?> Report — <?=date('F j, Y')?> — <?=count($data)?> records</div></div>
    <div class="panel-body"><div class="table-wrap">
      <?php if($type==='inquiries'):?>
      <table class="data-table"><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Service</th><th>Status</th><th>Date</th></tr></thead><tbody>
      <?php foreach($data as $r):?><tr><td>#<?=$r['inquiry_id']?></td><td><?=h($r['first_name'].' '.$r['last_name'])?></td><td style="font-size:.78rem"><?=h($r['email'])?></td><td><?=h(mb_strimwidth($r['subject'],0,30,'...'))?></td><td><?=h($r['service_type'])?></td><td><span class="badge badge-<?=$r['status']==='in_progress'?'progress':$r['status']?>"><?=fmtStatus($r['status'])?></span></td><td><?=fmtDate($r['created_at'])?></td></tr><?php endforeach;?>
      </tbody></table>
      <?php elseif(in_array($type,['inventory','products'])):?>
      <table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Min Stock</th><th>Status</th></tr></thead><tbody>
      <?php foreach($data as $r): $ok=$r['stock_quantity']>=$r['min_stock']; $crit=$r['stock_quantity']<20;?><tr><td><?=h($r['product_name'])?></td><td><?=h($r['category_name'])?></td><td>₱<?=fmtNum($r['price'])?></td><td style="color:<?=$crit?'var(--danger)':(!$ok?'var(--warning)':'inherit')?>"><?=$r['stock_quantity']?></td><td><?=$r['min_stock']?></td><td><span class="badge <?=$crit?'badge-critical':(!$ok?'badge-pending':'badge-resolved')?>"><?=$crit?'Critical':(!$ok?'Low':'Normal')?></span></td></tr><?php endforeach;?>
      </tbody></table>
      <?php elseif($type==='tools'):?>
      <table class="data-table"><thead><tr><th>#</th><th>Tool</th><th>Borrowed By</th><th>Qty</th><th>Borrow Date</th><th>Return Date</th><th>Status</th></tr></thead><tbody>
      <?php foreach($data as $r):?><tr><td>#<?=$r['borrow_id']?></td><td><?=h($r['tool_name'])?></td><td><?=h($r['borrowed_by'])?></td><td><?=$r['quantity']?></td><td><?=$r['borrow_date']?></td><td><?=$r['return_date']??'-'?></td><td><span class="badge badge-<?=$r['status']?>"><?=ucfirst($r['status'])?></span></td></tr><?php endforeach;?>
      </tbody></table>
      <?php endif;?>
    </div></div>
  </div>
  <?php elseif($type):?><div class="alert alert-warning show">No data found for this report.</div><?php endif;?>
</main></body></html>
