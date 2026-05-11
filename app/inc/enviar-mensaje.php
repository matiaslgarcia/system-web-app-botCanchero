<div class="d-flex flex-column flex-root">
	<div class="page d-flex flex-row flex-column-fluid">
		<?php inc('sidebar') ?>
		<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
			<?php inc('header') ?>
			<div class="content d-flex flex-column flex-column-fluid pt-5" id="kt_content">
				<div class="post d-flex flex-column-fluid" id="kt_post">
					<div id="kt_content_container" class="container-xxl">

						<div class="d-flex align-items-center mb-6">
							<i class="fa-brands fa-whatsapp fs-2x text-success me-3"></i>
							<div>
								<h2 class="fw-bolder text-dark mb-0">Enviar Mensaje WhatsApp</h2>
								<span class="text-muted fs-6">Enviá un mensaje a uno o varios clientes registrados.</span>
							</div>
						</div>

						<div class="row g-5">

							<!-- Panel izquierdo: lista de clientes -->
							<div class="col-lg-5">
								<div class="card shadow-sm h-100">
									<div class="card-header border-0 pt-5">
										<div class="card-title flex-column">
											<h4 class="fw-bolder text-dark mb-0">Destinatarios</h4>
											<span class="text-muted fs-7 mt-1">
												<span id="countSeleccionados">0</span> seleccionados de <span id="countTotal">0</span>
											</span>
										</div>
										<div class="card-toolbar">
											<button type="button" id="btnSeleccionarTodos" class="btn btn-sm btn-light-primary">
												Seleccionar todos
											</button>
										</div>
									</div>
									<div class="card-body pt-3 pb-3">
										<div class="mb-3">
											<input type="text" id="buscarCliente" class="form-control form-control-solid form-control-sm" placeholder="Buscar por nombre o teléfono...">
										</div>
										<div id="listaClientes" style="max-height: 450px; overflow-y: auto;">
											<div class="text-center text-muted py-10">
												<span class="spinner-border spinner-border-sm me-2"></span>Cargando clientes...
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Panel derecho: mensaje -->
							<div class="col-lg-7">
								<div class="card shadow-sm">
									<div class="card-header border-0 pt-5">
										<div class="card-title">
											<h4 class="fw-bolder text-dark mb-0">Mensaje</h4>
										</div>
									</div>
									<div class="card-body pt-3">
										<div class="mb-4">
											<textarea id="mensajeTexto" class="form-control form-control-solid" rows="8" maxlength="4096" placeholder="Escribí el mensaje que querés enviar a los clientes seleccionados..."></textarea>
											<div class="d-flex justify-content-between mt-1">
												<span class="text-muted fs-8">Máximo 4096 caracteres</span>
												<span id="charCount" class="text-muted fs-8">0 / 4096</span>
											</div>
										</div>

										<!-- Preview -->
										<div class="mb-5">
											<label class="form-label fw-bold text-gray-700 mb-2">Vista previa</label>
											<div class="bg-light-success rounded p-4" style="border-left: 4px solid #50cd89;">
												<div class="d-flex align-items-center mb-2">
													<i class="fa-brands fa-whatsapp text-success me-2 fs-4"></i>
													<span class="fw-bold text-gray-700 fs-7">Así verá el cliente el mensaje</span>
												</div>
												<div id="previewMensaje" class="text-gray-700 fs-6" style="white-space: pre-wrap; min-height: 40px;">
													<span class="text-muted fst-italic">El mensaje aparecerá aquí...</span>
												</div>
											</div>
										</div>

										<div class="separator my-4"></div>

										<div class="d-flex align-items-center justify-content-between">
											<div class="text-muted fs-7">
												Se enviará a <strong id="countEnviar">0</strong> destinatario(s).
												<br><span class="text-warning fs-8"><i class="fa-solid fa-clock me-1"></i>~300ms por mensaje para respetar límites de Meta.</span>
											</div>
											<button type="button" id="btnEnviar" class="btn btn-success" disabled>
												<span class="indicator-label">
													<i class="fa-brands fa-whatsapp me-2"></i>Enviar mensajes
												</span>
												<span class="indicator-progress d-none">
													<span class="spinner-border spinner-border-sm me-2"></span>Enviando...
												</span>
											</button>
										</div>
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
