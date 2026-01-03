<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\LogModel;

class LogsController extends Controller {
    
    public function index() {
        $this->requireAdmin();
        $model = new LogModel();

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
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
            'user'      => filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING),
            'level'     => filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING),
            'ip'        => filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_STRING),
            'action'    => filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING),
            'date_from' => filter_input(INPUT_POST, 'date_from', FILTER_SANITIZE_STRING),
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