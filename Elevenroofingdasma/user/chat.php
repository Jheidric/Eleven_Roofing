<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireLogin();
$qaList=db()->query("SELECT * FROM chatbot_qa WHERE is_active=1 ORDER BY category,qa_id")->fetchAll();

// Find or create active session
$sess=db()->prepare("SELECT * FROM chat_sessions WHERE user_id=? AND status NOT IN('closed') ORDER BY created_at DESC LIMIT 1");
$sess->execute([$user['id']]); $session=$sess->fetch();
if (!$session) {
    db()->prepare("INSERT INTO chat_sessions (user_id,user_name,status) VALUES (?,?,'bot')")->execute([$user['id'],$user['name']]);
    $sid=db()->lastInsertId();
    db()->prepare("INSERT INTO chat_messages (session_id,sender_name,message,msg_type) VALUES (?,'Assistant',?,'bot')")
       ->execute([$sid,"Hi {$user['name']}! 👋 I'm the Eleven Roofing Dasma assistant. How can I help you today? Type your question or pick one from the left."]);
    $sess->execute([$user['id']]); $session=$sess->fetch();
}
$sid=$session['session_id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $act=$_POST['action']??'';
    if ($act==='send') {
        $txt=trim($_POST['message']??'');
        if ($txt && in_array($session['status'],['bot','active'])) {
            db()->prepare("INSERT INTO chat_messages (session_id,sender_name,message,msg_type) VALUES (?,?,?,'user')")->execute([$sid,$user['name'],$txt]);
            if ($session['status']==='bot') {
                $low=strtolower($txt); $best=null; $bestScore=0;
                foreach($qaList as $qa) {
                    $words=preg_split('/\s+/',strtolower($qa['question']));
                    $m=0; foreach($words as $w) if(strlen($w)>3&&strpos($low,$w)!==false) $m++;
                    $score=$m/max(count($words),1);
                    if($score>$bestScore){$bestScore=$score;$best=$qa;}
                }
                $reply=$bestScore>0.2&&$best?$best['answer']:"I'm sorry, I couldn't find a specific answer for that. Try one of the suggested questions, or click **Request Human Agent** for direct support from our team.";
                db()->prepare("INSERT INTO chat_messages (session_id,sender_name,message,msg_type) VALUES (?,'Assistant',?,'bot')")->execute([$sid,$reply]);
            }
            db()->prepare("UPDATE chat_sessions SET updated_at=NOW() WHERE session_id=?")->execute([$sid]);
        }
        header('Location: chat.php'); exit;
    } elseif ($act==='request_human') {
        db()->prepare("UPDATE chat_sessions SET status='waiting',updated_at=NOW() WHERE session_id=?")->execute([$sid]);
        db()->prepare("INSERT INTO chat_messages (session_id,sender_name,message,msg_type) VALUES (?,'Assistant',?,'bot')")->execute([$sid,"No problem! Connecting you to a human agent now. Please hold on — a team member will join shortly. 🙏"]);
        header('Location: chat.php'); exit;
    } elseif ($act==='new_chat') {
        db()->prepare("UPDATE chat_sessions SET status='closed' WHERE session_id=?")->execute([$sid]);
        header('Location: chat.php'); exit;
    }
}
$sess->execute([$user['id']]); $session=$sess->fetch(); $sid=$session['session_id'];
$msgs=db()->prepare("SELECT * FROM chat_messages WHERE session_id=? ORDER BY sent_at ASC"); $msgs->execute([$sid]); $msgs=$msgs->fetchAll();
$grouped=[]; foreach($qaList as $q) $grouped[$q['category']][]=$q;
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat Support — ERD</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/user.css"><link rel="stylesheet" href="css/chat.css">
</head><body class="dash-body">
<aside class="dash-sidebar">
  <div class="sidebar-head"><a href="../public/index.php" class="nav-logo" style="font-size:1.15rem">ERDasma</a><div class="sidebar-badge">Customer Portal</div></div>
  <nav class="sidebar-nav">
    <div class="nav-sec">My Account</div>
    <a href="dashboard.php" class="nav-lnk"><span class="ni">📊</span> Overview</a>
    <a href="dashboard.php?tab=inquiries" class="nav-lnk"><span class="ni">💬</span> My Inquiries</a>
    <a href="chat.php" class="nav-lnk active"><span class="ni">🤖</span> Chat Support</a>
    <div class="nav-sec">Explore</div>
    <a href="../public/services.php" class="nav-lnk"><span class="ni">🏗️</span> Services</a>
    <a href="../public/products.php" class="nav-lnk"><span class="ni">📦</span> Products</a>
    <a href="../public/inquiry.php" class="nav-lnk"><span class="ni">✉️</span> New Inquiry</a>
  </nav>
  <div class="sidebar-foot">
    <div class="sidebar-user"><div class="user-av"><?=strtoupper(substr($user['name'],0,2))?></div><div><div class="user-nm"><?=h($user['name'])?></div><div class="user-rl">Customer</div></div></div>
    <a href="../auth/logout.php" class="logout-lnk">← Sign Out</a>
  </div>
</aside>
<main class="dash-main" style="padding:1.5rem 2rem">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
    <div><h1 style="font-family:var(--serif);font-size:1.5rem">Chat Support</h1><p style="color:var(--muted);font-size:.84rem;margin-top:.15rem">AI assistant — or request a live human agent</p></div>
    <?php if($session['status']!=='waiting'):?><form method="POST" style="display:inline"><input type="hidden" name="action" value="new_chat"><button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Start a new chat?')">🔄 New Chat</button></form><?php endif;?>
  </div>
  <div class="chat-layout">
    <!-- SUGGESTED Q's LEFT PANEL -->
    <div class="chat-sugg-panel">
      <div class="chat-sugg-head"><h3>Quick Questions</h3><p>Click to get an instant answer</p></div>
      <div class="chat-sugg-body">
        <?php foreach($grouped as $cat=>$qs):?>
        <div class="sugg-cat-label"><?=$cat?></div>
        <?php foreach($qs as $q):?>
        <form method="POST" style="margin-bottom:.35rem">
          <input type="hidden" name="action" value="send">
          <input type="hidden" name="message" value="<?=h($q['question'])?>">
          <button type="submit" class="sugg-btn" <?=$session['status']!=='bot'?'disabled':''?>><?=h($q['question'])?></button>
        </form>
        <?php endforeach;?>
        <?php endforeach;?>
      </div>
      <?php if($session['status']==='bot'):?>
      <div class="chat-sugg-foot">
        <form method="POST"><input type="hidden" name="action" value="request_human">
          <button type="submit" class="btn btn-outline btn-sm w-full">👤 Request Human Agent</button>
        </form>
      </div>
      <?php endif;?>
    </div>
    <!-- CHAT WINDOW -->
    <div class="chat-main-panel">
      <div class="chat-status-bar">
        <div style="display:flex;align-items:center;gap:.5rem">
          <div class="status-dot <?=$session['status']==='waiting'?'s-waiting':($session['status']==='active'?'s-active':'s-bot')?>"></div>
          <span style="font-size:.83rem"><?=$session['status']==='bot'?'AI Assistant':($session['status']==='waiting'?'Connecting to agent...':'Live Agent Connected')?></span>
        </div>
        <span style="font-size:.75rem;color:var(--muted)">Session #<?=$sid?></span>
      </div>
      <?php if($session['status']==='waiting'):?>
      <div class="waiting-screen">
        <div class="wait-spinner"></div>
        <h4>Connecting you to an agent...</h4>
        <p>Please wait — a team member will join shortly. This usually takes less than 2 minutes.</p>
        <form method="POST" style="margin-top:1.5rem"><input type="hidden" name="action" value="new_chat"><button type="submit" class="btn btn-outline btn-sm">Cancel — Back to Bot</button></form>
      </div>
      <?php else:?>
      <div class="chat-msgs-wrap" id="chat-msgs">
        <?php foreach($msgs as $m):
          $isUser=$m['msg_type']==='user';
          $isAgent=$m['msg_type']==='agent';
        ?>
        <div class="chat-bubble-row <?=$isUser?'row-user':'row-other'?>">
          <?php if(!$isUser):?><div class="bubble-av"><?=strtoupper(substr($m['sender_name']??'A',0,1))?></div><?php endif;?>
          <div>
            <?php if(!$isUser):?><div class="bubble-name"><?=h($m['sender_name'])?></div><?php endif;?>
            <div class="bubble <?=$isUser?'bubble-user':($isAgent?'bubble-agent':'bubble-bot')?>"><?=nl2br(h($m['message']))?></div>
            <div class="bubble-time"><?=date('H:i',strtotime($m['sent_at']))?></div>
          </div>
        </div>
        <?php endforeach;?>
      </div>
      <div class="chat-input-bar">
        <form method="POST" style="display:flex;gap:.65rem;align-items:flex-end">
          <input type="hidden" name="action" value="send">
          <textarea name="message" class="chat-textarea" placeholder="Type your message..." rows="1" required <?=$session['status']==='waiting'?'disabled':''?> onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit()}" oninput="this.style.height='';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
          <button type="submit" class="btn btn-primary btn-sm" <?=$session['status']==='waiting'?'disabled':''?>>Send</button>
        </form>
      </div>
      <?php endif;?>
    </div>
  </div>
</main>
<script>
const cm=document.getElementById('chat-msgs'); if(cm) cm.scrollTop=cm.scrollHeight;
<?php if(in_array($session['status'],['waiting','active'])):?>setTimeout(()=>location.reload(),5000);<?php endif;?>
</script>
</body></html>
