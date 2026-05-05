<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireStaff();
$prods=db()->query("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id ORDER BY c.category_name,p.product_name")->fetchAll();
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>View Products — Staff Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/staff.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Products (View Only)</h1><p>Product catalog — read-only for staff</p></div></div>
  <div class="panel"><div class="panel-body"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($prods as $p):?>
    <tr>
      <td><div style="width:48px;height:34px;background:var(--bg3);border-radius:var(--r);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.1rem"><?php if($p['image_path']&&file_exists(__DIR__.'/../'.$p['image_path'])):?><img src="../<?=h($p['image_path'])?>" style="width:100%;height:100%;object-fit:cover"><?php else:?><?=$p['icon_emoji']?><?php endif;?></div></td>
      <td><?=h($p['product_name'])?></td>
      <td><span class="badge badge-accent"><?=h($p['category_name'])?></span></td>
      <td style="color:var(--accent)">₱<?=fmtNum($p['price'])?></td>
      <td style="color:<?=$p['stock_quantity']<$p['min_stock']?'var(--warning)':'inherit'?>"><?=$p['stock_quantity']?></td>
      <td><span class="badge <?=$p['stock_quantity']<20?'badge-critical':($p['stock_quantity']<$p['min_stock']?'badge-pending':'badge-resolved')?>"><?=$p['stock_quantity']<20?'Critical':($p['stock_quantity']<$p['min_stock']?'Low':'OK')?></span></td>
    </tr>
    <?php endforeach;?>
    </tbody>
  </table></div></div></div>
</main></body></html>
