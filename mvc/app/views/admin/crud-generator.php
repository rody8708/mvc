<div class="container py-5">
  <h3 class="text-center mb-4">🛠️ Generador de Módulo CRUD</h3>

  <!-- Formulario para crear módulo -->
  <form id="crudGeneratorForm" class="card shadow-sm p-4 mb-5">
    <div class="mb-3">
      <label for="module" class="form-label">Nombre del Módulo (singular, ej: Producto)</label>
      <input type="text" class="form-control" id="module" name="module" required>
    </div>

    <div class="mb-3">
      <label for="fields" class="form-label">Nombre a mostrar en el Navbar</label>
      <input type="text" class="form-control" id="menu_label" name="menu_label" required>
    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary w-50">
        🚀 Generar Módulo
      </button>
    </div>
  </form>

  <!-- Lista de módulos existentes -->
  <div class="card shadow-sm p-4">
    <h5 class="mb-4">📚 Módulos Actuales</h5>

    <div class="table-responsive">
      <table class="table table-striped align-middle" id="modulesTable">
        <thead class="table-dark">
          <tr>
            <th>Nombre</th>
            <th>Ruta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <!-- Aquí se cargarán dinámicamente los módulos -->
        </tbody>
      </table>
    </div>
  </div>
</div>
