<div class="d-flex flex-column flex-root">
	<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed">
		<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
			<div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
				<div class="d-flex justify-content-center">
					<a href="<?php echo URL?>" class="mb-12">
						<img alt="Logo" src="assets/img/logos/logo_canchero.png" class="h-100px" />
					</a>
				</div>
				<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="../../demo1/dist/index.html" action="#">
					<div class="fv-row mb-10">
						<div class="input-floting-label">
							<label class="form-label" for="email">Email</label>
							<input type="text" name="email" id="email" class="form-control">
						</div>
					</div>
					<div class="fv-row mb-10">
						<div class="input-floting-label" >
							
							<label class="form-label" for="password">Contraseña</label>
							<div class="input-password-show" data-input="password">
								<i class="fa-solid fa-eye-slash"></i>
							</div>
							<input type="password" name="password" id="password" autocomplete="off" class="form-control">
						</div>
					</div>
					<div class="text-center">
						<!--begin::Submit button-->
						<button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
							<span class="indicator-label">Login</span>
						</button>
					</div>
					<!--end::Actions-->
				</form>
				<!--end::Form-->
			</div>
			<!--end::Wrapper-->
		</div>
	</div>
	<!--end::Authentication - Sign-in-->
</div>