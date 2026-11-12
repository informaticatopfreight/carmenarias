<?php
/**
 * Apariencia → Migrar contenido.
 *
 * Sustituye el contenido Divi de las páginas por bloques limpios (preparados
 * en inc/migracion/paginas.json), asigna plantillas, marca los centros, crea
 * los menús y configura la portada. WordPress guarda una revisión de cada
 * página antes de cambiarla, así que se puede volver atrás desde el editor
 * («Revisiones»).
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_theme_page( 'Migrar contenido', 'Migrar contenido', 'manage_options', 'ca-migracion', 'ca_migracion_pantalla' );
} );

// Aviso para recordar la migración tras activar el tema.
add_action( 'admin_notices', function () {
	if ( get_option( 'ca_migracion_fecha' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_ca-migracion' === $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p><strong>Tema Carmen Arias:</strong> falta pasar el contenido de las páginas al nuevo diseño. <a href="%s">Ir a Apariencia → Migrar contenido</a>.</p></div>',
		esc_url( admin_url( 'themes.php?page=ca-migracion' ) )
	);
} );

/**
 * Lee las páginas preparadas.
 *
 * @return array
 */
function ca_migracion_datos() {
	$f = CA_DIR . '/inc/migracion/paginas.json';
	if ( ! file_exists( $f ) ) {
		return array();
	}
	$d = json_decode( file_get_contents( $f ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return is_array( $d ) ? $d : array();
}

function ca_migracion_pantalla() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$datos     = ca_migracion_datos();
	$resultado = null;
	if ( isset( $_POST['ca_migrar'] ) && check_admin_referer( 'ca_migrar' ) ) {
		$opciones  = array(
			'paginas' => ! empty( $_POST['ca_paginas'] ),
			'menus'   => ! empty( $_POST['ca_menus'] ),
			'portada' => ! empty( $_POST['ca_portada'] ),
		);
		$resultado = ca_migracion_ejecutar( $datos, $opciones );
	}
	$fecha = get_option( 'ca_migracion_fecha' );
	?>
	<div class="wrap">
		<h1>Migrar contenido al tema Carmen Arias</h1>

		<?php if ( $resultado ) : ?>
			<div class="notice notice-success"><p><strong>Hecho.</strong></p><ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $resultado as $linea ) : ?><li><?php echo wp_kses_post( $linea ); ?></li><?php endforeach; ?>
			</ul><p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">Ver la web</a></p></div>
		<?php elseif ( $fecha ) : ?>
			<div class="notice notice-info"><p>La migración ya se hizo el <?php echo esc_html( $fecha ); ?>. Puedes repetirla: volverá a dejar las páginas como en el paquete del tema (perderás los cambios que hayas hecho en ellas después).</p></div>
		<?php endif; ?>

		<p style="max-width:70ch">Esta herramienta deja la web lista con el nuevo diseño:</p>
		<ol style="max-width:70ch">
			<li><strong>Páginas:</strong> cambia el contenido hecho con Divi de <?php echo count( $datos ); ?> páginas por bloques normales de WordPress (los mismos textos, imágenes, desplegables y galerías), pone títulos legibles y asigna su plantilla. Los centros quedan marcados para la portada.</li>
			<li><strong>Menús:</strong> crea el menú principal y el menú legal del pie y los asigna.</li>
			<li><strong>Portada:</strong> usa la página «Inicio» como portada.</li>
		</ol>
		<p style="max-width:70ch"><strong>Las noticias no se tocan.</strong> Las antiguas hechas con Divi se muestran limpias gracias al tema.</p>
		<p style="max-width:70ch">Antes de cambiar una página, WordPress guarda una revisión: desde el editor de esa página, en «Revisiones», se puede recuperar la versión anterior. Aun así, <strong>haz una copia de seguridad</strong> antes de empezar.</p>

		<form method="post">
			<?php wp_nonce_field( 'ca_migrar' ); ?>
			<p><label><input type="checkbox" name="ca_paginas" value="1" checked> Pasar las páginas al nuevo diseño</label></p>
			<p><label><input type="checkbox" name="ca_menus" value="1" checked> Crear y asignar los menús</label></p>
			<p><label><input type="checkbox" name="ca_portada" value="1" checked> Usar «Inicio» como portada</label></p>
			<p><button class="button button-primary button-hero" name="ca_migrar" value="1">Migrar contenido</button></p>
		</form>

		<h2>Páginas incluidas</h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th>Página</th><th>Dirección</th><th>Plantilla</th><th>Estado</th></tr></thead>
			<tbody>
			<?php foreach ( $datos as $p ) : ?>
				<?php $existe = get_page_by_path( $p['slug'] ); ?>
				<tr>
					<td><?php echo esc_html( $p['titulo'] ); ?></td>
					<td><code>/<?php echo esc_html( $p['slug'] ); ?>/</code></td>
					<td><?php echo esc_html( $p['plantilla'] ? basename( $p['plantilla'], '.php' ) : 'Página' ); ?></td>
					<td><?php echo $existe ? 'Se actualizará' : 'Se creará'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Ejecuta la migración.
 *
 * @param array $datos    Páginas.
 * @param array $opciones Qué partes hacer.
 * @return string[] Resumen.
 */
function ca_migracion_ejecutar( $datos, $opciones ) {
	$log = array();
	@set_time_limit( 300 ); // phpcs:ignore

	if ( $opciones['paginas'] ) {
		$n = 0;
		foreach ( $datos as $p ) {
			$id = ca_migracion_pagina( $p );
			if ( $id ) {
				$n++;
			} else {
				$log[] = 'No se pudo guardar la página ' . esc_html( $p['slug'] ) . '.';
			}
		}
		$log[] = "$n páginas actualizadas con el nuevo diseño.";
	}

	if ( $opciones['portada'] ) {
		$inicio = get_page_by_path( 'inicio' );
		if ( ! $inicio ) {
			$inicio_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Inicio',
				'post_name'   => 'inicio',
			) );
		} else {
			$inicio_id = $inicio->ID;
			// El contenido Divi de la antigua portada ya no se usa.
			update_post_meta( $inicio_id, '_et_pb_use_builder', 'off' );
		}
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $inicio_id );
		// Las noticias se listan con su propia plantilla, no hace falta «página de entradas».
		if ( (int) get_option( 'page_for_posts' ) === (int) ( get_page_by_path( 'noticias' )->ID ?? 0 ) ) {
			update_option( 'page_for_posts', 0 );
		}
		$log[] = 'La página «Inicio» es la portada.';
	}

	if ( $opciones['menus'] ) {
		ca_migracion_menus();
		$log[] = 'Menú principal y menú legal creados y asignados (Apariencia → Menús).';
	}

	update_option( 'ca_migracion_fecha', wp_date( 'j/n/Y H:i' ) );
	return $log;
}

/**
 * Crea o actualiza una página.
 */
function ca_migracion_pagina( $p ) {
	$existe = get_page_by_path( $p['slug'] );
	$post   = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $p['titulo'],
		'post_name'    => $p['slug'],
		'post_content' => $p['contenido'],
		'post_excerpt' => isset( $p['subtitulo'] ) ? $p['subtitulo'] : '',
	);
	if ( isset( $p['centro'] ) ) {
		$post['menu_order'] = (int) $p['centro']['orden'];
	}
	if ( $existe ) {
		$post['ID']          = $existe->ID;
		$post['post_status'] = $existe->post_status;
		$id                  = wp_update_post( wp_slash( $post ), true );
	} else {
		$id = wp_insert_post( wp_slash( $post ), true );
	}
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	update_post_meta( $id, '_wp_page_template', $p['plantilla'] ? $p['plantilla'] : 'default' );
	// Si algún día se reactiva Divi, que use este contenido y no el antiguo.
	update_post_meta( $id, '_et_pb_use_builder', 'off' );
	delete_post_meta( $id, '_et_pb_old_content' );

	if ( isset( $p['centro'] ) ) {
		update_post_meta( $id, CA_META_ES_CENTRO, '1' );
		update_post_meta( $id, CA_META_NOMBRE, $p['centro']['titulo'] );
		update_post_meta( $id, CA_META_ETIQUETA, $p['centro']['etiqueta'] );
		update_post_meta( $id, CA_META_RESUMEN, $p['centro']['resumen'] );
	}

	// Imagen destacada: la primera del contenido si la página no tiene.
	if ( ! has_post_thumbnail( $id ) && preg_match( '/wp-image-(\d+)/', $p['contenido'], $m ) && wp_attachment_is_image( (int) $m[1] ) ) {
		set_post_thumbnail( $id, (int) $m[1] );
	}
	return $id;
}

/**
 * Crea los menús principal y legal.
 */
function ca_migracion_menus() {
	$pid = function ( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? $p->ID : 0;
	};
	$add = function ( $menu_id, $page_id, $padre = 0, $titulo = '', $clases = '' ) {
		if ( ! $page_id ) {
			return 0;
		}
		return wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $padre,
			'menu-item-title'     => $titulo,
			'menu-item-classes'   => $clases,
		) );
	};
	$nuevo = function ( $nombre ) {
		$m = wp_get_nav_menu_object( $nombre );
		if ( $m ) {
			wp_delete_nav_menu( $m->term_id );
		}
		return wp_create_nav_menu( $nombre );
	};

	// Principal.
	$menu = $nuevo( 'Carmen Arias · Principal' );
	$fun  = $add( $menu, $pid( 'mision' ), 0, 'La Fundación' );
	foreach ( array( 'mision' => 'Misión', 'vision' => 'Visión', 'valores' => 'Valores', 'organigrama' => 'Organigrama', 'calidad' => 'Calidad', 'transparencia' => 'Transparencia', 'colabora-con-nosotros' => 'Colabora con nosotros' ) as $s => $t ) {
		$add( $menu, $pid( $s ), $fun, $t );
	}
	$cen = $add( $menu, $pid( 'servicios' ), 0, 'Centros' );
	foreach ( ca_centros_sin_cache() as $c ) {
		$add( $menu, $c->ID, $cen, get_post_meta( $c->ID, CA_META_NOMBRE, true ) );
	}
	$not = $add( $menu, $pid( 'noticias' ), 0, 'Noticias' );
	$add( $menu, $pid( 'noticias' ), $not, 'Noticias' );
	$add( $menu, $pid( 'actividades' ), $not, 'Actividades' );
	$add( $menu, $pid( 'canal-de-informacion-2' ), 0, 'Canal de información' );
	$add( $menu, $pid( 'contacto' ), 0, 'Contacto', 'menu-contacto' );

	// Legal.
	$legal = $nuevo( 'Carmen Arias · Legal' );
	foreach ( array( 'transparencia' => 'Transparencia', 'canal-de-informacion-2' => 'Canal de información', 'aviso-legal' => 'Aviso legal', 'politica-privacidad' => 'Privacidad', 'politica-de-cookies' => 'Cookies' ) as $s => $t ) {
		$add( $legal, $pid( $s ), 0, $t );
	}

	$loc              = get_theme_mod( 'nav_menu_locations', array() );
	$loc['principal'] = $menu;
	$loc['legal']     = $legal;
	set_theme_mod( 'nav_menu_locations', $loc );
}

/**
 * Centros leídos de nuevo (sin la caché estática de ca_centros()).
 */
function ca_centros_sin_cache() {
	return get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 30,
		'meta_key'       => CA_META_ES_CENTRO,
		'meta_value'     => '1',
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
}
