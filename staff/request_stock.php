<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireStaff();


// Submit new request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {

    $pid    = (int)$_POST['product_id'];
    $type   = $_POST['change_type'] === 'remove' ? 'remove' : 'add';
    $qty    = abs((int)$_POST['quantity']);
    $reason = trim($_POST['reason'] ?? '');

    if ($qty < 1) {
        header('Location: request_stock.php?err=qty');
        exit;
    }

    db()->prepare("
        INSERT INTO inventory_requests
        (
            product_id,
            change_type,
            quantity,
            reason,
            requested_by
        )
        VALUES
        (?, ?, ?, ?, ?)
    ")->execute([
        $pid,
        $type,
        $qty,
        $reason,
        $user['id']
    ]);

    logActivity(
        $user['id'],
        "Submitted stock request: {$type} {$qty} for product #{$pid}",
        'Inventory'
    );

    header('Location: request_stock.php?msg=submitted');
    exit;
}


// Products
$products = db()->query("
    SELECT p.*, c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.is_active = 1
    ORDER BY p.product_name
")->fetchAll();


// My requests
$myReqs = db()->prepare("
    SELECT ir.*, p.product_name, u.full_name AS reviewer
    FROM inventory_requests ir
    JOIN products p ON ir.product_id = p.product_id
    LEFT JOIN users u ON ir.reviewed_by = u.user_id
    WHERE ir.requested_by = ?
    ORDER BY ir.requested_at DESC
    LIMIT 30
");

$myReqs->execute([$user['id']]);
$myReqs = $myReqs->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Request Stock Change — Staff Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/staff.css">
</head>

<body class="dash-body">

<!-- ✅ FIXED SIDEBAR (shared) -->
<?php include __DIR__.'/partials/sidebar.php'; ?>


<main class="dash-main">

    <div class="topbar">
        <div class="topbar-left">
            <h1>Request Stock Change</h1>
            <p>Submit add/remove stock requests for admin approval</p>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'submitted'): ?>
        <div class="alert alert-success show mb-3">
            ✅ Request submitted successfully and waiting for approval.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['err']) && $_GET['err'] === 'qty'): ?>
        <div class="alert alert-error show mb-3">
            Invalid quantity. Minimum is 1.
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.75rem">

        <!-- FORM -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">New Stock Request</div>
            </div>

            <div class="panel-body">

                <form method="POST">

                    <div class="form-group">
                        <label>Product *</label>
                        <select name="product_id" class="form-control" required id="pid-sel">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['product_id'] ?>">
                                    <?= h($p['product_name']) ?> (<?= $p['stock_quantity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type *</label>
                        <select name="change_type" class="form-control">
                            <option value="add">Add Stock</option>
                            <option value="remove">Remove Stock</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Reason *</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>

                    <button class="btn btn-primary w-full">Submit Request</button>

                </form>

            </div>
        </div>

        <!-- INFO -->
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">My Requests</div>
            </div>

            <div class="panel-body">

                <?php if ($myReqs): ?>
                    <?php foreach ($myReqs as $r): ?>
                        <div style="padding:.75rem;border-bottom:1px solid var(--border)">
                            <strong><?= h($r['product_name']) ?></strong><br>
                            <small><?= ucfirst($r['change_type']) ?> <?= $r['quantity'] ?> pcs</small><br>
                            <span class="badge badge-<?= $r['status'] ?>">
                                <?= ucfirst($r['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center;color:var(--muted);padding:2rem">
                        No requests yet
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

</main>

</body>
</html>