<?php
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../../includes/sidebar.php';

// Count staff's pending requests
$pendingMyReqs = 0;
if (isset($user['id'])) {
    $s = db()->prepare("SELECT COUNT(*) FROM inventory_requests WHERE requested_by=? AND status='pending'");
    $s->execute([$user['id']]);
    $pendingMyReqs = (int)$s->fetchColumn();
}

$links = [
    ['section'=>'Main'],
    ['file'=>'index.php','icon'=>'📊','label'=>'Dashboard'],
    ['section'=>'My Work'],
    ['file'=>'tools.php','icon'=>'🔧','label'=>'Borrow Tools'],
    ['file'=>'request_stock.php','icon'=>'📦','label'=>'Request Stock Change'],
    ['file'=>'../staff/livechat.php','icon'=>'🟢','label'=>'Live Chat Support','badge'=>'live'],
    ['section'=>'Inquiries'],
    ['file'=>'../staff/inquiries.php','icon'=>'💬','label'=>'View Inquiries','badge'=>'pending'],
    ['section'=>'View Only'],
    ['file'=>'view_inventory.php','icon'=>'🗂️','label'=>'View Inventory'],
    ['file'=>'view_products.php','icon'=>'📋','label'=>'View Products'],
];
renderSidebar($user, $links, 'Staff Portal', 'av-staff');
