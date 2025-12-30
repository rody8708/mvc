<?php
namespace App\Core;

use App\Core\Logger;
use App\Core\Functions;
use App\Core\User;
use App\models\NotificationModel;

class Controller {    
    protected $functions; // Instancia de la clase Functions
    protected $notificationModel;
    protected $user;


    public function __construct() {
        $this->notificationModel = new NotificationModel();
    }


    /**
     * Cargar un modelo
     * @param string $model - Nombre del modelo
     * @return object - Instancia del modelo
     */
    public function loadModel($model) {
        $modelPath = BASE_PATH . "/app/models/$model.php";

        // Verifica si el archivo del modelo existe antes de cargarlo
        if (file_exists($modelPath)) {
            require_once $modelPath; // Incluye el archivo del modelo
            $modelClass = "\\App\\Models\\$model"; // Espacio de nombres del modelo
            return new $modelClass(); // Retorna una instancia del modelo
        } else {
            // Registra un error en el log y detiene la ejecución si el modelo no existe
            Logger::error("El modelo '$model' no existe.");
            die("El modelo '$model' no existe.");
        }
    }

    /**
     * Cargar una vista
     * @param string $view - Nombre de la vista
     * @param array $data - Datos a pasar a la vista
     */
    public function loadView($view, $data = []) {
        $viewPath = BASE_PATH . "/app/views/$view.php";

        if (file_exists($viewPath)) {
            $viewName = basename($view); // ✅ nombre como 'admin_users'
            $data['viewName'] = $viewName; // ✅ agregarlo al array de datos

            extract($data); // ← ahora también existe $viewName

            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            require BASE_PATH . '/app/views/layouts/main.php';
        } else {
            Logger::error("La vista '$view' no existe.");
            die("Error: La vista '$view' no existe.");
        }
    }





    /**
     * Método para probar las funciones del archivo functions.php
     */
    public function testFunctions() {
        echo $this->functions->sayHello(); // Llama a una función de prueba desde functions.php
    }


    protected function requireLogin() {
        if (!\App\Core\Functions::isLoggedIn()) {
            \App\Core\Logger::warning("Intento de acceso sin sesión iniciada a " . $_SERVER['REQUEST_URI']);
            header("Location: " . BASE_URL . "auth/login");
            exit;
        }
    }

    protected function requireAdmin() {
        if (!\App\Core\User::isAdmin()) {
            \App\Core\Logger::warning("Intento de acceso restringido (no admin) a " . $_SERVER['REQUEST_URI']);
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