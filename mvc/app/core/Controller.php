<?php
namespace App\Core;

use App\Core\Logger;
use App\Core\Functions;
use App\Core\User;
use App\Models\NotificationModel;

class Controller {    
    protected $functions; // Instance of the Functions class
    protected $notificationModel;
    protected $user;


    public function __construct() {
        $this->functions        = new Functions();       // initialize
        $this->notificationModel = new NotificationModel();
        $this->user             = new User();
    }


    /**
     * Load a model
     * @param string $model - Model name
     * @return object - Model instance
     */
    public function loadModel($model) {
        $modelPath = BASE_PATH . "/app/models/$model.php";

        // Check if the model file exists before loading it
        if (file_exists($modelPath)) {
            require_once $modelPath; // Include the model file
            $modelClass = "\\App\\Models\\$model"; // Model namespace
            return new $modelClass(); // Return an instance of the model
        } else {
            // Log an error and stop execution if the model does not exist
            Logger::error("The model '$model' does not exist.");
            die("The model '$model' does not exist.");
        }
    }

    /**
     * Load a view
     * @param string $view - View name
     * @param array $data - Data to pass to the view
     */
    public function loadView($view, $data = []) {
        $viewPath = BASE_PATH . "/app/views/$view.php";

        if (file_exists($viewPath)) {
            $viewName = basename($view); // ✅ name like 'admin_users'
            $data['viewName'] = $viewName; // ✅ add it to the data array

            extract($data); // ← now $viewName also exists

            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            require BASE_PATH . '/app/views/layouts/main.php';
        } else {
            Logger::error("The view '$view' does not exist.");
            die("Error: The view '$view' does not exist.");
        }
    }





    /**
     * Method to test the functions of the functions.php file
     */
    public function testFunctions() {
        echo $this->functions->sayHello(); // Calls a test function from functions.php
    }


    protected function requireLogin() {
        if (!\App\Core\Functions::isLoggedIn()) {
            \App\Core\Logger::warning("Attempt to access without an active session to " . $_SERVER['REQUEST_URI']);
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }
    }

    protected function requireAdmin() {
        if (!\App\Core\User::isAdmin()) {
            \App\Core\Logger::warning("Attempt to access restricted area (non-admin) to " . $_SERVER['REQUEST_URI']);
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }
    }


    protected function setFlashAlert($message, $type = 'success') {
    $_SESSION['flash_alert'] = [
        'message' => $message,
        'type' => $type // 'success', 'danger', 'info', etc.
    ];
}



}


?>

