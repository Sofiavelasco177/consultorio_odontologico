<?php
 require_once 'views/templates/header.php';
  ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Registrarse</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>auth/register" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>" 
                                   id="username" name="username" value="<?php echo $data['username']; ?>">
                            <div class="invalid-feedback"><?php echo $data['username_err']; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                                   id="email" name="email" value="<?php echo $data['email']; ?>">
                            <div class="invalid-feedback"><?php echo $data['email_err']; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" 
                                   id="password" name="password">
                            <div class="invalid-feedback"><?php echo $data['password_err']; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control <?php echo (!empty($data['confirm_password_err'])) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" name="confirm_password">
                            <div class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">¿Ya tiene una cuenta? <a href="<?php echo BASE_URL; ?>auth/login">Inicie sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/templates/footer.php'; ?>