<?php
/**
 * Template Name: Contacto
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	ca_cabecera( array(
		'titulo'    => get_the_title(),
		'subtitulo' => has_excerpt() ? get_the_excerpt() : 'Llámanos, escríbenos o ven a vernos a Socuéllamos. Te atendemos con calma.',
	) );
	?>
	<section class="contacto-pagina">
		<div class="wrap contacto-grid">
			<div class="contacto-datos">
				<div class="dato reveal">
					<?php echo ca_icono( 'pin' ); // phpcs:ignore ?>
					<div>
						<h2>Dónde estamos</h2>
						<p><?php echo nl2br( esc_html( ca_opt( 'direccion' ) ) ); ?></p>
						<p><a href="<?php echo esc_url( ca_opt( 'mapa' ) ); ?>" target="_blank" rel="noopener">Ver en Google Maps</a></p>
					</div>
				</div>
				<div class="dato reveal">
					<?php echo ca_icono( 'tel' ); // phpcs:ignore ?>
					<div>
						<h2>Teléfono</h2>
						<p class="dato-grande"><a href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo esc_html( ca_opt( 'telefono' ) ); ?></a></p>
						<?php if ( ca_opt( 'fax' ) ) : ?><p>Fax: <?php echo esc_html( ca_opt( 'fax' ) ); ?></p><?php endif; ?>
					</div>
				</div>
				<div class="dato reveal">
					<?php echo ca_icono( 'mail' ); // phpcs:ignore ?>
					<div>
						<h2>Correo electrónico</h2>
						<p><a href="mailto:<?php echo esc_attr( ca_opt( 'email' ) ); ?>"><?php echo esc_html( ca_opt( 'email' ) ); ?></a></p>
					</div>
				</div>
				<div class="dato reveal">
					<div class="dato-redes">
						<h2>Síguenos</h2>
						<?php ca_redes(); ?>
					</div>
				</div>
			</div>
			<div class="contacto-form reveal">
				<h2>Escríbenos</h2>
				<?php
				if ( trim( get_the_content() ) ) {
					echo '<div class="entrada-contenido">';
					the_content();
					echo '</div>';
				}
				if ( ! has_shortcode( get_the_content(), 'carmenarias_contacto' ) ) {
					echo do_shortcode( '[carmenarias_contacto]' );
				}
				?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
