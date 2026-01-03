<?php
namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller {

    public function fetch() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }

        $userId = $_SESSION['user']['id'];        
        $notifications = $this->notificationModel->getUserNotifications($userId);

        echo json_encode(['success' => true, 'notifications' => $notifications]);
    }

    public function markAsRead() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de notificación no proporcionado']);
            return;
        }
        
        $success = $this->notificationModel->markAsRead($id);

        echo json_encode(['success' => $success]);
    }

    public function markAllAsRead() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->notificationModel->markAllAsRead($_SESSION['user']['id']);

        echo json_encode(['success' => true]);
    }

}
?>