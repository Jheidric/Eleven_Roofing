<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireAdmin(); $locked = isLocked('about');
if ($_SERVER['REQUEST_METHOD']==='POST' && !$locked) {
    foreach ($_POST as $key=>$val) {
        if ($key==='action') continue;
        $exists=db()->prepare("SELECT COUNT(*) FROM about_content WHERE section_key=?"); $exists->execute([$key]);
        if ($exists->fetchColumn()) {
            db()->prepare("UPDATE about_content SET content=?,updated_by=?,updated_at=NOW() WHERE section_key=?")->execute([trim($val),$user['id'],$key]);
        } else {
            db()->prepare("INSERT INTO about_content (section_key,title,content,updated_by) VALUES (?,?,?,?)")->execute([$key,$key,trim($val),$user['id']]);
        }
    }
    logActivity($user['id'],'Updated About Us content','Content');
    header('Location: about_edit.php?msg=1'); exit;
}
$content=[];
foreach(db()->query("SELECT * FROM about_content")->fetchAll() as $row) $content[$row['section_key']]=$row['content'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Edit About Us — ERD Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/admin.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Edit About Us</h1><p>Update the About Us page content visible to website visitors</p></div>
    <a href="../public/about.php" target="_blank" class="btn btn-outline btn-sm">👁️ Preview Page</a>
  </div>
  <?php if($locked): echo renderLockBadge('about'); endif;?>
  <?php if(isset($_GET['msg'])):?><div class="alert alert-success show mb-3">About Us content updated successfully!</div><?php endif;?>
  <div style="display:grid;grid-template-columns:1fr 280px;gap:1.35rem">
    <form method="POST" <?=$locked?'onsubmit="return false"':''?>>
      <div class="panel mb-3"><div class="panel-header"><div class="panel-title">🏢 Company Information</div></div><div class="panel-body">
        <div class="form-group"><label>Page Title</label><input type="text" name="hero_title" class="form-control" value="<?=h($content['hero_title']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Page Subtitle</label><input type="text" name="hero_subtitle" class="form-control" value="<?=h($content['hero_subtitle']??'')?>" <?=$locked?'disabled':''?>></div>
        <div class="form-group"><label>Our Story / About Text</label><textarea name="story_body" class="form-control" style="min-height:130px" <?=$locked?'disabled':''?>><?=h($content['story_body']??'')?></textarea></div>
        <div class="form-group"><label>Mission Statement</label><textarea name="mission" class="form-control" <?=$locked?'disabled':''?>><?=h($content['mission']??'')?></textarea></div>
        <div class="form-group"><label>Vision Statement</label><textarea name="vision" class="form-control" <?=$locked?'disabled':''?>><?=h($content['vision']??'')?></textarea></div>
      </div></div>
      <div class="panel mb-3"><div class="panel-header"><div class="panel-title">📊 Statistics</div></div><div class="panel-body">
        <div class="form-row">
          <div class="form-group"><label>Years in Business</label><input type="text" name="years" class="form-control" value="<?=h($content['years']??'')?>" <?=$locked?'disabled':''?>></div>
          <div class="form-group"><label>Projects Completed</label><input type="text" name="projects" class="form-control" value="<?=h($content['projects']??'')?>" <?=$locked?'disabled':''?>></div>
        </div>
        <div class="form-group"><label>Team Size</label><input type="text" name="team_size" class="form-control" value="<?=h($content['team_size']??'')?>" <?=$locked?'disabled':''?>></div>
      </div></div>
      <?php if(!$locked):?><button type="submit" class="btn btn-primary">💾 Save About Us Content</button><?php endif;?>
    </form>
    <div>
      <div class="panel mb-3"><div class="panel-header"><div class="panel-title">ℹ️ Instructions</div></div>
        <div class="panel-body"><p style="font-size:.835rem;color:var(--muted);line-height:1.75;font-weight:300">All changes here will immediately be reflected on the public About Us page. You can preview the result before saving.</p></div>
      </div>
      <div class="panel"><div class="panel-header"><div class="panel-title">Change History</div></div>
        <div class="panel-body" style="padding:0">
          <?php $logs=db()->query("SELECT al.*,u.full_name FROM activity_logs al JOIN users u ON al.user_id=u.user_id WHERE al.module='Content' ORDER BY al.logged_at DESC LIMIT 8")->fetchAll();
          foreach($logs as $l):?>
          <div style="padding:.65rem 1.1rem;border-bottom:1px solid var(--border)">
            <div style="font-size:.815rem;font-weight:500"><?=h($l['action'])?></div>
            <div style="font-size:.72rem;color:var(--muted)"><?=h($l['full_name'])?> · <?=fmtDateTime($l['logged_at'])?></div>
          </div>
          <?php endforeach;?>
          <?php if(!$logs):?><div style="padding:1.5rem;text-align:center;color:var(--muted);font-size:.845rem">No changes yet</div><?php endif;?>
        </div>
      </div>
    </div>
  </div>
</main></body></html>
