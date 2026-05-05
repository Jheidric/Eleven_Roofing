<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireStaff();


/* ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $act = $_POST['action'] ?? '';
    $sid = (int)($_POST['session_id'] ?? 0);
    $tab = $_POST['tab'] ?? 'waiting';


    if ($act === 'join') {

        db()->prepare("
            UPDATE chat_sessions
            SET
                status='active',
                assigned_to=?,
                updated_at=NOW()
            WHERE session_id=?
        ")->execute([
            $user['id'],
            $sid
        ]);


        db()->prepare("
            INSERT INTO chat_messages
            (session_id,sender_name,message,msg_type)
            VALUES (?,?,?,'agent')
        ")->execute([
            $sid,
            $user['name'],
            "Hello! I'm {$user['name']} from Eleven Roofing Dasma. How can I help you today?"
        ]);


        logActivity(
            $user['id'],
            "Joined live chat #$sid",
            'LiveChat'
        );


        header("Location: livechat.php?open=$sid&tab=$tab");
        exit;
    }


    if ($act === 'send') {

        $txt = trim($_POST['message'] ?? '');

        if ($txt) {

            db()->prepare("
                INSERT INTO chat_messages
                (session_id,sender_name,message,msg_type)
                VALUES (?,?,?,'agent')
            ")->execute([
                $sid,
                $user['name'],
                $txt
            ]);


            db()->prepare("
                UPDATE chat_sessions
                SET updated_at=NOW()
                WHERE session_id=?
            ")->execute([
                $sid
            ]);
        }


        header("Location: livechat.php?open=$sid&tab=$tab");
        exit;
    }


    if ($act === 'close') {

        db()->prepare("
            UPDATE chat_sessions
            SET
                status='closed',
                updated_at=NOW()
            WHERE session_id=?
        ")->execute([
            $sid
        ]);


        header("Location: livechat.php?tab=$tab");
        exit;
    }
}


/* TAB */
$tab = $_GET['tab'] ?? 'waiting';


if ($tab === 'active') {

    $sessions = db()->query("
        SELECT cs.*,u.full_name
        FROM chat_sessions cs
        LEFT JOIN users u
            ON cs.assigned_to=u.user_id
        WHERE cs.status='active'
        ORDER BY cs.updated_at DESC
    ")->fetchAll();

}
elseif ($tab === 'all') {

    $sessions = db()->query("
        SELECT cs.*,u.full_name
        FROM chat_sessions cs
        LEFT JOIN users u
            ON cs.assigned_to=u.user_id
        ORDER BY cs.updated_at DESC
        LIMIT 30
    ")->fetchAll();

}
else {

    $sessions = db()->query("
        SELECT cs.*,u.full_name
        FROM chat_sessions cs
        LEFT JOIN users u
            ON cs.assigned_to=u.user_id
        WHERE cs.status='waiting'
        ORDER BY cs.updated_at ASC
    ")->fetchAll();
}


/* OPEN SESSION */
$openId = isset($_GET['open'])
    ? (int)$_GET['open']
    : null;


$openSess = null;
$msgs = [];


if ($openId) {

    $s = db()->prepare("
        SELECT *
        FROM chat_sessions
        WHERE session_id=?
    ");

    $s->execute([$openId]);

    $openSess = $s->fetch();


    $m = db()->prepare("
        SELECT *
        FROM chat_messages
        WHERE session_id=?
        ORDER BY sent_at ASC
    ");

    $m->execute([$openId]);

    $msgs = $m->fetchAll();
}


$waitCount = db()->query("
    SELECT COUNT(*)
    FROM chat_sessions
    WHERE status='waiting'
")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Live Chats — Staff Portal</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../admin/css/admin.css">

</head>

<body class="dash-body">

<?php include __DIR__.'/partials/sidebar.php'; ?>


<main class="dash-main">


    <div class="topbar">

        <div class="topbar-left">
            <h1>Live Chat Support</h1>
            <p>Respond to customers requesting human support</p>
        </div>

        <button
            class="btn btn-outline btn-sm"
            onclick="location.reload()">

            🔄 Refresh

        </button>

    </div>



    <div style="display:grid;grid-template-columns:280px 1fr;gap:1.35rem">


        <!-- LEFT -->
        <div>

            <div class="tabs">

                <a href="?tab=waiting"
                   class="tab <?= $tab==='waiting'?'active':'' ?>">

                    Waiting

                    <?php if($waitCount>0): ?>
                        <span class="nav-badge">
                            <?= $waitCount ?>
                        </span>
                    <?php endif; ?>

                </a>


                <a href="?tab=active"
                   class="tab <?= $tab==='active'?'active':'' ?>">

                    Active

                </a>


                <a href="?tab=all"
                   class="tab <?= $tab==='all'?'active':'' ?>">

                    All

                </a>

            </div>


            <?php foreach($sessions as $s):

                $last = db()->prepare("
                    SELECT message
                    FROM chat_messages
                    WHERE session_id=?
                    ORDER BY sent_at DESC
                    LIMIT 1
                ");

                $last->execute([
                    $s['session_id']
                ]);

                $lastMsg = $last->fetchColumn();

            ?>

                <div
                    class="lc-item <?= $openId==$s['session_id']?'selected':'' ?>"
                    onclick="window.location='?open=<?= $s['session_id'] ?>&tab=<?= $tab ?>'">

                    <div class="lc-user">

                        <div class="lc-av">
                            <?= strtoupper(substr($s['user_name'] ?? 'G',0,1)) ?>
                        </div>

                        <div class="lc-name">
                            <?= h($s['user_name'] ?? 'Guest') ?>
                        </div>

                        <div class="lc-time">
                            <?= date('H:i',strtotime($s['updated_at'])) ?>
                        </div>

                    </div>


                    <div style="display:flex;justify-content:space-between">

                        <div class="lc-preview">
                            <?= h(mb_strimwidth($lastMsg ?: '...',0,38,'...')) ?>
                        </div>


                        <span class="badge badge-<?= $s['status']==='waiting' ? 'pending' : ($s['status']==='active' ? 'progress' : 'inactive') ?>">
                            <?= $s['status'] ?>
                        </span>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>



        <!-- RIGHT -->
        <div>

            <?php if($openSess): ?>

                <div class="chat-window" style="height:calc(100vh - 11rem)">


                    <div class="chat-win-head">

                        <div>

                            <strong>
                                <?= h($openSess['user_name'] ?? 'Guest') ?>
                            </strong>

                        </div>


                        <div>

                            <?php if($openSess['status']==='waiting'): ?>

                                <form method="POST">

                                    <input type="hidden" name="action" value="join">
                                    <input type="hidden" name="session_id" value="<?= $openSess['session_id'] ?>">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">

                                    <button class="btn btn-success btn-sm">
                                        Join
                                    </button>

                                </form>

                            <?php endif; ?>


                            <?php if($openSess['status']==='active'): ?>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Close this chat?')">

                                    <input type="hidden" name="action" value="close">
                                    <input type="hidden" name="session_id" value="<?= $openSess['session_id'] ?>">
                                    <input type="hidden" name="tab" value="<?= $tab ?>">

                                    <button class="btn btn-danger btn-sm">
                                        Close
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </div>



                    <div
                        class="chat-win-body"
                        id="cb">

                        <?php foreach($msgs as $m):

                            $cls =
                                $m['msg_type']==='user'
                                ? 'from-user'
                                : 'from-agent';

                        ?>

                            <div class="msg-bub <?= $cls ?>">

                                <div class="msg-sender">
                                    <?= h($m['sender_name']) ?>
                                </div>

                                <?= nl2br(h($m['message'])) ?>

                            </div>

                        <?php endforeach; ?>

                    </div>



                    <?php if($openSess['status']==='active'): ?>

                        <div class="chat-win-input">

                            <form
                                method="POST"
                                style="display:flex;gap:.6rem;width:100%">

                                <input type="hidden" name="action" value="send">
                                <input type="hidden" name="session_id" value="<?= $openSess['session_id'] ?>">
                                <input type="hidden" name="tab" value="<?= $tab ?>">

                                <input
                                    type="text"
                                    name="message"
                                    placeholder="Type your response..."
                                    required>

                                <button class="btn btn-primary btn-sm">
                                    Send
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>


                </div>

            <?php endif; ?>

        </div>


    </div>


</main>



<script>

const cb = document.getElementById("cb");

if(cb){
    cb.scrollTop = cb.scrollHeight;
}


const input = document.querySelector(
    'input[name="message"]'
);


let isTyping = false;


const draftKey =
    "chat_draft_<?= $openId ?>";


if(input){

    const saved =
        localStorage.getItem(
            draftKey
        );


    if(saved){
        input.value = saved;
    }


    input.addEventListener(
        "input",
        function(){

            isTyping =
                input.value.trim() !== "";


            localStorage.setItem(
                draftKey,
                input.value
            );
        }
    );


    input.form.addEventListener(
        "submit",
        function(){

            localStorage.removeItem(
                draftKey
            );
        }
    );
}


<?php if($openSess && in_array($openSess['status'],['waiting','active'])): ?>

setInterval(function(){

    if(!isTyping){

        location.reload();

    }

}, 6000);

<?php endif; ?>

</script>

</body>
</html>