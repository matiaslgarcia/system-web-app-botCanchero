<?php
    $resetToken = trim((string) ($_GET['reset_token'] ?? ''));
    $showForgotPanel = (string) ($_GET['forgot'] ?? '') === '1';
    $validResetToken = false;
    if ($resetToken !== '') {
        $validResetToken = (bool) Users::validatePasswordResetToken($resetToken);
    }
?>
<div class="d-flex flex-column flex-root">
	<div 
	class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed login-bg"
	style="background-image: url('assets/img/logos/bg.png');"
	>
		<div class="d-flex flex-center flex-column flex-column-fluid login-page-content">
			<div class="w-lg-500px rounded-4 mx-auto login-card">
				<div class="d-flex justify-content-center">
					<a href="<?php echo URL?>" class="mb-8">
						<img alt="Logo_BotCanchero" src="assets/img/logos/logoNew.png" class="h-100px logo-animate login-logo" />
					</a>
				</div>
                <h1 class="text-center fs-2hx fw-bolder mb-3">Bienvenido</h1>
                <p class="text-center text-muted mb-9 login-subtitle">
                    Gestioná tus reservas y tu cuenta desde cualquier dispositivo.
                </p>

                <div id="login-panel" class="<?php echo ($validResetToken || $showForgotPanel) ? 'd-none' : '';?>">
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="#">
                        <div class="fv-row mb-8">
                            <label class="form-label" for="email">Email</label>
                            <input type="text" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com">
                        </div>
                        <div class="fv-row mb-8">
                            <label class="form-label" for="password">Contraseña</label>
                            <div class="position-relative">
                                <button type="button" class="input-password-show" data-input="password" aria-label="Mostrar contraseña" onclick="togglePasswordVisibility('password', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                                <input type="password" name="password" id="password" autocomplete="off" class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mb-6">
                            <a href="?forgot=1" class="btn btn-link p-0 fs-6 fw-semibold" id="forgot-password-trigger">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        <div class="text-center">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 py-4">
                                <span class="indicator-label fw-bold">Iniciar sesión</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div id="forgot-panel" class="<?php echo (!$validResetToken && $showForgotPanel) ? '' : 'd-none';?>">
                    <form class="form w-100" novalidate="novalidate" id="kt_forgot_form" action="#">
                        <div class="fv-row mb-8">
                            <label class="form-label" for="forgot_email">Email</label>
                            <input type="email" name="email" id="forgot_email" class="form-control" placeholder="ejemplo@correo.com">
                            <div class="form-text mt-3">
                                Te enviaremos un enlace para crear una nueva contraseña.
                            </div>
                        </div>
                        <div class="d-flex gap-3 login-actions">
                            <button type="submit" class="btn btn-primary flex-fill">Enviar enlace</button>
                            <a href="./" class="btn btn-light flex-fill" id="back-to-login-trigger">Volver</a>
                        </div>
                    </form>
                </div>

                <div id="reset-panel" class="<?php echo $validResetToken ? '' : 'd-none';?>">
                    <form class="form w-100" novalidate="novalidate" id="kt_reset_form" action="#">
                        <input type="hidden" id="reset_token" value="<?php echo htmlspecialchars($resetToken, ENT_QUOTES); ?>">
                        <div class="fv-row mb-8">
                            <label class="form-label" for="reset_password">Nueva contraseña</label>
                            <div class="position-relative">
                                <button type="button" class="input-password-show" data-input="reset_password" aria-label="Mostrar contraseña" onclick="togglePasswordVisibility('reset_password', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                                <input type="password" name="password" id="reset_password" autocomplete="off" class="form-control" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>
                        <div class="fv-row mb-8">
                            <label class="form-label" for="reset_password_confirm">Confirmar contraseña</label>
                            <div class="position-relative">
                                <button type="button" class="input-password-show" data-input="reset_password_confirm" aria-label="Mostrar contraseña" onclick="togglePasswordVisibility('reset_password_confirm', this)">
                                    <i class="fa-solid fa-eye-slash"></i>
                                </button>
                                <input type="password" name="password_confirm" id="reset_password_confirm" autocomplete="off" class="form-control" placeholder="Repetí la nueva contraseña">
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-lg btn-primary w-100 py-4">Cambiar contraseña</button>
                        </div>
                    </form>
                </div>

                <div id="invalid-reset-panel" class="<?php echo ($resetToken !== '' && !$validResetToken) ? '' : 'd-none';?>">
                    <div class="alert alert-warning mb-6">
                        El enlace de recuperación no es válido o ya venció.
                    </div>
                    <a href="?forgot=1" class="btn btn-primary w-100" id="go-to-forgot-trigger">Solicitar nuevo enlace</a>
                </div>
			</div>
		</div>
	</div>
</div>
