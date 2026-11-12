<?php
/**
 * Configuración básica del tema: soportes, menús, estilos y scripts.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'carmenarias', CA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 160,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_editor_style( array( 'assets/css/fuentes.css', 'assets/css/editor.css' ) );

	// Paleta del tema en el editor de bloques.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => 'Cal', 'slug' => 'cal', 'color' => '#FBF9F3' ),
		array( 'name' => 'Cal oscura', 'slug' => 'cal-2', 'color' => '#F2EEE3' ),
		array( 'name' => 'Oro', 'slug' => 'oro', 'color' => '#C99A3E' ),
		array( 'name' => 'Añil', 'slug' => 'anil', 'color' => '#2A3A93' ),
		array( 'name' => 'Carbón', 'slug' => 'carbon', 'color' => '#262826' ),
	) );
	add_theme_support( 'disable-custom-colors' );

	register_nav_menus( array(
		'principal' => __( 'Menú principal', 'carmenarias' ),
		'legal'     => __( 'Enlaces legales (pie)', 'carmenarias' ),
	) );

	add_image_size( 'ca-tarjeta', 800, 600, true );
	add_image_size( 'ca-cabecera', 1600, 900, true );
} );

add_filter( 'image_size_names_choose', function ( $sizes ) {
	return $sizes + array( 'ca-tarjeta' => 'Tarjeta 4:3' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$v = CA_VERSION . '.' . filemtime( CA_DIR . '/assets/css/main.css' );
	wp_enqueue_style( 'ca-fuentes', CA_URI . '/assets/css/fuentes.css', array(), CA_VERSION );
	wp_enqueue_style( 'ca-main', CA_URI . '/assets/css/main.css', array( 'ca-fuentes' ), $v );
	wp_enqueue_script( 'ca-main', CA_URI . '/assets/js/main.js', array(), CA_VERSION . '.' . filemtime( CA_DIR . '/assets/js/main.js' ), array(
		'in_footer' => true,
		'strategy'  => 'defer',
	) );
} );

// Precarga de las fuentes principales.
add_action( 'wp_head', function () {
	foreach ( array( 'bricolage-grotesque-latin-opsz-normal.woff2', 'atkinson-hyperlegible-latin-400-normal.woff2' ) as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( CA_URI . '/assets/fonts/' . $f ) );
	}
	echo '<meta name="theme-color" content="#FBF9F3">' . "\n";
}, 1 );

// Marca para que el CSS sepa que hay JavaScript (animaciones de aparición).
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js');try{if(localStorage.getItem('ca-texto')==='grande')document.documentElement.classList.add('texto-grande')}catch(e){}</script>\n";
}, 0 );

// Extractos más cortos y sin "[...]".
add_filter( 'excerpt_length', function () {
	return 26;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

// Quita los emojis de WordPress (peso innecesario).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Contenido de ancho máximo para incrustados.
if ( ! isset( $content_width ) ) {
	$content_width = 760;
}

// Las galerías clásicas (shortcode [gallery]) enlazan a la imagen grande para el visor.
add_filter( 'shortcode_atts_gallery', function ( $out ) {
	if ( empty( $out['link'] ) ) {
		$out['link'] = 'file';
	}
	$out['size'] = 'medium_large' === $out['size'] || 'thumbnail' === $out['size'] ? 'medium_large' : $out['size'];
	return $out;
} );
add_filter( 'use_default_gallery_style', '__return_false' );
