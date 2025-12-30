<?php $modulesPath = BASE_PATH . '/config/modules.json';
      $customModules = file_exists($modulesPath) ? json_decode(file_get_contents($modulesPath), true) : [];
?>     
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= BASE_URL ?>">App</a>

    <!-- Botón hamburguesa -->
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
      aria-controls="mobileSidebar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- ⚙️ Menú lateral para móviles -->
    <div class="offcanvas offcanvas-start bg-dark text-white d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white" id="mobileSidebarLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
    
      <div class="offcanvas-body">
        <ul class="navbar-nav">
    
          <?php
            $isLoggedIn = !empty($_SESSION['user']);
            $isAdmin    = $isLoggedIn && \App\Core\User::isAdmin();
          ?>
    
          <li><a class="nav-link text-white" href="<?= BASE_URL ?>">🏠 Dashboard</a></li>
    
          <?php foreach ($customModules as $mod): ?>
            <?php
              $authOnly  = !empty($mod['auth_only']) && $mod['auth_only'] === true;
              $adminOnly = !empty($mod['admin_only']) && $mod['admin_only'] === true;
    
              // 1) Si requiere login y no está logueado -> no mostrar
              if ($authOnly && !$isLoggedIn) {
                continue;
              }
    
              // 2) Si es admin only y no es admin -> no mostrar
              if ($adminOnly && !$isAdmin) {
                continue;
              }
            ?>
    
            <li class="nav-item">
              <a class="nav-link text-white" href="<?= BASE_URL . $mod['slug'] ?>">
                <?= ucfirst(htmlspecialchars($mod['menu_label'])) ?>
              </a>
            </li>
          <?php endforeach; ?>
    
          <?php if ($isLoggedIn): ?>
            <hr class="text-secondary">
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>profile">👤 Mi perfil</a></li>
    
            <?php if ($isAdmin): ?>
              <li><a class="nav-link text-white" href="<?= BASE_URL ?>logs">📜 Logs</a></li>
              <li><a class="nav-link text-white" href="<?= BASE_URL ?>admin/users">👥 Usuarios</a></li>
              <li><a class="nav-link text-white" href="<?= BASE_URL ?>admin/crud-generator">🛠️ Generador de módulos</a></li>
            <?php endif; ?>
    
            <li class="nav-item">
              <button class="nav-link text-white border-0 bg-transparent" id="toggleDarkModeBtn">🌙 Modo oscuro</button>
            </li>
            <li><a class="nav-link text-white" href="<?= BASE_URL ?>auth/logout">🚪 Cerrar sesión</a></li>
          <?php else: ?>
            <hr class="text-secondary">
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>auth/login">Iniciar sesión</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>auth/register">Registrarse</a></li>
          <?php endif; ?>
    
        </ul>
      </div>
    </div>


    <!-- Menú de escritorio -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>">Dashboard</a>
          </li>
        <?php foreach ($customModules as $mod): ?>
            <?php
                $isLogged = !empty($_SESSION['user']);
        
                $authOnly  = isset($mod['auth_only']) && $mod['auth_only'] === true;
                $adminOnly = isset($mod['admin_only']) && $mod['admin_only'] === true;
        
                // IMPORTANT: only check admin if logged in
                $isAdmin = $isLogged ? \App\Core\User::isAdmin() : false;
        
                // 1) Requires login but user is not logged -> hide
                if ($authOnly && !$isLogged) {
                    continue;
                }
        
                // 2) Requires admin but user is not admin -> hide
                if ($adminOnly && !$isAdmin) {
                    continue;
                }
            ?>
        
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL . $mod['slug'] ?>">
                    <?= ucfirst(htmlspecialchars($mod['menu_label'])) ?>
                </a>
            </li>
        <?php endforeach; ?>



      </ul>

      <!-- Lado derecho: notificaciones y perfil -->
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if (!empty($_SESSION['user'])): ?>

          <!-- 🔔 Notificaciones -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle position-relative" href="#" role="button"
              data-bs-toggle="dropdown" id="notificationToggle">
              <i class="bi bi-bell fs-5"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                id="notifBadge">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 350px;">
              <li style="max-height: 350px; overflow-y: auto;" id="notificationList">
                <div class="dropdown-item text-center text-muted py-3">Cargando notificaciones...</div>
              </li>
              <li style="cursor: pointer;" class="mark-read-all text-center border-top p-2 d-none" id="markAllContainer">
                <div id="markAllBtn"><i class="bi bi-check2-circle me-2"></i>Marcar todas como leídas</div>
              </li>
            </ul>
          </li>

          <!-- 👤 Usuario -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <img id="avatarPreviewNav" src="<?= \App\Core\Functions::getUserAvatarUrl($_SESSION['user']['id']) ?>" class="rounded-circle me-2" width="30" height="30"
               style="object-fit: cover;"
               alt="Avatar">
              <?= htmlspecialchars($_SESSION['user']['name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end animated-dropdown">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>profile">👤 Mi perfil</a></li>
              <li><hr class="dropdown-divider"></li>
              <?php if (\App\Core\User::isAdmin()): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>logs">📜 Logs</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/users">👥 Usuarios</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/crud-generator">🛠️ Generador de módulos</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout">🚪 Cerrar sesión</a></li>
            </ul>
          </li>

          <!-- 🌙 Botón modo oscuro escritorio -->
          <li class="nav-item d-none d-lg-block">
            <button class="btn btn-outline-light btn-sm" id="toggleDarkModeBtn">🌙</button>
          </li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/login">Iniciar sesión</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/register">Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>