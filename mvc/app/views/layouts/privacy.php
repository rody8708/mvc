<?php
/**
 * View: privacy.php
 * Zendrhax - General Privacy Policy (covers Zendrhax apps & websites)
 */

$brandName     = 'Zendrhax';
$contactEmail  = 'zendrhax@gmail.com';
$effectiveDate = '2025-12-18';

$base = '/layouts';
?>
<div class="legal-page">
  <div class="legal-container">

    <header class="legal-header">
      <h1 class="legal-title">Privacy Policy</h1>
      <p class="legal-subtitle">
        Brand: <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?> • Effective date: <?= htmlspecialchars($effectiveDate, ENT_QUOTES, 'UTF-8') ?>
      </p>
    </header>

    <section class="legal-card">
      <p>
        This Privacy Policy explains how <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?> (“we”, “us”, “our”)
        collects, uses, and protects information when you use our websites, applications, and services (collectively, the “Services”).
      </p>

      <h2>1) Information we collect</h2>
      <p>
        The type of data we collect depends on which Zendrhax Service you use. We may collect:
      </p>
      <ul>
        <li><strong>Contact information</strong> (such as email) when you request support, activate licenses, or manage subscriptions.</li>
        <li><strong>Device & app information</strong> (platform, app version, device model, installation ID, hardware identifiers) when needed for security, licensing, or troubleshooting.</li>
        <li><strong>Subscription & purchase metadata</strong> (plan name, status, expiration, subscription identifiers) for billing and access control.</li>
        <li><strong>Usage data</strong> (basic diagnostics, error logs) to improve stability and user experience.</li>
        <li><strong>User content</strong> you create inside our apps (for example, invoices, clients, documents), depending on the specific app and its features.</li>
      </ul>

      <h2>2) How we use your information</h2>
      <ul>
        <li>Provide, operate, and maintain the Services.</li>
        <li>Validate licenses, manage device linking, and control access to paid features.</li>
        <li>Process subscriptions and keep your billing/access status updated.</li>
        <li>Respond to support requests and communicate important service updates.</li>
        <li>Prevent fraud, abuse, and security incidents.</li>
        <li>Improve performance, reliability, and features across Zendrhax products.</li>
      </ul>

      <h2>3) Payments</h2>
      <p>
        Some Zendrhax Services may offer paid subscriptions or purchases. Payments are processed by third-party payment providers
        (for example, PayPal). We do not store full payment card details.
        Payment providers may collect additional information necessary to complete transactions.
      </p>

      <h2>4) Where data is stored</h2>
      <p>
        Storage depends on the Service:
      </p>
      <ul>
        <li>Some apps store most data locally on your device unless you export, sync, or upload content.</li>
        <li>Some services may store data on Zendrhax servers to provide cloud features (if/when offered).</li>
      </ul>

      <h2>5) Sharing of information</h2>
      <p>
        We do not sell your personal information. We may share limited data only when necessary to:
      </p>
      <ul>
        <li>Operate essential features (for example, billing/licensing via payment providers).</li>
        <li>Comply with legal obligations and enforce policies.</li>
        <li>Protect the rights, safety, and security of users, Zendrhax, and the public.</li>
      </ul>

      <h2>6) Data retention</h2>
      <p>
        We retain data only as long as needed to provide the Services, comply with legal obligations, resolve disputes,
        and enforce agreements. Local app data can typically be removed by uninstalling the app or deleting app data
        (this does not automatically cancel subscriptions).
      </p>

      <h2>7) Security</h2>
      <p>
        We use reasonable safeguards to protect information. However, no method of transmission or storage is 100% secure.
      </p>

      <h2>8) Children’s privacy</h2>
      <p>
        Zendrhax Services are not intended for children under 13 (or the minimum legal age in your jurisdiction).
        We do not knowingly collect personal information from children.
      </p>

      <h2>9) International users</h2>
      <p>
        If you use the Services from outside the United States, you understand that your information may be processed
        in jurisdictions where Zendrhax or its providers operate.
      </p>

      <h2>10) Changes to this policy</h2>
      <p>
        We may update this Privacy Policy from time to time. The latest version will be posted on this page with an updated effective date.
      </p>

      <h2>11) Contact us</h2>
      <p>
        If you have questions about this Privacy Policy, contact us at
        <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>
        </a>.
      </p>

      <div class="legal-links">
        <a href="<?= $base ?>/help">Help</a>
        <span class="dot">•</span>
        <a href="<?= $base ?>/contact">Contact</a>
      </div>
    </section>

    <footer class="legal-footer">
      <div class="small">© <?= date('Y') ?> <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</div>
    </footer>

  </div>
</div>

<style>
  .legal-page{ padding:24px 16px; }
  .legal-container{ max-width:900px; margin:0 auto; }
  .legal-header{ margin-bottom:14px; }
  .legal-title{ margin:0; font-size:28px; line-height:1.2; }
  .legal-subtitle{ margin-top:6px; opacity:.75; }
  .legal-card{
    border:1px solid rgba(0,0,0,.12);
    border-radius:16px;
    padding:18px;
  }
  .legal-card h2{ margin:18px 0 8px; font-size:18px; }
  .legal-card p{ margin:0 0 12px; line-height:1.6; }
  .legal-card ul{ margin:0 0 12px 18px; line-height:1.6; }
  .legal-links{ margin-top:16px; opacity:.85; }
  .legal-links a{ text-decoration:none; font-weight:700; }
  .dot{ padding:0 8px; }
  .legal-footer{ margin-top:16px; opacity:.75; }
  .small{ font-size:12px; }
  body.dark .legal-card{ border-color: rgba(255,255,255,.14); }
</style>
