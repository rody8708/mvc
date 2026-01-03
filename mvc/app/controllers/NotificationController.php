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

    public function index() {
        $this->requireAdmin();

        $notifications = $this->notificationModel->getAllNotifications();
        $this->loadView('admin/notifications', compact('notifications'));
    }

    public function create() {
        $this->requireAdmin();

        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

        if (!$title || !$message) {
            echo json_encode(['success' => false, 'message' => 'Título y mensaje son obligatorios']);
            return;
        }

        $success = $this->notificationModel->createNotification($title, $message);
        echo json_encode(['success' => $success]);
    }

    public function delete() {
        $this->requireAdmin();

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de notificación no proporcionado']);
            return;
        }

        $success = $this->notificationModel->deleteNotification($id);
        echo json_encode(['success' => $success]);
    }

    public function getNotificationsData() {
        header('Content-Type: application/json');
        ob_clean();

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }

        $notifications = $this->notificationModel->getAllNotifications();
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    }

}
?>