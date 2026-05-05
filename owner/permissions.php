<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$user = requireOwner();
$matrix=[
    'Feature'               =>['Owner','System Admin','Administrator','Staff','Customer'],
    'View Public Website'   =>[true,true,true,true,true],
    'Send Inquiries'        =>[true,true,true,true,true],
    'Chat Support'          =>[true,true,true,true,true],
    'Admin Dashboard'       =>[true,true,true,false,false],
    'Manage Services'       =>[true,true,true,false,false],
    'Manage Products'       =>[true,true,true,false,false],
    'Manage Inquiries'      =>[true,true,true,false,false],
    'Manage Inventory'      =>[true,true,true,false,false],
    'Borrow Tools (Staff)'  =>[true,true,true,true,false],
    'Edit About Us'         =>[true,true,true,false,false],
    'Edit Contact Us'       =>[true,true,true,false,false],
    'Chatbot Q&A Editor'    =>[true,true,true,false,false],
    'Live Chat Agent'       =>[true,true,true,true,false],
    'Feature Locks'         =>[true,true,false,false,false],
    'Backup & Restore DB'   =>[true,true,false,false,false],
    'Activity Logs'         =>[true,true,false,false,false],
    'Inventory Monitor'     =>[true,true,false,false,false],
    'User Management'       =>[true,'View only',false,false,false],
    'Deactivate Any User'   =>[true,false,false,false,false],
    'Lock Any Account'      =>[true,true,false,false,false],
    'Change User Roles'     =>[true,false,false,false,false],
    'Delete Users'          =>[true,false,false,false,false],
    'Full System Access'    =>[true,false,false,false,false],
];
$cols=array_shift($matrix);
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Permissions — Owner</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="../assets/css/dashboard.css"><link rel="stylesheet" href="css/owner.css">
</head><body class="dash-body"><?php include __DIR__.'/partials/sidebar.php';?>
<main class="dash-main">
  <div class="topbar"><div class="topbar-left"><h1>Role Permissions</h1><p>Overview of what each role can access and do</p></div></div>
  <div class="panel"><div class="panel-body"><div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Feature / Permission</th><?php foreach($cols as $c):?><th style="text-align:center"><?=h($c)?></th><?php endforeach;?></tr></thead>
      <tbody>
      <?php foreach($matrix as $feat=>$vals):?>
      <tr>
        <td style="font-size:.845rem;font-weight:500"><?=h($feat)?></td>
        <?php foreach($vals as $v):?>
        <td style="text-align:center">
          <?php if($v===true):?><span style="color:var(--success);font-size:1rem">✅</span>
          <?php elseif($v===false):?><span style="color:var(--muted);font-size:.9rem">—</span>
          <?php else:?><span style="font-size:.75rem;color:var(--warning)"><?=h($v)?></span><?php endif;?>
        </td>
        <?php endforeach;?>
      </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div></div></div>
</main></body></html>
