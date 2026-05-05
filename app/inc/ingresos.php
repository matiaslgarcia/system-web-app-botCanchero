<?php
	$totalDiario = Invoices::getTotalIngresos();
?>
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
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-0" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<!--begin::Container-->
					<div id="kt_content_container" class="container-xxl">
						<!--begin::Navbar-->
						<div class="card mb-2">
							<div class="card-body">
								<form id="form-filtro" class="d-flex justify-content-between">
									<div class="col-3">
										<label for="id_field" class="form-label">Cancha</label>
										<select class="form-control" name="id_field" id="id_field">
											<option selected disabled value="%">--SELECIONE--</option>
											<?php foreach (Canchas::getAll() as $cancha) { ?>
												<option value="<?php echo $cancha->id ?>"><?php echo $cancha->name ?></option>
											<?php } ?>
										</select>
									</div>
									<div class="col-3">
										<label for="date" class="form-label">Cancha</label>
										<input type="text" name="date" id="date" class="form-control" value="<?php echo date('d/m/Y') ?>">
									</div>
									<div class="col-3 align-self-center pt-6">
										<button type="submit" class="btn btn-primary">Buscar</button>
									</div>
								</form>
							</div>
						</div>
						<div class="card mb-5 mb-xl-10">
							<div class="card-body pt-9 pb-0">
								<table id="kt_datatable_example_1" class="table table-row-bordered gy-5">
									<thead>
										<tr class="fw-bold fs-6 text-muted">
											<th>N° Reserva</th>
											<th>Fecha</th>
											<th>Estado del Pago</th>
											<th>Método de Pago</th>
											<th>SubTotal</th>										
										</tr>
									</thead>
									<tbody>
										<?php foreach (Invoices::getIngresos() as $invoice) { ?>
										<tr>
											<td><?php echo $invoice->nroReserva ?></td>
											<td><?php echo date('d/m/Y', strtotime($invoice->date)) ?></td>
											<td>
													<?php
														$estados = array(
															"approved" => "Aprobado",
															"refunded" => "Reembolsado",
															"pending" => "Pendiente"
														);

														echo isset($estados[$invoice->estado]) ? $estados[$invoice->estado] : ucfirst($invoice->estado);
													?>
												</td>
											<td><img class="logo-card-type" src="<?php echo showLogoPaymetMethod($invoice->paymet_method) ?>" ></td>
											<td style="color: <?php echo ((float)$invoice->signed_total < 0) ? 'red' : 'green'; ?>">
													<?php echo '$' . number_format((float)$invoice->signed_total, 2); ?>
												</td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="card mb-2">
							<div class="card-body">
								 <div class="d-flex justify-content-end">
								 <span class="fw-bolder text-gray-800 text-hover-primary me-3 fs-4">
									Total:
								</span>
								<span class="fw-bolder <?php echo (isset($totalDiario) && $totalDiario > 0) ? 'text-success' : 'text-danger'; ?> text-hover-primary me-3 fs-4">
									<?php echo isset($totalDiario) ? '$' . $totalDiario : 'En este momento no hay Ingresos'; ?>
								</span>
								</div>
							</div>
						</div>
						<!--end::Row-->
					</div>
					<!--end::Container-->
				</div>
				<!--end::Post-->
			</div>
			<!--end::Content-->
			<!--begin::Footer-->
			<?php inc('footer') ?>
			<!--end::Footer-->
		</div>
		<!--end::Wrapper-->
	</div>
</div>
