<?php
/**
 * Template Name: Página con secciones
 *
 * Página ancha para contenido con tarjetas, documentos y fotos
 * (Sector solidario, Calidad, Transparencia, Organigrama, Canal de información).
 * Las clases que dan forma a cada bloque (ca-tarjetas, ca-docs, ca-aviso…)
 * se ponen en «Avanzado → Clases CSS adicionales» del bloque Grupo.
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
	<article <?php post_class( 'pagina pagina-secciones' ); ?>>
		<div class="wrap entrada-contenido contenido-ancho">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

ca_llamada();
get_footer();
