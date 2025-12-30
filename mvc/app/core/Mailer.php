<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer/Exception.php';



class Mailer {

    protected static function getConfig() {
        return require BASE_PATH . '/config/mail.php';  // si está fuera de /app
        // o BASE_PATH . '/config/mail.php'; si está dentro de /app/config
    }


    public static function sendActivationEmail($to, $name, $token) {
        $mail = new PHPMailer(true);
        $config = self::getConfig();

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];


            // Remitente
            $mail->setFrom($config['from_email'], $config['from_name']);

            // Receptor
            $mail->addAddress($to, $name);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Activa tu cuenta';
            $activationUrl = BASE_URL . 'activar?token=' . $token;

            $mail->Body = "
                <h3>Hola $name</h3>
                <p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
                <p><a href='$activationUrl'>$activationUrl</a></p>
            ";

            $mail->send();
            Logger::info("Correo de activación enviado a $to");
            return true;
        } catch (Exception $e) {
            Logger::error("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }


    public static function sendPasswordResetEmail($to, $name, $token) {
        $mail = new PHPMailer(true);
        $config = self::getConfig();

        try {
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Recupera tu password';

            $resetLink = BASE_URL . 'auth/reset-password-form?token=' . urlencode($token);

            $mail->Body = "
                <h3>Hola $name</h3>
                <p>Hemos recibido una solicitud para restablecer tu password.</p>
                <p>Haz clic en el siguiente enlace para cambiarla:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>Este enlace expirará en 1 hora.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            Logger::error("Error al enviar correo de recuperacion: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendPasswordChangedEmail($to, $name) {
        $mail = new PHPMailer(true);
        $config = self::getConfig();
        
        try {
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Tu password ha sido actualizada';
            $mail->Body = "
                <h4>Hola $name,</h4>
                <p>Te informamos que tu password fue cambiada recientemente. Si no realizaste este cambio, por favor contáctanos de inmediato.</p>
            ";

            $mail->send();
            Logger::info("Correo de confirmación de cambio de password enviado a $to");
            return true;
        } catch (Exception $e) {
            Logger::error("Fallo al enviar correo de cambio de password: {$mail->ErrorInfo}");
            return false;
        }
    }


}
