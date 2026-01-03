<div class="container mt-5">

    <h3 class="mb-4 text-center">Administración de Usuarios</h3>   

    <!-- Modal para nuevo usuario -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" id="addUserForm">
          <input type="hidden" name="csrf_token" value="<?= \App\Core\Functions::generateCSRFToken() ?>">  
          <div class="modal-header">
            <h5 class="modal-title">Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="addUserMsg" class="alert d-none"></div> <!-- Solo en el formulario de agregar -->
            <div id="editUserMsg" class="alert d-none"></div> <!-- Solo en el formulario de editar -->

            <div class="mb-3">
              <label>Nombre</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label>Correo</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label>Contraseña</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
              <label>Rol</label>
              <select name="role_id" class="form-select" required>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label>¿Activo?</label>
              <select name="is_active" class="form-select" required>                
                <option value="0" selected>No</option>
                <option value="1">Sí</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success" type="submit">Guardar</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para editar usuario -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" id="editUserForm">
          <input type="hidden" name="csrf_token" value="<?= \App\Core\Functions::generateCSRFToken() ?>">  
          <div class="modal-header">
            <h5 class="modal-title">Editar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
          <div id="addUserMsg" class="alert d-none"></div> <!-- Solo en el formulario de agregar -->
          <div id="editUserMsg" class="alert d-none"></div> <!-- Solo en el formulario de editar -->
  
            <input type="hidden" name="id" id="edit-id">
            <div class="mb-3">
              <label>Nombre</label>
              <input type="text" class="form-control" name="name" id="edit-name" required>
            </div>
            <div class="mb-3">
              <label>Correo</label>
              <input type="email" class="form-control" name="email" id="edit-email" required>
            </div>
            <div class="mb-3">
              <label>Contraseña (opcional)</label>
              <input type="password" class="form-control" name="password" id="edit-password">
            </div>
            <div class="mb-3">
              <label>Rol</label>
              <select name="role_id" class="form-select" id="edit-role" required>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Estado</label> <!-- cambiado nombre de etiqueta -->
              <select name="is_active" class="form-select" id="edit-active" required>
                <?php foreach ($statuses as $status): ?>
                  <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success" type="submit">Guardar Cambios</button>
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  

    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-6">
        <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre o correo">
      </div>
      <div class="col-md-4">
        <select id="filterRole" class="form-select">
          <option value="">Todos los roles</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['name'] ?>"><?= ucfirst($role['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <!-- Botón para abrir el modal -->
      <div class="text-end col-md-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
            + Agregar Usuario
        </button>
      </div>
    </div>

    <!-- Tabla de usuarios -->
   <div class="table-responsive">
      <table class="table table-bordered table-hover" id="usersTable">
          <thead class="table-dark text-nowrap">
              <tr>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Activo</th>
                  <th>Rol</th>
                  <th>Acción</th>
              </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $user): ?>
              <tr>
                  <td class="text-limit"><?= htmlspecialchars($user['name']) ?></td>
                  <td class="text-limit"><?= htmlspecialchars($user['email']) ?></td>
                  <td class="text-limit"><?= htmlspecialchars($user['account_status_name']) ?></td>
                  <td class="text-limit"><?= ucfirst($user['role_name']) ?></td>
                  <td class="d-flex gap-2">
                      <button class="btn btn-sm btn-warning edit-user-btn"
                        data-user='<?= json_encode([
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'email' => $user['email'],
                            'role_id' => $user['role_id'],
                            'is_active' => $user['account_status_id']
                        ]) ?>'>Editar</button>

                      <button class="btn btn-sm btn-danger delete-user-btn" data-user="<?= $user['id'] ?>">Eliminar</button>
                  </td>
              </tr>
          <?php endforeach; ?>
          </tbody>
      </table>
  </div>

  <div class="row mb-3">
        <div class="col-md-12 text-end">
            <a href="/admin/logs" class="btn btn-info">Ver Logs del Sistema</a>
        </div>
    </div>

</div>