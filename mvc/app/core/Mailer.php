<?php

namespace App\Core;

use App\Libs\MailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Core\Logger;

/**
 * Mailer service class
 *
 * This class centralizes email sending logic using PHPMailer. It
 * instantiates the MailService in the constructor so that a configured
 * PHPMailer instance can be reused across different email methods.
 */
class Mailer
{
    /**
     * Configured PHPMailer instance
     *
     * @var PHPMailer
     */
    protected PHPMailer $mail;

    public function __construct()
    {
        // Instantiate the MailService and obtain a configured PHPMailer
        $mailService = new MailService();
        // Assumes MailService exposes a public getMailer() method that
        // returns a configured PHPMailer instance
        $this->mail = $mailService->getMailer();
    }

    /**
     * Send account activation email
     *
     * @param string $to    Recipient email address
     * @param string $name  Recipient name
     * @param string $token Activation token
     *
     * @return bool
     */
    public function sendActivationEmail(string $to, string $name, string $token): bool
    {
        $mail = clone $this->mail; // Clone to avoid recipient list overlap

        try {
            // Set recipient and subject
            $mail->addAddress($to, $name);
            $mail->Subject = 'Activa tu cuenta';
            $activationUrl = BASE_URL . 'activar?token=' . urlencode($token);

            // Email body (HTML)
            $mail->Body = "<h3>Hola {$name}</h3>\n<p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>\n<p><a href='{$activationUrl}'>{$activationUrl}</a></p>";
            $mail->isHTML(true);

            $mail->send();
            Logger::info("Correo de activación enviado a {$to}");
            return true;
        } catch (Exception $e) {
            Logger::error("Error al enviar correo de activación: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send password reset email
     *
     * @param string $to
     * @param string $name
     * @param string $token
     *
     * @return bool
     */
    public function sendPasswordResetEmail(string $to, string $name, string $token): bool
    {
        $mail = clone $this->mail;

        try {
            $mail->addAddress($to, $name);
            $mail->Subject = 'Recupera tu password';
            $resetLink = BASE_URL . 'auth/reset-password-form?token=' . urlencode($token);

            $mail->Body = "<h3>Hola {$name}</h3>\n<p>Hemos recibido una solicitud para restablecer tu password.</p>\n<p>Haz clic en el siguiente enlace para cambiarla:</p>\n<p><a href='{$resetLink}'>{$resetLink}</a></p>\n<p>Este enlace expirará en 1 hora.</p>";
            $mail->isHTML(true);

            $mail->send();
            Logger::info("Correo de recuperación enviado a {$to}");
            return true;
        } catch (Exception $e) {
            Logger::error("Error al enviar correo de recuperación: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send password changed confirmation email
     *
     * @param string $to
     * @param string $name
     *
     * @return bool
     */
    public function sendPasswordChangedEmail(string $to, string $name): bool
    {
        $mail = clone $this->mail;

        try {
            $mail->addAddress($to, $name);
            $mail->Subject = 'Tu password ha sido actualizada';

            $mail->Body = "<h4>Hola {$name},</h4>\n<p>Te informamos que tu password fue cambiada recientemente. Si no realizaste este cambio, por favor contáctanos de inmediato.</p>";
            $mail->isHTML(true);

            $mail->send();
            Logger::info("Correo de confirmación de cambio de password enviado a {$to}");
            return true;
        } catch (Exception $e) {
            Logger::error("Fallo al enviar correo de cambio de password: {$mail->ErrorInfo}");
            return false;
        }
    }
}

