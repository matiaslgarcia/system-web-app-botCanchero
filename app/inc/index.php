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
			<div class="content d-flex flex-column flex-column-fluid pt-5 pt-md-0" id="kt_content">
                <!--begin::Post-->
                <div class="post d-flex flex-column-fluid" id="kt_post">
                    <!--begin::Container-->
                    <div id="kt_content_container" class="container-xxl">
                        <!--begin::Row-->
                        <div class="row g-5 g-xl-8">
                            <!-- Turnos Hoy -->
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-primary hoverable card-xl-stretch mb-xl-8">
                                    <div class="card-body">
                                        <i class="fa-solid fa-calendar-check text-white fs-2x ms-n1"></i>
                                        <div class="text-white fw-bolder fs-2 mb-2 mt-5">Turnos de Hoy</div>
                                        <div class="fw-bold text-white">Administrá los partidos del día</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Ingresos -->
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-success hoverable card-xl-stretch mb-xl-8">
                                    <div class="card-body">
                                        <i class="fa-solid fa-money-bill-trend-up text-white fs-2x ms-n1"></i>
                                        <div class="text-white fw-bolder fs-2 mb-2 mt-5">Ingresos del Mes</div>
                                        <div class="fw-bold text-white">Visualizá tus ganancias</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Reservas Fijas -->
                            <div class="col-xl-4 col-md-6">
                                <div class="card bg-info hoverable card-xl-stretch mb-5 mb-xl-8">
                                    <div class="card-body">
                                        <i class="fa-solid fa-arrows-rotate text-white fs-2x ms-n1"></i>
                                        <div class="text-white fw-bolder fs-2 mb-2 mt-5">Clientes Fijos</div>
                                        <div class="fw-bold text-white">Gestioná tus turnos recurrentes</div>
                                    </div>
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
	<!--end::Page-->
</div>