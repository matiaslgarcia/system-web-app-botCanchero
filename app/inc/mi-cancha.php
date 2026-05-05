<?php
	$cancha = Canchas::getMyCancha();
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
											<a class="nav-link active" data-bs-toggle="tab" href="#config">Configuración</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#horarios">Horarios</a>
										</li>
										<li class="nav-item">
											<a class="nav-link" data-bs-toggle="tab" href="#servicios">Servicios</a>
										</li>
									</ul>
								</div>
							</div>
							<div class="card-body pt-9 pb-0">
								<div class="tab-content" id="myTabContent">
									<div class="tab-pane fade show active" id="config" role="tabpanel">
										<div class="d-flex justify-content-center mb-4">
											<div class="position-relative" style="width: 120px; height: 120px;">
												<img id="add-user-select-avatar" src="<?php echo $cancha->logo ?>" style="width: 100px; height: 100px; border-radius: 1.5rem; cursor: pointer; object-fit: cover; border: 3px solid var(--bc-primary); padding: 3px;">
												<div class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 35px; height: 35px; border: 3px solid #fff; cursor: pointer;" onclick="document.getElementById('logo').click()">
													<i class="fa-solid fa-camera fs-7 text-white"></i>
												</div>
											</div>
										</div>
										<div class="text-center mb-8">
											<span class="badge badge-light-primary text-uppercase fs-8 fw-bold">Presiona la imagen o la cámara para subir</span>
										</div>
										<form id="form-edit-cancha" class="row pb-5">
											<div class="d-none">
												<input type="text" name="id" id="id" value="<?php echo $cancha->id ?>">
												<input type="file" name="logo" id="logo" accept="image/*">
											</div>

											<!-- Fila 1: Nombre y Teléfono -->
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="full_name">Nombre de la Cancha</label>
												<input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo $cancha->name ?>">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="phone">Teléfono de Contacto</label>
												<input type="text" name="phone" id="phone" class="form-control" value="<?php echo $cancha->phone ?>">
											</div>

											<!-- Fila 2: Precio y Cantidad -->
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="price_hour">Precio por hora ($)</label>
												<input type="text" name="price_hour" id="price_hour" class="form-control" value="<?php echo $cancha->price_hour ?>">
												<small class="text-muted">Este precio se usa como respaldo cuando no haya franja definida para un horario.</small>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label" for="threshold">Cupos simultáneos por horario</label>
												<input type="number" name="threshold" id="threshold" class="form-control" value="<?php echo $cancha->threshold ?>">
												<small class="text-muted">Define cuántos turnos se pueden tomar al mismo tiempo en esta cancha (ej: 2 cupos = 2 partidos simultáneos en el mismo horario).</small>
											</div>
											<div class="col-12 mb-2">
												<div class="separator separator-dashed my-4"></div>
											</div>
											<div class="col-12 mb-3">
												<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
													<div>
														<label class="form-label mb-1">Precios por Franja Horaria</label>
														<div class="text-muted fs-7">Podés configurar precio de tarde (sin luz) y de noche (con luz), o las franjas que necesites.</div>
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

											<!-- Fila 3: Dirección (con búsqueda) -->
											<div class="col-12 mb-5">
												<label class="form-label" for="address">Dirección Exacta</label>
												<div class="input-group">
													<input type="text" name="address" id="address" class="form-control" placeholder="Ej: Av. Rivadavia 1234, CABA" value="<?php echo $cancha->address ?>">
													<button type="button" id="btn-search-address" class="btn btn-light-primary">
														<i class="fa-solid fa-magnifying-glass"></i>
													</button>
												</div>
												<small class="text-muted">Escribe la dirección y presiona la lupa, o selecciona directamente en el mapa.</small>
											</div>

											<!-- Fila 4: Mapa -->
											<div class="col-12 mb-5">
												<label class="form-label d-flex justify-content-between align-items-center">
													Ubicación en el Mapa
													<button type="button" id="btn-recenter" class="btn btn-sm btn-light-primary py-1 px-3 fs-8">
														<i class="fa-solid fa-location-crosshairs me-1"></i>Mi ubicación actual
													</button>
												</label>
												<div id="map-picker" style="height: 350px; border-radius: 1rem; border: 1.5px solid #f1f1f4; z-index: 1;"></div>
											</div>

											<!-- Fila 5: Coordenadas (Solo Lectura) -->
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label text-muted fs-7" for="latitude">Latitud (No editable)</label>
												<input type="text" name="latitude" id="latitude" class="form-control bg-light-dark border-dashed text-gray-600" readonly value="<?php echo $cancha->latitude ?>" tabindex="-1">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label text-muted fs-7" for="length">Longitud (No editable)</label>
												<input type="text" name="length" id="length" class="form-control bg-light-dark border-dashed text-gray-600" readonly value="<?php echo $cancha->length ?>" tabindex="-1">
											</div>

											<div class="text-end mt-5">
												<button type="submit" class="btn btn-primary px-10">
													<i class="fa-solid fa-save me-2"></i>Guardar Cambios
												</button>
											</div>
										</form>
									</div>

									<div class="tab-pane fade" id="horarios" role="tabpanel">
										<div class="alert alert-light-info border border-info border-dashed mb-6">
											<div class="fw-bold mb-1">Horarios compartidos por cupo</div>
											<div class="text-gray-700">
												Los horarios que configures acá aplican a los <span class="fw-bold"><?php echo max(1, (int) $cancha->threshold) ?></span> cupos simultáneos de esta cancha.
												Cada horario se habilita una sola vez y el sistema permite reservas hasta completar ese cupo.
											</div>
										</div>
										<ul class="nav nav-pills nav-pills-custom mb-8 fs-6 justify-content-center gap-2">
											<?php foreach (Schedules::getDay() as $day) { ?>
												<li class="nav-item">
													<a class="nav-link px-6 py-3 fw-bold btn btn-active-light-primary btn-color-gray-600 <?php showArgument(1, $day->id, 'active') ?>" data-bs-toggle="tab" href="#tab_<?php echo $day->name ?>"><?php echo $day->name ?></a>
												</li>
											<?php } ?>
										</ul>
										<div class="tab-content" id="myTabContentHorarios">
											<?php foreach (Schedules::getDay() as $d) { ?>
												<div class="tab-pane fade <?php showArgument(1, $d->id, 'show active') ?>" id="tab_<?php echo $d->name ?>" role="tabpanel">
													<form class="form-horario">
														<div class="d-flex justify-content-between align-items-center schedule-toolbar">
															<div class="d-flex gap-2">
																<button type="button" class="btn btn-select-group" data-group="morning">Mañana</button>
																<button type="button" class="btn btn-select-group" data-group="afternoon">Tarde</button>
																<button type="button" class="btn btn-select-group" data-group="night">Noche</button>
															</div>
															<button type="button" class="btn btn-copy-to-all">
																<i class="fa-solid fa-copy me-1"></i>Copiar a todos los días
															</button>
														</div>
														<div class="row g-3">
														<input type="hidden" name="id_field" value="<?php echo $cancha->id?>">
														<input type="hidden" name="id_day" value="<?php echo $d->id?>">
														<?php 
														foreach (Schedules::getAllDayCancha($d->id, $cancha->id) as $h) { 
															$startTime = explode(' - ', $h->hour12)[0];
															$militaryHour = (strpos($startTime, 'PM') !== false && intval($startTime) != 12) ? intval($startTime) + 12 : intval($startTime);
															if (strpos($startTime, 'AM') !== false && intval($startTime) == 12) $militaryHour = 0;
															
															$slotClass = 'slot-morning';
															$icon = '<i class="fa-solid fa-sun fs-7 text-warning"></i>';
															if ($militaryHour >= 12 && $militaryHour < 18) {
																$slotClass = 'slot-afternoon';
																$icon = '<i class="fa-solid fa-cloud-sun fs-7 text-success"></i>';
															} else if ($militaryHour >= 18 || $militaryHour < 6) {
																$slotClass = 'slot-night';
																$icon = '<i class="fa-solid fa-moon fs-7 text-primary"></i>';
															}
														?>
															<div class="col-lg-4 col-md-6 col-sm-12 <?php echo $slotClass ?>">
																<div class="form-check form-check-custom mb-0">
																	<input class="form-check-input me-3" <?php echo $h->checked ?> name="horario[]" id="h_<?php echo $d->id ?>_<?php echo $h->id?>" type="checkbox" value="<?php echo $h->id?>" >
																	<label class="form-check-label" for="h_<?php echo $d->id ?>_<?php echo $h->id?>">
																		<?php echo $icon ?>
																		<div class="fw-bolder text-gray-800"><?php echo $h->hour12 ?></div>
																	</label>
																</div>
															</div>
														<?php } ?>
														</div>
														<div class="text-end mt-8 border-top pt-5">
															<button type="submit" class="btn btn-primary btn-lg px-10">
																<i class="fa-solid fa-save me-2"></i>Guardar Horarios del <?php echo $d->name ?>
															</button>
														</div>
													</form>
												</div>
											<?php } ?>
										</div>
									</div>

									<div class="tab-pane fade" id="servicios" role="tabpanel">
										<form class="form-servicios">
											<input type="hidden" name="id_field" value="<?php echo $cancha->id ?>">
											<div class="row g-5 mb-8">
												<?php 
												$serviceIcons = [
													'Zona de Parrillas' => 'fa-solid fa-fire-burner',
													'Servicio de Emergencia' => 'fa-solid fa-truck-medical',
													'Bar con Wifi' => 'fa-solid fa-mug-hot',
													'Estacionamiento' => 'fa-solid fa-car',
													'Cumpleaños' => 'fa-solid fa-cake-candles',
													'Vestuarios' => 'fa-solid fa-shirt',
													'Escuela de Fútbol' => 'fa-solid fa-graduation-cap'
												];
												foreach (Services::getAllServicesCancha($cancha->id) as $servicio) { 
													$iconClass = isset($serviceIcons[$servicio->name_service]) ? $serviceIcons[$servicio->name_service] : 'fa-solid fa-star';
												?>
													<div class="col-lg-3 col-md-4 col-sm-6">
														<div class="form-check">
															<input class="form-check-input" <?php echo $servicio->checked ? 'checked' : '';?> name="servicio[]" id="servicio_<?php echo $servicio->id_service?>" type="checkbox" value="<?php echo $servicio->id_service?>" >
															<label class="form-check-label" for="servicio_<?php echo $servicio->id_service?>">
																<i class="<?php echo $iconClass ?>"></i>
																<div class="fw-bolder text-gray-800 fs-6"><?php echo $servicio->name_service ?></div>
															</label>
														</div>
													</div>
												<?php } ?>
											</div>
											<div class="d-flex justify-content-end border-top pt-5">
												<button type="submit" class="btn btn-primary px-10 h-50px fs-5">
													<i class="fa-solid fa-save me-2"></i>Guardar Servicios
												</button>
											</div>
										</form>
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
