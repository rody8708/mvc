function loadLogs(page = 1) {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);
    formData.append("page", page);

    fetch(BASE_URL + "logs/fetch", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById("logTableBody").innerHTML = data.table;
            document.getElementById("paginationNav").innerHTML = data.pagination;

            // Reasignar eventos a los links del nuevo paginador
            document.querySelectorAll(".pagination a").forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    const page = this.dataset.page;
                    if (page) loadLogs(page);
                });
            });
        }
    })
    .catch(err => console.error("Error al cargar logs:", err));
}

// Al cargar la página por primera vez
document.addEventListener("DOMContentLoaded", () => loadLogs());

// Envío de filtros
document.getElementById("filterForm").addEventListener("submit", function (e) {
    e.preventDefault();
    loadLogs(1); // Cargar desde la página 1 cuando se filtra
});

// Limpiar filtros
document.getElementById("resetBtn").addEventListener("click", function () {
    document.getElementById("filterForm").reset(); // Limpiar formulario
    loadLogs(1); // Volver a cargar desde cero
});