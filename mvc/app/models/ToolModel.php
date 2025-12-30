<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Logger;

class ToolModel extends Model
{
    /**
     * Generate a basic CRUD-like module skeleton:
     * - Controller
     * - Model
     * - View
     * - CSS + JS
     * - Route file
     * - Image directory for the module
     */
    public function generateModule($module, $fields)
    {
        $module    = strtolower($module);
        $fieldsArr = array_map('trim', explode(',', $fields));

        // Paths for files and directories
        $paths = [
            'controller' => BASE_PATH . "/app/controllers/" . ucfirst($module) . "Controller.php",
            'model'      => BASE_PATH . "/app/models/" . ucfirst($module) . "Model.php",
            'viewDir'    => BASE_PATH . "/app/views/modules",
            'view'       => BASE_PATH . "/app/views/modules/$module.php",

            'cssDir'     => BASE_PATH . "/public/assets/css",
            'css'        => BASE_PATH . "/public/assets/css/{$module}.css",

            'jsDir'      => BASE_PATH . "/public/assets/js",
            'js'         => BASE_PATH . "/public/assets/js/{$module}.js",

            // Directorio de imágenes SOLO para este módulo
            'imgDir'     => BASE_PATH . "/public/assets/images/modules/{$module}",

            'routeDir'   => BASE_PATH . "/routes/modules",
            'route'      => BASE_PATH . "/routes/modules/{$module}.php",
        ];

        // Create needed directories if they don't exist
        foreach (['viewDir', 'cssDir', 'jsDir', 'imgDir', 'routeDir'] as $dirKey) {
            if (!is_dir($paths[$dirKey])) {
                if (!mkdir($paths[$dirKey], 0755, true)) {
                    Logger::error("Could not create directory: {$paths[$dirKey]}");
                    return false;
                }
            }
        }

        // Controller stub
        $controllerContent = "<?php\n"
            . "namespace App\Controllers;\n\n"
            . "use App\Core\Controller;\n\n"
            . "class " . ucfirst($module) . "Controller extends Controller\n"
            . "{\n"
            . "    public function index()\n"
            . "    {\n"
            . "        \$this->loadView('modules/$module');\n"
            . "    }\n"
            . "}\n";

        // Model stub
        $modelContent = "<?php\n"
            . "namespace App\Models;\n\n"
            . "use App\Core\Model;\n\n"
            . "class " . ucfirst($module) . "Model extends Model\n"
            . "{\n"
            . "    // Model for $module\n"
            . "}\n";

        // View stub (HEREDOC, ojo con el margen izquierdo del cierre HTML)
        $viewContent = <<<HTML
<div class="container py-5">
  <h1 class="mb-4">🚀 Module <strong>$module</strong> generated successfully</h1>

  <p class="lead mb-5">
    This module was created automatically to help you start faster. Below you can see the generated files and routes:
  </p>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">📂 Generated file structure</h5>
      <ul class="text-start">
        <li><strong>Controller:</strong> app/controllers/<?= ucfirst('$module') ?>Controller.php</li>
        <li><strong>Model:</strong> app/models/<?= ucfirst('$module') ?>Model.php</li>
        <li><strong>View:</strong> app/views/modules/<?= '$module' ?>.php</li>
        <li><strong>CSS:</strong> public/assets/css/<?= '$module' ?>.css</li>
        <li><strong>JavaScript:</strong> public/assets/js/<?= '$module' ?>.js</li>
        <li><strong>Images directory:</strong> public/assets/images/modules/<?= '$module' ?>/</li>
        <li><strong>Route file:</strong> routes/modules/<?= '$module' ?>.php</li>
      </ul>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title">🔗 Access URL</h5>
      <p class="card-text">
        You can access this module at:
        <code><?= BASE_URL . '$module' ?></code>
      </p>
    </div>
  </div>
</div>
HTML;

        // Route file for this module
        $controllerName = ucfirst($module);
        $routeContent = "<?php\n\n"
            . "\$router->addRoute('GET', '/$module', '{$controllerName}Controller@index');\n";

        // Create files
        if (
            file_put_contents($paths['controller'], $controllerContent) === false ||
            file_put_contents($paths['model'], $modelContent) === false ||
            file_put_contents($paths['view'], $viewContent) === false ||
            file_put_contents($paths['css'], "/* Styles for $module module */\n") === false ||
            file_put_contents($paths['js'], "// JS for $module module\n") === false ||
            file_put_contents($paths['route'], $routeContent) === false
        ) {
            Logger::error("Module '$module' files could not be created.");
            return false;
        }

        return true;
    }

    /**
     * Delete a module:
     * - Only the files belonging to that module
     * - The images directory ONLY for that module
     * - It DOES NOT delete the global "modules" folders
     */
    public function deleteModule($module)
    {
        $module = strtolower($module);

        $paths = [
            // Specific files for that module
            BASE_PATH . "/app/controllers/" . ucfirst($module) . "Controller.php",
            BASE_PATH . "/app/models/" . ucfirst($module) . "Model.php",
            BASE_PATH . "/app/views/modules/{$module}.php",
            BASE_PATH . "/public/assets/css/{$module}.css",
            BASE_PATH . "/public/assets/js/{$module}.js",
            BASE_PATH . "/routes/modules/{$module}.php",
        ];

        // Images directory ONLY for that module
        $imgDir = BASE_PATH . "/public/assets/images/modules/{$module}";

        // Delete files
        foreach ($paths as $path) {
            if (file_exists($path) && !is_dir($path)) {
                if (!unlink($path)) {
                    Logger::error("Could not delete file: $path");
                }
            }
        }

        // Delete module's image directory (if exists)
        if (is_dir($imgDir)) {
            $this->deleteDir($imgDir);
        }

        return true;
    }

    /**
     * Recursively delete a directory (used only for module-specific directories).
     */
    private function deleteDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                if (!unlink($file)) {
                    Logger::error("Could not delete file: $file");
                }
            }
        }

        if (!rmdir($dir)) {
            Logger::error("Could not remove directory: $dir");
        }
    }
}
