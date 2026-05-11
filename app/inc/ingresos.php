<?php
	$selectedDate = isset($_GET['date']) ? trim((string) $_GET['date']) : date('d/m/Y');
	$selectedField = isset($_GET['cancha']) ? (string) $_GET['cancha'] : '%';
	$invoices = Invoices::getIngresos();
	$extraIngresos = Invoices::getExtraIngresos();
	$totalDiario = Invoices::getTotalIngresos() + Invoices::getTotalExtraIngresos();

	$countApproved = 0;
	$countRefunded = 0;
	$countPending = 0;
	foreach ($invoices as $inv) {
		if (($inv->estado ?? '') === 'approved') $countApproved++;
		if (($inv->estado ?? '') === 'refunded') $countRefunded++;
		if (($inv->estado ?? '') === 'pending') $countPending++;
	}
?>
<div class="d-flex flex-column flex-root">
	<div class="page d-flex flex-row flex-column-fluid">
		<?php inc('sidebar') ?>
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<?php inc('header') ?>
			<div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<div id="kt_content_container" class="container-xxl">
						<div class="row g-5 g-xl-8 mb-6">
							<div class="col-md-4">
								<div class="card card-flush h-md-100" style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%) !important;" data-bs-theme="dark">
									<div class="card-header pt-5">
										<div class="card-title d-flex flex-column">
											<span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">$<?php echo number_format((float) $totalDiario, 2); ?></span>
											<span class="text-white opacity-75 pt-1 fw-semibold fs-6">Ingresos del día</span>
										</div>
									</div>
									<div class="card-body d-flex align-items-end pt-0">
										<span class="text-white opacity-75 fs-7">Vista consolidada para superAdmin.</span>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="card card-flush h-md-100 shadow-sm">
									<div class="card-header pt-5">
										<div class="card-title d-flex flex-column">
											<span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2"><?php echo $countApproved; ?></span>
											<span class="text-gray-400 pt-1 fw-semibold fs-6">Pagos aprobados</span>
										</div>
									</div>
									<div class="card-body d-flex align-items-end pt-0">
										<span class="text-gray-500 fs-7">Operaciones cobradas correctamente.</span>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="card card-flush h-md-100 shadow-sm">
									<div class="card-header pt-5">
										<div class="card-title d-flex flex-column">
											<span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2"><?php echo $countRefunded; ?></span>
											<span class="text-gray-400 pt-1 fw-semibold fs-6">Reembolsos</span>
										</div>
									</div>
									<div class="card-body d-flex align-items-end pt-0">
										<span class="text-gray-500 fs-7">Pendientes: <strong><?php echo $countPending; ?></strong></span>
									</div>
								</div>
							</div>
						</div>

						<div class="card mb-6 shadow-sm">
							<div class="card-body">
								<form id="form-filtro" class="row align-items-end g-4">
									<div class="col-md-4">
										<label for="id_field" class="form-label fw-bold text-gray-700">Cancha</label>
										<select class="form-select form-select-solid" name="id_field" id="id_field">
											<option value="%">Todas las canchas</option>
											<?php foreach (Canchas::getAll() as $cancha) { ?>
												<option value="<?php echo $cancha->id ?>" <?php echo ((string) $cancha->id === (string) $selectedField) ? 'selected' : ''; ?>>
													<?php echo $cancha->name ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<div class="col-md-4">
										<label for="date" class="form-label fw-bold text-gray-700">Fecha</label>
										<input type="text" name="date" id="date" class="form-control form-control-solid" value="<?php echo htmlspecialchars($selectedDate, ENT_QUOTES); ?>">
									</div>
									<div class="col-md-4 d-flex gap-2">
										<button type="submit" class="btn btn-primary flex-fill">
											<i class="fa-solid fa-magnifying-glass me-2"></i>Aplicar filtros
										</button>
										<a href="ingresos" class="btn btn-light flex-fill">Limpiar</a>
									</div>
								</form>
							</div>
						</div>

						<div class="card shadow-sm">
							<div class="card-header border-0 pt-6">
								<div class="card-title">
									<h3 class="card-label fw-bolder text-dark">Detalle de ingresos</h3>
								</div>
								<div class="card-toolbar gap-2">
									<button type="button" id="btnAgregarIngresoExtra" class="btn btn-light-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExtraIngreso">
										<i class="fa-solid fa-plus me-2"></i>Ingreso extra
									</button>
									<button type="button" id="btnExportarIngresosPDF" class="btn btn-light-danger btn-sm">
										<i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
									</button>
								</div>
							</div>
							<div class="card-body pt-0">
								<table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
									<thead>
										<tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
											<th>Concepto</th>
											<th>Fecha</th>
											<th>Estado / Tipo</th>
											<th>Método</th>
											<th class="text-end">Total</th>
											<th></th>
										</tr>
									</thead>
									<tbody class="text-gray-700 fw-semibold">
														<?php foreach ($invoices as $invoice) { ?>
											<tr>
												<td>#<?php echo $invoice->nroReserva ?></td>
												<td><?php echo date('d/m/Y', strtotime($invoice->date)) ?></td>
												<td>
													<?php if ($invoice->estado === 'approved') { ?>
														<span class="badge badge-light-success">Aprobado</span>
													<?php } elseif ($invoice->estado === 'refunded') { ?>
														<span class="badge badge-light-danger">Reembolsado</span>
													<?php } else { ?>
														<span class="badge badge-light-warning">Pendiente</span>
													<?php } ?>
												</td>
												<td>
													<div class="d-flex align-items-center">
														<img class="logo-card-type me-2" src="<?php echo showLogoPaymetMethod($invoice->paymet_method) ?>" alt="Método de pago">
														<span><?php echo ucfirst(str_replace('_', ' ', (string) $invoice->paymet_method)); ?></span>
													</div>
												</td>
												<td class="text-end fw-bolder <?php echo ((float) $invoice->signed_total < 0) ? 'text-danger' : 'text-success'; ?>">
													<?php
														$value = (float) $invoice->signed_total;
														$prefix = $value < 0 ? '-' : '';
														echo $prefix . '$' . number_format(abs($value), 2);
													?>
												</td>
												<td></td>
											</tr>
										<?php } ?>
										<?php foreach ($extraIngresos as $extra) { ?>
											<tr class="bg-light-success bg-opacity-25">
												<td>
													<span class="fw-bold text-gray-800"><?php echo htmlspecialchars($extra->description, ENT_QUOTES) ?></span>
												</td>
												<td><?php echo date('d/m/Y', strtotime($extra->date_income)) ?></td>
												<td><span class="badge badge-light-info">Ingreso extra</span></td>
												<td><?php echo ucfirst(str_replace('_', ' ', $extra->method_payment)) ?></td>
												<td class="text-end fw-bolder text-success">$<?php echo number_format((float) $extra->amount, 2) ?></td>
												<td class="text-end">
													<button class="btn btn-icon btn-sm btn-light-danger btn-delete-extra" data-id="<?php echo $extra->id ?>" title="Eliminar">
														<i class="fa-solid fa-trash fs-7"></i>
													</button>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php inc('footer') ?>
		</div>
	</div>
</div>

<!-- Modal: Agregar Ingreso Extra -->
<div class="modal fade" id="modalExtraIngreso" tabindex="-1" aria-labelledby="modalExtraIngresoLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title fw-bolder" id="modalExtraIngresoLabel"><i class="fa-solid fa-plus-circle text-success me-2"></i>Agregar ingreso extra</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
			</div>
			<form id="formExtraIngreso" autocomplete="off">
				<div class="modal-body">
					<div class="mb-4">
						<label class="form-label fw-bold required">Descripción</label>
						<input type="text" name="description" class="form-control form-control-solid" placeholder="Ej: Alquiler de equipamiento, cuota mensual..." maxlength="255" required>
					</div>
					<div class="row g-3 mb-4">
						<div class="col-6">
							<label class="form-label fw-bold required">Monto ($)</label>
							<input type="number" name="amount" class="form-control form-control-solid" placeholder="0.00" min="0.01" step="0.01" required>
						</div>
						<div class="col-6">
							<label class="form-label fw-bold required">Fecha</label>
							<input type="text" name="date_income" id="extraIngresoDate" class="form-control form-control-solid" value="<?php echo htmlspecialchars($selectedDate, ENT_QUOTES) ?>" required>
						</div>
					</div>
					<div class="mb-4">
						<label class="form-label fw-bold required">Método de cobro</label>
						<select name="method_payment" class="form-select form-select-solid" required>
							<option value="efectivo">Efectivo</option>
							<option value="transferencia">Transferencia</option>
							<option value="mercado_pago">Mercado Pago</option>
							<option value="otro">Otro</option>
						</select>
					</div>
					<div class="mb-2">
						<label class="form-label fw-bold required">Cancha</label>
						<select name="id_field" class="form-select form-select-solid" required>
							<?php foreach (Canchas::getAll() as $cancha) { ?>
								<option value="<?php echo $cancha->id ?>" <?php echo ((string) $cancha->id === (string) $selectedField || $selectedField === '%') ? 'selected' : '' ?>>
									<?php echo htmlspecialchars($cancha->name, ENT_QUOTES) ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div id="extraIngresoError" class="alert alert-danger d-none mt-3 py-2"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-success" id="btnGuardarExtra">
						<span class="indicator-label"><i class="fa-solid fa-check me-2"></i>Guardar</span>
						<span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm me-2"></span>Guardando...</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<style>
.logo-card-type { width: 28px; height: 28px; object-fit: contain; }
.badge-light-success { background-color: #e8fff3 !important; color: #50cd89 !important; border: 1px solid #ccf6e4; }
.badge-light-danger { background-color: #fff5f8 !important; color: #f1416c !important; border: 1px solid #ffd0db; }
.badge-light-warning { background-color: #fff8dd !important; color: #ffc700 !important; border: 1px solid #ffecb5; }
.badge-light-info { background-color: #f0f9ff !important; color: #009ef7 !important; border: 1px solid #b8e6ff; }
</style>
