<?php
/**
 * Compatibilidad con el contenido antiguo creado con Divi.
 *
 * Cerca de 540 noticias se escribieron con el constructor Divi. Sin Divi
 * activo, su contenido mostraría códigos como [et_pb_section ...]. Aquí se
 * interpretan los módulos que se usaron (texto, imagen, galería, vídeo,
 * código, desplegable, botón) y se eliminan el resto de etiquetas.
 * No se modifica nada en la base de datos.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	// Si el constructor Divi está activo (plugin), no hacemos nada.
	if ( shortcode_exists( 'et_pb_section' ) ) {
		return;
	}

	$contenedores = array(
		'et_pb_section', 'et_pb_row', 'et_pb_row_inner', 'et_pb_column', 'et_pb_column_inner',
		'et_pb_text', 'et_pb_fullwidth_section', 'et_pb_specialty_section',
	);
	foreach ( $contenedores as $tag ) {
		add_shortcode( $tag, function ( $atts, $content = '' ) {
			return do_shortcode( (string) $content );
		} );
	}

	add_shortcode( 'et_pb_image', function ( $atts ) {
		$atts = (array) $atts;
		$src  = isset( $atts['src'] ) ? ca_divi_url( $atts['src'] ) : '';
		if ( ! $src ) {
			return '';
		}
		$id  = attachment_url_to_postid( $src );
		$alt = isset( $atts['alt'] ) ? $atts['alt'] : '';
		$img = $id ? wp_get_attachment_image( $id, 'large', false, array( 'alt' => $alt ) ) : sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( $src ), esc_attr( $alt ) );
		return '<figure class="wp-block-image size-large">' . $img . '</figure>';
	} );

	add_shortcode( 'et_pb_gallery', function ( $atts ) {
		$atts = (array) $atts;
		$ids  = isset( $atts['gallery_ids'] ) ? preg_replace( '/[^0-9,]/', '', $atts['gallery_ids'] ) : '';
		if ( ! $ids ) {
			return '';
		}
		return gallery_shortcode( array(
			'ids'     => $ids,
			'size'    => 'medium_large',
			'link'    => 'file',
			'columns' => 3,
		) );
	} );

	add_shortcode( 'et_pb_video', function ( $atts ) {
		$atts = (array) $atts;
		$src  = isset( $atts['src'] ) ? ca_divi_url( $atts['src'] ) : '';
		if ( ! $src ) {
			return '';
		}
		$embed = wp_oembed_get( $src );
		return $embed ? '<figure class="wp-block-embed">' . $embed . '</figure>' : wp_video_shortcode( array( 'src' => $src ) );
	} );

	foreach ( array( 'et_pb_code', 'et_pb_fullwidth_code' ) as $tag ) {
		add_shortcode( $tag, function ( $atts, $content = '' ) {
			return wp_kses_post( html_entity_decode( (string) $content ) );
		} );
	}

	add_shortcode( 'et_pb_toggle', function ( $atts, $content = '' ) {
		$atts  = (array) $atts;
		$title = isset( $atts['title'] ) ? $atts['title'] : 'Más información';
		return '<details class="wp-block-details"><summary>' . esc_html( $title ) . '</summary>' . do_shortcode( (string) $content ) . '</details>';
	} );

	add_shortcode( 'et_pb_button', function ( $atts ) {
		$atts = (array) $atts;
		if ( empty( $atts['button_url'] ) ) {
			return '';
		}
		$txt = isset( $atts['button_text'] ) ? $atts['button_text'] : 'Ver más';
		return '<div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="' . esc_url( ca_divi_url( $atts['button_url'] ) ) . '">' . esc_html( $txt ) . '</a></div></div>';
	} );

	// Módulos que no aportan nada fuera de Divi.
	foreach ( array( 'et_pb_post_title', 'et_pb_divider', 'et_pb_social_media_follow', 'et_pb_social_media_follow_network', 'et_pb_fullwidth_header' ) as $tag ) {
		add_shortcode( $tag, '__return_empty_string' );
	}
} );

/**
 * Corrige URLs del dominio de pruebas antiguo.
 */
function ca_divi_url( $url ) {
	return str_replace( array( 'carmenarias.artconsulting.es', 'http://carmenarias.es' ), array( 'carmenarias.es', 'https://carmenarias.es' ), (string) $url );
}

/**
 * Limpia cualquier etiqueta Divi que haya quedado y los párrafos vacíos.
 */
add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, 'et_pb_' ) ) {
		return $content;
	}
	$content = preg_replace( '/\[\/?et_pb_[^\]]*\]/u', '', $content );
	$content = preg_replace( '#<p>\s*(?:&nbsp;|\x{00A0})?\s*</p>#u', '', $content );
	return $content;
}, 12 );

/**
 * Los extractos también se generan sin códigos Divi.
 */
add_filter( 'get_the_excerpt', function ( $excerpt, $post ) {
	if ( '' === trim( $post->post_excerpt ) && false !== strpos( $post->post_content, 'et_pb_' ) ) {
		$texto   = preg_replace( '/\[\/?et_pb_[^\]]*\]/u', ' ', $post->post_content );
		$texto   = wp_strip_all_tags( strip_shortcodes( $texto ) );
		$excerpt = wp_trim_words( $texto, apply_filters( 'excerpt_length', 26 ), '…' );
	}
	return $excerpt;
}, 5, 2 );

/**
 * Quita el HTML basura copiado de Facebook (atributos dir, clases x1…, ids :r2mv:).
 */
add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, 'dir="auto"' ) && false === strpos( $content, 'class="x' ) ) {
		return $content;
	}
	$content = preg_replace( '/\s(?:dir="auto"|class="(?:x[0-9a-z]+\s*)+"|class=""|id=":[^"]*"|data-ad-[a-z-]+="[^"]*")/', '', $content );
	return $content;
}, 13 );
