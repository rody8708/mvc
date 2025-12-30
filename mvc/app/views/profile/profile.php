<div class="container mt-5 mb-5">
  <h3 class="mb-4 text-center">Mi Perfil</h3>

  <div class="row gy-4">
    <!-- 📷 Columna izquierda: avatar y preferencias -->
    <div class="col-lg-4">
      <!-- Avatar -->
      <div class="card text-center shadow-sm">
        <div class="card-header bg-dark text-white">Foto de perfil</div>
        <div class="card-body">
          <img id="avatarPreview" src="<?= \App\Core\Functions::getUserAvatarUrl($_SESSION['user']['id']) ?>" class="rounded-circle" width="120" height="120"
               style="object-fit: cover;"
               alt="Avatar">
          <form id="avatarForm" enctype="multipart/form-data">
            <input type="file" name="avatar" id="avatarInput" class="form-control mb-2 mt-2" accept="image/*" required>
            <button type="submit" class="btn btn-primary w-100">Actualizar Avatar</button>
          </form>
        </div>
      </div>

      <!-- Preferencias -->
      <div class="card mt-4 shadow-sm">
        <div class="card-header bg-primary text-white">Preferencias</div>
        <div class="card-body">
          <?php
          // Cargar los datos del usuario desde su JSON
          $userJson = \App\Core\Functions::getUserJson($_SESSION['user']['id']);
          $darkMode = isset($userJson['dark_mode']) && $userJson['dark_mode'] ? true : false;
          ?>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="toggleDarkModeSwitch"
              <?= $darkMode ? 'checked' : '' ?>>
            <label class="form-check-label" for="toggleDarkMode">Modo Oscuro</label>
          </div>
            <?php
            $userJson = \App\Core\Functions::getUserJson($_SESSION['user']['id']);
            $language = isset($userJson['language']) ? $userJson['language'] : 'en'; // 🔥 por defecto 'en' si no está
            ?>
          <div class="mb-2">
            <label for="languageSwitch" class="form-label">Idioma</label>
            <select id="languageSwitch" class="form-select">
              <option value="es" <?= $language === 'es' ? 'selected' : '' ?>>Español</option>
              <option value="en" <?= $language === 'en' ? 'selected' : '' ?>>English</option>
            </select>
          </div>

        </div>
      </div>
    </div>

    <!-- 👤 Columna derecha: perfil, contraseña, historial, eliminación -->
    <div class="col-lg-8">
      <!-- Información del perfil -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">Datos del Usuario</div>
        <form id="profileForm" class="card-body">
          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Actualizar Datos</button>
        </form>
      </div>

      <!-- Contraseña -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">Cambiar Contraseña</div>
        <form id="passwordForm" class="card-body">
          <div class="mb-3">
            <label class="form-label">Contraseña Actual</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar Contraseña</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-dark w-100">Cambiar Contraseña</button>
        </form>
      </div>

      <!-- Historial de actividad -->
      <!-- 🕓 Historial de actividad mejorado -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">Historial de Actividad</div>
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
          <?php if (!empty($logs)): ?>
            <ul class="list-group list-group-flush small">
              <?php foreach ($logs as $log): ?>
                <li class="list-group-item d-flex align-items-center">
                  <span class="badge bg-primary me-2" style="min-width: 70px;">
                    <?= date('d/m', strtotime($log['timestamp'])) ?>
                  </span>
                  <div>
                    <div class="fw-bold"><?= htmlspecialchars(preg_replace('/\: ID \d+/', '', $log['action'])) ?></div>
                    <small class=""><?= date('H:i:s', strtotime($log['timestamp'])) ?></small>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="text-center">Sin actividad reciente.</p>
          <?php endif; ?>
        </div>
      </div>


      <!-- Eliminar cuenta -->
      <div class="text-center">
        <button class="btn btn-danger w-100" id="deleteAccountBtn">Eliminar Cuenta</button>
      </div>
    </div>
  </div>
</div>
