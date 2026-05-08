<?php 
    $selectedDate = isset($_GET['date']) ? trim((string) $_GET['date']) : date('d/m/Y');
	$totalDiario = Invoices::getTotalMiIngresos();
    $invoices = Invoices::getMiIngresos();
    
    // Configuración de Meta Diaria (Fácil de cambiar)
    $metaDiaria = 100000; 
    $porcentajeMeta = min(100, round(($totalDiario / $metaDiaria) * 100));

    // Estadísticas
    $countApproved = 0;
    $countRefunded = 0;
    foreach($invoices as $inv) {
        if($inv->estado == 'approved') $countApproved++;
        if($inv->estado == 'refunded') $countRefunded++;
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
                        
                        <!-- Resumen de Ingresos -->
                        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                            <div class="col-md-4">
                                <div class="card card-flush h-md-100" style="background: linear-gradient(112.14deg, #00D2FF 0%, #3A7BD5 100%) !important;" data-bs-theme="dark">
                                    <div class="card-header pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">$<?php echo number_format($totalDiario, 2) ?></span>
                                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Ingreso Total del Día</span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex align-items-end pt-0">
                                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                                <span class="fw-boldest text-white fs-6">Meta Diaria</span>
                                                <span class="fw-boldest text-white fs-6"><?php echo $porcentajeMeta ?>%</span>
                                            </div>
                                            <div class="h-8px mx-3 w-100 bg-white bg-opacity-25 rounded">
                                                <div class="bg-white rounded h-8px" role="progressbar" style="width: <?php echo $porcentajeMeta ?>%;" aria-valuenow="<?php echo $porcentajeMeta ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-flush h-md-100 shadow-sm">
                                    <div class="card-header pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2"><?php echo $countApproved ?></span>
                                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Pagos Aprobados</span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                                        <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">Transacciones exitosas hoy</span>
                                        <div class="symbol-group symbol-hover">
                                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Pago Online">
                                                <span class="symbol-label bg-light-success text-success fw-bold">ON</span>
                                            </div>
                                            <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Pago Presencial">
                                                <span class="symbol-label bg-light-primary text-primary fw-bold">PR</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card card-flush h-md-100 shadow-sm">
                                    <div class="card-header pt-5">
                                        <div class="card-title d-flex flex-column">
                                            <span class="fs-2hx fw-bold text-danger me-2 lh-1 ls-n2"><?php echo $countRefunded ?></span>
                                            <span class="text-gray-400 pt-1 fw-semibold fs-6">Devoluciones</span>
                                        </div>
                                    </div>
                                    <div class="card-body d-flex align-items-end pt-0">
                                        <div class="text-gray-400 fw-semibold fs-6">Monto total reembolsado hoy por cancelaciones.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

						<!-- Filtros -->
						<div class="card mb-7 shadow-sm">
							<div class="card-body">
								<form id="form-filtro" class="row align-items-end">
									<div class="col-md-4 mb-3 mb-md-0">
										<label for="date" class="form-label fw-bold text-gray-700">Filtrar por Fecha</label>
                                        <div class="position-relative d-flex align-items-center">
                                            <i class="fa-solid fa-calendar position-absolute ms-4 fs-4 text-gray-500"></i>
                                            <input type="text" name="date" id="date" class="form-control form-control-solid ps-12 fs-6 fw-semibold" value="<?php echo htmlspecialchars($selectedDate, ENT_QUOTES); ?>" style="padding-left: 3.25rem !important;">
                                        </div>
									</div>
									<div class="col-md-2">
										<button type="submit" class="btn btn-primary w-100">
                                            <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar
                                        </button>
									</div>
								</form>
							</div>
						</div>

                        <!-- Tabla de Ingresos -->
						<div class="card shadow-sm">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title">
                                    <h3 class="card-label fw-bolder text-dark">Detalle de Ingresos</h3>
                                </div>
                                <div class="card-toolbar">
                                    <button type="button" id="btnExportarIngresosPDF" class="btn btn-light-danger btn-sm me-2">
                                        <i class="fa-solid fa-file-pdf me-2"></i>Exportar PDF
                                    </button>
                                    <button type="button" class="btn btn-light-primary btn-sm" onclick="window.print()">
                                        <i class="fa-solid fa-print me-2"></i>Imprimir Reporte
                                    </button>
                                </div>
                            </div>
							<div class="card-body pt-0">
								<table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
									<thead>
										<tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
											<th>N° Reserva</th>
											<th>Fecha</th>
											<th>Estado Pago</th>
											<th>Método</th>
											<th class="text-end">Total</th>
										</tr>
									</thead>
									<tbody class="text-gray-600 fw-bold">
										<?php foreach ($invoices as $invoice) { ?>
											<tr>
												<td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-40px me-3">
                                                            <div class="symbol-label fs-7 fw-bold bg-light-dark text-gray-800">#<?php echo $invoice->nroReserva ?></div>
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <span class="text-gray-800 fw-bolder text-hover-primary mb-1 fs-6">Reserva</span>
                                                            <span class="text-muted fw-bold d-block fs-7">Detalle de transacción</span>
                                                        </div>
                                                    </div>
                                                </td>
												<td>
                                                    <span class="text-gray-800 fw-bold d-block fs-6"><?php echo date('d/m/Y', strtotime($invoice->date)) ?></span>
                                                    <span class="text-muted fw-semibold d-block fs-7">Fecha de pago</span>
                                                </td>
												<td>
													<?php
														if ($invoice->estado == 'approved') {
                                                            echo '<span class="badge badge-light-success fs-7 fw-bold">Aprobado</span>';
                                                        } elseif ($invoice->estado == 'refunded') {
                                                            echo '<span class="badge badge-light-danger fs-7 fw-bold">Reembolsado</span>';
                                                        } elseif ($invoice->estado == 'pending') {
                                                            echo '<span class="badge badge-light-warning fs-7 fw-bold">Pendiente</span>';
                                                        } else {
                                                            echo '<span class="badge badge-light-warning fs-7 fw-bold">'.ucfirst($invoice->estado).'</span>';
                                                        }
													?>
												</td>
												<td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-30px me-3">
                                                            <img src="<?php echo showLogoPaymetMethod($invoice->paymet_method) ?>" class="object-fit-contain" alt="Method">
                                                        </div>
                                                        <div class="d-flex justify-content-start flex-column">
                                                            <span class="text-gray-800 fw-bold text-hover-primary fs-6"><?php echo ucfirst(str_replace('_', ' ', $invoice->paymet_method)) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
												<td class="text-end">
													<?php
                                                    $val = abs((float) $invoice->signed_total);
                                                    $isNegative = ((float) $invoice->signed_total) < 0;
                                                    $colorClass = $isNegative ? 'text-danger' : 'text-success';
                                                    $prefix = $isNegative ? '-' : '';
                                                    
                                                    echo '<span class="fw-boldest '.$colorClass.' fs-5">'.$prefix.'$'.number_format($val, 2).'</span>';
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
.object-fit-contain { object-fit: contain; }
#kt_datatable_example_1 thead tr { background-color: #f9fafb !important; }
#kt_datatable_example_1 thead th { color: #5e6278 !important; font-weight: 700 !important; text-transform: none !important; }
#kt_datatable_example_1 tbody tr:hover { background-color: #f1f5f9; transition: all 0.2s ease; cursor: default; }
.badge-light-success { background-color: #e8fff3 !important; color: #50cd89 !important; border: 1px solid #ccf6e4; }
.badge-light-danger { background-color: #fff5f8 !important; color: #f1416c !important; border: 1px solid #ffd0db; }
.badge-light-dark { background-color: #f1f1f2 !important; color: #181c32 !important; }
.fw-boldest { font-weight: 800 !important; letter-spacing: -0.02em; }

@media print {
    .sidebar, .header, .card-toolbar, #form-filtro, .footer { display: none !important; }
    .wrapper { margin-left: 0 !important; padding: 0 !important; }
    .content { padding-top: 0 !important; }
}
</style>
