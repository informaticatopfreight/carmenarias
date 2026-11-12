<?php
/**
 * Cabecera.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="saltar" href="#contenido">Saltar al contenido</a>

<div class="barra-superior">
	<div class="wrap barra-superior-in">
		<a href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo ca_icono( 'tel' ); // phpcs:ignore ?><span><?php echo esc_html( ca_opt( 'telefono' ) ); ?></span></a>
		<a class="solo-ancho" href="mailto:<?php echo esc_attr( ca_opt( 'email' ) ); ?>"><?php echo ca_icono( 'mail' ); // phpcs:ignore ?><span><?php echo esc_html( ca_opt( 'email' ) ); ?></span></a>
		<?php ca_redes(); ?>
		<button class="btn-texto" type="button" aria-pressed="false" data-texto-grande><?php echo ca_icono( 'texto' ); // phpcs:ignore ?><span>Letra grande</span></button>
	</div>
</div>

<header class="cabecera-sitio">
	<div class="wrap nav">
		<?php ca_logo(); ?>
		<button class="btn-menu" type="button" aria-expanded="false" aria-controls="menu-principal"><?php echo ca_icono( 'menu', 'abrir' ) . ca_icono( 'cerrar', 'cerrar' ); // phpcs:ignore ?><span>Menú</span></button>
		<nav class="menu-principal" id="menu-principal" aria-label="Principal">
			<?php
			if ( has_nav_menu( 'principal' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'principal',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
				) );
			} else {
				ca_menu_reserva();
			}
			?>
		</nav>
	</div>
</header>

<main id="contenido">
