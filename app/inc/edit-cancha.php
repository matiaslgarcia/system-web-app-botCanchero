<?php
	$fieldId = (int) ($_GET['cancha'] ?? 0);
	$cancha = $fieldId > 0 ? Canchas::getById($fieldId) : null;
	$priceRanges = $cancha ? Canchas::getPriceRangesByField($cancha->id) : [];
?>
<div class="d-flex flex-column flex-root">
	<div class="page d-flex flex-row flex-column-fluid">
		<?php inc('sidebar') ?>
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<?php inc('header') ?>
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<div id="kt_content_container" class="container-xxl">
						<?php if (!$cancha) { ?>
							<div class="card mb-5">
								<div class="card-body py-10 text-center">
									<div class="text-gray-700 fs-5 mb-5">La cancha solicitada no existe o no es válida.</div>
									<a href="canchas-list" class="btn btn-primary">Volver al listado</a>
								</div>
							</div>
						<?php } else { ?>
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
											<img id="add-user-select-avatar" src="<?php echo $cancha->logo ?>" style="width:120px;height:120px;max-width:120px;max-height:120px;object-fit:cover;border-radius:1rem;border:2px solid #f1f1f4;cursor:pointer;background:#fff;">
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
											<div class="col-12 mb-3">
												<label class="form-label" for="address">Dirección</label>
												<div class="input-group">
													<input type="text" name="address" id="address" class="form-control" placeholder="Ej: Av. Rivadavia 1234, CABA" autocomplete="address" value="<?php echo $cancha->address ?>">
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
												<input type="text" name="latitude" id="latitude" class="form-control bg-light-dark border-dashed text-gray-600" placeholder="Latitud" value="<?php echo $cancha->latitude ?>" readonly tabindex="-1">
											</div>
											<div class="col-12 col-md-6 mb-3">
												<label class="form-label text-muted fs-7" for="length">Longitud (No editable)</label>
												<input type="text" name="length" id="length" class="form-control bg-light-dark border-dashed text-gray-600" placeholder="Longitud" value="<?php echo $cancha->length ?>" readonly tabindex="-1">
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
																	<input class="form-check-input me-3" <?php echo $h->checked ?> name="horario[]" id="h_<?php echo $d->id ?>_<?php echo $h->id ?>" type="checkbox" value="<?php echo $h->id ?>">
																	<!--end::Input-->
																	<!--begin::Label-->
																	<label class="form-check-label" for="h_<?php echo $d->id ?>_<?php echo $h->id ?>">
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
						<?php } ?>
					</div>
				</div>
			</div>
			<?php inc('footer') ?>
		</div>
	</div>
</div>
