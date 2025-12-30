<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Logger;
use App\Models\ToolModel;
use App\Core\Functions;

class ToolController extends Controller {

    public function crudGeneratorView() {
        $this->requireAdmin(); // Solo admins pueden entrar
        $this->loadView('admin/crud-generator');
    }

    public function generateCrud() {
        header('Content-Type: application/json');
        ob_clean();

        // Leer datos del formulario
        $moduleName = trim($_POST['module'] ?? '');
        $fieldsRaw = trim($_POST['fields'] ?? '');
        $menuLabel = trim($_POST['menu_label'] ?? '');

        $modulesFile = BASE_PATH . '/config/modules.json';
        $modules = file_exists($modulesFile) ? json_decode(file_get_contents($modulesFile), true) : [];

        // Verificar si ya existe
        $exists = false;
        foreach ($modules as $mod) {
            if (strtolower($mod['slug']) === strtolower($moduleName)) {
                $exists = true;
                echo json_encode(['success' => false, 'message' => "El módulo '$moduleName' ya existe."]);
                return;
            }
        }

        if (!$moduleName /*|| !$fieldsRaw*/) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            return;
        }

        $slug = strtolower($moduleName);
        $tool = new ToolModel();
        $success = $tool->generateModule($moduleName, $fieldsRaw);

        if ($success) {
            Functions::addModuleToJson($moduleName, $slug, $menuLabel); // ✅ Añadir al modules.json
            Logger::info("Módulo '$moduleName' generado por el administrador.");
            echo json_encode(['success' => true, 'message' => "Módulo '$moduleName' creado exitosamente."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error al crear el módulo '$moduleName'."]);
        }
    }

    public function deleteModule() {
        header('Content-Type: application/json');
        ob_clean();

        $moduleName = trim($_POST['module'] ?? '');
        if (!$moduleName) {
            echo json_encode(['success' => false, 'message' => 'Nombre del módulo requerido.']);
            return;
        }

        $slug = strtolower($moduleName);
        $tool = new ToolModel();
        $success = $tool->deleteModule($moduleName);

        if ($success) {
            Functions::removeModuleFromJson($slug); // ✅ Quitar del modules.json
            Logger::info("Módulo '$moduleName' eliminado por el administrador.");
            echo json_encode(['success' => true, 'message' => "Módulo '$moduleName' eliminado correctamente."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error al eliminar el módulo '$moduleName'."]);
        }
    }

    public function getModules() {
        header('Content-Type: application/json');
        ob_clean();

        $modulesFile = BASE_PATH . '/config/modules.json';
        if (!file_exists($modulesFile)) {
            echo json_encode([]);
            return;
        }

        $modules = json_decode(file_get_contents($modulesFile), true) ?? [];
        echo json_encode($modules);
    }


}
