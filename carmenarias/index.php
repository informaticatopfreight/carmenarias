<?php
/**
 * Listados: blog, categorías, etiquetas, fechas y búsqueda.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	$titulo = 'Resultados para «' . get_search_query() . '»';
	$sub    = '';
} elseif ( is_category() ) {
	$titulo = ca_nombre_categoria( get_queried_object() );
	$sub    = wp_strip_all_tags( category_description() );
} elseif ( is_archive() ) {
	$titulo = wp_strip_all_tags( get_the_archive_title() );
	$sub    = '';
} else {
	$titulo = 'Noticias';
	$sub    = 'El día a día de la Fundación: celebraciones, talleres, salidas y novedades.';
}

ca_cabecera( array(
	'titulo'    => $titulo,
	'subtitulo' => $sub,
) );
?>
<section class="listado">
	<div class="wrap">
		<?php if ( is_search() ) : ?>
			<div class="buscador-listado"><?php get_search_form(); ?></div>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<div class="rejilla">
				<?php
				while ( have_posts() ) {
					the_post();
					ca_tarjeta();
				}
				?>
			</div>
			<?php ca_paginacion(); ?>
		<?php else : ?>
			<p class="vacio">No hemos encontrado nada. Prueba con otras palabras o vuelve al <a href="<?php echo esc_url( home_url( '/' ) ); ?>">inicio</a>.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
