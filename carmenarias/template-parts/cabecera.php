<?php
/**
 * Cabecera de las páginas interiores.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

$a = wp_parse_args( $args ?? array(), array(
	'titulo'     => '',
	'subtitulo'  => '',
	'antetitulo' => '',
	'etiqueta'   => '',
	'migas'      => true,
) );
?>
<header class="cabecera-pagina">
	<div class="wrap cabecera-pagina-in">
		<div class="cabecera-texto">
			<?php
			if ( $a['migas'] ) {
				ca_migas();
			}
			?>
			<?php if ( $a['antetitulo'] ) : ?><p class="antetitulo"><?php echo esc_html( $a['antetitulo'] ); ?></p><?php endif; ?>
			<h1 class="titulo-animado"><?php echo esc_html( $a['titulo'] ); ?></h1>
			<?php if ( $a['subtitulo'] ) : ?><p class="cabecera-sub"><?php echo esc_html( $a['subtitulo'] ); ?></p><?php endif; ?>
			<?php if ( $a['etiqueta'] ) : ?><p class="pastilla"><?php echo esc_html( $a['etiqueta'] ); ?></p><?php endif; ?>
		</div>
		<div class="cabecera-molino" aria-hidden="true"><?php ca_molino(); ?></div>
	</div>
	<svg class="cabecera-horizonte" viewBox="0 0 1200 40" preserveAspectRatio="none" aria-hidden="true"><path d="M0 30 Q300 6 600 22 T1200 18 V40 H0Z"/></svg>
</header>
