<?php
/**
 * Escena de la portada: la fachada de la Residencia «Ntra. Sra. del Carmen»
 * (C/ Bonillo, Socuéllamos) con molinos manchegos girando en la loma del fondo.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pinta un molino. La base queda en ($x, $y); $s es la escala y $vel los segundos por vuelta.
 */
$molino = function ( $x, $y, $s, $vel, $retraso = 0 ) {
	$tx = $x - 200 * $s;
	$ty = $y - 372 * $s;
	?>
	<g transform="translate(<?php echo esc_attr( $tx ); ?> <?php echo esc_attr( $ty ); ?>) scale(<?php echo esc_attr( $s ); ?>)">
		<path class="molino-cuerpo" d="M168 168 L232 168 L248 372 L152 372Z"/>
		<path class="molino-trazo" d="M168 168 L232 168 L248 372 L152 372Z"/>
		<path class="molino-tejado" d="M160 170 Q200 112 240 170Z"/>
		<path class="molino-puerta" d="M188 372 V330 Q200 318 212 330 V372Z"/>
		<rect class="molino-puerta" x="194" y="230" width="12" height="18" rx="6"/>
		<g class="aspas-fondo" style="animation-duration:<?php echo esc_attr( $vel ); ?>s;animation-delay:-<?php echo esc_attr( $retraso ); ?>s">
			<use href="#ca-aspa"/>
			<use href="#ca-aspa" transform="rotate(90 200 150)"/>
			<use href="#ca-aspa" transform="rotate(180 200 150)"/>
			<use href="#ca-aspa" transform="rotate(270 200 150)"/>
			<circle cx="200" cy="150" r="7" class="molino-tejado"/>
		</g>
	</g>
	<?php
};
?>
<svg class="escena-svg" viewBox="0 0 1200 400" preserveAspectRatio="xMidYMax slice" role="img" aria-labelledby="escena-titulo">
	<title id="escena-titulo">Ilustración de la fachada de la Residencia Nuestra Señora del Carmen, en Socuéllamos, con molinos manchegos en la loma</title>
	<defs>
		<g id="ca-aspa">
			<line class="aspa-marco" x1="200" y1="150" x2="200" y2="20"/>
			<rect class="aspa-lona" x="202" y="30" width="22" height="104"/>
			<path class="aspa-marco" d="M202 52 H224 M202 74 H224 M202 96 H224 M202 118 H224"/>
		</g>
		<pattern id="ca-celosia" width="14" height="14" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
			<rect width="14" height="14" class="cristal"/>
			<path d="M0 0 V14 M0 0 H14" class="celosia"/>
		</pattern>
		<pattern id="ca-barandilla" width="7" height="30" patternUnits="userSpaceOnUse">
			<path d="M3.5 0 V30" class="barrote"/>
		</pattern>
		<pattern id="ca-teja" width="9" height="9" patternUnits="userSpaceOnUse">
			<rect width="9" height="9" class="teja"/>
			<path d="M0 8.5 Q4.5 4 9 8.5" class="teja-linea"/>
		</pattern>
	</defs>

	<!-- Cielo -->
	<circle class="sol" cx="1010" cy="92" r="52"/>
	<g class="nube"><ellipse cx="120" cy="70" rx="46" ry="13"/><ellipse cx="148" cy="60" rx="26" ry="14"/></g>
	<g class="nube nube-2"><ellipse cx="760" cy="48" rx="36" ry="10"/><ellipse cx="780" cy="40" rx="20" ry="11"/></g>

	<!-- Loma con molinos (se mueve un poco al hacer scroll) -->
	<g class="escena-fondo" data-parallax>
		<?php
		$molino( 92, 268, 0.40, 16, 0 );
		$molino( 205, 258, 0.29, 12, 3 );
		$molino( 1062, 266, 0.44, 19, 7 );
		$molino( 1160, 262, 0.30, 14, 2 );
		?>
		<path class="loma" d="M0 262 Q140 236 290 258 T600 250 T900 256 T1200 256 V400 H0Z"/>
	</g>

	<!-- Residencia -->
	<g class="residencia">
		<!-- muro -->
		<rect class="muro" x="250" y="212" width="700" height="148"/>
		<!-- ala derecha: pilastras y paños -->
		<g class="panos">
			<rect x="712" y="236" width="62" height="106"/>
			<rect x="792" y="236" width="62" height="106"/>
			<rect x="872" y="236" width="62" height="106"/>
		</g>
		<rect class="tablon" x="902" y="288" width="34" height="22"/>
		<path class="trazo-fino" d="M906 310 V352 M932 310 V352"/>
		<!-- tejados -->
		<rect x="244" y="202" width="282" height="12" fill="url(#ca-teja)" class="borde"/>
		<rect x="684" y="202" width="272" height="12" fill="url(#ca-teja)" class="borde"/>
		<!-- columnata izquierda -->
		<rect class="viga" x="250" y="214" width="272" height="12"/>
		<rect x="262" y="314" width="250" height="30" fill="url(#ca-barandilla)"/>
		<path class="trazo" d="M262 314 H512"/>
		<g class="columnas">
			<rect x="268" y="226" width="18" height="122" rx="3"/>
			<rect x="326" y="226" width="18" height="122" rx="3"/>
			<rect x="384" y="226" width="18" height="122" rx="3"/>
			<rect x="442" y="226" width="18" height="122" rx="3"/>
			<rect x="500" y="226" width="18" height="122" rx="3"/>
		</g>
		<rect class="placa" x="470" y="268" width="14" height="16"/>
		<!-- pórtico a dos aguas -->
		<polygon class="muro borde" points="502,214 605,126 708,214"/>
		<polygon class="sombra" points="530,208 605,146 680,208"/>
		<rect class="cristalera borde" x="536" y="244" width="138" height="48" fill="url(#ca-celosia)"/>
		<g class="puertas">
			<rect x="536" y="292" width="138" height="44"/>
			<path d="M553 292 V336 M570 292 V336 M587 292 V336 M623 292 V336 M640 292 V336 M657 292 V336"/>
		</g>
		<rect class="viga" x="512" y="206" width="186" height="11"/>
		<!-- pilar central en cruz -->
		<rect class="pilar" x="598" y="134" width="14" height="214"/>
		<!-- escalinata -->
		<rect class="escalon" x="528" y="336" width="154" height="8"/>
		<rect class="escalon" x="518" y="344" width="174" height="8"/>
		<rect class="escalon" x="508" y="352" width="194" height="8"/>
		<!-- banco -->
		<path class="trazo" d="M764 344 H810 M768 344 V358 M806 344 V358"/>
		<rect class="banco" x="762" y="336" width="50" height="6" rx="2"/>
	</g>

	<!-- Suelo -->
	<rect class="acera" x="0" y="358" width="1200" height="14"/>
	<path class="campo1" d="M0 372 Q300 362 600 370 T1200 366 V400 H0Z"/>

	<!-- Pájaros cruzando el cielo -->
	<g class="pajaros">
		<g transform="translate(0 70)"><path class="pajaro" d="M0 0 Q6 -6 12 0 Q18 -6 24 0"/></g>
		<g transform="translate(34 58)"><path class="pajaro pajaro-2" d="M0 0 Q5 -5 10 0 Q15 -5 20 0"/></g>
		<g transform="translate(58 78)"><path class="pajaro pajaro-3" d="M0 0 Q4 -4 8 0 Q12 -4 16 0"/></g>
	</g>

	<!-- Residente sentado en el banco, saludando -->
	<g class="sentado">
		<circle cx="782" cy="314" r="7" class="piel"/>
		<path d="M775 324 Q782 319 789 324 L790 338 H774Z" class="rebeca"/>
		<path d="M776 338 H792 M790 338 L792 352" class="pierna"/>
		<path class="brazo-saludo" d="M788 327 L797 316"/>
	</g>

	<!-- Paseo: persona mayor con bastón y cuidadora, de ida y vuelta -->
	<g transform="translate(-150 0)"><g class="paseo">
		<g class="cuidadora andar">
			<path d="M808 346 L808 360" class="pierna pierna-a"/>
			<path d="M816 346 L816 360" class="pierna pierna-b"/>
			<path d="M804 322 Q812 316 820 322 L821 346 H803Z" class="uniforme"/>
			<circle cx="812" cy="312" r="7" class="piel"/>
			<path d="M820 330 Q825 334 827 333" class="brazo"/>
		</g>
		<g class="persona-mayor andar andar-lento">
			<path d="M826 348 L826 360" class="pierna pierna-a"/>
			<path d="M834 348 L834 360" class="pierna pierna-b"/>
			<path d="M823 328 Q830 323 837 328 L838 348 H822Z" class="abrigo"/>
			<circle cx="830" cy="318" r="7" class="piel"/>
			<path d="M837 334 L846 338 L848 360" class="baston"/>
		</g>
	</g></g>
</svg>
