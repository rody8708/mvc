<?php
// Obtener vista actual para cargar CSS y JS específicos si existen
$cssFile = "assets/css/{$viewName}.css";
$jsFile  = "assets/js/{$viewName}.js";

$cssPath = BASE_PATH . '/public/' . $cssFile;
$jsPath  = BASE_PATH . '/public/' . $jsFile;

$cssUrl = file_exists($cssPath) ? BASE_URL . $cssFile : null;
$jsUrl  = file_exists($jsPath)  ? BASE_URL . $jsFile  : null;

$notifConfig = require BASE_PATH . '/config/notifications.php';

// 🌓 Detectar si el usuario tiene modo oscuro activado desde el JSON
$darkMode = false;
if (isset($_SESSION['user']['id'])) {
    $userJson = \App\Core\Functions::getUserJson($_SESSION['user']['id']);
    $darkMode = isset($userJson['dark_mode']) && $userJson['dark_mode'] ? true : false;
}
?>

<?php
// ✅ Encabezado global con Bootstrap, íconos y estilos principales
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<!-- ✅ Estilos específicos de la vista si existen -->
<?php if ($cssUrl): ?>
  <link rel="stylesheet" href="<?= $cssUrl ?>">
<?php endif; ?>

<?php
// ✅ Barra de navegación
require_once BASE_PATH . '/app/views/layouts/navbar.php';
?>

<!-- Loader de Modo Oscuro -->
<div id="darkModeLoader" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.5); z-index: 1050;">
  <div class="d-flex justify-content-center align-items-center h-100">
    <div class="spinner-border text-light" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
  </div>
</div>


<!-- ✅ Alerta flotante reutilizable -->
<div id="floatingAlert"
   class="alert position-fixed start-50 translate-middle-x text-center d-none"
   style="z-index: 99999; top: 70px; min-width: 300px; max-width: 90vw;">
</div>

<!-- ✅ Contenido principal -->
<main class="container py-5">
  <?php if (isset($content)) echo $content; ?>
</main>

<?php
// ✅ Pie de página
require_once BASE_PATH . '/app/views/layouts/footer.php';
?>

<!-- 🔁 Variables globales disponibles para JavaScript -->
<script>
  const BASE_URL = "<?= BASE_URL ?>";
  const NOTIFICATION_METHOD = "<?= $notifConfig['method'] ?>";
</script>

<!-- ✅ Script global de la aplicación -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ✅ Script específico de la vista si existe -->
<?php if ($jsUrl): ?>
  <script src="<?= $jsUrl ?>"></script>
<?php endif; ?>

<!-- ✅ Mostrar alerta flotante si existe un mensaje en sesión -->
<?php if (!empty($_SESSION['flash_alert'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      showFloatingAlert("<?= $_SESSION['flash_alert']['message'] ?>", "<?= $_SESSION['flash_alert']['type'] ?>");
    });
  </script>
  <?php unset($_SESSION['flash_alert']); ?>
<?php endif; ?>
