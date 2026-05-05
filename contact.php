<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../includes/helpers.php';
$success=false; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fname=trim($_POST['fname']??''); $lname=trim($_POST['lname']??'');
    $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??'');
    $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??'');
    if (!$fname||!$lname||!$email||!$subject||!$message) { $error='Please fill in all required fields.'; }
    else {
        db()->prepare("INSERT INTO contact_messages (first_name,last_name,email,phone,subject,message) VALUES (?,?,?,?,?,?)")->execute([$fname,$lname,$email,$phone,$subject,$message]);
        $success=true;
    }
}
$cc=[];
foreach(db()->query("SELECT field_key,field_value FROM contact_content")->fetchAll() as $r) $cc[$r['field_key']]=$r['field_value'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Contact Us — Eleven Roofing</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/base.css"><link rel="stylesheet" href="css/contact.css">
</head><body>
<nav class="site-nav"><a href="index.php" class="nav-logo">Eleven Roofing</a><div class="nav-links"><a href="index.php">Home</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="about.php">About</a><a href="contact.php" class="active">Contact</a><?php if(isLoggedIn()):?><a href="<?=getDashboardUrl()?>">Dashboard</a><?php else:?><a href="../auth/login.php">Login</a><a href="../auth/register.php" class="nav-cta">Get Started</a><?php endif;?></div></nav>
<div class="page-hero"><div class="page-hero-inner"><p class="page-label">Get In Touch</p><h1 class="page-title">Contact <em>Us</em></h1><p class="page-sub">Have a question or project in mind? Reach out and we'll respond within 24 hours.</p></div></div>
<div class="contact-layout">
  <div>
    <?php if($success): ?>
    <div style="background:var(--success-dim);border:1px solid rgba(76,175,114,.3);border-radius:var(--r2);padding:2.5rem;text-align:center">
      <div style="font-size:2.5rem;margin-bottom:1rem">✅</div>
      <h3 style="font-family:var(--serif);color:var(--success);margin-bottom:.5rem">Message Sent!</h3>
      <p style="color:var(--muted);font-size:.875rem;font-weight:300">Thank you! Our team will contact you within 24 business hours.</p>
      <a href="index.php" style="display:inline-block;margin-top:1.25rem;color:var(--accent);font-size:.875rem">← Back to Home</a>
    </div>
    <?php else: ?>
    <div class="contact-form-card">
      <h2>Send Us a Message</h2>
      <p>Fill out the form and our team will get back to you as soon as possible.</p>
      <?php if($error):?><div class="alert alert-error show"><?=h($error)?></div><?php endif;?>
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label>First Name <span class="req">*</span></label><input type="text" name="fname" class="form-control" value="<?=h($_POST['fname']??'')?>" required></div>
          <div class="form-group"><label>Last Name <span class="req">*</span></label><input type="text" name="lname" class="form-control" value="<?=h($_POST['lname']??'')?>" required></div>
        </div>
        <div class="form-group"><label>Email <span class="req">*</span></label><input type="email" name="email" class="form-control" value="<?=h($_POST['email']??'')?>" required></div>
        <div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?=h($_POST['phone']??'')?>"></div>
        <div class="form-group"><label>Subject <span class="req">*</span></label>
          <select name="subject" class="form-control" required>
            <option value="">-- Select a topic --</option>
            <?php foreach(['Service Inquiry','Project Quotation','General Question','Partnership / Supplier','Complaint / Feedback','Other'] as $s):?><option value="<?=$s?>" <?=($_POST['subject']??'')===$s?'selected':''?>><?=$s?></option><?php endforeach;?>
          </select>
        </div>
        <div class="form-group"><label>Message <span class="req">*</span></label><textarea name="message" class="form-control" style="min-height:130px" required><?=h($_POST['message']??'')?></textarea></div>
        <button type="submit" class="btn btn-primary w-full">Send Message</button>
      </form>
    </div>
    <?php endif;?>
  </div>
  <div class="contact-info-col">
    <?php
    $infoItems=[['📞','Phone',$cc['phone']??''],['📧','Email',$cc['email']??''],['📍','Address',$cc['address']??''],['🕒','Weekday Hours',$cc['hours_weekday']??''],['🕒','Saturday Hours',$cc['hours_saturday']??'']];
    foreach($infoItems as [$ico,$label,$val]):if(!$val)continue;?>
    <div class="contact-info-card fade-in">
      <div class="contact-ico"><?=$ico?></div>
      <div><h4><?=$label?></h4><p><?=nl2br(h($val))?></p></div>
    </div>
    <?php endforeach;?>
    <?php if(!empty($cc['emergency_phone'])):?>
    <div class="emg-box fade-in">
      <h4>🚨 Emergency Service</h4>
      <p>For urgent roof repairs — storm damage, severe leaks — call our 24/7 hotline:</p>
      <div style="font-family:var(--serif);font-size:1.45rem;color:var(--accent);margin-top:.5rem"><?=h($cc['emergency_phone'])?></div>
    </div>
    <?php endif;?>
    <?php
    $branches=array_filter([$cc['branch_1']??'', $cc['branch_2']??'', $cc['branch_3']??'']);
    if($branches):?>
    <div class="panel fade-in">
      <div class="panel-header"><div class="panel-title">📍 Our Branches</div></div>
      <div class="panel-body" style="padding:0">
        <?php foreach($branches as $b):?>
        <div style="padding:.85rem 1.25rem;border-bottom:1px solid var(--border)"><p style="font-size:.845rem;color:var(--muted);font-weight:300"><?=nl2br(h($b))?></p></div>
        <?php endforeach;?>
      </div>
    </div>
    <?php endif;?>
  </div>
</div>
<footer class="site-footer"><div class="footer-grid"><div class="footer-brand"><a href="index.php" class="nav-logo">ERDasma</a><p>Professional roofing services since 2013.</p></div><div class="footer-col"><h5>Services</h5><a href="services.php">Installation</a><a href="services.php">Repair</a></div><div class="footer-col"><h5>Company</h5><a href="about.php">About</a><a href="contact.php">Contact</a></div><div class="footer-col"><h5>Account</h5><a href="../auth/login.php">Login</a><a href="../auth/register.php">Register</a></div></div><div class="footer-bottom"><p>© 2025 Eleven Roofing Dasma.</p></div></footer>
<script>const o=new IntersectionObserver(e=>{e.forEach((x,i)=>{if(x.isIntersecting)setTimeout(()=>x.target.classList.add('visible'),i*80)})},{threshold:.08});document.querySelectorAll('.fade-in').forEach(el=>o.observe(el));</script>
</body></html>
