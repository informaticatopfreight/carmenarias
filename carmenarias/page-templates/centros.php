<?php
/**
 * Template Name: Lista de centros
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	ca_cabecera( array(
		'titulo'    => get_the_title(),
		'subtitulo' => has_excerpt() ? get_the_excerpt() : 'Cada persona necesita un apoyo distinto. Por eso la Fundación se organiza en centros y servicios especializados.',
	) );
	?>
	<section class="centros centros-pagina">
		<div class="wrap">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="contenido entrada-contenido"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/lista-centros' ); ?>
		</div>
	</section>
	<?php
endwhile;

ca_llamada();
get_footer();
