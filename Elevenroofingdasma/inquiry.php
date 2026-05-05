<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$success=false; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fname=trim($_POST['fname']??''); $lname=trim($_POST['lname']??'');
    $email=trim($_POST['email']??''); $contact=trim($_POST['contact']??'');
    $service=trim($_POST['service']??''); $subject=trim($_POST['subject']??'');
    $message=trim($_POST['message']??'');
    if (!$fname||!$lname||!$email||!$service||!$subject||!$message) { $error='Please fill in all required fields.'; }
    else {
        $uid=isLoggedIn()?currentUser()['id']:null;
        db()->prepare("INSERT INTO inquiries (user_id,first_name,last_name,email,contact,service_type,subject,message) VALUES (?,?,?,?,?,?,?,?)")->execute([$uid,$fname,$lname,$email,$contact,$service,$subject,$message]);
        $ref=db()->lastInsertId();
        logActivity($uid??0,"New inquiry #$ref submitted",'Inquiries');
        $success=true;
    }
}
$services=db()->query("SELECT service_name FROM services WHERE is_active=1 ORDER BY service_name")->fetchAll(PDO::FETCH_COLUMN);
$pre=h($_GET['service']??'');
$contact_phone=getContact('phone'); $contact_email=getContact('email'); $contact_addr=getContact('address');
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Send Inquiry — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="css/inquiry.css">
</head><body>
<nav class="site-nav"><a href="index.php" class="nav-logo">Eleven Roofing</a><div class="nav-links"><a href="index.php">Home</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="about.php">About</a><a href="contact.php" class="active">Contact</a><?php if(isLoggedIn()):?><a href="<?=getDashboardUrl()?>">Dashboard</a><?php else:?><a href="../auth/login.php">Login</a><a href="../auth/register.php" class="nav-cta">Get Started</a><?php endif;?></div></nav>
<div class="page-hero"><div class="page-hero-inner"><p class="page-label">Get a Quote</p><h1 class="page-title">Send an <em>Inquiry</em></h1><p class="page-sub">Describe your roofing project and our team will respond within 24 hours with a detailed quotation.</p></div></div>
<div class="inq-layout">
  <div>
    <?php if($success): ?>
    <div class="inq-success">
      <div style="font-size:2.5rem;margin-bottom:1rem">✅</div>
      <h3>Inquiry Submitted!</h3>
      <p>Thank you! Your inquiry <strong style="color:var(--accent)">#<?=$ref?></strong> has been received. Our team will contact you within 24 hours.</p>
      <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap">
        <a href="inquiry.php" class="btn btn-outline btn-sm">Submit Another</a>
        <?php if(isLoggedIn()):?><a href="../user/dashboard.php?tab=inquiries" class="btn btn-primary btn-sm">Track My Inquiries</a><?php else:?><a href="../auth/register.php" class="btn btn-primary btn-sm">Create Account to Track</a><?php endif;?>
      </div>
    </div>
    <?php else: ?>
    <div class="inq-card">
      <h2>Project Inquiry Form</h2>
      <p>Fill out all required fields and our experts will follow up promptly.</p>
      <?php if($error):?><div class="alert alert-error show"><?=h($error)?></div><?php endif;?>
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label>First Name <span class="req">*</span></label><input type="text" name="fname" class="form-control" value="<?=h($_POST['fname']??'')?>" required></div>
          <div class="form-group"><label>Last Name <span class="req">*</span></label><input type="text" name="lname" class="form-control" value="<?=h($_POST['lname']??'')?>" required></div>
        </div>
        <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" class="form-control" value="<?=h($_POST['email']??isLoggedIn()?currentUser()['email']:'')?>" required></div>
        <div class="form-group"><label>Contact Number</label><input type="tel" name="contact" class="form-control" value="<?=h($_POST['contact']??'')?>"></div>
        <div class="form-group"><label>Service Required <span class="req">*</span></label>
          <select name="service" class="form-control" required>
            <option value="">-- Select a service --</option>
            <?php foreach($services as $s):?><option value="<?=h($s)?>" <?=($_POST['service']??$pre)===h($s)?'selected':''?>><?=h($s)?></option><?php endforeach;?>
            <option value="Other" <?=($_POST['service']??'')==='Other'?'selected':''?>>Other</option>
          </select>
        </div>
        <div class="form-group"><label>Subject <span class="req">*</span></label><input type="text" name="subject" class="form-control" value="<?=h($_POST['subject']??'')?>" required></div>
        <div class="form-group"><label>Message / Details <span class="req">*</span></label><textarea name="message" class="form-control" style="min-height:130px" required><?=h($_POST['message']??'')?></textarea></div>
        <button type="submit" class="btn btn-primary w-full">Submit Inquiry</button>
      </form>
    </div>
    <?php endif;?>
  </div>
  <div class="inq-sidebar">
    <div class="inq-info-card">
      <h4>📞 Contact Information</h4>
      <div class="irow"><div class="iico">📞</div><div><strong>Phone</strong><p><?=h($contact_phone)?></p></div></div>
      <div class="irow"><div class="iico">📧</div><div><strong>Email</strong><p><?=h($contact_email)?></p></div></div>
      <div class="irow"><div class="iico">📍</div><div><strong>Office</strong><p><?=h($contact_addr)?></p></div></div>
      <div class="irow"><div class="iico">🕒</div><div><strong>Hours</strong><p><?=h(getContact('hours_weekday'))?></p></div></div>
    </div>
    <div class="inq-info-card">
      <h4>📋 How It Works</h4>
      <?php foreach(['Submit inquiry with project details','Team contacts you within 24 hours','Site visit and quotation prepared','Project begins upon agreement'] as $i=>$step):?>
      <div style="display:flex;gap:.65rem;align-items:flex-start;margin-bottom:.65rem">
        <div style="width:22px;height:22px;background:var(--accent);color:#0d0f0e;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;flex-shrink:0"><?=$i+1?></div>
        <p style="font-size:.8rem;color:var(--muted);font-weight:300;line-height:1.55"><?=$step?></p>
      </div>
      <?php endforeach;?>
    </div>
    <div class="inq-info-card" style="background:rgba(200,169,110,.06);border-color:var(--accent)">
      <h4 style="color:var(--accent)">🚨 Emergency Hotline</h4>
      <p style="font-size:.845rem;color:var(--muted);font-weight:300">Urgent repairs? Call 24/7:</p>
      <div style="font-family:var(--serif);font-size:1.35rem;color:var(--accent);margin-top:.4rem"><?=h(getContact('emergency_phone'))?></div>
    </div>
    <div class="inq-info-card" style="background:rgba(74,159,212,.06);border-color:rgba(74,159,212,.3)">
      <h4 style="color:var(--info)">💬 Prefer to Chat?</h4>
      <p style="font-size:.845rem;color:var(--muted);font-weight:300;margin-bottom:1rem">Get instant answers or talk to a live agent.</p>
      <a href="../user/chat.php" class="btn btn-outline btn-sm w-full" style="text-align:center;display:block">Open Chat Support</a>
    </div>
  </div>
</div>
<footer class="site-footer"><div class="footer-grid"><div class="footer-brand"><a href="index.php" class="nav-logo">ERDasma</a><p>Professional roofing services since 2013.</p></div><div class="footer-col"><h5>Services</h5><a href="services.php">Installation</a><a href="services.php">Repair</a></div><div class="footer-col"><h5>Company</h5><a href="about.php">About</a><a href="contact.php">Contact</a></div><div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a></div></div><div class="footer-bottom"><p>© 2025 Eleven Roofing Dasma.</p></div></footer>
</body></html>
