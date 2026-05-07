<div class="d-flex flex-column flex-root">
	<!--begin::Page-->
	<div class="page d-flex flex-row flex-column-fluid">
		<!--begin::Aside-->
		<?php inc('sidebar') ?>
		<!--end::Aside-->
		<!--begin::Wrapper-->
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<!--begin::Header-->
			<?php inc('header') ?>
			<!--end::Header-->
			<!--begin::Content-->
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<!--begin::Container-->
					<div id="kt_content_container" class="container-xxl">
						<!--begin::Navbar-->
						<div class="card mb-5 mb-xl-10">
							<div class="card-body pt-9 pb-0">
								<div class="d-flex justify-content-center mb-4">
									<img id="add-user-select-avatar" src="assets/img/avatars/blank.png" style="width:120px;height:120px;max-width:120px;max-height:120px;object-fit:cover;border-radius:1rem;border:2px solid #f1f1f4;cursor:pointer;background:#fff;">
								</div>
								<form id="form-add-user" class="row pb-5">
									<div class="d-none">
										<input type="file" name="avatar" id="avatar" accept="image/*" >
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="full_name" >Nombre</label>
										<input type="text" name="full_name" id="full_name" class="form-control" placeholder="Nombre">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="phone" >Teléfono</label>
										<input type="text" name="phone" id="phone" class="form-control" placeholder="Teléfono">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="email" >Correo</label>
										<input type="email" name="email" id="email" class="form-control" placeholder="Correo">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="rol" >Rol</label>
										<select name="rol" id="rol" class="form-select">
											<option value="canchero" >Canchero</option>
											<option value="superAdmin" >SuperAdmin</option>
										</select>
									</div>
									<div class="col-12 col-md-6 mb-3 position-relative">
										<label class="form-label" for="password" >Contraseña</label>
										<div id="btn-generate-password" class="input-password-gen top-40px end-50px" data-input="password">
											<i class="fa-solid fa-wand-magic-sparkles"></i>
										</div>
										<div class="input-password-show top-40px end-25px" data-input="password">
											<i class="fa-solid fa-eye-slash"></i>
										</div>
										<input type="password" name="password" id="password" class="form-control" autocomplete="off" placeholder="Contraseña">

									</div>
									<div class="col-12 mb-3">
										<label class="form-label" for="id_field" >Cancha</label>
										<select name="id_field" id="id_field" data-control="select2" data-placeholder="Selección cancha" class="form-select">
											<option selected disabled hidden>--SELECCIONE--</option>
											<?php foreach(canchas::getAll() AS $cancha) { ?>
												<option value="<?php echo $cancha->id ?>"><?php echo $cancha->name ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="mb-3 text-end">
										<button class="btn btn-primary">Crear</button>
									</div>
								</form>
							</div>
						</div>
						<!--end::Row-->
					</div>
					<!--end::Container-->
				</div>
				<!--end::Post-->
			</div>
			<!--end::Content-->
			<!--begin::Footer-->
			<?php inc('footer')?>
			<!--end::Footer-->
		</div>
		<!--end::Wrapper-->
	</div>
