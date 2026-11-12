<?php
/**
 * Formulario de contacto propio: [carmenarias_contacto] y [carmenarias_contacto tipo="canal"].
 *
 * El tipo "canal" es el del Sistema Interno de Información (Ley 2/2023):
 * permite comunicaciones anónimas y se envía al email del canal.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'carmenarias_contacto', 'ca_formulario_contacto' );

function ca_formulario_contacto( $atts ) {
	$atts  = shortcode_atts( array( 'tipo' => 'contacto' ), $atts, 'carmenarias_contacto' );
	$canal = 'canal' === $atts['tipo'];
	$id    = $canal ? 'formulario-canal' : 'formulario';

	$estado = isset( $_GET['ca_form'] ) ? sanitize_key( $_GET['ca_form'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$priv   = get_page_by_path( 'politica-privacidad' );

	ob_start();
	?>
	<form class="formulario" id="<?php echo esc_attr( $id ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php if ( 'enviado' === $estado ) : ?>
			<p class="aviso aviso-ok" role="status">Mensaje enviado. <?php echo $canal ? 'La persona Responsable del Sistema Interno de Información lo recibirá y le dará el tratamiento oportuno.' : 'Te responderemos lo antes posible.'; ?></p>
		<?php elseif ( 'error' === $estado ) : ?>
			<p class="aviso aviso-error" role="alert">Falta algún dato. Revisa que has escrito el mensaje<?php echo $canal ? '' : ', tu nombre y un correo válido'; ?> y que has aceptado la política de privacidad.</p>
		<?php elseif ( 'fallo' === $estado ) : ?>
			<p class="aviso aviso-error" role="alert">No hemos podido enviar el mensaje por un problema del servidor. Llámanos al <?php echo esc_html( ca_opt( 'telefono' ) ); ?> o escribe a <?php echo esc_html( $canal ? ca_opt( 'email_canal' ) : ca_opt( 'email' ) ); ?>.</p>
		<?php endif; ?>

		<input type="hidden" name="action" value="ca_contacto">
		<input type="hidden" name="ca_tipo" value="<?php echo esc_attr( $canal ? 'canal' : 'contacto' ); ?>">
		<input type="hidden" name="ca_volver" value="<?php echo esc_url( get_permalink() ); ?>">
		<?php wp_nonce_field( 'ca_contacto', 'ca_nonce' ); ?>

		<div class="campos">
			<p class="campo">
				<label for="<?php echo esc_attr( $id ); ?>-nombre">Nombre y apellidos<?php echo $canal ? ' <span class="opcional">(opcional)</span>' : ''; ?></label>
				<input type="text" id="<?php echo esc_attr( $id ); ?>-nombre" name="ca_nombre" autocomplete="name" <?php echo $canal ? '' : 'required'; ?>>
			</p>
			<p class="campo">
				<label for="<?php echo esc_attr( $id ); ?>-email">Correo electrónico<?php echo $canal ? ' <span class="opcional">(opcional)</span>' : ''; ?></label>
				<input type="email" id="<?php echo esc_attr( $id ); ?>-email" name="ca_email" autocomplete="email" <?php echo $canal ? '' : 'required'; ?>>
			</p>
			<?php if ( ! $canal ) : ?>
			<p class="campo">
				<label for="<?php echo esc_attr( $id ); ?>-tel">Teléfono <span class="opcional">(opcional)</span></label>
				<input type="tel" id="<?php echo esc_attr( $id ); ?>-tel" name="ca_tel" autocomplete="tel">
			</p>
			<p class="campo">
				<label for="<?php echo esc_attr( $id ); ?>-asunto">Te interesa</label>
				<select id="<?php echo esc_attr( $id ); ?>-asunto" name="ca_asunto">
					<option value="Información general">Información general</option>
					<?php foreach ( ca_centros() as $c ) : ?>
						<option value="<?php echo esc_attr( ca_centro_nombre( $c ) ); ?>"><?php echo esc_html( ca_centro_nombre( $c ) ); ?></option>
					<?php endforeach; ?>
					<option value="Trabajar con nosotros">Trabajar con nosotros</option>
					<option value="Colaborar o hacer un donativo">Colaborar o hacer un donativo</option>
				</select>
			</p>
			<?php endif; ?>
			<p class="campo campo-ancho">
				<label for="<?php echo esc_attr( $id ); ?>-mensaje"><?php echo $canal ? 'Describe los hechos' : 'Mensaje'; ?></label>
				<textarea id="<?php echo esc_attr( $id ); ?>-mensaje" name="ca_mensaje" rows="6" required></textarea>
			</p>
			<p class="campo-trampa" aria-hidden="true">
				<label for="<?php echo esc_attr( $id ); ?>-web">No rellenes este campo</label>
				<input type="text" id="<?php echo esc_attr( $id ); ?>-web" name="ca_web" tabindex="-1" autocomplete="off">
			</p>
		</div>

		<p class="campo-check">
			<input type="checkbox" id="<?php echo esc_attr( $id ); ?>-priv" name="ca_privacidad" value="1" required>
			<label for="<?php echo esc_attr( $id ); ?>-priv">He leído y acepto la <a href="<?php echo esc_url( $priv ? get_permalink( $priv ) : home_url( '/politica-privacidad/' ) ); ?>">política de privacidad</a>.</label>
		</p>
		<p class="letra-pequena">Responsable: Fundación Carmen Arias. Finalidad: responder a tu consulta. Puedes ejercer tus derechos de acceso, rectificación, limitación, supresión y oposición escribiendo a direccion@carmenarias.es.</p>

		<button class="btn" type="submit"><?php echo $canal ? 'Enviar comunicación' : 'Enviar mensaje'; ?> <?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></button>
	</form>
	<?php
	return ob_get_clean();
}

add_action( 'admin_post_nopriv_ca_contacto', 'ca_procesar_contacto' );
add_action( 'admin_post_ca_contacto', 'ca_procesar_contacto' );

function ca_procesar_contacto() {
	$volver = isset( $_POST['ca_volver'] ) ? esc_url_raw( wp_unslash( $_POST['ca_volver'] ) ) : home_url( '/' );
	$volver = wp_validate_redirect( $volver, home_url( '/' ) );
	$canal  = isset( $_POST['ca_tipo'] ) && 'canal' === $_POST['ca_tipo'];
	$ancla  = $canal ? '#formulario-canal' : '#formulario';

	$error = function () use ( $volver, $ancla ) {
		wp_safe_redirect( add_query_arg( 'ca_form', 'error', $volver ) . $ancla );
		exit;
	};

	if ( ! isset( $_POST['ca_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ca_nonce'] ), 'ca_contacto' ) ) {
		$error();
	}
	// Campo trampa para robots: si viene relleno, se descarta sin avisar.
	if ( ! empty( $_POST['ca_web'] ) ) {
		wp_safe_redirect( add_query_arg( 'ca_form', 'enviado', $volver ) . $ancla );
		exit;
	}

	$nombre  = sanitize_text_field( wp_unslash( $_POST['ca_nombre'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['ca_email'] ?? '' ) );
	$tel     = sanitize_text_field( wp_unslash( $_POST['ca_tel'] ?? '' ) );
	$asunto  = sanitize_text_field( wp_unslash( $_POST['ca_asunto'] ?? '' ) );
	$mensaje = sanitize_textarea_field( wp_unslash( $_POST['ca_mensaje'] ?? '' ) );
	$priv    = ! empty( $_POST['ca_privacidad'] );

	if ( '' === $mensaje || ! $priv ) {
		$error();
	}
	if ( ! $canal && ( '' === $nombre || ! is_email( $email ) ) ) {
		$error();
	}

	$para = $canal ? ca_opt( 'email_canal' ) : ca_opt( 'email' );
	if ( $canal ) {
		$titulo = '[Canal de Información] Nueva comunicación';
		$cuerpo = "Nueva comunicación recibida a través del Sistema Interno de Información de la web.\n\n"
			. 'Nombre: ' . ( $nombre ? $nombre : '(anónimo)' ) . "\n"
			. 'Email: ' . ( $email ? $email : '(no indicado)' ) . "\n\n"
			. "Hechos:\n" . $mensaje . "\n";
	} else {
		$titulo = '[Web] ' . ( $asunto ? $asunto : 'Consulta' ) . ' · ' . $nombre;
		$cuerpo = "Nombre: $nombre\nEmail: $email\nTeléfono: " . ( $tel ? $tel : '-' ) . "\nTe interesa: $asunto\n\nMensaje:\n$mensaje\n";
	}
	$cabeceras = array();
	if ( $email && is_email( $email ) ) {
		$cabeceras[] = 'Reply-To: ' . ( $nombre ? $nombre . ' ' : '' ) . '<' . $email . '>';
	}

	$ok = wp_mail( $para, $titulo, $cuerpo, $cabeceras );
	wp_safe_redirect( add_query_arg( 'ca_form', $ok ? 'enviado' : 'fallo', $volver ) . $ancla );
	exit;
}
