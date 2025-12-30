<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    /**
     * Método para mostrar la vista de inicio
     */
    public function index() {
        
        $path1 = __DIR__ . '/../config/app_version.php';
        $path2 = __DIR__ . '/../../config/app_version.php';
    
        $cfg = [
            'latest_version'        => '1.0.0',
            'min_supported_version' => '1.0.0',
            'download_url_android'  => BASE_URL . 'download/apk',
            'download_url_windows'  => BASE_URL . 'download/windows',
            'changelog'             => '',
        ];
    
        if (file_exists($path1)) {
            $fileCfg = require $path1;
            $cfg = array_merge($cfg, $fileCfg);
        } elseif (file_exists($path2)) {
            $fileCfg = require $path2;
            $cfg = array_merge($cfg, $fileCfg);
        }
    
        $data = [
            'title'               => 'Zendrhax Invoices – Downloads',
            'latest_version'      => $cfg['latest_version']        ?? '1.0.0',
            'min_supported'       => $cfg['min_supported_version'] ?? '1.0.0',
            'download_android'    => $cfg['download_url_android']  ?? (BASE_URL . 'download/apk'),
            'download_windows'    => $cfg['download_url_windows']  ?? (BASE_URL . 'download/windows'),
            'changelog'           => $cfg['changelog']             ?? '',
            // Ajusta la ruta del logo a donde lo tengas realmente
            'logo_url'            => BASE_URL . 'assets/images/zendrhax-invoices-logo.png',
    ];    
        
        $this->loadView('home', $data);
    }
    
    
    public function contact() {
        
        $this->loadView('layouts/contact');
    }
    
    public function privacy(): void
    {
        $this->loadView('layouts/privacy');
    }
    
    public function help(): void
    {
        $this->loadView('layouts/help');
    }

}
?>