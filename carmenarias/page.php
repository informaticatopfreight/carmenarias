<?php
/**
 * Página genérica.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	ca_cabecera( array(
		'titulo'    => get_the_title(),
		'subtitulo' => has_excerpt() ? get_the_excerpt() : '',
	) );
	?>
	<article <?php post_class( 'pagina' ); ?>>
		<div class="wrap contenido entrada-contenido">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</article>
	<?php
endwhile;

ca_llamada();
get_footer();
