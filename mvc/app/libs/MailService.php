<?php
// app/libs/MailService.php

namespace App\Libs;

use App\Core\Logger;

// ================== CARGAR PHPMailer A MANO ==================
// Probamos varias rutas típicas donde podría estar PHPMailer.
// AJUSTA esto si tu estructura es distinta.
$phpMailerPaths = [
    __DIR__ . '/PHPMailer/',       // app/app/libs/PHPMailer/src
    __DIR__ . '/../PHPMailer/src',    // app/app/PHPMailer/src
    __DIR__ . '/../phpmailer/src',    // app/app/phpmailer/src
];

$loaded = false;
foreach ($phpMailerPaths as $base) {
    if (file_exists($base . '/PHPMailer.php')) {
        require_once $base . '/Exception.php';
        require_once $base . '/PHPMailer.php';
        require_once $base . '/SMTP.php';
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    throw new \RuntimeException(
        "No se encontraron los archivos de PHPMailer. ".
        "Revisa \$phpMailerPaths en MailService.php y ajusta la ruta a tu carpeta PHPMailer/src."
    );
}

// Ahora que ya están requeridos, podemos usar las clases:
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ================== CLASE MAILSERVICE ==================

class MailService
{
    /**
     * Mail config array loaded from config/mail.php
     *  - host
     *  - port
     *  - username
     *  - password
     *  - from_email
     *  - from_name
     *  - encryption
     *
     * @var array<string,mixed>
     */
    protected array $config = [];

    public function __construct()
    {
        // Intentamos primero app/app/config/mail.php
        $path1 = __DIR__ . '/../config/mail.php';
        // Luego app/config/mail.php (por si lo tienes allí)
        $path2 = __DIR__ . '/../../config/mail.php';

        if (file_exists($path1)) {
            $this->config = require $path1;
        } elseif (file_exists($path2)) {
            $this->config = require $path2;
        } else {
            throw new \RuntimeException(
                "Mail config file not found. Tried:\n - {$path1}\n - {$path2}"
            );
        }
    }

    /**
     * Create and configure PHPMailer instance from config.
     */
    protected function createMailer(): PHPMailer
    {
        $cfg = $this->config;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['host'] ?? 'smtp.gmail.com';
        $mail->Port       = (int)($cfg['port'] ?? 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['username'] ?? '';
        $mail->Password   = $cfg['password'] ?? '';
        $mail->SMTPSecure = $cfg['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;

        $fromEmail = $cfg['from_email'] ?? 'noreply@zendrhax.com';
        $fromName  = $cfg['from_name']  ?? 'Zendrhax';

        $mail->setFrom($fromEmail, $fromName);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    /**
     * Send the trial license email.
     *
     * @param string $toEmail
     * @param string $licenseKey
     * @param string $expUtc     Expiration datetime in "Y-m-d H:i:s" (UTC) format
     *
     * @return bool
     */
    
    public function sendTrialLicenseEmail(
        string $toEmail,
        string $licenseKey,
        string $expUtc
    ): bool {
        try {
            $mail = $this->createMailer();

            $mail->addAddress($toEmail);

            $mail->Subject = 'Your trial license - Zendrhax Invoices';

            // Texto amigable
            $bodyHtml = '
                <p>Hi!</p>
                <p>Thanks for trying <strong>Zendrhax Invoices</strong>.</p>
                <p>Here is your <strong>7-day trial license</strong>:</p>
                <p style="font-size:18px;">
                    <strong>License code:</strong> ' . htmlspecialchars($licenseKey, ENT_QUOTES, 'UTF-8') . '
                </p>
                <p>
                    <strong>Valid until (UTC):</strong> ' . htmlspecialchars($expUtc, ENT_QUOTES, 'UTF-8') . '
                </p>
                <p>
                    Open the app and enter this code on the license screen to start your trial.
                </p>
                <br>
                <p>Best regards,<br>Zendrhax Invoices</p>
            ';

            $mail->Body    = $bodyHtml;
            $mail->AltBody =
                "Hi!\n\n" .
                "Here is your 7-day trial license for Zendrhax Invoices:\n\n" .
                "License code: {$licenseKey}\n" .
                "Valid until (UTC): {$expUtc}\n\n" .
                "Open the app and enter this code on the license screen to start your trial.\n\n" .
                "Best regards,\nZendrhax Invoices";

            $mail->send();
            Logger::info("sendTrialLicenseEmail => {$toEmail}");
            return true;
        } catch (Exception $e) {
            Logger::error('sendTrialLicenseEmail failed', [
                'email' => $toEmail,
                'error' => $e->getMessage(),
            ]);;
            return false;
        }
    }
    
    
    public function sendLicenseEmail(
        string $toEmail,
        string $licenseKey,
        string $plan = 'pro',
        ?string $expUtc = null,
        ?int $maxDevices = null
    ): bool {
        try {
            $toEmail = trim($toEmail);
            if ($toEmail === '' || $licenseKey === '') {
                return false;
            }
    
            $plan = strtolower(trim($plan));
            $planLabel    = ($plan === 'business') ? 'Business' : 'Pro';
            $devicesLabel = ($maxDevices !== null)
                ? (string)$maxDevices
                : (($planLabel === 'Business') ? '2' : '1');
    
            $mail = $this->createMailer();
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
    
            $mail->addAddress($toEmail);
            $mail->Subject = "Your {$planLabel} subscription is active - Zendrhax Invoice";
    
            $validUntilHtml = '';
            $validUntilText = '';
            if ($expUtc) {
                $validUntilHtml = "<p><strong>Valid until (UTC):</strong> " . htmlspecialchars($expUtc, ENT_QUOTES, 'UTF-8') . "</p>";
                $validUntilText = "Valid until (UTC): {$expUtc}\n";
            }
    
            $mail->Body = '
                <p>Hi!</p>
                <p>Your subscription is now <strong>ACTIVE</strong>.</p>
                <p>
                    <strong>Plan:</strong> ' . $planLabel . '<br>
                    <strong>Max devices:</strong> ' . htmlspecialchars($devicesLabel, ENT_QUOTES, 'UTF-8') . '
                </p>
                ' . $validUntilHtml . '
                <p>
                    <strong>License key:</strong><br>
                    ' . htmlspecialchars($licenseKey, ENT_QUOTES, 'UTF-8') . '
                </p>
                <br>
                <p>Best regards,<br>Zendrhax Invoice</p>
            ';
    
            $mail->AltBody =
                "Hi!\n\n" .
                "Your subscription is now ACTIVE.\n\n" .
                "Plan: {$planLabel}\n" .
                "Max devices: {$devicesLabel}\n" .
                $validUntilText . "\n" .
                "License key:\n{$licenseKey}\n\n" .
                "Best regards,\nZendrhax Invoice";
    
            $mail->send();
    
            Logger::info("sendLicenseEmail => to={$toEmail} plan={$planLabel}");
            return true;
    
        } catch (\Exception $e) {
            Logger::error('sendLicenseEmail failed', [
                'email' => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }


    public function sendSubscriptionCancelledEmail(
        string $toEmail,
        string $licenseKey,
        string $expUtc
    ): bool {
        try {
            $toEmail = trim($toEmail);
            if ($toEmail === '' || $licenseKey === '') {
                return false;
            }
    
            $mail = $this->createMailer();
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
    
            $mail->addAddress($toEmail);
            $mail->Subject = 'Your subscription has been cancelled - Zendrhax Invoice';
    
            $mail->Body = '
                <p>Hi,</p>
                <p>Your subscription has been <strong>successfully cancelled</strong>.</p>
                <p>
                    You will continue to have access until:<br>
                    <strong>' . htmlspecialchars($expUtc, ENT_QUOTES, 'UTF-8') . ' (UTC)</strong>
                </p>
                <p>
                    <strong>License key:</strong><br>
                    ' . htmlspecialchars($licenseKey, ENT_QUOTES, 'UTF-8') . '
                </p>
                <br>
                <p>Best regards,<br>Zendrhax Invoice</p>
            ';
    
            $mail->AltBody =
                "Hi,\n\n" .
                "Your subscription has been successfully cancelled.\n\n" .
                "You will continue to have access until:\n" .
                "{$expUtc} (UTC)\n\n" .
                "License key:\n{$licenseKey}\n\n" .
                "Best regards,\nZendrhax Invoice";
    
            $mail->send();
    
            Logger::info("sendSubscriptionCancelledEmail => {$toEmail}");
            return true;
    
        } catch (\Exception $e) {
            Logger::error('sendSubscriptionCancelledEmail failed', [
                'email' => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    
    public function sendVerificationCodeEmail(string $toEmail, string $code): bool
    {
        try {
            $toEmail = trim($toEmail);
            if ($toEmail === '' || $code === '') {
                return false;
            }
    
            $mail = $this->createMailer();
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
    
            $mail->addAddress($toEmail);
            $mail->Subject = 'Your verification code - Zendrhax Invoice';
    
            $mail->Body = '
                <p>Hi,</p>
                <p>Here is your verification code:</p>
                <p style="font-size:22px; font-weight:bold;">
                    ' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '
                </p>
                <p>This code is valid for <strong>1 minute</strong>.</p>
                <p>If you did not request this code, you can safely ignore this email.</p>
                <br>
                <p>Best regards,<br>Zendrhax Invoice</p>
            ';
    
            $mail->AltBody =
                "Hi,\n\n" .
                "Here is your verification code:\n\n" .
                "{$code}\n\n" .
                "This code is valid for 1 minute.\n\n" .
                "If you did not request this code, you can safely ignore this email.\n\n" .
                "Best regards,\nZendrhax Invoice";
    
            $mail->send();
    
            Logger::info("sendVerificationCodeEmail => {$toEmail}");
            return true;
    
        } catch (\Exception $e) {
            Logger::error('sendVerificationCodeEmail failed', [
                'email' => $toEmail,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

}
