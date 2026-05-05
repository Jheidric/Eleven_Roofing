<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
if (isLoggedIn()) { header('Location: '.getDashboardUrl()); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    if (!$email||!$pass) { $error='Please enter your email and password.'; }
    else {
        $st=db()->prepare("SELECT u.*,r.role_name FROM users u JOIN roles r ON u.role_id=r.role_id WHERE u.email=?");
        $st->execute([$email]); $u=$st->fetch();
        if ($u && $u['status']==='locked') { $error='Your account has been locked. Contact the Owner.'; }
        elseif ($u && $u['status']==='inactive') { $error='Your account is inactive. Contact an administrator.'; }
        elseif ($u && password_verify($pass,$u['password'])) {
            $_SESSION['user']=['id'=>$u['user_id'],'name'=>$u['full_name'],'email'=>$u['email'],'role'=>$u['role_name'],'role_id'=>$u['role_id']];
            logActivity($u['user_id'],'Login','Auth','User logged in');
            header('Location: '.getDashboardUrl()); exit;
        } else { $error='Invalid email or password.'; }
    }
}
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css">
<link rel="stylesheet" href="css/auth.css">
</head><body class="auth-page">
<nav class="auth-nav">
  <a href="../public/index.php" class="nav-logo">Eleven Roofing<span></span></a>
  <span style="font-size:.855rem;color:var(--muted)">No account? <a href="register.php" style="color:var(--accent)">Register</a></span>
</nav>
<div class="auth-wrap"><div class="auth-card">
  <h1 class="auth-title">Welcome Back</h1>
  <p class="auth-sub">Sign in to your Eleven Roofing account</p>
  <?php if($error):?><div class="alert alert-error show"><?=htmlspecialchars($error)?></div><?php endif;?>
  <?php if(isset($_GET['registered'])):?><div class="alert alert-success show">Account created! You can now sign in.</div><?php endif;?>
  <?php if(isset($_GET['error'])&&$_GET['error']==='access_denied'):?><div class="alert alert-error show">Access denied for your role.</div><?php endif;?>
  <form method="POST">
    <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" placeholder="yourname@email.com" value="<?=htmlspecialchars($_POST['email']??'')?>" required autofocus></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" placeholder="Enter your password" required></div>
    <button type="submit" class="btn btn-primary w-full">Sign In</button>
  </form>

  <div class="auth-switch">Don't have an account? <a href="register.php">Register here</a></div>
</div></div>
</body></html>
