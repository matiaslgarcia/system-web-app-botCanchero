<?php
$user = Users::getById($_SESSION['canchero']);
$mpInfo = query(
    "SELECT
        e.mp_user_id,
        e.mp_access_token,
        e.mp_token_expires_at
     FROM users u
     LEFT JOIN soccer_field sf ON sf.id = u.id_field
     LEFT JOIN establishment e ON e.id = sf.establishment_id
     WHERE u.id = ?
     LIMIT 1",
    'ARRAY',
    [$_SESSION['canchero']]
);
$mpConnected = !empty($mpInfo['mp_access_token']);
$maskedUserId = !empty($mpInfo['mp_user_id']) ? '***' . substr((string) $mpInfo['mp_user_id'], -4) : '';
$oauthConfigured = MercadoPago::isOAuthConfigured();
$oauthError = $_GET['error'] ?? '';
$oauthErrorMsg = '';
if ($oauthError === 'mp_oauth_failed') {
    $mpHttp = isset($_GET['mp_http']) ? (int) $_GET['mp_http'] : 0;
    $mpError = trim((string)($_GET['mp_error'] ?? ''));
    $oauthErrorMsg = 'Falló la vinculación con Mercado Pago.';
    if ($mpHttp > 0) $oauthErrorMsg .= ' HTTP ' . $mpHttp . '.';
    if ($mpError !== '') $oauthErrorMsg .= ' Detalle: ' . htmlspecialchars($mpError, ENT_QUOTES, 'UTF-8');
}
?>
<div class="d-flex flex-column flex-root">
    <div class="page d-flex flex-row flex-column-fluid">
        <?php inc('sidebar') ?>
        <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
            <?php inc('header') ?>
            <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <div id="kt_content_container" class="container-xxl">
                        
                        <!-- Header de la Cuenta -->
                        <?php if ($oauthErrorMsg !== '') : ?>
                            <div class="alert alert-danger d-flex align-items-center p-5 mb-8">
                                <i class="fa-solid fa-triangle-exclamation fs-2hx text-danger me-4"></i>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-danger">Error al vincular Mercado Pago</h4>
                                    <span><?php echo $oauthErrorMsg; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-body pt-9 pb-0">
                                <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                                    <div class="me-7 mb-4">
                                        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                            <img src="<?php echo $user->avatar ?>" alt="image" id="profile-avatar-preview" style="object-fit: cover;" />
                                            <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-white h-20px w-20px"></div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-2">
                                                    <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bolder me-1"><?php echo $user->name ?></a>
                                                    <a href="#"><i class="fa-solid fa-circle-check text-primary fs-4"></i></a>
                                                </div>
                                                <div class="d-flex flex-wrap fw-bold fs-6 mb-4 pe-2">
                                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                        <i class="fa-solid fa-user-tie me-1"></i><?php echo ucfirst($user->rol) ?>
                                                    </a>
                                                    <a href="#" class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                                        <i class="fa-solid fa-envelope me-1"></i><?php echo $user->email ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabs de Navegación -->
                                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder">
                                    <li class="nav-item">
                                        <a class="nav-link text-active-primary py-5 me-10 active" data-bs-toggle="tab" href="#kt_user_profile_tab">Perfil</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-active-primary py-5 me-10" data-bs-toggle="tab" href="#kt_user_security_tab">Seguridad</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-active-primary py-5 me-10" data-bs-toggle="tab" href="#kt_user_payments_tab">Pagos (MP)</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Contenido de los Tabs -->
                        <div class="tab-content">
                            
                            <!-- Tab: Perfil -->
                            <div class="tab-pane fade show active" id="kt_user_profile_tab" role="tabpanel">
                                <div class="card mb-5 mb-xl-10">
                                    <div class="card-header border-0 cursor-pointer">
                                        <div class="card-title m-0">
                                            <h3 class="fw-bolder m-0">Información Personal</h3>
                                        </div>
                                    </div>
                                    <div class="collapse show">
                                        <form id="form-profile-settings" class="form" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="csrf_token" value="<?php echo Users::getCsrfToken() ?>">
                                            <input type="hidden" name="id" value="<?php echo $user->id ?>">
                                            <div class="card-body border-top p-9">
                                                
                                                <!-- Avatar Upload -->
                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label fw-bold fs-6">Foto de Perfil</label>
                                                    <div class="col-lg-8">
                                                        <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('assets/img/avatars/blank.png')">
                                                            <div class="image-input-wrapper w-125px h-125px" id="avatar-preview-bg" style="background-image: url(<?php echo $user->avatar ?>); background-size: cover;"></div>
                                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Cambiar avatar">
                                                                <i class="fa-solid fa-pencil fs-7"></i>
                                                                <input type="file" name="avatar" id="avatar-input" accept=".png, .jpg, .jpeg" />
                                                            </label>
                                                        </div>
                                                        <div class="form-text">Tipos permitidos: png, jpg, jpeg.</div>
                                                    </div>
                                                </div>

                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">Nombre Completo</label>
                                                    <div class="col-lg-8">
                                                        <input type="text" name="full_name" class="form-control form-control-lg form-control-solid" placeholder="Nombre completo" value="<?php echo $user->name ?>" required />
                                                    </div>
                                                </div>

                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">Email</label>
                                                    <div class="col-lg-8">
                                                        <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="email@ejemplo.com" value="<?php echo $user->email ?>" required />
                                                    </div>
                                                </div>

                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label fw-bold fs-6">Teléfono</label>
                                                    <div class="col-lg-8">
                                                        <input type="tel" name="phone" class="form-control form-control-lg form-control-solid" placeholder="Teléfono" value="<?php echo $user->phone ?>" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                                <button type="submit" class="btn btn-primary" id="btn-save-profile">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Seguridad (Contraseña) -->
                            <div class="tab-pane fade" id="kt_user_security_tab" role="tabpanel">
                                <div class="card mb-5 mb-xl-10">
                                    <div class="card-header border-0 cursor-pointer">
                                        <div class="card-title m-0">
                                            <h3 class="fw-bolder m-0">Cambiar Contraseña</h3>
                                        </div>
                                    </div>
                                    <div class="collapse show">
                                        <form id="form-change-password" class="form" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo Users::getCsrfToken() ?>">
                                            <input type="hidden" name="id" value="<?php echo $user->id ?>">
                                            <!-- Campo oculto para accesibilidad de gestores de contraseñas -->
                                            <input type="text" name="username" value="<?php echo $user->email ?>" style="display:none;" autocomplete="username" />
                                            
                                            <div class="card-body border-top p-9">
                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">Nueva Contraseña</label>
                                                    <div class="col-lg-8">
                                                        <div class="position-relative mb-3">
                                                            <input type="password" name="password" id="new_password" class="form-control form-control-lg form-control-solid" placeholder="Nueva contraseña" required autocomplete="new-password" />
                                                            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                                                                <i class="fa-solid fa-eye-slash fs-2"></i>
                                                                <i class="fa-solid fa-eye fs-2 d-none"></i>
                                                            </span>
                                                        </div>
                                                        <div class="form-text">Mínimo 6 caracteres.</div>
                                                    </div>
                                                </div>
                                                <div class="row mb-6">
                                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">Confirmar Contraseña</label>
                                                    <div class="col-lg-8">
                                                        <input type="password" id="confirm_password" class="form-control form-control-lg form-control-solid" placeholder="Repite la contraseña" required autocomplete="new-password" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                                <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab: Pagos (Mercado Pago) -->
                            <div class="tab-pane fade" id="kt_user_payments_tab" role="tabpanel">
                                <div class="card mb-5 mb-xl-10">
                                    <div class="card-header border-0 cursor-pointer">
                                        <div class="card-title m-0">
                                            <h3 class="fw-bolder m-0">Vincular Mercado Pago</h3>
                                        </div>
                                    </div>
                                    <div class="card-body border-top p-9">
                                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                                            <img src="assets/img/marcado_pago.png" alt="" class="w-80px h-80px me-10">
                                            <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                                                <div class="mb-3 mb-md-0 fw-bold">
                                                    <h4 class="text-gray-900 fw-bolder">Configuración de Pagos</h4>
                                                    <div class="fs-6 text-gray-700 pe-7">
                                                        <?php if(!$oauthConfigured) : ?>
                                                            Falta configurar OAuth de Mercado Pago en el servidor (<code>MP_OAUTH_CLIENT_ID</code> y <code>MP_OAUTH_CLIENT_SECRET</code>).
                                                        <?php elseif(!$mpConnected) : ?>
                                                            Vincula tu cuenta de Mercado Pago para empezar a recibir señas y pagos online.
                                                        <?php else : ?>
                                                            Tu cuenta ya está vinculada.
                                                            <?php if(!empty($maskedUserId)) : ?>
                                                                Usuario MP: <span class="badge badge-light-success"><?php echo $maskedUserId ?></span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if(!$oauthConfigured) : ?>
                                                    <button disabled class="btn btn-secondary px-6 align-self-center text-nowrap">Configurar OAuth (.env)</button>
                                                <?php elseif(!$mpConnected) : ?>
                                                    <a href="<?php echo MercadoPago::getUrlOAuth() ?>" target="_blank" class="btn btn-primary px-6 align-self-center text-nowrap">Vincular Ahora</a>
                                                <?php else : ?>
                                                    <div class="d-flex gap-2 align-self-center">
                                                        <a href="<?php echo MercadoPago::getUrlOAuth() ?>" target="_blank" class="btn btn-primary px-6 text-nowrap">Re-vincular</a>
                                                        <button id="btn-mp-unlink" type="button" class="btn btn-light-danger px-6 text-nowrap">Desvincular</button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?php inc('footer') ?>
        </div>
    </div>
</div>
