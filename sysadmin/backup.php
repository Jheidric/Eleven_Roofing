<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';

$user = requireSysAdmin();

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $act = $_POST['action'] ?? '';

    /* =========================
       CREATE BACKUP
    ========================== */
    if ($act === 'create') {

        $result = backupDatabase();

        db()->prepare("
            INSERT INTO backup_logs 
            (filename, file_size, created_by, notes) 
            VALUES (?, ?, ?, ?)
        ")->execute([
            $result['filename'],
            $result['size'],
            $user['id'],
            $_POST['notes'] ?? 'Manual backup'
        ]);

        logActivity(
            $user['id'],
            "Created backup: {$result['filename']}",
            'Backup'
        );

        $msg = "Backup created: {$result['filename']} (" . round($result['size'] / 1024) . " KB)";
        $msgType = 'success';
    }

    /* =========================
       RESTORE BACKUP
    ========================== */
    elseif ($act === 'restore') {

        $file = basename($_POST['filename'] ?? '');
        $path = __DIR__ . '/../database/backups/' . $file;

        if ($file && file_exists($path) && str_ends_with($file, '.sql')) {

            $sql = file_get_contents($path);

            $statements = array_filter(
                array_map('trim', explode(";\n", $sql))
            );

            $errors = 0;

            foreach ($statements as $stmt) {
                if (empty($stmt) || str_starts_with($stmt, '--')) continue;
                try {
                    db()->exec($stmt);
                } catch (Exception $e) {
                    $errors++;
                }
            }

            logActivity(
                $user['id'],
                "Restored backup: $file (errors: $errors)",
                'Backup'
            );

            $msg = $errors === 0
                ? "✅ Database restored from: $file"
                : "⚠️ Restored with $errors errors from: $file";

            $msgType = $errors === 0 ? 'success' : 'warning';

        } else {
            $msg = 'Backup file not found.';
            $msgType = 'error';
        }
    }

    /* =========================
       DELETE BACKUP (FIXED)
       SYSTEM ADMIN CANNOT DELETE
    ========================== */
    elseif ($act === 'delete') {

        $file = basename($_POST['filename'] ?? '');
        $path = __DIR__ . '/../database/backups/' . $file;

        // ❌ BLOCK SYSTEM ADMIN
        if ($user['role'] === 'System Admin') {

            $msg = "❌ System Admin is not allowed to delete backups.";
            $msgType = 'error';

        } else {

            if ($file && file_exists($path)) {

                unlink($path);

                db()->prepare("
                    DELETE FROM backup_logs 
                    WHERE filename = ?
                ")->execute([$file]);

                logActivity(
                    $user['id'],
                    "Deleted backup: $file",
                    'Backup'
                );

                $msg = "Backup deleted: $file";
                $msgType = 'success';

            } else {
                $msg = "Backup file not found.";
                $msgType = 'error';
            }
        }
    }

    /* =========================
       DOWNLOAD BACKUP
    ========================== */
    elseif ($act === 'download') {

        $file = basename($_POST['filename'] ?? '');
        $path = __DIR__ . '/../database/backups/' . $file;

        if ($file && file_exists($path)) {

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($path));

            readfile($path);
            exit;
        }
    }
}

/* =========================
   BACKUP FILES LIST
========================= */
$backupDir = __DIR__ . '/../database/backups/';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$files = array_filter(
    scandir($backupDir, SCANDIR_SORT_DESCENDING) ?? [],
    fn($f) => str_ends_with($f, '.sql')
);

$bkLogs = [];

foreach (
    db()->query("
        SELECT bl.*, u.full_name
        FROM backup_logs bl
        JOIN users u ON bl.created_by = u.user_id
        ORDER BY bl.created_at DESC
    ")->fetchAll() as $b
) {
    $bkLogs[$b['filename']] = $b;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Backup & Restore — Sysadmin</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="css/sysadmin.css">
</head>

<body class="dash-body">

<?php include __DIR__.'/partials/sidebar.php'; ?>

<main class="dash-main">

<div class="topbar">
    <div class="topbar-left">
        <h1>Backup & Restore</h1>
        <p>Create database backups and restore from previous versions</p>
    </div>
</div>

<?php if($msg): ?>
<div class="alert alert-<?= $msgType ?> show mb-3">
    <?= $msg ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1.35rem">

<!-- LEFT -->
<div>

    <div class="panel mb-3">
        <div class="panel-header">
            <div class="panel-title">💾 Create New Backup</div>
        </div>
        <div class="panel-body">

            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Notes (optional)</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Before update">
                </div>

                <button type="submit" class="btn btn-primary">
                    💾 Create Backup
                </button>

            </form>

        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">📂 Backup Files</div>
        </div>

        <div class="panel-body" style="padding:0">

        <?php if($files): foreach($files as $f): 
            $path = $backupDir.$f;
            $size = file_exists($path) ? round(filesize($path)/1024) : 0;
            $log = $bkLogs[$f] ?? null;
        ?>

        <div style="padding:1rem;border-bottom:1px solid var(--border)">

            <div style="font-weight:500"><?= h($f) ?></div>
            <div style="font-size:.8rem;color:var(--muted)"><?= $size ?> KB</div>

            <div style="display:flex;gap:.4rem;margin-top:.5rem;flex-wrap:wrap">

                <form method="POST">
                    <input type="hidden" name="action" value="download">
                    <input type="hidden" name="filename" value="<?= h($f) ?>">
                    <button class="btn btn-outline btn-sm">Download</button>
                </form>

                <form method="POST" onsubmit="return confirm('Restore backup?')">
                    <input type="hidden" name="action" value="restore">
                    <input type="hidden" name="filename" value="<?= h($f) ?>">
                    <button class="btn btn-warning btn-sm">Restore</button>
                </form>

                <?php if($user['role'] !== 'System Admin'): ?>
                <form method="POST" onsubmit="return confirm('Delete backup?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="filename" value="<?= h($f) ?>">
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
                <?php endif; ?>

            </div>

        </div>

        <?php endforeach; else: ?>

        <div style="padding:2rem;text-align:center;color:var(--muted)">
            No backups found
        </div>

        <?php endif; ?>

        </div>
    </div>

</div>

<!-- RIGHT -->
<div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">⚠️ System Rules</div>
        </div>
        <div class="panel-body">

            <div style="font-size:.85rem;color:var(--muted);line-height:1.7">

                ✔ System Admin can create & restore backups<br>
                ✔ Only Owner can delete backups<br>
                ✔ All actions are logged<br>
                ❌ Deletion restricted for security

            </div>

        </div>
    </div>

</div>

</div>

</main>

</body>
</html>