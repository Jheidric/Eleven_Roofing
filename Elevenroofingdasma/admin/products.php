<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin(); $locked = isLocked('products');
if ($_SERVER['REQUEST_METHOD']==='POST' && !$locked) {
    $act=$_POST['action']??''; $img=handleImageUpload('image',__DIR__.'/../assets/images/uploads/','prod_');
    $n=trim($_POST['product_name']??''); $cat=(int)$_POST['category_id']; $pr=(float)$_POST['price']; $st=(int)$_POST['stock_quantity']; $mn=(int)$_POST['min_stock']; $ic=trim($_POST['icon_emoji']??'📦'); $desc=trim($_POST['description']??'');
    if ($act==='add') {
        db()->prepare("INSERT INTO products (category_id,product_name,description,price,stock_quantity,min_stock,icon_emoji,image_path) VALUES (?,?,?,?,?,?,?,?)")->execute([$cat,$n,$desc,$pr,$st,$mn,$ic,$img?:null]);
        logActivity($user['id'],"Added product: $n",'Products');
    } elseif ($act==='edit') {
        $id=(int)$_POST['product_id']; $cur=db()->prepare("SELECT image_path FROM products WHERE product_id=?"); $cur->execute([$id]); $old=$cur->fetchColumn();
        db()->prepare("UPDATE products SET category_id=?,product_name=?,description=?,price=?,stock_quantity=?,min_stock=?,icon_emoji=?,image_path=? WHERE product_id=?")->execute([$cat,$n,$desc,$pr,$st,$mn,$ic,$img?:$old,$id]);
        logActivity($user['id'],"Edited product #$id",'Products');
    } elseif ($act==='delete') {
        $id=(int)$_POST['product_id']; $r=db()->prepare("SELECT image_path FROM products WHERE product_id=?"); $r->execute([$id]); $p=$r->fetchColumn();
        if($p&&file_exists(__DIR__.'/../'.$p)) @unlink(__DIR__.'/../'.$p);
        db()->prepare("DELETE FROM products WHERE product_id=?")->execute([$id]);
    }
    header('Location: products.php?msg=1'); exit;
}
$cats=db()->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
$prods=db()->query("SELECT p.*,c.category_name FROM products p JOIN categories c ON p.category_id=c.category_id ORDER BY c.category_name,p.product_name")->fetchAll();
$edit=null; if(isset($_GET['edit'])){$e=db()->prepare("SELECT * FROM products WHERE product_id=?");$e->execute([(int)$_GET['edit']]);$edit=$e->fetch();}
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Products — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Product Management</h1><p>Manage the product catalog with images</p></div>
    <?php if(!$locked):?><button class="btn btn-primary btn-sm" onclick="toggle('pform')">+ Add Product</button><?php endif;?>
  </div>
  <?php if($locked): echo renderLockBadge('products'); endif;?>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-2">Saved!</div><?php endif;?>
  <div class="panel mb-3" id="pform" style="<?=$edit?'':'display:none'?>">
    <div class="panel-header"><div class="panel-title"><?=$edit?'Edit':'Add'?> Product</div><span class="panel-action" onclick="toggle('pform')">✕</span></div>
    <div class="panel-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
        <?php if($edit):?><input type="hidden" name="product_id" value="<?=$edit['product_id']?>"><?php endif;?>
        <div class="form-row">
          <div class="form-group"><label>Product Name <span class="req">*</span></label><input type="text" name="product_name" class="form-control" value="<?=h($edit['product_name']??'')?>" required></div>
          <div class="form-group"><label>Category</label><select name="category_id" class="form-control"><?php foreach($cats as $c):?><option value="<?=$c['category_id']?>" <?=($edit['category_id']??'')==$c['category_id']?'selected':''?>><?=h($c['category_name'])?></option><?php endforeach;?></select></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Price (₱) <span class="req">*</span></label><input type="number" name="price" class="form-control" step="0.01" value="<?=h($edit['price']??'')?>" required></div>
          <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock_quantity" class="form-control" value="<?=h($edit['stock_quantity']??'0')?>" min="0"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Min Stock Alert</label><input type="number" name="min_stock" class="form-control" value="<?=h($edit['min_stock']??'50')?>" min="0"></div>
          <div class="form-group"><label>Icon Emoji (fallback)</label><input type="text" name="icon_emoji" class="form-control" value="<?=h($edit['icon_emoji']??'📦')?>"></div>
        </div>
        <div class="form-group"><label>Description</label><input type="text" name="description" class="form-control" value="<?=h($edit['description']??'')?>"></div>
        <div class="form-group"><label>Product Image</label>
          <div class="img-upload"><input type="file" name="image" accept="image/*" onchange="prevImg(this,'pp')"><div class="img-upload-icon">📷</div><p>Click to upload (max 5MB)</p></div>
          <?php if($edit&&$edit['image_path']&&file_exists(__DIR__.'/../'.$edit['image_path'])):?><img src="../<?=h($edit['image_path'])?>" class="img-preview show" id="pp"><?php else:?><img class="img-preview" id="pp"><?php endif;?>
        </div>
        <div style="display:flex;gap:.75rem">
          <button type="submit" class="btn btn-primary btn-sm"><?=$edit?'Update':'Add'?></button>
          <?php if($edit):?><a href="products.php" class="btn btn-outline btn-sm">Cancel</a><?php endif;?>
        </div>
      </form>
    </div>
  </div>
  <div class="panel"><div class="panel-body"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>ID</th><th>Img</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Min</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($prods as $p): $ok=$p['stock_quantity']>=$p['min_stock']; $crit=$p['stock_quantity']<20; ?>
    <tr>
      <td style="color:var(--muted)">#<?=$p['product_id']?></td>
      <td><div style="width:48px;height:34px;background:var(--bg3);border-radius:var(--r);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.1rem"><?php if($p['image_path']&&file_exists(__DIR__.'/../'.$p['image_path'])):?><img src="../<?=h($p['image_path'])?>" style="width:100%;height:100%;object-fit:cover"><?php else:?><?=$p['icon_emoji']?><?php endif;?></div></td>
      <td><?=h($p['product_name'])?></td><td><span class="badge badge-accent"><?=h($p['category_name'])?></span></td>
      <td style="color:var(--accent)">₱<?=fmtNum($p['price'])?></td>
      <td style="color:<?=$crit?'var(--danger)':(!$ok?'var(--warning)':'inherit')?>;font-weight:500"><?=$p['stock_quantity']?></td>
      <td style="color:var(--muted)"><?=$p['min_stock']?></td>
      <td><span class="badge <?=$crit?'badge-critical':(!$ok?'badge-pending':'badge-resolved')?>"><?=$crit?'Critical':(!$ok?'Low':'OK')?></span></td>
      <td style="display:flex;gap:.4rem">
        <?php if(!$locked):?>
        <a href="?edit=<?=$p['product_id']?>" class="btn btn-outline btn-sm">Edit</a>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="product_id" value="<?=$p['product_id']?>"><button type="submit" class="btn btn-danger btn-sm">Del</button></form>
        <?php else:?><span style="color:var(--muted);font-size:.78rem">🔒</span><?php endif;?>
      </td>
    </tr>
    <?php endforeach;?>
    </tbody>
  </table></div></div></div>
</main>
<script>
function toggle(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'':'none';}
function prevImg(i,id){const r=new FileReader();r.onload=e=>{const x=document.getElementById(id);x.src=e.target.result;x.classList.add('show')};r.readAsDataURL(i.files[0])}
</script>
</body></html>
