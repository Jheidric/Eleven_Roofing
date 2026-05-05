<?php
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function fmtStatus(string $s): string { return ucwords(str_replace('_',' ',$s)); }
function fmtNum(float $n): string { return number_format($n); }
function fmtDate(string $d): string { return date('M j, Y', strtotime($d)); }
function fmtDateTime(string $d): string { return date('M j, Y H:i', strtotime($d)); }
function isLocked(string $feature): bool {
    $st = db()->prepare("SELECT setting_value FROM system_settings WHERE setting_key=?");
    $st->execute(['lock_'.$feature]);
    return $st->fetchColumn() === '1';
}
function handleImageUpload(string $field, string $dir, string $prefix='img_'): string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return '';
    if ($_FILES[$field]['size'] > 5*1024*1024) return '';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = $prefix.uniqid().'.'.$ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], $dir.$name);
    return 'assets/images/uploads/'.$name;
}
function getAbout(string $key): string {
    $st=db()->prepare("SELECT content FROM about_content WHERE section_key=?"); $st->execute([$key]);
    return $st->fetchColumn() ?: '';
}
function getContact(string $key): string {
    $st=db()->prepare("SELECT field_value FROM contact_content WHERE field_key=?"); $st->execute([$key]);
    return $st->fetchColumn() ?: '';
}
function renderLockBadge(string $feature): string {
    if (isLocked($feature)) return "<div class='lock-notice'>🔒 This section is currently <strong>locked</strong> by the System Administrator. Editing is disabled.</div>";
    return '';
}
function backupDatabase(): array {
    $dir = __DIR__.'/../database/backups/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = 'backup_'.date('Y-m-d_His').'.sql';
    $filepath = $dir.$filename;
    // Build backup using PDO queries (no exec needed)
    $tables = db()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $sql  = "-- Eleven Roofing Dasma Backup\n-- Created: ".date('Y-m-d H:i:s')."\n-- Database: ".DB_NAME."\n\n";
    $sql .= "USE ".DB_NAME.";\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    foreach ($tables as $table) {
        $create = db()->query("SHOW CREATE TABLE `$table`")->fetch();
        $sql .= "DROP TABLE IF EXISTS `$table`;\n".$create['Create Table'].";\n\n";
        $rows = db()->query("SELECT * FROM `$table`")->fetchAll();
        if ($rows) {
            $cols = array_keys($rows[0]);
            $colList = implode(',', array_map(fn($c)=>"`$c`", $cols));
            foreach ($rows as $row) {
                $vals = implode(',', array_map(fn($v) => $v===null?'NULL':("'".addslashes($v)."'"), $row));
                $sql .= "INSERT INTO `$table` ($colList) VALUES ($vals);\n";
            }
            $sql .= "\n";
        }
    }
    $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
    file_put_contents($filepath, $sql);
    return ['filename'=>$filename,'filepath'=>$filepath,'size'=>filesize($filepath)];
}
