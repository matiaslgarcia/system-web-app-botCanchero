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
			<div class="content d-flex flex-column flex-column-fluid pt-0 pt-md-0" id="kt_content">
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
											<span class="card-label fw-bolder fs-3 mb-1">Metodos de Pago</span>
										</h3>
									</div>
									<!--end::Header-->
									<!--begin::Body-->
									<?php $methodPayment = Paymet::getMethodPayment() ?>
									<div class="card-body py-3">
										<ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
											<?php foreach($methodPayment as $navPayment) { ?>
											<li class="nav-item">
												<a class="nav-link <?php showClass($navPayment->id, 1, 'active') ?>" data-bs-toggle="tab" href="#kt_tab_pane_<?php echo $navPayment->tag ?>"><?php echo $navPayment->name ?></a>
											</li>
											<?php } ?>
										</ul>

										<div class="tab-content" id="myTabContent">
											<?php foreach($methodPayment as $contentPayment) {
												require 'inc/contenPaymentMethod/' . $contentPayment->tag . '.php'; 
											} ?>
										</div>
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
			<?php inc('footer') ?>
			<!--end::Footer-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Page-->
</div>