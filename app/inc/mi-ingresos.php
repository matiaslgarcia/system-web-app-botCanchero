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
									<div class="col-6">
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
											<th>Fecha</th>
											<th>Estado</th>
											<th>Tarjeta</th>
											<th>Total</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach (Invoices::getMiIngresos() as $invoice) { ?>
											<tr>
												<td><?php echo $invoice->date ?></td>
												<td><?php echo $invoice->status ?></td>
												<td><img class="logo-card-type" src="<?php echo showLogoPaymetMethod($invoice->paymet_method) ?>" ></td>

												<td><?php echo $invoice->total ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
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