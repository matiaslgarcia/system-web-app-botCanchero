<?php
	$cancha = Canchas::getById($_GET['cancha']);
	$priceRanges = Canchas::getPriceRangesByField($cancha->id);
?>
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
												<div class="form-text">Precio base usado como fallback si no existe franja para ese horario.</div>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="limit">Cantidad de Canchas</label>
												<input type="number" name="threshold" id="threshold" class="form-control" placeholder="1" value="<?php echo $cancha->threshold ?>">
											</div>
											<div class="col-12 mb-2">
												<hr>
											</div>
											<div class="col-12 mb-3">
												<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
													<div>
														<label class="form-label mb-1">Precios por Franja Horaria</label>
														<div class="text-muted fs-7">Configurá valores de día/noche u otras franjas sin superposición.</div>
													</div>
													<div class="d-flex gap-2">
														<button type="button" id="btn-preset-price-ranges" class="btn btn-light-primary btn-sm">Plantilla Día/Noche</button>
														<button type="button" id="btn-add-price-range" class="btn btn-light btn-sm">Agregar Franja</button>
													</div>
												</div>
												<div class="table-responsive border rounded p-3">
													<table class="table align-middle table-row-dashed mb-0">
														<thead>
															<tr class="text-muted fw-bold">
																<th>Desde</th>
																<th>Hasta</th>
																<th>Precio ($)</th>
																<th class="text-end">Quitar</th>
															</tr>
														</thead>
														<tbody id="price-ranges-body">
															<?php
																$rows = is_array($priceRanges) && count($priceRanges) > 0
																	? $priceRanges
																	: [
																		(object) ['start_time' => '08:00:00', 'end_time' => '18:00:00', 'price' => $cancha->price_hour],
																		(object) ['start_time' => '18:00:00', 'end_time' => '23:59:00', 'price' => $cancha->price_hour],
																	];
																foreach ($rows as $range) {
																	$start = substr((string) ($range->start_time ?? '08:00:00'), 0, 5);
																	$end = substr((string) ($range->end_time ?? '18:00:00'), 0, 5);
																	$price = (string) ($range->price ?? '');
															?>
															<tr class="price-range-row">
																<td><input type="time" class="form-control form-control-sm range-start" value="<?php echo $start; ?>"></td>
																<td><input type="time" class="form-control form-control-sm range-end" value="<?php echo $end; ?>"></td>
																<td><input type="number" min="1" step="0.01" class="form-control form-control-sm range-price" value="<?php echo $price; ?>" placeholder="0.00"></td>
																<td class="text-end"><button type="button" class="btn btn-icon btn-sm btn-light-danger btn-remove-range"><i class="fa-solid fa-trash"></i></button></td>
															</tr>
															<?php } ?>
														</tbody>
													</table>
												</div>
												<div class="d-flex justify-content-end mt-3">
													<button type="button" id="btn-save-price-ranges" class="btn btn-primary btn-sm">Guardar Franjas</button>
												</div>
												<script type="application/json" id="price-ranges-data"><?php echo json_encode($priceRanges, JSON_UNESCAPED_UNICODE); ?></script>
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
