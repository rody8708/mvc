<style>
    .pagination {
        flex-wrap: wrap;
        gap: 5px;
    }

    .pagination .page-item .page-link {
        min-width: 40px;
        text-align: center;
    }
</style>


<div class="container mt-5">
    <h2 class="mb-4 text-center">System Log</h2>
    <div class="table-responsive">
        <form id="filterForm" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="user" class="form-control" placeholder="Usuario (contiene)">
            </div>
            <div class="col-md-2">
                <select name="level" class="form-select">
                    <option value="">Todos los niveles</option>
                    <option value="INFO">INFO</option>
                    <option value="WARNING">WARNING</option>
                    <option value="ERROR">ERROR</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="ip" class="form-control" placeholder="IP exacta">
            </div>
            <div class="col-md-2">
                <input type="text" name="action" class="form-control" placeholder="Acción (contiene)">
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="date" name="date_from" class="form-control" placeholder="Desde">
                    <input type="date" name="date_to" class="form-control" placeholder="Hasta">
                </div>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ms-2">Limpiar</button>
            </div>
        </form>



        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark text-nowrap">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>IP</th>
                        <th>SO</th>
                        <th>Navegador</th>
                        <th>Acción</th>
                        <th>Nivel</th>
                    </tr>
                </thead>
                <tbody id="logTableBody"></tbody>
            </table>
        </div>

    </div>

    <!-- Contenedor del paginador -->
    <nav class="mt-4" id="paginationNav"></nav>
</div>


<script>

</script>


