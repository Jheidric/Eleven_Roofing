<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin();
$locked = isLocked('services');
if ($_SERVER['REQUEST_METHOD']==='POST' && !$locked) {
    $act=$_POST['action']??'';
    $imgPath=handleImageUpload('image',__DIR__.'/../assets/images/uploads/','svc_');
    if ($act==='add') {
        db()->prepare("INSERT INTO services (service_name,description,category,price_from,duration,image_path) VALUES (?,?,?,?,?,?)")
           ->execute([trim($_POST['service_name']),trim($_POST['description']),trim($_POST['category']),(float)$_POST['price_from'],trim($_POST['duration']),$imgPath?:null]);
        logActivity($user['id'],'Added service: '.trim($_POST['service_name']),'Services');
    } elseif ($act==='edit') {
        $id=(int)$_POST['service_id'];
        $cur=db()->prepare("SELECT image_path FROM services WHERE service_id=?"); $cur->execute([$id]); $old=$cur->fetchColumn();
        db()->prepare("UPDATE services SET service_name=?,description=?,category=?,price_from=?,duration=?,image_path=? WHERE service_id=?")
           ->execute([trim($_POST['service_name']),trim($_POST['description']),trim($_POST['category']),(float)$_POST['price_from'],trim($_POST['duration']),$imgPath?:$old,$id]);
        logActivity($user['id'],"Edited service #$id",'Services');
    } elseif ($act==='delete') {
        $id=(int)$_POST['service_id'];
        $r=db()->prepare("SELECT image_path FROM services WHERE service_id=?"); $r->execute([$id]); $p=$r->fetchColumn();
        if($p&&file_exists(__DIR__.'/../'.$p)) @unlink(__DIR__.'/../'.$p);
        db()->prepare("DELETE FROM services WHERE service_id=?")->execute([$id]);
        logActivity($user['id'],"Deleted service #$id",'Services');
    }
    header('Location: services.php?msg=Saved'); exit;
}
$svcs=db()->query("SELECT * FROM services ORDER BY service_id")->fetchAll();
$edit=null; if(isset($_GET['edit'])){$e=db()->prepare("SELECT * FROM services WHERE service_id=?");$e->execute([(int)$_GET['edit']]);$edit=$e->fetch();}
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Services — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Service Management</h1><p>Add, edit, and manage roofing services</p></div>
    <?php if(!$locked):?><button class="btn btn-primary btn-sm" onclick="toggle('svc-form')">+ Add Service</button><?php endif;?>
  </div>
  <?php if($locked): echo renderLockBadge('services'); endif;?>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-2">Saved successfully!</div><?php endif;?>
  <div class="panel mb-3" id="svc-form" style="<?=$edit?'':'display:none'?>">
    <div class="panel-header"><div class="panel-title"><?=$edit?'Edit':'Add'?> Service</div><span class="panel-action" onclick="toggle('svc-form')">✕</span></div>
    <div class="panel-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
        <?php if($edit):?><input type="hidden" name="service_id" value="<?=$edit['service_id']?>"><?php endif;?>
        <div class="form-row">
          <div class="form-group"><label>Service Name <span class="req">*</span></label><input type="text" name="service_name" class="form-control" value="<?=h($edit['service_name']??'')?>" required></div>
          <div class="form-group"><label>Category</label><select name="category" class="form-control"><?php foreach(['Installation','Repair','Maintenance','Inspection'] as $c):?><option value="<?=$c?>" <?=($edit['category']??'')===$c?'selected':''?>><?=$c?></option><?php endforeach;?></select></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Price From (₱)</label><input type="number" name="price_from" class="form-control" step="0.01" value="<?=h($edit['price_from']??'')?>"></div>
          <div class="form-group"><label>Duration</label><input type="text" name="duration" class="form-control" value="<?=h($edit['duration']??'')?>"></div>
        </div>
        <div class="form-group"><label>Description</label><textarea name="description" class="form-control"><?=h($edit['description']??'')?></textarea></div>
        <div class="form-group"><label>Image</label>
          <div class="img-upload"><input type="file" name="image" accept="image/*" onchange="prevImg(this,'sp')"><div class="img-upload-icon">🖼️</div><p>Click to upload (JPG/PNG, max 5MB)</p></div>
          <?php if($edit&&$edit['image_path']&&file_exists(__DIR__.'/../'.$edit['image_path'])):?><img src="../<?=h($edit['image_path'])?>" class="img-preview show" id="sp"><?php else:?><img class="img-preview" id="sp"><?php endif;?>
        </div>
        <div style="display:flex;gap:.75rem">
          <button type="submit" class="btn btn-primary btn-sm"><?=$edit?'Update':'Add'?> Service</button>
          <?php if($edit):?><a href="services.php" class="btn btn-outline btn-sm">Cancel</a><?php endif;?>
        </div>
      </form>
    </div>
  </div>
  <div class="panel"><div class="panel-body"><div class="table-wrap"><table class="data-table">
    <thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Duration</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($svcs as $s):?>
    <tr>
      <td style="color:var(--muted)">#<?=$s['service_id']?></td>
      <td><div style="width:52px;height:36px;background:var(--bg3);border-radius:var(--r);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.2rem"><?php if($s['image_path']&&file_exists(__DIR__.'/../'.$s['image_path'])):?><img src="../<?=h($s['image_path'])?>" style="width:100%;height:100%;object-fit:cover"><?php else:?>🏗️<?php endif;?></div></td>
      <td><?=h($s['service_name'])?></td>
      <td><span class="badge badge-accent"><?=h($s['category'])?></span></td>
      <td style="color:var(--accent)">₱<?=fmtNum($s['price_from'])?></td>
      <td style="color:var(--muted)"><?=h($s['duration'])?></td>
      <td style="display:flex;gap:.4rem">
        <?php if(!$locked):?>
        <a href="?edit=<?=$s['service_id']?>" class="btn btn-outline btn-sm">Edit</a>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="service_id" value="<?=$s['service_id']?>"><button type="submit" class="btn btn-danger btn-sm">Del</button></form>
        <?php else:?><span style="color:var(--muted);font-size:.78rem">🔒 Locked</span><?php endif;?>
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
