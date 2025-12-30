<div class="container mt-5">
  <h3 class="mb-4 text-center">Restablecer contraseña</h3> 

  <form id="resetPasswordForm" class="mx-auto" style="max-width: 400px;">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

    <div class="mb-3">
      <label class="form-label">Nueva contraseña</label>
      <input type="password" name="password" class="form-control" required minlength="6">
    </div>

    <div class="mb-3">
      <label class="form-label">Confirmar contraseña</label>
      <input type="password" name="confirm_password" class="form-control" required minlength="6">
    </div>

    <button type="submit" class="btn btn-success w-100">Restablecer contraseña</button>
  </form>
</div>