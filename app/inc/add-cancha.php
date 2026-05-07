<div class="d-flex flex-column flex-root">
	<div class="page d-flex flex-row flex-column-fluid">
		<?php inc('sidebar') ?>
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<?php inc('header') ?>
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<div id="kt_content_container" class="container-xxl">
						<div class="card mb-5 mb-xl-10">
							<div class="card ">
								<div class="card-body">
									<div class="tab-content" id="myTabContent">
										<div class="card-body pt-9 pb-0">
											<div class="d-flex justify-content-center mb-4 ">
												<img id="add-user-select-avatar" class="select-img-input" data-input-file="#logo" src="assets/img/cancha.png">
											</div>
											<form id="form-add-cancha" class="row pb-5">
												<div class="d-none">
													<input type="file" name="logo" id="logo" accept="image/*">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="full_name">Nombre de la Cancha</label>
													<input type="text" name="full_name" id="full_name" class="form-control" placeholder="Cancha">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="phone">Teléfono</label>
													<input type="text" name="phone" id="phone" class="form-control" placeholder="Celular">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="price_hour">Precio por hora</label>
													<input type="text" name="price_hour" id="price_hour" class="form-control" placeholder="00.0">
												</div>
												<div class="col-12 col-md-6 mb-3">

													<label class="form-label" for="limit">Cantidad de Canchas</label>
													<input type="number" name="limit" id="limit" class="form-control" placeholder="1">
												</div>
												<div class="col-12 mb-3">
													<label class="form-label" for="address">Dirección</label>
													<div class="input-group">
														<input type="text" name="address" id="address" class="form-control" placeholder="Ej: Av. Rivadavia 1234, CABA">
														<button type="button" id="btn-search-address" class="btn btn-light-primary">
															<i class="fa-solid fa-magnifying-glass"></i>
														</button>
													</div>
													<small class="text-muted">Escribe una dirección y buscala, o elegí el punto directo en el mapa.</small>
												</div>
												<div class="col-12 mb-4">
													<label class="form-label d-flex justify-content-between align-items-center">
														Ubicación en el Mapa
														<button type="button" id="btn-recenter" class="btn btn-sm btn-light-primary py-1 px-3 fs-8">
															<i class="fa-solid fa-location-crosshairs me-1"></i>Mi ubicación actual
														</button>
													</label>
													<div id="map-picker" style="height: 330px; border-radius: 1rem; border: 1.5px solid #f1f1f4; z-index: 1;"></div>
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label text-muted fs-7" for="latitude">Latitud (No editable)</label>
													<input type="text" name="latitude" id="latitude" class="form-control bg-light-dark border-dashed text-gray-600" placeholder="Latitud" readonly tabindex="-1">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label text-muted fs-7" for="length">Longitud (No editable)</label>
													<input type="text" name="length" id="length" class="form-control bg-light-dark border-dashed text-gray-600" placeholder="Longitud" readonly tabindex="-1">
												</div>
												<div class="text-end">
													<button type="submit" class="btn btn-primary">Crear</button>
												</div>
											</form>
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
