<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

/* STAFF + ABOVE */
$user = requireStaff();

/* HANDLE REPLY */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inquiry_id'])) {

    $id = (int)($_POST['inquiry_id'] ?? 0);
    $resp = trim($_POST['response'] ?? '');

    $status = in_array(
        $_POST['status'] ?? '',
        ['pending','in_progress','resolved']
    ) ? $_POST['status'] : 'in_progress';

    if ($id && $resp !== '') {

        db()->prepare("
            UPDATE inquiries
            SET response = ?,
                status = ?,
                responded_by = ?,
                updated_at = NOW()
            WHERE inquiry_id = ?
        ")->execute([
            $resp,
            $status,
            $user['id'],
            $id
        ]);

        logActivity(
            $user['id'],
            "Replied to inquiry #{$id}",
            'Inquiries'
        );
    }

    $filter = $_POST['current_filter'] ?? 'all';

    header("Location: inquiries.php?status={$filter}&replied=1");
    exit;
}


/* FILTER */
$filter = $_GET['status'] ?? 'all';
$replyId = isset($_GET['reply']) ? (int)$_GET['reply'] : 0;


/* LOAD INQUIRIES */
$where = '';

if ($filter !== 'all') {
    $where = "WHERE status = ?";
}

$stmt = db()->prepare("
    SELECT *
    FROM inquiries
    {$where}
    ORDER BY created_at DESC
");

if ($filter !== 'all') {
    $stmt->execute([$filter]);
} else {
    $stmt->execute();
}

$inqs = $stmt->fetchAll();


/* OPEN ONE INQUIRY */
$replyInq = null;

if ($replyId > 0) {

    $stmt = db()->prepare("
        SELECT *
        FROM inquiries
        WHERE inquiry_id = ?
    ");

    $stmt->execute([$replyId]);

    $replyInq = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>Inquiries — Eleven Roofing Staff</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/staff.css">

</head>

<body class="dash-body">

<?php include __DIR__.'/partials/sidebar.php'; ?>

<main class="dash-main">

    <div class="topbar">

        <div class="topbar-left">
            <h1>Customer Inquiries</h1>
            <p>View and respond to customer inquiries</p>
        </div>

        <form method="GET" style="display:flex;gap:.5rem">

            <select
                name="status"
                class="form-control"
                style="width:auto;padding:.42rem .85rem;font-size:.82rem"
                onchange="this.form.submit()"
            >
                <option value="all" <?=$filter==='all'?'selected':''?>>
                    All
                </option>

                <option value="pending" <?=$filter==='pending'?'selected':''?>>
                    Pending
                </option>

                <option value="in_progress" <?=$filter==='in_progress'?'selected':''?>>
                    In Progress
                </option>

                <option value="resolved" <?=$filter==='resolved'?'selected':''?>>
                    Resolved
                </option>

            </select>

        </form>

    </div>


    <?php if (isset($_GET['replied'])): ?>
        <div class="alert alert-success show mb-2">
            Reply sent successfully!
        </div>
    <?php endif; ?>


    <div class="panel mb-3">

        <div class="panel-body">

            <div class="table-wrap">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($inqs as $i): ?>

                        <tr>

                            <td>#<?=$i['inquiry_id']?></td>

                            <td>
                                <?=h($i['first_name'].' '.$i['last_name'])?>
                            </td>

                            <td style="font-size:.78rem;color:var(--muted)">
                                <?=h($i['email'])?>
                            </td>

                            <td>
                                <?=h(mb_strimwidth($i['subject'],0,30,'...'))?>
                            </td>

                            <td style="font-size:.8rem;color:var(--muted)">
                                <?=h($i['service_type'])?>
                            </td>

                            <td>
                                <span class="badge badge-<?=$i['status']==='in_progress'?'progress':$i['status']?>">
                                    <?=fmtStatus($i['status'])?>
                                </span>
                            </td>

                            <td style="font-size:.78rem;color:var(--muted)">
                                <?=fmtDate($i['created_at'])?>
                            </td>

                            <td>

                                <a
                                    href="?reply=<?=$i['inquiry_id']?>&status=<?=$filter?>"
                                    class="btn btn-outline btn-sm"
                                >
                                    <?=$i['status']==='resolved' ? 'View' : 'Reply'?>
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$inqs): ?>

                        <tr>
                            <td colspan="8" class="text-center" style="padding:2rem">
                                No inquiries found
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <?php if ($replyInq): ?>

        <div class="panel">

            <div class="panel-header">

                <div class="panel-title">
                    Inquiry #<?=$replyInq['inquiry_id']?>
                </div>

                <a href="inquiries.php" class="panel-action">
                    ✕ Close
                </a>

            </div>


            <div class="panel-body">


                <div
                    style="
                        background:var(--bg3);
                        border-radius:var(--r);
                        padding:1rem;
                        margin-bottom:1rem;
                        line-height:1.7;
                    "
                >

                    <strong>
                        <?=h($replyInq['subject'])?>
                    </strong>

                    <br><br>

                    <?=nl2br(h($replyInq['message']))?>

                    <?php if ($replyInq['response']): ?>

                        <hr style="margin:1rem 0">

                        <strong>
                            Previous Response:
                        </strong>

                        <br>

                        <?=nl2br(h($replyInq['response']))?>

                    <?php endif; ?>

                </div>



                <form method="POST">

                    <input
                        type="hidden"
                        name="inquiry_id"
                        value="<?=$replyInq['inquiry_id']?>"
                    >

                    <input
                        type="hidden"
                        name="current_filter"
                        value="<?=$filter?>"
                    >


                    <div class="form-group">

                        <label>
                            Staff Response
                        </label>

                        <textarea
                            name="response"
                            class="form-control"
                            style="min-height:100px"
                            required
                        ></textarea>

                    </div>


                    <div style="display:flex;gap:.75rem">

                        <button
                            type="submit"
                            name="status"
                            value="in_progress"
                            class="btn btn-primary btn-sm"
                        >
                            In Progress & Reply
                        </button>

                        <button
                            type="submit"
                            name="status"
                            value="resolved"
                            class="btn btn-success btn-sm"
                        >
                            Resolve & Reply
                        </button>

                    </div>

                </form>

            </div>

        </div>

    <?php endif; ?>


</main>

</body>
</html>