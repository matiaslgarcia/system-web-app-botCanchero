<?php
	$selectedDate = isset($_GET['date']) ? trim((string) $_GET['date']) : date('d/m/Y');
	$selectedField = isset($_GET['cancha']) ? (string) $_GET['cancha'] : '%';
	$invoices = Invoices::getIngresos();
	$totalDiario = Invoices::getTotalIngresos();

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
								<div class="card-toolbar">
									<button type="button" id="btnExportarIngresosPDF" class="btn btn-light-danger btn-sm">
										<i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
									</button>
								</div>
							</div>
							<div class="card-body pt-0">
								<table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
									<thead>
										<tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
											<th>N° Reserva</th>
											<th>Fecha</th>
											<th>Estado pago</th>
											<th>Método</th>
											<th class="text-end">Total</th>
										</tr>
									</thead>
									<tbody class="text-gray-700 fw-semibold">
										<?php if (empty($invoices)) { ?>
											<tr>
												<td colspan="5" class="text-center text-muted py-10">No se encontraron ingresos con los filtros seleccionados.</td>
											</tr>
										<?php } ?>
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

<style>
.logo-card-type { width: 28px; height: 28px; object-fit: contain; }
.badge-light-success { background-color: #e8fff3 !important; color: #50cd89 !important; border: 1px solid #ccf6e4; }
.badge-light-danger { background-color: #fff5f8 !important; color: #f1416c !important; border: 1px solid #ffd0db; }
.badge-light-warning { background-color: #fff8dd !important; color: #ffc700 !important; border: 1px solid #ffecb5; }
</style>
