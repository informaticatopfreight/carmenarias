<?php
/**
 * Template Name: Centro de la Fundación
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$id = get_the_ID();
	ca_cabecera( array(
		'titulo'     => get_the_title(),
		'antetitulo' => 'Nuestros centros',
		'subtitulo'  => get_post_meta( $id, CA_META_RESUMEN, true ),
		'etiqueta'   => get_post_meta( $id, CA_META_ETIQUETA, true ),
	) );
	?>
	<article <?php post_class( 'pagina pagina-centro' ); ?>>
		<div class="wrap centro-layout">
			<div class="contenido entrada-contenido">
				<?php the_content(); ?>
			</div>
			<aside class="centro-lateral" aria-label="Más información">
				<div class="lateral-caja">
					<p class="antetitulo">¿Hablamos?</p>
					<p>Te explicamos cómo acceder a <?php echo esc_html( ca_centro_nombre( get_post() ) ); ?> y resolvemos tus dudas.</p>
					<a class="lateral-tel" href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo ca_icono( 'tel' ); // phpcs:ignore ?><?php echo esc_html( ca_opt( 'telefono' ) ); ?></a>
					<?php $contacto = get_page_by_path( 'contacto' ); ?>
					<?php if ( $contacto ) : ?>
						<a class="btn" href="<?php echo esc_url( get_permalink( $contacto ) ); ?>">Escríbenos</a>
					<?php endif; ?>
				</div>
				<nav class="lateral-centros" aria-label="Otros centros">
					<p class="antetitulo">Otros centros</p>
					<ul>
						<?php foreach ( ca_centros() as $c ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $c ) ); ?>" <?php echo $c->ID === $id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( ca_centro_nombre( $c ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</aside>
		</div>
	</article>
	<?php
endwhile;

ca_llamada();
get_footer();
