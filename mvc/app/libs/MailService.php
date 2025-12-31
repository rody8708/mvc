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
    

}

