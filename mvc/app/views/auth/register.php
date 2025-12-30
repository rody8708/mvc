<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h3 class="text-center mb-4">Registro de Usuario</h3>
            <form id="registerForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Functions::generateCSRFToken() ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                <p class="mt-3 text-center">
                    ¿Ya tienes una cuenta? <a href="<?= BASE_URL ?>auth/login">Inicia sesión aquí</a>
                </p>

            </form>            
        </div>
    </div>
</div>