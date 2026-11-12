<?php
/**
 * Lista de centros (portada y página «Nuestros centros»).
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

$excluir = isset( $args['excluir'] ) ? (int) $args['excluir'] : 0;
$centros = array_filter( ca_centros(), function ( $c ) use ( $excluir ) {
	return $c->ID !== $excluir;
} );
if ( ! $centros ) {
	return;
}
?>
<ul class="lista-centros">
	<?php foreach ( $centros as $c ) : ?>
		<li class="centro reveal">
			<a href="<?php echo esc_url( get_permalink( $c ) ); ?>">
				<h3><?php echo esc_html( ca_centro_nombre( $c ) ); ?></h3>
				<p><?php echo esc_html( get_post_meta( $c->ID, CA_META_RESUMEN, true ) ); ?></p>
				<span class="centro-pie">
					<?php if ( get_post_meta( $c->ID, CA_META_ETIQUETA, true ) ) : ?>
						<span class="pastilla"><?php echo esc_html( get_post_meta( $c->ID, CA_META_ETIQUETA, true ) ); ?></span>
					<?php endif; ?>
					<span class="centro-flecha"><?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></span>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
