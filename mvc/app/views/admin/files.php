<div class="container mt-5">
    <h2 class="mb-4 text-center">Gestión de Archivos</h2>
    <form id="uploadForm" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md-8">
                <input type="file" class="form-control" name="file" id="fileInput">
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-primary">Subir Archivo</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tamaño</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="filesTableBody">
            <!-- Archivos dinámicos -->
        </tbody>
    </table>
</div>
<script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('fileInput');
        if (fileInput.files.length > 0) {
            // Lógica para subir archivo
            console.log('Archivo subido:', fileInput.files[0].name);
        }
    });
</script>