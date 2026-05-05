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
													<label class="form-label" for="phone">Telefono</label>
													<input type="text" name="phone" id="phone" class="form-control" placeholder="Celular">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="latitude">Latitud</label>
													<input type="text" name="latitude" id="latitude" class="form-control" placeholder="Latitud">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="length">Longitud</label>
													<input type="text" name="length" id="length" class="form-control" placeholder="Longitud">
												</div>
												<div class="col-12 col-md-6 mb-3">
													<label class="form-label" for="price_hour">Precio por hora</label>
													<input type="text" name="price_hour" id="price_hour" class="form-control" placeholder="00.0" ">
												</div>
												<div class="col-12 col-md-6 mb-3">

													<label class="form-label" for="limit">Cantidad de Canchas</label>
													<input type="number" name="limit" id="limit" class="form-control" placeholder="1">
												</div>
												<div class="col-6 my-3">
                                            		<label for="time_booking" class="form-label">Provincia</label>
                                            		<select name="id_province" id="id_province" class="form-control" required>
                                                		<option selected="true" disabled="" value="" data-select2-id="select2-data-2-ih0l">--Provincia--</option>
														<?php foreach(Address::getProvincias() as $provincie) { ?>
															<option value="<?php echo $provincie->id ?>"><?php echo $provincie->name ?></option>
														<?php }  ?>
                                            		</select>
                                        	</div>
												<div class="col-6 my-3">
                                            		<label for="time_booking" class="form-label">Ciudad</label>
                                            		<select name="id_city" id="id_city" class="form-control" required>
                                                		<option selected="true" disabled="" value="" data-select2-id="select2-data-2-ih0l">--Ciudad--</option>
														<?php foreach(Address::getCity() as $city) { ?>
															<option data-id-province="<?php echo $city->id_provincia?>" value="<?php echo $city->id ?>"><?php echo $city->name ?></option>
														<?php }  ?>
                                            		</select>
                                        	</div>
												<div class="col-12 mb-3">
													<label class="form-label" for="address">Direccion</label>
													<textarea type="email" name="address" id="address" class="form-control" placeholder="Direccion"></textarea>
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