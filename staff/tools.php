<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireStaff();


/* ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $act = $_POST['action'] ?? '';

    /* BORROW */
    if ($act === 'borrow') {

        $tid = (int)($_POST['tool_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $ret = $_POST['expected_return'] ?? null;

        $tool = db()->prepare("
            SELECT available
            FROM tools
            WHERE tool_id = ?
        ");
        $tool->execute([$tid]);
        $tool = $tool->fetch();

        if ($tool && $tool['available'] >= $qty) {

            db()->prepare("
                INSERT INTO borrowed_tools
                (
                    tool_id,
                    borrowed_by,
                    quantity,
                    borrow_date,
                    expected_return,
                    condition_out,
                    recorded_by
                )
                VALUES
                (?, ?, ?, CURDATE(), ?, ?, ?)
            ")->execute([
                $tid,
                $user['name'],
                $qty,
                $ret ?: null,
                'Good',
                $user['id']
            ]);


            db()->prepare("
                UPDATE tools
                SET available = available - ?
                WHERE tool_id = ?
            ")->execute([
                $qty,
                $tid
            ]);


            logActivity(
                $user['id'],
                "Borrowed tool #{$tid} (qty: {$qty})",
                'Tools'
            );

            header('Location: tools.php?msg=borrowed');
            exit;
        }

        header('Location: tools.php?msg=not_available');
        exit;
    }


    /* RETURN */
    if ($act === 'return') {

        $bid = (int)($_POST['borrow_id'] ?? 0);
        $cond = trim($_POST['condition_in'] ?? 'Good');


        $stmt = db()->prepare("
            SELECT tool_id, quantity
            FROM borrowed_tools
            WHERE borrow_id = ?
        ");

        $stmt->execute([$bid]);

        $borrow = $stmt->fetch();


        if ($borrow) {

            db()->prepare("
                UPDATE borrowed_tools
                SET
                    status = 'returned',
                    return_date = CURDATE(),
                    condition_in = ?
                WHERE borrow_id = ?
            ")->execute([
                $cond,
                $bid
            ]);


            db()->prepare("
                UPDATE tools
                SET available = available + ?
                WHERE tool_id = ?
            ")->execute([
                $borrow['quantity'],
                $borrow['tool_id']
            ]);


            logActivity(
                $user['id'],
                "Returned borrow #{$bid}",
                'Tools'
            );
        }

        header('Location: tools.php?msg=returned');
        exit;
    }
}


/* DATA */
$tools = db()->query("
    SELECT *
    FROM tools
    ORDER BY tool_name
")->fetchAll();


$allBorrows = db()->query("
    SELECT bt.*, t.tool_name
    FROM borrowed_tools bt
    JOIN tools t ON bt.tool_id = t.tool_id
    ORDER BY bt.created_at DESC
")->fetchAll();


$myBorrows = array_filter(
    $allBorrows,
    fn($b) =>
        $b['borrowed_by'] === $user['name']
        && $b['status'] === 'borrowed'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>Borrow Tools — Staff Portal</title>

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
            <h1>Borrow Tools</h1>
            <p>Record tool borrowing and returns</p>
        </div>

        <button
            class="btn btn-primary btn-sm"
            onclick="toggle('bform')"
        >
            + Record Borrow
        </button>

    </div>


    <?php if(isset($_GET['msg'])): ?>

        <div class="alert alert-success show mb-3">

            <?=
                $_GET['msg'] === 'borrowed'
                    ? 'Tool borrowed successfully!'
                    : (
                        $_GET['msg'] === 'returned'
                            ? 'Tool returned successfully!'
                            : 'Not enough tools available.'
                    )
            ?>

        </div>

    <?php endif; ?>



    <!-- BORROW FORM -->
    <div class="panel mb-3" id="bform" style="display:none">

        <div class="panel-header">
            <div class="panel-title">Record Tool Borrow</div>
        </div>

        <div class="panel-body">

            <form method="POST">

                <input type="hidden" name="action" value="borrow">

                <div class="form-row">

                    <div class="form-group">

                        <label>Tool</label>

                        <select name="tool_id" class="form-control">

                            <?php foreach($tools as $t): ?>

                                <option value="<?=$t['tool_id']?>">

                                    <?=h($t['tool_name'])?>

                                    (<?=$t['available']?> available)

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>Quantity</label>

                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            class="form-control"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label>Expected Return</label>

                    <input
                        type="date"
                        name="expected_return"
                        class="form-control"
                        value="<?=date('Y-m-d', strtotime('+3 days'))?>"
                    >

                </div>


                <button type="submit" class="btn btn-primary btn-sm">
                    Record Borrow
                </button>

            </form>

        </div>

    </div>



    <!-- MY BORROWS -->
    <div class="panel">

        <div class="panel-header">
            <div class="panel-title">
                My Borrowed Tools
            </div>
        </div>

        <div class="panel-body">

            <?php foreach($myBorrows as $b): ?>

                <?php
                $overdue =
                    $b['expected_return']
                    && strtotime($b['expected_return']) < time();
                ?>

                <div style="margin-bottom:1rem">

                    <strong>
                        <?=h($b['tool_name'])?>
                    </strong>

                    × <?=$b['quantity']?>

                    <br>

                    <span
                        class="badge <?=$overdue ? 'badge-critical' : 'badge-borrowed'?>"
                    >
                        <?=$overdue ? 'OVERDUE' : 'Borrowed'?>
                    </span>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="action"
                            value="return"
                        >

                        <input
                            type="hidden"
                            name="borrow_id"
                            value="<?=$b['borrow_id']?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-success btn-sm"
                        >
                            Return
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        </div>

    </div>



    <!-- AVAILABLE -->
    <div class="panel mt-3">

        <div class="panel-header">
            <div class="panel-title">
                Available Tools
            </div>
        </div>

        <div class="panel-body">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Total</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach($tools as $t): ?>

                        <tr>

                            <td>
                                <?=h($t['tool_name'])?>
                            </td>

                            <td>
                                <?=$t['quantity']?>
                            </td>

                            <td>
                                <?=$t['available']?>
                            </td>

                            <td>

                                <span
                                    class="badge <?=$t['available'] > 0 ? 'badge-resolved' : 'badge-critical'?>"
                                >

                                    <?=$t['available'] > 0 ? 'Available' : 'All Out'?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>


</main>


<script>
function toggle(id){
    const el = document.getElementById(id);

    el.style.display =
        el.style.display === 'none'
            ? ''
            : 'none';
}
</script>

</body>
</html>