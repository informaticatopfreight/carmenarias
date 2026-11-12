<?php
/**
 * Template Name: Listado de noticias
 *
 * Muestra las entradas de la categoría que tiene el mismo nombre (slug) que la
 * página: la página «Noticias» lista la categoría NOTICIAS y «Actividades»
 * la categoría ACTIVIDADES. Si no existe esa categoría, muestra todas.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

the_post();
$slug  = get_post_field( 'post_name', get_the_ID() );
$cat   = get_category_by_slug( $slug );
$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$q = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'cat'                 => $cat ? $cat->term_id : 0,
	'ignore_sticky_posts' => true,
) );

ca_cabecera( array(
	'titulo'    => get_the_title(),
	'subtitulo' => has_excerpt() ? get_the_excerpt() : 'El día a día de la Fundación: celebraciones, talleres, salidas y novedades.',
) );

$otras = get_categories( array( 'hide_empty' => true ) );
?>
<section class="listado">
	<div class="wrap">
		<?php if ( count( $otras ) > 1 ) : ?>
			<nav class="filtros" aria-label="Filtrar por tipo">
				<?php
				foreach ( $otras as $o ) {
					$pag = get_page_by_path( $o->slug );
					$url = $pag ? get_permalink( $pag ) : get_category_link( $o );
					printf(
						'<a href="%s" %s>%s <span>%s</span></a>',
						esc_url( $url ),
						( $cat && $cat->term_id === $o->term_id ) ? 'aria-current="page"' : '',
						esc_html( ca_nombre_categoria( $o ) ),
						esc_html( number_format_i18n( $o->count ) )
					);
				}
				?>
			</nav>
		<?php endif; ?>

		<?php if ( trim( get_the_content() ) && 1 === $paged ) : ?>
			<div class="contenido entrada-contenido"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( $q->have_posts() ) : ?>
			<div class="rejilla">
				<?php
				while ( $q->have_posts() ) {
					$q->the_post();
					ca_tarjeta();
				}
				wp_reset_postdata();
				?>
			</div>
			<?php ca_paginacion( $q ); ?>
		<?php else : ?>
			<p class="vacio">Todavía no hay publicaciones aquí.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
