<?php $usuario = Users::getById($_GET['usuario']); ?>

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
									<img id="add-user-select-avatar" src="<?php echo $usuario->avatar ?>">
								</div>
								<form id="form-edit-user" class="row pb-5">
									<div class="d-none">
										<input type="file" name="avatar" id="avatar" accept="image/*" >
										<input type="text" name="id" id="id" value="<?php echo $usuario->id ?>" >
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="full_name" >Nombre</label>
										<input type="text" name="full_name" id="full_name" class="form-control" placeholder="Nombre" value="<?php echo $usuario->name ?>">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="phone" >Teléfono</label>
										<input type="text" name="phone" id="phone" class="form-control" placeholder="Telefono" value="<?php echo $usuario->phone ?>" autocomplete="off">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="email" >Correo Electrónico</label>
										<input type="email" name="email" id="email" class="form-control" placeholder="Correo" value="<?php echo $usuario->email ?>" autocomplete="off">
									</div>
									<div class="col-12 col-md-6 mb-3">
										<label class="form-label" for="rol" >Rol</label>
										<select name="rol" id="rol" class="form-select">
											<option <?php showSelected('cancero', $usuario->rol) ?> value="canchero" >Canchero</option>
											<option <?php showSelected('superAdmin', $usuario->rol) ?> value="superAdmin" >SuperAdmin</option>
										</select>
									</div>
									<div class="col-12 mb-3">
										<label class="form-label" for="id_field" >Cancha</label>
										<select name="id_field" id="id_field" data-control="select2" data-placeholder="Selección cancha" class="form-select">
											<option selected disabled hidden>--SELECCIONE--</option>
											<?php foreach(canchas::getAll() AS $cancha) { ?>
												<option  <?php showSelected($usuario->id_field, $cancha->id) ?> value="<?php echo $cancha->id ?>"><?php echo $cancha->name ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="mb-3 text-end">
										<button class="btn btn-primary">Guardar</button>
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