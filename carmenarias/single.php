<?php
/**
 * Noticia.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cats = get_the_category();
	?>
	<article <?php post_class( 'noticia' ); ?>>
		<header class="cabecera-pagina cabecera-noticia">
			<div class="wrap cabecera-pagina-in">
				<div class="cabecera-texto">
					<?php ca_migas(); ?>
					<p class="antetitulo">
						<?php echo $cats ? esc_html( ca_nombre_categoria( $cats[0] ) ) . ' · ' : ''; ?>
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j \d\e F \d\e Y' ) ); ?></time>
					</p>
					<h1 class="titulo-animado"><?php the_title(); ?></h1>
				</div>
				<div class="cabecera-molino" aria-hidden="true"><?php ca_molino(); ?></div>
			</div>
			<svg class="cabecera-horizonte" viewBox="0 0 1200 40" preserveAspectRatio="none" aria-hidden="true"><path d="M0 30 Q300 6 600 22 T1200 18 V40 H0Z"/></svg>
		</header>

		<div class="wrap contenido entrada-contenido">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="noticia-imagen"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?></figure>
			<?php endif; ?>
			<?php the_content(); ?>
		</div>

		<nav class="wrap contenido noticia-nav" aria-label="Más noticias">
			<?php
			$ant = get_previous_post();
			$sig = get_next_post();
			if ( $ant ) {
				printf( '<a class="nav-ant" href="%s"><span>%s Anterior</span><b>%s</b></a>', esc_url( get_permalink( $ant ) ), ca_icono( 'atras' ), esc_html( get_the_title( $ant ) ) ); // phpcs:ignore
			}
			if ( $sig ) {
				printf( '<a class="nav-sig" href="%s"><span>Siguiente %s</span><b>%s</b></a>', esc_url( get_permalink( $sig ) ), ca_icono( 'flecha' ), esc_html( get_the_title( $sig ) ) ); // phpcs:ignore
			}
			?>
		</nav>
	</article>

	<?php
	$rel = new WP_Query( array(
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'category__in'        => wp_list_pluck( $cats, 'term_id' ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	if ( $rel->have_posts() ) :
		?>
		<section class="noticias noticias-rel">
			<div class="wrap">
				<div class="noticias-cab"><h2>Más noticias</h2></div>
				<div class="rejilla">
					<?php
					while ( $rel->have_posts() ) {
						$rel->the_post();
						ca_tarjeta();
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
