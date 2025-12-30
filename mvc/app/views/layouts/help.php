<?php
/**
 * View: help.php
 * Zendrhax - General Help page (covers Zendrhax apps & websites)
 */

$brandName    = 'Zendrhax';
$supportEmail = 'zendrhax@gmail.com';
$base = '/layouts';
?>
<div class="help-page">
  <div class="help-container">

    <header class="help-header">
      <h1 class="help-title">Help</h1>
      <p class="help-subtitle">
        Support and troubleshooting for Zendrhax products and services.
      </p>
    </header>

    <section class="help-card">
      <h2>Support</h2>
      <p>
        For help with any Zendrhax product (including apps like Zendrhax Invoices), contact us by email:
        <a href="mailto:<?= htmlspecialchars($supportEmail, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($supportEmail, ENT_QUOTES, 'UTF-8') ?>
        </a>.
      </p>

      <h2>Common topics</h2>
      <ul>
        <li><strong>Licenses:</strong> Some apps require a valid license to access premium features.</li>
        <li><strong>Subscriptions:</strong> If an app offers subscriptions, billing terms are shown inside the app and/or the website.</li>
        <li><strong>Plan changes:</strong> Switching plans may replace your current plan and start a new billing cycle (app-specific).</li>
        <li><strong>Connectivity:</strong> If a service needs internet, check your connection and try again.</li>
      </ul>

      <h2>Troubleshooting</h2>
      <ul>
        <li><strong>Checkout won’t start:</strong> confirm you’re connected to the internet and your email is valid.</li>
        <li><strong>Status looks wrong:</strong> refresh inside the app or restart it to re-check license/subscription cache.</li>
        <li><strong>App can’t load data:</strong> verify the server is reachable and the endpoint returns JSON.</li>
      </ul>

      <h2>Links</h2>
      <div class="help-links">
        <a href="<?= $base ?>/privacy">Privacy Policy</a>
        <span class="dot">•</span>
        <a href="<?= $base ?>/contact">Contact</a>
      </div>
    </section>

    <footer class="help-footer">
      <div class="small">© <?= date('Y') ?> <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</div>
    </footer>

  </div>
</div>

<style>
  .help-page{ padding:24px 16px; }
  .help-container{ max-width:900px; margin:0 auto; }
  .help-header{ margin-bottom:14px; }
  .help-title{ margin:0; font-size:28px; line-height:1.2; }
  .help-subtitle{ margin-top:6px; opacity:.75; }
  .help-card{
    border:1px solid rgba(0,0,0,.12);
    border-radius:16px;
    padding:18px;
  }
  .help-card h2{ margin:18px 0 8px; font-size:18px; }
  .help-card p{ margin:0 0 12px; line-height:1.6; }
  .help-card ul{ margin:0 0 12px 18px; line-height:1.6; }
  .help-links{ margin-top:10px; opacity:.85; }
  .help-links a{ text-decoration:none; font-weight:700; }
  .dot{ padding:0 8px; }
  .help-footer{ margin-top:16px; opacity:.75; }
  .small{ font-size:12px; }
  body.dark .help-card{ border-color: rgba(255,255,255,.14); }
</style>
