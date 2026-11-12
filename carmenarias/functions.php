<?php
/**
 * Tema Carmen Arias.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

define( 'CA_VERSION', '1.0.0' );
define( 'CA_DIR', get_template_directory() );
define( 'CA_URI', get_template_directory_uri() );

require CA_DIR . '/inc/setup.php';
require CA_DIR . '/inc/opciones.php';
require CA_DIR . '/inc/plantilla.php';
require CA_DIR . '/inc/centros.php';
require CA_DIR . '/inc/contacto.php';
require CA_DIR . '/inc/divi.php';

if ( is_admin() ) {
	require CA_DIR . '/inc/migracion.php';
}
