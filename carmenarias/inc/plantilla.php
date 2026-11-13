<?php
/**
 * Funciones de ayuda para las plantillas.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

/**
 * Iconos SVG simples (trazo), heredan el color del texto.
 */
function ca_icono( $nombre, $clase = '' ) {
	$p = array(
		'tel'       => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'pin'       => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'flecha'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'atras'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'abajo'     => '<path d="m6 9 6 6 6-6"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'cerrar'    => '<path d="M6 6l12 12M18 6 6 18"/>',
		'facebook'  => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8a0 0 0 0 1 0 0z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>',
		'texto'     => '<path d="M4 18 9 6l5 12M5.6 14h6.8M16 18l2.5-6 2.5 6M16.8 16h3.4"/>',
		'buscar'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'pdf'       => '<path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6"/>',
	);
	if ( ! isset( $p[ $nombre ] ) ) {
		return '';
	}
	return '<svg class="icono ' . esc_attr( $clase ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $nombre ] . '</svg>';
}

/**
 * Logo: el personalizado si existe, si no el del tema.
 */
function ca_logo() {
	$id  = get_theme_mod( 'custom_logo' );
	$alt = 'Fundación Carmen Arias · nos gusta cuidarte';
	if ( $id ) {
		$img = wp_get_attachment_image( $id, 'medium', false, array( 'alt' => $alt, 'class' => 'logo-img' ) );
	} else {
		$img = sprintf( '<img class="logo-img" src="%s" alt="%s" width="480" height="362">', esc_url( CA_URI . '/assets/img/logo.png' ), esc_attr( $alt ) );
	}
	printf( '<a class="logo" href="%s" rel="home">%s</a>', esc_url( home_url( '/' ) ), $img ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Molino de viento en SVG. $variante: 'escena' (portada) o 'linea' (cabeceras).
 */
function ca_molino( $variante = 'linea' ) {
	if ( 'escena' === $variante ) {
		get_template_part( 'template-parts/molino', 'escena' );
		return;
	}
	?>
	<svg class="molino-linea" viewBox="0 0 120 140" aria-hidden="true" focusable="false">
		<path d="M40 40 L80 40 L90 135 L30 135Z" fill="none" stroke="currentColor" stroke-width="3"/>
		<path d="M34 42 Q60 10 86 42Z" fill="currentColor"/>
		<path d="M54 135 V115 Q60 108 66 115 V135" fill="none" stroke="currentColor" stroke-width="3"/>
		<g class="aspas-linea"><path d="M60 34 V0 M60 34 H94 M60 34 V68 M60 34 H26" stroke="currentColor" stroke-width="3"/></g>
	</svg>
	<?php
}

/**
 * Imagen de una entrada: la destacada o, si no hay, la primera del contenido.
 *
 * @return string HTML de la imagen o cadena vacía.
 */
function ca_imagen_entrada( $post = null, $tam = 'medium_large', $attr = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy', 'alt' => '' ) );
	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail( $post, $tam, $attr );
	}
	$cache = get_post_meta( $post->ID, '_ca_primera_imagen', true );
	if ( '' === $cache ) {
		$cache = 'no';
		if ( preg_match( '/wp-image-(\d+)/', $post->post_content, $m ) && wp_attachment_is_image( (int) $m[1] ) ) {
			$cache = 'id:' . (int) $m[1];
		} elseif ( preg_match( '/gallery_ids=["\x{00BB}\x{201D}]?(\d+)/u', $post->post_content, $m ) && wp_attachment_is_image( (int) $m[1] ) ) {
			$cache = 'id:' . (int) $m[1];
		} elseif ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $post->post_content, $m ) ) {
			$cache = 'url:' . $m[1];
		} elseif ( preg_match( '/\bsrc=["\x{00BB}\x{201D}]?(https?:\/\/[^"\s\x{00AB}\x{201C}\x{2033}\]]+\.(?:jpe?g|png|webp))/iu', $post->post_content, $m ) ) {
			$cache = 'url:' . $m[1];
		}
		update_post_meta( $post->ID, '_ca_primera_imagen', $cache );
	}
	if ( 0 === strpos( $cache, 'id:' ) ) {
		return wp_get_attachment_image( (int) substr( $cache, 3 ), $tam, false, $attr );
	}
	if ( 0 === strpos( $cache, 'url:' ) ) {
		$url = substr( $cache, 4 );
		$id  = attachment_url_to_postid( preg_replace( '/-\d+x\d+(?=\.\w+$)/', '', $url ) );
		if ( $id ) {
			update_post_meta( $post->ID, '_ca_primera_imagen', 'id:' . $id );
			return wp_get_attachment_image( $id, $tam, false, $attr );
		}
		return sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( $url ), esc_attr( $attr['alt'] ) );
	}
	return '';
}

// Al guardar una entrada se vuelve a buscar su primera imagen.
add_action( 'save_post', function ( $id ) {
	delete_post_meta( $id, '_ca_primera_imagen' );
} );

/**
 * Tarjeta de noticia.
 */
function ca_tarjeta( $post = null ) {
	$post = get_post( $post );
	$img  = ca_imagen_entrada( $post, 'medium_large', array( 'alt' => '', 'sizes' => '(max-width: 620px) 100vw, (max-width: 960px) 50vw, 380px' ) );
	$cat  = get_the_category( $post->ID );
	?>
	<a class="tarjeta reveal" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<div class="tarjeta-img<?php echo $img ? '' : ' sin-imagen'; ?>">
			<?php if ( $cat ) : ?><span class="etiqueta"><?php echo esc_html( ca_nombre_categoria( $cat[0] ) ); ?></span><?php endif; ?>
			<?php
			if ( $img ) {
				echo $img; // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				ca_molino();
			}
			?>
		</div>
		<time datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>"><?php echo esc_html( get_the_date( 'j \d\e F \d\e Y', $post ) ); ?></time>
		<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
	</a>
	<?php
}

/**
 * Las categorías antiguas estaban en MAYÚSCULAS.
 */
function ca_nombre_categoria( $cat ) {
	$n = $cat->name;
	if ( function_exists( 'mb_strtoupper' ) && mb_strtoupper( $n ) === $n ) {
		$n = mb_convert_case( mb_strtolower( $n ), MB_CASE_TITLE );
	}
	return $n;
}

/**
 * Migas de pan sencillas.
 */
function ca_migas() {
	$items = array( '<a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a>' );
	if ( is_singular( 'post' ) ) {
		$cat = get_the_category();
		if ( $cat ) {
			$pag = get_page_by_path( $cat[0]->slug );
			$url = $pag ? get_permalink( $pag ) : get_category_link( $cat[0] );
			$items[] = '<a href="' . esc_url( $url ) . '">' . esc_html( ca_nombre_categoria( $cat[0] ) ) . '</a>';
		}
	} elseif ( is_page() ) {
		if ( get_post_meta( get_the_ID(), CA_META_ES_CENTRO, true ) ) {
			$serv = get_page_by_path( 'servicios' );
			if ( $serv ) {
				$items[] = '<a href="' . esc_url( get_permalink( $serv ) ) . '">Centros</a>';
			}
		}
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $a ) {
			$items[] = '<a href="' . esc_url( get_permalink( $a ) ) . '">' . esc_html( get_the_title( $a ) ) . '</a>';
		}
	}
	echo '<nav class="migas" aria-label="Estás aquí">' . implode( '<span aria-hidden="true">/</span>', $items ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Cabecera de página interior.
 *
 * @param array $args titulo, subtitulo, antetitulo, etiqueta.
 */
function ca_cabecera( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'titulo'     => '',
		'subtitulo'  => '',
		'antetitulo' => '',
		'etiqueta'   => '',
		'migas'      => true,
	) );
	get_template_part( 'template-parts/cabecera', null, $args );
}

/**
 * Enlaces a redes sociales.
 */
function ca_redes() {
	$redes = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
	);
	echo '<ul class="redes">';
	foreach ( $redes as $k => $n ) {
		$url = ca_opt( $k );
		if ( $url ) {
			printf( '<li><a href="%s" target="_blank" rel="noopener">%s<span class="screen-reader-text">%s</span></a></li>', esc_url( $url ), ca_icono( $k ), esc_html( $n ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
	echo '</ul>';
}

/**
 * Menú principal de reserva (si no se ha asignado ninguno en Apariencia → Menús).
 */
function ca_menu_reserva() {
	$url = function ( $slug ) {
		$p = get_page_by_path( $slug );
		return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
	};
	$ss = $url( 'sector-solidario' );
	echo '<ul class="menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a></li>';
	echo '<li class="menu-item-has-children"><a href="' . esc_url( $ss ) . '">Sector solidario</a><ul class="sub-menu">';
	foreach ( array( 'mision' => 'Misión', 'vision' => 'Visión', 'valores' => 'Valores' ) as $a => $t ) {
		echo '<li><a href="' . esc_url( $ss . '#' . $a ) . '">' . esc_html( $t ) . '</a></li>';
	}
	echo '</ul></li>';
	$fundacion = array(
		'organigrama'           => 'Organigrama',
		'calidad'               => 'Calidad',
		'transparencia'         => 'Transparencia',
		'colabora-con-nosotros' => 'Colabora con nosotros',
	);
	echo '<li class="menu-item-has-children"><a href="' . esc_url( $url( 'organigrama' ) ) . '">La Fundación</a><ul class="sub-menu">';
	foreach ( $fundacion as $s => $t ) {
		echo '<li><a href="' . esc_url( $url( $s ) ) . '">' . esc_html( $t ) . '</a></li>';
	}
	echo '</ul></li>';
	echo '<li class="menu-item-has-children"><a href="' . esc_url( $url( 'servicios' ) ) . '">Centros</a><ul class="sub-menu">';
	foreach ( ca_centros() as $c ) {
		echo '<li><a href="' . esc_url( get_permalink( $c ) ) . '">' . esc_html( ca_centro_nombre( $c ) ) . '</a></li>';
	}
	echo '</ul></li>';
	echo '<li class="menu-item-has-children"><a href="' . esc_url( $url( 'noticias' ) ) . '">Noticias</a><ul class="sub-menu">';
	echo '<li><a href="' . esc_url( $url( 'noticias' ) ) . '">Noticias</a></li>';
	echo '<li><a href="' . esc_url( $url( 'actividades' ) ) . '">Actividades</a></li>';
	echo '</ul></li>';
	echo '<li><a href="' . esc_url( $url( 'canal-de-informacion-2' ) ) . '">Canal de información</a></li>';
	echo '<li class="menu-contacto"><a href="' . esc_url( $url( 'contacto' ) ) . '">Contacto</a></li>';
	echo '</ul>';
}

/**
 * Menú legal de reserva.
 */
function ca_menu_legal_reserva() {
	$enlaces = array(
		'transparencia'          => 'Transparencia',
		'canal-de-informacion-2' => 'Canal de información',
		'aviso-legal'            => 'Aviso legal',
		'politica-privacidad'    => 'Privacidad',
		'politica-de-cookies'    => 'Cookies',
	);
	echo '<ul class="menu">';
	foreach ( $enlaces as $s => $t ) {
		$p = get_page_by_path( $s );
		if ( $p ) {
			echo '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $t ) . '</a></li>';
		}
	}
	echo '</ul>';
}

/**
 * Añade una flecha a los elementos con submenú (para abrirlos en móvil).
 */
add_filter( 'walker_nav_menu_start_el', function ( $html, $item, $depth, $args ) {
	if ( isset( $args->theme_location ) && 'principal' === $args->theme_location && in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		$html .= '<button class="submenu-btn" aria-expanded="false" aria-label="Abrir submenú de ' . esc_attr( $item->title ) . '">' . ca_icono( 'abajo' ) . '</button>';
	}
	return $html;
}, 10, 4 );

/**
 * Paginación accesible.
 */
function ca_paginacion( $query = null ) {
	global $wp_query;
	$q     = $query ? $query : $wp_query;
	$links = paginate_links( array(
		'total'     => $q->max_num_pages,
		'current'   => max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ),
		'mid_size'  => 1,
		'prev_text' => ca_icono( 'atras' ) . '<span class="screen-reader-text">Anterior</span>',
		'next_text' => '<span class="screen-reader-text">Siguiente</span>' . ca_icono( 'flecha' ),
		'type'      => 'list',
	) );
	if ( $links ) {
		echo '<nav class="paginacion" aria-label="Páginas">' . $links . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Bloque "¿Hablamos?" al final de las páginas.
 */
function ca_llamada() {
	$contacto = get_page_by_path( 'contacto' );
	?>
	<section class="llamada">
		<div class="wrap llamada-grid">
			<div>
				<p class="antetitulo">¿Necesitas información?</p>
				<h2>Te atendemos con calma, por teléfono o en persona.</h2>
			</div>
			<div class="llamada-acciones">
				<a class="llamada-tel" href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo ca_icono( 'tel' ); // phpcs:ignore ?><span><?php echo esc_html( ca_opt( 'telefono' ) ); ?></span></a>
				<?php if ( $contacto ) : ?>
					<a class="btn btn-oro" href="<?php echo esc_url( get_permalink( $contacto ) ); ?>">Escríbenos <?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Redirige las páginas antiguas (Misión, Visión, Valores…) a su nuevo sitio.
 * El destino se guarda en el campo personalizado «_ca_redirige» de la página.
 */
add_action( 'template_redirect', function () {
	if ( ! is_page() ) {
		return;
	}
	$destino = get_post_meta( get_queried_object_id(), '_ca_redirige', true );
	if ( ! $destino ) {
		return;
	}
	$partes = explode( '#', $destino, 2 );
	$pagina = get_page_by_path( $partes[0] );
	if ( ! $pagina || (int) $pagina->ID === get_queried_object_id() ) {
		return;
	}
	$url = get_permalink( $pagina ) . ( isset( $partes[1] ) ? '#' . $partes[1] : '' );
	wp_safe_redirect( $url, 301 );
	exit;
} );
