<?php
/**
 * Buscador.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;
$uid = wp_unique_id( 'buscar-' );
?>
<form role="search" method="get" class="buscador" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>">Buscar</label>
	<input type="search" id="<?php echo esc_attr( $uid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Buscar en la web…">
	<button type="submit"><?php echo ca_icono( 'buscar' ); // phpcs:ignore ?><span class="screen-reader-text">Buscar</span></button>
</form>
