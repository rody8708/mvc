<div class="container mt-5">
    <div class="row justify-content-center">        
        <div class="col-md-6">
            <h3 class="text-center mb-4">Inicio de Sesión</h3>            
            <form id="loginForm" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                <p class="mt-3 text-center">
                    ¿No tienes una cuenta? <a href="<?= BASE_URL ?>auth/register">Regístrate aquí</a>
                </p>
                <p class="mt-3 text-center">
                    ¿Olvido su Contraseña? <a href="<?= BASE_URL ?>auth/forgot-password">Recuperele aquí</a>
                </p>

            </form>            
        </div>
    </div>
</div>