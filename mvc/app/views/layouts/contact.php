<?php
/**
 * View: contact.php
 * Simple contact page
 */

$email = 'zendrhax@gmail.com';

/**
 * Optional BASE_URL support
 */
$base = '/layouts';
?>
<div class="container mt-5 mb-5">
    <div class="contact-page">
      <div class="contact-container">
    
        <header class="contact-header">
          <h1 class="contact-title">Contact</h1>
          <p class="contact-subtitle">
            Need help or have questions about Zendrhax?
            We’re here to help.
          </p>
        </header>
    
        <section class="contact-card">
          <h2>Get in touch</h2>
    
          <p>
            For support, licensing questions, billing issues, or general inquiries,
            please contact us using the email below.
          </p>
    
          <div class="contact-email-box">
            <span class="contact-email-label">Email</span>
            <a
              href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
              class="contact-email"
            >
              <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
            </a>
          </div>
    
          <p class="contact-note">
            We usually respond within 24–48 business hours.
          </p>
        </section>
    
      </div>
    </div>
</div>
<style>
  .contact-page{
    padding:24px 16px;
  }

  .contact-container{
    max-width:720px;
    margin:0 auto;
  }

  .contact-header{
    margin-bottom:16px;
  }

  .contact-title{
    margin:0;
    font-size:28px;
    line-height:1.2;
  }

  .contact-subtitle{
    margin-top:6px;
    opacity:.85;
    line-height:1.45;
  }

  .contact-card{
    border:1px solid rgba(0,0,0,.12);
    border-radius:16px;
    padding:18px;
  }

  .contact-card h2{
    margin:0 0 10px;
    font-size:18px;
  }

  .contact-card p{
    margin:0 0 12px;
    line-height:1.55;
  }

  .contact-email-box{
    display:flex;
    flex-direction:column;
    gap:6px;
    padding:14px;
    border-radius:12px;
    background:rgba(0,0,0,.04);
    margin:12px 0;
  }

  .contact-email-label{
    font-size:13px;
    opacity:.7;
    font-weight:600;
  }

  .contact-email{
    font-size:16px;
    font-weight:700;
    text-decoration:none;
    word-break:break-all;
  }

  .contact-note{
    font-size:13px;
    opacity:.75;
  }

  .contact-footer{
    margin-top:16px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
    opacity:.85;
  }

  .contact-footer__links a{
    text-decoration:none;
    font-weight:700;
  }

  .contact-footer__links .dot{
    padding:0 8px;
  }

  .contact-footer__small{
    font-size:12px;
  }

  /* Dark mode compatibility (same pattern as legal.php) */
  body.dark .contact-card{
    border-color: rgba(255,255,255,.14);
  }

  body.dark .contact-email-box{
    background: rgba(255,255,255,.06);
  }
</style>
