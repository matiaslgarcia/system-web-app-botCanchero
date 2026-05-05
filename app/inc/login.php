<div class="d-flex flex-column flex-root">
	<div 
	class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed"
	style="background-image: url('assets/img/logos/bg.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"
	>
		<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
			<div class="w-lg-500px rounded-4 p-10 p-lg-15 mx-auto" style="background: rgba(255, 255, 255, 0.88); backdrop-filter: blur(20px) saturate(180%); border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);">
				<div class="d-flex justify-content-center">
					<a href="<?php echo URL?>" class="mb-12">
						<img alt="Logo_BotCanchero" src="assets/img/logos/logoNew.png" class="h-100px logo-animate" />
					</a>
				</div>
				<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="#">
					<div class="fv-row mb-10">
						<label class="form-label" for="email">Email</label>
						<input type="text" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com">
					</div>
					<div class="fv-row mb-10">
						<label class="form-label" for="password">Contraseña</label>
						<div class="position-relative">
							<div class="input-password-show" data-input="password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;">
								<i class="fa-solid fa-eye-slash" style="color: #a1a5b7;"></i>
							</div>
							<input type="password" name="password" id="password" autocomplete="off" class="form-control" placeholder="••••••••" style="padding-right: 45px;">
						</div>
					</div>
					<div class="text-center">
						<button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5 py-4">
							<span class="indicator-label fw-bold">Login</span>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>