<?php
/**
 * Pie.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;
$contacto = get_page_by_path( 'contacto' );
?>
</main>

<footer class="pie">
	<div class="pie-horizonte" aria-hidden="true">
		<svg viewBox="0 0 1200 80" preserveAspectRatio="none"><path d="M0 60 Q200 30 420 48 T860 40 T1200 50 V80 H0Z"/></svg>
		<div class="pie-molino"><?php ca_molino(); ?></div>
	</div>
	<div class="pie-in">
		<div class="wrap pie-grid">
			<div class="pie-marca">
				<?php ca_logo(); ?>
				<p><?php echo wp_kses_post( ca_opt( 'pie_texto' ) ); ?></p>
				<?php ca_redes(); ?>
			</div>
			<div>
				<h2 class="pie-titulo">Dónde estamos</h2>
				<address>
					<?php echo nl2br( esc_html( ca_opt( 'direccion' ) ) ); ?><br>
					<a href="<?php echo esc_url( ca_opt( 'mapa' ) ); ?>" target="_blank" rel="noopener">Cómo llegar</a>
				</address>
			</div>
			<div>
				<h2 class="pie-titulo">Contacto</h2>
				<ul class="pie-lista">
					<li><a href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo ca_icono( 'tel' ); // phpcs:ignore ?> <?php echo esc_html( ca_opt( 'telefono' ) ); ?></a></li>
					<?php if ( ca_opt( 'fax' ) ) : ?><li>Fax <?php echo esc_html( ca_opt( 'fax' ) ); ?></li><?php endif; ?>
					<li><a href="mailto:<?php echo esc_attr( ca_opt( 'email' ) ); ?>"><?php echo ca_icono( 'mail' ); // phpcs:ignore ?> <?php echo esc_html( ca_opt( 'email' ) ); ?></a></li>
					<?php if ( $contacto ) : ?><li><a href="<?php echo esc_url( get_permalink( $contacto ) ); ?>">Formulario de contacto</a></li><?php endif; ?>
				</ul>
			</div>
			<div>
				<h2 class="pie-titulo">Centros</h2>
				<ul class="pie-lista">
					<?php foreach ( ca_centros() as $c ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><?php echo esc_html( ca_centro_nombre( $c ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="wrap pie-legal">
			<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fundación Carmen Arias</span>
			<nav aria-label="Información legal">
				<?php
				if ( has_nav_menu( 'legal' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'legal',
						'container'      => false,
						'depth'          => 1,
					) );
				} else {
					ca_menu_legal_reserva();
				}
				?>
			</nav>
		</div>
	</div>
</footer>

<div class="visor" hidden role="dialog" aria-modal="true" aria-label="Imagen ampliada">
	<button class="visor-cerrar" type="button" aria-label="Cerrar"><?php echo ca_icono( 'cerrar' ); // phpcs:ignore ?></button>
	<button class="visor-ant" type="button" aria-label="Anterior"><?php echo ca_icono( 'atras' ); // phpcs:ignore ?></button>
	<img alt="">
	<button class="visor-sig" type="button" aria-label="Siguiente"><?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></button>
</div>

<?php wp_footer(); ?>
</body>
</html>
