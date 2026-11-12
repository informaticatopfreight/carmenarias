<?php
/**
 * Centros: páginas marcadas como centro, con su etiqueta (plazas) y resumen.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

const CA_META_ES_CENTRO = '_ca_centro';
const CA_META_ETIQUETA  = '_ca_centro_etiqueta';
const CA_META_RESUMEN   = '_ca_centro_resumen';
const CA_META_NOMBRE    = '_ca_centro_nombre';

/**
 * Devuelve las páginas que son centros, ordenadas.
 *
 * @return WP_Post[]
 */
function ca_centros() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 30,
		'meta_key'       => CA_META_ES_CENTRO,
		'meta_value'     => '1',
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	return $cache;
}

/**
 * Nombre corto del centro (para listados) o el título de la página.
 */
function ca_centro_nombre( $post ) {
	$n = get_post_meta( $post->ID, CA_META_NOMBRE, true );
	return $n ? $n : get_the_title( $post );
}

add_action( 'add_meta_boxes_page', function () {
	add_meta_box( 'ca_centro', 'Centro de la Fundación', 'ca_centro_metabox', 'page', 'side', 'high' );
} );

function ca_centro_metabox( $post ) {
	wp_nonce_field( 'ca_centro_guardar', 'ca_centro_nonce' );
	$es  = get_post_meta( $post->ID, CA_META_ES_CENTRO, true );
	$eti = get_post_meta( $post->ID, CA_META_ETIQUETA, true );
	$res = get_post_meta( $post->ID, CA_META_RESUMEN, true );
	$nom = get_post_meta( $post->ID, CA_META_NOMBRE, true );
	?>
	<p><label><input type="checkbox" name="ca_centro_es" value="1" <?php checked( $es, '1' ); ?>> Mostrar en «Nuestros centros» (portada y menú)</label></p>
	<p><label for="ca_centro_nombre"><strong>Nombre corto</strong></label><br>
	<input type="text" class="widefat" id="ca_centro_nombre" name="ca_centro_nombre" value="<?php echo esc_attr( $nom ); ?>" placeholder="Ej.: Centro de Día"></p>
	<p><label for="ca_centro_etiqueta"><strong>Etiqueta</strong></label><br>
	<input type="text" class="widefat" id="ca_centro_etiqueta" name="ca_centro_etiqueta" value="<?php echo esc_attr( $eti ); ?>" placeholder="Ej.: 40 plazas"></p>
	<p><label for="ca_centro_resumen"><strong>Resumen</strong> (1–2 frases)</label><br>
	<textarea class="widefat" rows="4" id="ca_centro_resumen" name="ca_centro_resumen"><?php echo esc_textarea( $res ); ?></textarea></p>
	<p class="description">El orden se cambia en «Atributos de página → Orden».</p>
	<?php
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['ca_centro_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ca_centro_nonce'] ), 'ca_centro_guardar' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, CA_META_ES_CENTRO, isset( $_POST['ca_centro_es'] ) ? '1' : '' );
	update_post_meta( $post_id, CA_META_NOMBRE, sanitize_text_field( wp_unslash( $_POST['ca_centro_nombre'] ?? '' ) ) );
	update_post_meta( $post_id, CA_META_ETIQUETA, sanitize_text_field( wp_unslash( $_POST['ca_centro_etiqueta'] ?? '' ) ) );
	update_post_meta( $post_id, CA_META_RESUMEN, sanitize_textarea_field( wp_unslash( $_POST['ca_centro_resumen'] ?? '' ) ) );
} );

// El editor de bloques necesita que las metas estén registradas para no perderlas.
add_action( 'init', function () {
	foreach ( array( CA_META_ES_CENTRO, CA_META_ETIQUETA, CA_META_RESUMEN, CA_META_NOMBRE ) as $k ) {
		register_post_meta( 'page', $k, array(
			'single'        => true,
			'type'          => 'string',
			'show_in_rest'  => false,
			'auth_callback' => function () {
				return current_user_can( 'edit_pages' );
			},
		) );
	}
} );
