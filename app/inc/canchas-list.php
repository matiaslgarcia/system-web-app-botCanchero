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
			<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<!--begin::Container-->
					<div id="kt_content_container" class="container-xxl">
						<div class="row gy-5 g-xl-8">
							<div class="col-xl-12">
								<!--begin::Tables Widget 9-->
								<div class="card card-xl-stretch mb-5 mb-xl-8">
									<!--begin::Header-->
									<div class="card-header border-0 pt-5">
										<h3 class="card-title align-items-start flex-column">
											<span class="card-label fw-bolder fs-3 mb-1">Listado de Canchas</span>
										</h3>
										<div class="card-toolbar">
											<a href="add-cancha" class="btn btn-sm btn-light btn-active-primary">
												<span class="svg-icon svg-icon-3">
													<i class="fa-solid fa-plus"></i>
												</span>
												<!--end::Svg Icon-->Agregar Cancha</a>
										</div>
									</div>
									<!--end::Header-->
									<!--begin::Body-->
									<div class="card-body py-3">
										<!--begin::Table container-->
										<div class="table-responsive d-none d-md-block">
											<?php $canchas = Canchas::getAll(); ?>
											<!--begin::Table-->
											<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
												<!--begin::Table head-->
												<thead>
													<tr class="fw-bolder text-muted">
														<th class="min-w-150px">Nombre</th>
														<th class="min-w-150px">Latitud</th>
														<th class="min-w-150px">Longitud</th>
														<th class="min-w-100px text-end">Acciones</th>
													</tr>
												</thead>
												<!--end::Table head-->
												<!--begin::Table body-->
												<tbody>
													<?php if (empty($canchas)) { ?>
														<tr>
															<td colspan="4" class="text-center text-muted py-10">No hay canchas para mostrar.</td>
														</tr>
													<?php } ?>
													<?php foreach($canchas AS $cancha) { ?>
													<tr>
														<td>
															<div class="d-flex align-items-center">
																<div class="symbol symbol-45px me-5">
																	<img src="<?php echo $cancha->logo ?>" alt="">
																</div>
																<div class="d-flex justify-content-start flex-column">
																	<a href="edit-cancha/<?php echo $cancha->id ?>" class="text-dark fw-bolder text-hover-primary fs-6"><?php echo $cancha->name ?></a>

																</div>
																</div>
															</td>
														<td>
															<span class="text-dark fw-bolder text-hover-primary d-block fs-6"><?php echo $cancha->latitude ?></span>
														</td>
														<td>
															<span class="text-dark fw-bolder text-hover-primary d-block fs-6"><?php echo $cancha->length ?></span>
														</td>
														<td>
															<div class="d-flex justify-content-end flex-shrink-0">
																<a href="edit-cancha/<?php echo $cancha->id ?>" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
																	<!--begin::Svg Icon | path: icons/duotune/art/art005.svg-->
																	<span class="svg-icon svg-icon-3">
																		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																			<path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="currentColor" />
																			<path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z" fill="currentColor" />
																		</svg>
																	</span>
																	<!--end::Svg Icon-->
																</a>
																<span  class="btn-delete-cancha btn btn-icon btn-bg-light btn-active-color-primary btn-sm btn-delete-user" data-id="<?php echo $cancha->id ?>">
																	<!--begin::Svg Icon | path: icons/duotune/general/gen027.svg-->
																	<span class="svg-icon svg-icon-3">
																		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
																			<path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z" fill="currentColor" />
																			<path opacity="0.5" d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z" fill="currentColor" />
																			<path opacity="0.5" d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z" fill="currentColor" />
																		</svg>
																	</span>
																	<!--end::Svg Icon-->
																</span>
															</div>
														</td>
													</tr>
													<?php } ?>
												</tbody>
												<!--end::Table body-->
											</table>
											<!--end::Table-->
										</div>
										<div class="d-block d-md-none px-2">
											<?php if (empty($canchas)) { ?>
												<div class="bc-mobile-card text-center text-muted py-8">No hay canchas para mostrar.</div>
											<?php } ?>
											<?php foreach($canchas AS $cancha) { ?>
												<div class="bc-mobile-card mb-3">
													<div class="d-flex align-items-center mb-3">
														<div class="symbol symbol-45px me-3">
															<img src="<?php echo $cancha->logo ?>" alt="">
														</div>
														<div class="fw-bolder fs-6"><?php echo $cancha->name ?></div>
													</div>
													<div class="mb-1">
														<div class="text-muted fs-8">Latitud</div>
														<div class="fw-bold"><?php echo $cancha->latitude ?></div>
													</div>
													<div class="mb-3">
														<div class="text-muted fs-8">Longitud</div>
														<div class="fw-bold"><?php echo $cancha->length ?></div>
													</div>
													<div class="d-flex flex-wrap gap-2">
														<a href="edit-cancha/<?php echo $cancha->id ?>" class="btn btn-sm btn-light-primary">Editar</a>
														<button type="button" class="btn btn-sm btn-light-danger btn-delete-cancha" data-id="<?php echo $cancha->id ?>">Eliminar</button>
													</div>
												</div>
											<?php } ?>
										</div>
										<!--end::Table container-->
									</div>
									<!--begin::Body-->
								</div>
								<!--end::Tables Widget 9-->
							</div>
							<!--end::Col-->
						</div>
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
	<!--end::Page-->
</div>
