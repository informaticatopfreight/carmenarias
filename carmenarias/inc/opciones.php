<?php
/**
 * Opciones editables desde Apariencia → Personalizar → Fundación Carmen Arias.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valores por defecto: el contenido real de la Fundación.
 */
function ca_opciones_por_defecto() {
	return array(
		'telefono'      => '926 530 612',
		'fax'           => '926 539 000',
		'email'         => 'info@carmenarias.es',
		'email_canal'   => 'sistemadeinformacion@carmenarias.es',
		'direccion'     => "C/ Bonillo 106\n13630 Socuéllamos (Ciudad Real)",
		'mapa'          => 'https://www.google.com/maps/search/?api=1&query=C%2F+Bonillo+106%2C+13630+Socu%C3%A9llamos',
		'facebook'      => 'https://www.facebook.com/profile.php?id=100066497388569',
		'instagram'     => 'https://www.instagram.com/fundacioncarmenarias/',

		'hero_antetitulo' => 'Complejo Residencial “Ntra. Sra. del Carmen” · Socuéllamos',
		'hero_linea_1'  => 'Nos gusta',
		'hero_linea_2'  => 'cuidarte.',
		'hero_texto'    => 'Trabajamos para que los cuidados lleguen donde más se necesita, con profesionalidad, respeto y calidez humana.',

		'marquesina'    => "Dignidad\nLa vejez, etapa de plenitud\nCalidad\nCompromiso ético\nSolidaridad",

		'intro_titulo'  => 'Un legado de Carmen Arias al pueblo de Socuéllamos.',
		'intro_texto'   => "Dña. Carmen Arias cedió todos sus bienes al pueblo de Socuéllamos. Así nació en 1986 la Fundación Carmen Arias, una entidad sin ánimo de lucro al servicio de la localidad.\n\nHoy atendemos a personas mayores, a adultos jóvenes con daño cerebral sobrevenido y a personas adultas con problemas de salud mental, con una atención de calidad y calidez, personalizada.",

		'cifras'        => "146 | plazas residenciales\n40 | plazas en Centro de Día\n65 | plazas en el SEPAP\n+100 | personas atendidas en su casa",

		'historia'      => "1986 | Nace la Fundación Carmen Arias\n1992 | Abre la Residencia “Ntra. Sra. del Carmen”\n2008 | Módulo de Daño Cerebral Sobrevenido\n2012 | Servicio de Ayuda a Domicilio\n2013 | Centro de Día y ampliación de plazas\n2015 | SEPAP\n2018 | Plazas para personas con problemas de salud mental",

		'historia_texto' => 'La historia de la Fundación “Carmen Arias” se inicia con la donación de terrenos por parte de Dña. Carmen Arias, quien cedió todos sus bienes al pueblo de Socuéllamos. Con el paso de los años el centro ha integrado nuevas figuras profesionales, mejorando la calidad de sus servicios. La filosofía permanente de la Fundación ha sido avanzar, progresar y ampliar servicios de calidad a disposición de las personas más necesitadas.',

		'cita_texto'    => 'Sábete, Sancho, que no es un hombre más que otro si no hace más que otro.',
		'cita_autor'    => 'Miguel de Cervantes, <i>Don Quijote de la Mancha</i>, I, cap. 18',

		'pie_texto'     => 'Entidad sin ánimo de lucro al servicio de Socuéllamos desde 1986. Miembro de ACESCAM, Asociación de Residencias y Servicios de Atención a los Mayores – sector solidario.',
	);
}

/**
 * Lee una opción del tema.
 *
 * @param string $clave Nombre de la opción.
 * @return string
 */
function ca_opt( $clave ) {
	$def = ca_opciones_por_defecto();
	$val = get_theme_mod( 'ca_' . $clave, null );
	if ( null === $val || '' === $val ) {
		return isset( $def[ $clave ] ) ? $def[ $clave ] : '';
	}
	return $val;
}

/**
 * Convierte un texto con líneas "valor | texto" en pares.
 *
 * @param string $clave Opción a leer.
 * @return array<int, array{0:string,1:string}>
 */
function ca_opt_pares( $clave ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', ca_opt( $clave ) ) as $linea ) {
		$linea = trim( $linea );
		if ( '' === $linea ) {
			continue;
		}
		$partes = array_map( 'trim', explode( '|', $linea, 2 ) );
		$out[]  = array( $partes[0], isset( $partes[1] ) ? $partes[1] : '' );
	}
	return $out;
}

/**
 * Lista de líneas no vacías.
 *
 * @param string $clave Opción a leer.
 * @return string[]
 */
function ca_opt_lineas( $clave ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', ca_opt( $clave ) ) ) ) );
}

/**
 * Teléfono preparado para un enlace tel:.
 */
function ca_tel_href( $tel ) {
	return 'tel:+34' . preg_replace( '/\D+/', '', $tel );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wpc ) {
	$def = ca_opciones_por_defecto();

	$wpc->add_panel( 'ca_panel', array(
		'title'    => 'Fundación Carmen Arias',
		'priority' => 20,
	) );

	$secciones = array(
		'ca_contacto' => array( 'Datos de contacto', array(
			'telefono'    => array( 'Teléfono', 'text' ),
			'fax'         => array( 'Fax', 'text' ),
			'email'       => array( 'Email de contacto (recibe el formulario)', 'email' ),
			'email_canal' => array( 'Email del Canal de Información', 'email' ),
			'direccion'   => array( 'Dirección', 'textarea' ),
			'mapa'        => array( 'Enlace a Google Maps', 'url' ),
			'facebook'    => array( 'Facebook', 'url' ),
			'instagram'   => array( 'Instagram', 'url' ),
		) ),
		'ca_portada'  => array( 'Portada', array(
			'hero_antetitulo' => array( 'Antetítulo', 'text' ),
			'hero_linea_1'    => array( 'Titular · línea 1', 'text' ),
			'hero_linea_2'    => array( 'Titular · línea 2 (en dorado)', 'text' ),
			'hero_texto'      => array( 'Texto bajo el titular', 'textarea' ),
			'marquesina'      => array( 'Franja en movimiento (una frase por línea)', 'textarea' ),
			'intro_titulo'    => array( 'La Fundación · título', 'text' ),
			'intro_texto'     => array( 'La Fundación · texto (deja una línea en blanco entre párrafos)', 'textarea' ),
			'cifras'          => array( 'Cifras (una por línea: número | texto)', 'textarea' ),
			'historia'        => array( 'Historia (una por línea: año | texto)', 'textarea' ),
			'historia_texto'  => array( 'Historia · texto', 'textarea' ),
			'cita_texto'      => array( 'Cita', 'textarea' ),
			'cita_autor'      => array( 'Autor de la cita', 'text' ),
		) ),
		'ca_pie'      => array( 'Pie de página', array(
			'pie_texto' => array( 'Texto del pie', 'textarea' ),
		) ),
	);

	foreach ( $secciones as $id => $sec ) {
		$wpc->add_section( $id, array(
			'title' => $sec[0],
			'panel' => 'ca_panel',
		) );
		foreach ( $sec[1] as $clave => $campo ) {
			$san = 'sanitize_text_field';
			if ( 'textarea' === $campo[1] ) {
				$san = 'wp_kses_post';
			} elseif ( 'url' === $campo[1] ) {
				$san = 'esc_url_raw';
			} elseif ( 'email' === $campo[1] ) {
				$san = 'sanitize_email';
			}
			$wpc->add_setting( 'ca_' . $clave, array(
				'default'           => $def[ $clave ],
				'sanitize_callback' => $san,
			) );
			$wpc->add_control( 'ca_' . $clave, array(
				'label'   => $campo[0],
				'section' => $id,
				'type'    => $campo[1],
			) );
		}
	}
} );
