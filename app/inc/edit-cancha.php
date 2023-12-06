<?php $cancha = Canchas::getById($_GET['cancha']); ?>
<div class="d-flex flex-column flex-root">
	<div class="page d-flex flex-row flex-column-fluid">
		<?php inc('sidebar') ?>
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<?php inc('header') ?>
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<div id="kt_content_container" class="container-xxl">
						<div class="card mb-5 mb-xl-10">
							<div class="card-header card-header-stretch">
								<div class="card-toolbar">
									<ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
										<li class="nav-item">
											<a class="nav-link active" data-bs-toggle="tab" href="#config">Configuracion</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#horarios">Horarios</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="card-body pt-9 pb-0">
								<div class="tab-content" id="myTabContent">
									<div class="tab-pane fade show active" id="config" role="tabpanel">
										<div class="d-flex justify-content-center mb-4">
											<img id="add-user-select-avatar" src="<?php echo $cancha->logo ?>">
										</div>
										<form id="form-edit-cancha" class="row pb-5">
											<div class="d-none">
												<input type="text" name="id" id="id" value="<?php echo $cancha->id ?>">
											</div>
											<div class="d-none">
												<input type="file" name="logo" id="logo" accept="image/*">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="full_name">Nombre de la Cancha</label>
												<input type="text" name="full_name" id="full_name" class="form-control" placeholder="Cancha" value="<?php echo $cancha->name ?>">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="phone">Teléfono</label>
												<input type="text" name="phone" id="phone" class="form-control" placeholder="Celular" value="<?php echo $cancha->phone ?>" autocomplete="phone">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="latitude">Latitud</label>
												<input type="text" name="latitude" id="latitude" class="form-control" placeholder="Latitud" value="<?php echo $cancha->latitude ?>">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="length">Longitud</label>
												<input type="text" name="length" id="length" class="form-control" placeholder="Longitud" value="<?php echo $cancha->length ?>">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="price_hour">Precio por Hora</label>
												<input type="text" name="price_hour" id="price_hour" class="form-control" placeholder="00.0" value="<?php echo $cancha->price_hour ?>">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="limit">Cantidad de Canchas</label>
												<input type="number" name="threshold" id="threshold" class="form-control" placeholder="1" value="<?php echo $cancha->threshold ?>">
											</div>
											<div class="col-6 my-3">
                                            	<label for="time_booking" class="form-label">Provincia</label>
                                            	<select name="id_province" id="id_province" class="form-control" required>
                                                	<option selected="true" disabled="" value="" data-select2-id="select2-data-2-ih0l">--Provincia--</option>
													<?php foreach(Address::getProvincias() as $provincie) { ?>
														<option <?php showArgument($cancha->id_province, $provincie->id, 'selected')?> value="<?php echo $provincie->id ?>"><?php echo $provincie->name ?></option>
													<?php }  ?>
                                            	</select>
                                        	</div>
											<div class="col-6 my-3">
                                            	<label for="time_booking" class="form-label">Ciudad</label>
                                            	<select name="id_city" id="id_city" class="form-control" required>
                                                	<option selected="true" disabled="" value="" data-select2-id="select2-data-2-ih0l">--Ciudad--</option>
													<?php foreach(Address::getCity() as $city) { ?>
														<option data-id-province="<?php echo $city->id_provincia?>" <?php showArgument($cancha->id_city , $city->id, 'selected')?> value="<?php echo $city->id ?>"><?php echo $city->name ?></option>
													<?php }  ?>
                                            	</select>
                                        	</div>
											<div class="col-12 mb-3">
												<label class="form-label" for="address">Dirección</label>
												<textarea type="email" name="address" id="address" class="form-control" placeholder="Direccion" autocomplete="address"><?php echo $cancha->address ?></textarea>
											</div>
											<div class="text-end">
												<button type="submit" class="btn btn-primary">Guardar</button>
											</div>
										</form>
									</div>
									<div class="tab-pane fade" id="horarios" role="tabpanel">
										<ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
											<?php foreach (Schedules::getDay() as $day) { ?>
												<li class="nav-item">
													<a class="nav-link <?php showArgument(1, $day->id, 'active') ?>" data-bs-toggle="tab" href="#tab_<?php echo $day->name ?>"><?php echo $day->name ?></a>
												</li>
											<?php } ?>
										</ul>
										<div class="tab-content" id="myTabContent">
											<?php foreach (Schedules::getDay() as $d) { ?>
												<div class="tab-pane fade <?php showArgument(1, $d->id, 'show active') ?>" id="tab_<?php echo $d->name ?>" role="tabpanel">
													<form class="row justify-content-center form-horario">
														<input type="hidden" name="id_field" value="<?php echo $cancha->id ?>">
														<input type="hidden" name="id_day" value="<?php echo $d->id ?>">
														<?php foreach (Schedules::getAllDayCancha($d->id, $cancha->id) as $h) { ?>
															<div class="col-4">
																<div class="form-check form-check-custom form-check-solid mb-5">
																	<!--begin::Input-->
																	<input class="form-check-input me-3" <?php echo $h->checked ?> name="horario[]" id="h_<?php echo $h->id ?>" type="checkbox" value="<?php echo $h->id ?>">
																	<!--end::Input-->
																	<!--begin::Label-->
																	<label class="form-check-label" for="h_<?php echo $h->id ?>">
																		<div class="fw-bolder text-gray-800"><?php echo $h->hour12 ?></div>
																	</label>
																	<!--end::Label-->
																</div>
															</div>
														<?php } ?>
														<div class="text-end">
															<button type="submit" class="btn btn-primary">Guardar</button>
														</div>
													</form>
												</div>
											<?php } ?>
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