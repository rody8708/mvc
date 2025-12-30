<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LogModel;

class LogsController extends Controller {
    
    public function index() {
        $this->requireAdmin();
        $model = new LogModel();

        $page = $_GET['page'] ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $logs = $model->getLogs($perPage, $offset);
        $total = $model->countLogs();
        $totalPages = ceil($total / $perPage);

        $this->loadView('logs', compact('logs', 'page', 'totalPages'));
    }


    public function fetchPage() {
        header('Content-Type: application/json');
        ob_clean();

        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Recoger filtros del formulario
        $filters = [
            'user'      => trim($_POST['user'] ?? ''),
            'level'     => trim($_POST['level'] ?? ''),
            'ip'        => trim($_POST['ip'] ?? ''),
            'action'    => trim($_POST['action'] ?? ''),
            'date_from' => trim($_POST['date_from'] ?? ''),
            'date_to'   => trim($_POST['date_to'] ?? '')
        ];

        $logModel = new \App\Models\LogModel();
        $logs = $logModel->getFilteredLogs($perPage, $offset, $filters);
        $totalLogs = $logModel->countFilteredLogs($filters);
        $totalPages = ceil($totalLogs / $perPage);

        // Render tabla
        ob_start();
        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($log['timestamp']) . '</td>';
            echo '<td class="text-limit">' . htmlspecialchars($log['user']) . '</td>';
            echo '<td>' . htmlspecialchars($log['ip']) . '</td>';
            echo '<td class="text-limit">' . htmlspecialchars($log['os']) . '</td>';
            echo '<td class="text-limit">' . htmlspecialchars($log['browser']) . '</td>';
            echo '<td class="text-limit">' . htmlspecialchars($log['action']) . '</td>';
            echo '<td>' . htmlspecialchars($log['level']) . '</td>';
            echo '</tr>';
        }
        $tableHtml = ob_get_clean();

        // Render paginador
        ob_start();
        echo '<ul class="pagination justify-content-center">';
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page - 1) . '">Anterior</a></li>';
        }

        $range = 2;
        $start = max(1, $page - $range);
        $end = min($totalPages, $page + $range);

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $page ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }

        if ($page < $totalPages) {
            echo '<li class="page-item"><a class="page-link" href="#" data-page="' . ($page + 1) . '">Siguiente</a></li>';
        }
        echo '</ul>';
        $paginationHtml = ob_get_clean();

        // Respuesta en JSON
        echo json_encode([
            'success' => true,
            'table' => $tableHtml,
            'pagination' => $paginationHtml
        ]);
        exit;
    }




}

?>