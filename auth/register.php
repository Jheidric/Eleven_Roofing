<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fname=trim($_POST['fname']??''); $lname=trim($_POST['lname']??'');
    $email=trim($_POST['email']??''); $contact=trim($_POST['contact']??'');
    $address=trim($_POST['address']??''); $pass=$_POST['password']??''; $pass2=$_POST['password2']??'';
    if (!$fname||!$lname||!$email||!$pass) $error='Please fill in all required fields.';
    elseif (strlen($pass)<8) $error='Password must be at least 8 characters.';
    elseif ($pass!==$pass2) $error='Passwords do not match.';
    else {
        $ex=db()->prepare("SELECT user_id FROM users WHERE email=?"); $ex->execute([$email]);
        if ($ex->fetch()) $error='An account with this email already exists.';
        else {
            $hash=password_hash($pass,PASSWORD_BCRYPT);
            db()->prepare("INSERT INTO users (full_name,email,password,contact_number,address,role_id) VALUES (?,?,?,?,?,5)")
               ->execute(["$fname $lname",$email,$hash,$contact,$address]);
            header('Location: login.php?registered=1'); exit;
        }
    }
}
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register — Eleven Roofing Dasma</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="css/auth.css">
</head><body class="auth-page">
<nav class="auth-nav"><a href="../public/index.php" class="nav-logo">ElevenRoofing<span>Dasma</span></a>
  <span style="font-size:.855rem;color:var(--muted)">Have an account? <a href="login.php" style="color:var(--accent)">Sign in</a></span>
</nav>
<div class="auth-wrap"><div class="auth-card" style="max-width:500px">
  <h1 class="auth-title">Create Account</h1>
  <p class="auth-sub">Register as a customer to submit inquiries and track your service requests</p>
  <?php if($error):?><div class="alert alert-error show"><?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="POST">
    <div class="form-row">
      <div class="form-group"><label>First Name <span class="req">*</span></label><input type="text" name="fname" class="form-control" value="<?=htmlspecialchars($_POST['fname']??'')?>" required></div>
      <div class="form-group"><label>Last Name <span class="req">*</span></label><input type="text" name="lname" class="form-control" value="<?=htmlspecialchars($_POST['lname']??'')?>" required></div>
    </div>
    <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" class="form-control" value="<?=htmlspecialchars($_POST['email']??'')?>" required></div>
    <div class="form-group"><label>Contact Number</label><input type="tel" name="contact" class="form-control" value="<?=htmlspecialchars($_POST['contact']??'')?>"></div>
    <div class="form-group"><label>Address</label><textarea name="address" class="form-control" style="min-height:65px"><?=htmlspecialchars($_POST['address']??'')?></textarea></div>
    <div class="form-section">Security</div>
    <div class="form-group"><label>Password <span class="req">*</span></label><input type="password" name="password" class="form-control" required></div>
    <div class="form-group"><label>Confirm Password <span class="req">*</span></label><input type="password" name="password2" class="form-control" required></div>
    <button type="submit" class="btn btn-primary w-full">Create Account</button>
  </form>
  <div class="auth-switch">Already have an account? <a href="login.php">Sign in</a></div>
</div></div></body></html>
