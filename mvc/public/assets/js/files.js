// Funciones para la vista de gestión de archivos

document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.delete-file');

    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const fileId = event.target.dataset.fileId;
            if (confirm('¿Estás seguro de que deseas eliminar este archivo?')) {
                // Lógica para eliminar el archivo
                console.log(`Archivo con ID ${fileId} eliminado.`);
            }
        });
    });
});