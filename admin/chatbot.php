<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin(); $locked = isLocked('chatbot');
if ($_SERVER['REQUEST_METHOD']==='POST' && !$locked) {
    $act=$_POST['action']??'';
    if ($act==='add') { db()->prepare("INSERT INTO chatbot_qa (category,question,answer,created_by) VALUES (?,?,?,?)")->execute([trim($_POST['category']),trim($_POST['question']),trim($_POST['answer']),$user['id']]); logActivity($user['id'],'Added chatbot Q&A','Chatbot'); }
    elseif ($act==='edit') { db()->prepare("UPDATE chatbot_qa SET category=?,question=?,answer=? WHERE qa_id=?")->execute([trim($_POST['category']),trim($_POST['question']),trim($_POST['answer']),(int)$_POST['qa_id']]); }
    elseif ($act==='delete') { db()->prepare("DELETE FROM chatbot_qa WHERE qa_id=?")->execute([(int)$_POST['qa_id']]); }
    header('Location: chatbot.php?msg=1'); exit;
}
$qa=db()->query("SELECT * FROM chatbot_qa ORDER BY category,qa_id")->fetchAll();
$edit=null; if(isset($_GET['edit'])){$e=db()->prepare("SELECT * FROM chatbot_qa WHERE qa_id=?");$e->execute([(int)$_GET['edit']]);$edit=$e->fetch();}
$cats=['Services','Pricing','Process','Emergency','Products','Warranty','Contact','General'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Chatbot Q&A — Eleven Roofing Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Chatbot Q&amp;A Manager</h1><p>Edit questions &amp; answers customers see in the chatbot</p></div>
    <?php if(!$locked):?><button class="btn btn-primary btn-sm" onclick="toggle('qaform')">+ Add Q&amp;A</button><?php endif;?>
  </div>
  <?php if($locked): echo renderLockBadge('chatbot'); endif;?>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-2">Saved!</div><?php endif;?>
  <div style="display:grid;grid-template-columns:1fr 280px;gap:1.35rem">
    <div>
      <div class="panel mb-3" id="qaform" style="<?=$edit?'':'display:none'?>">
        <div class="panel-header"><div class="panel-title"><?=$edit?'Edit':'Add'?> Q&amp;A</div><span class="panel-action" onclick="toggle('qaform')">✕</span></div>
        <div class="panel-body">
          <form method="POST">
            <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
            <?php if($edit):?><input type="hidden" name="qa_id" value="<?=$edit['qa_id']?>"><?php endif;?>
            <div class="form-group"><label>Category</label><select name="category" class="form-control"><?php foreach($cats as $c):?><option value="<?=$c?>" <?=($edit['category']??'')===$c?'selected':''?>><?=$c?></option><?php endforeach;?></select></div>
            <div class="form-group"><label>Question <span class="req">*</span></label><input type="text" name="question" class="form-control" value="<?=h($edit['question']??'')?>" required></div>
            <div class="form-group"><label>Answer <span class="req">*</span></label><textarea name="answer" class="form-control" style="min-height:100px" required><?=h($edit['answer']??'')?></textarea></div>
            <div style="display:flex;gap:.75rem">
              <button type="submit" class="btn btn-primary btn-sm"><?=$edit?'Update':'Save'?></button>
              <?php if($edit):?><a href="chatbot.php" class="btn btn-outline btn-sm">Cancel</a><?php endif;?>
            </div>
          </form>
        </div>
      </div>
      <div class="panel">
        <div class="panel-header"><div class="panel-title">Q&amp;A Bank (<?=count($qa)?>)</div>
          <input type="text" class="form-control" style="width:180px;padding:.38rem .75rem;font-size:.79rem" placeholder="Search..." oninput="filterQA(this.value)">
        </div>
        <div class="panel-body" style="padding:.75rem" id="qa-list">
          <?php foreach($qa as $q):?>
          <div class="qa-item" data-q="<?=strtolower(h($q['question']))?>">
            <div class="qa-cat"><?=h($q['category'])?></div>
            <div class="qa-q"><?=h($q['question'])?></div>
            <div class="qa-a"><?=h(mb_strimwidth($q['answer'],0,100,'...'))?></div>
            <div class="qa-actions">
              <?php if(!$locked):?>
              <a href="?edit=<?=$q['qa_id']?>" class="btn btn-outline btn-sm">Edit</a>
              <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="qa_id" value="<?=$q['qa_id']?>"><button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
              <?php else:?><span style="color:var(--muted);font-size:.78rem">🔒 Locked</span><?php endif;?>
            </div>
          </div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
    <div>
      <div class="panel mb-3"><div class="panel-header"><div class="panel-title">💡 How It Works</div></div>
        <div class="panel-body"><p style="font-size:.835rem;color:var(--muted);line-height:1.75;font-weight:300">Chatbot matches messages to your Q&A using keyword matching. More Q&As = better accuracy.</p>
          <div style="margin-top:1rem;display:flex;flex-direction:column;gap:.55rem">
            <?php foreach(['🔍 Customer types → bot finds best match','💬 No match → offers suggestions','👤 Customer requests human agent','🟢 Agent joins live conversation'] as $s):?>
            <div style="font-size:.815rem;color:var(--muted)"><?=$s?></div>
            <?php endforeach;?>
          </div>
        </div>
      </div>
      <div class="panel"><div class="panel-header"><div class="panel-title">Categories</div></div>
        <div class="panel-body" style="padding:0">
          <?php $catCounts=[];foreach($qa as $q) $catCounts[$q['category']]=($catCounts[$q['category']]??0)+1;
          foreach($catCounts as $c=>$n):?>
          <div style="display:flex;justify-content:space-between;padding:.6rem 1.1rem;border-bottom:1px solid var(--border);font-size:.845rem"><span><?=h($c)?></span><span style="color:var(--accent);font-weight:500"><?=$n?></span></div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
  </div>
</main>
<script>
function toggle(id){const el=document.getElementById(id);el.style.display=el.style.display==='none'?'':'none';}
function filterQA(q){document.querySelectorAll('.qa-item').forEach(el=>{el.style.display=!q||el.dataset.q.includes(q.toLowerCase())?'':'none'});}
</script>
</body></html>
