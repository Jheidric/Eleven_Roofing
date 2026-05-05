<?php
define('DB_HOST','localhost');
define('DB_NAME','Elevenroofingdasmadatabase');
define('DB_USER','root');
define('DB_PASS','');
define('DB_CHARSET','utf8mb4');
define('BACKUP_DIR', __DIR__.'/../database/backups/');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function getSetting(string $key): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $st = db()->prepare("SELECT setting_value FROM system_settings WHERE setting_key=?");
        $st->execute([$key]);
        $cache[$key] = $st->fetchColumn() ?? '';
    }
    return $cache[$key];
}

function setSetting(string $key, string $value, ?int $userId = null): void {
    db()->prepare("INSERT INTO system_settings (setting_key,setting_value,locked_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=?,locked_by=?")
       ->execute([$key,$value,$userId,$value,$userId]);
}


function logActivity(int $userId, string $action, string $module, string $details = ''): void {
    db()->prepare("INSERT INTO activity_logs (user_id,action,module,details,ip_address) VALUES (?,?,?,?,?)")
       ->execute([$userId,$action,$module,$details,$_SERVER['REMOTE_ADDR']??'']);
}
