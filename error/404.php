<?php

    require '../app/int.php';

    Theme::header([
        'title' => 'Reservas',
		'base' => '../app/',
        'css'   => [
            'plugins.bundle',
            'style.bundle',
        ]
    ]);
?>

	<div class="d-flex flex-column flex-root">
		<div class="d-flex flex-column flex-center flex-column-fluid p-10">
			<img src="assets/img/logos/logo_canchero.png" alt="" class="mw-100 mb-10 h-lg-450px" />
				<h1 class="fw-bold mb-10" style="color: #A3A3C7">Parece que no hay nada aquí</h1>
				<a href="<?php echo URL ?>" class="btn btn-primary">Volver</a>
			</div>
		</div>
	</div>