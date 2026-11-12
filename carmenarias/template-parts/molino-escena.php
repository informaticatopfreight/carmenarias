<?php
/**
 * Escena de la portada: llanura, sol, nube y molino con aspas que giran.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;
?>
<svg class="escena-svg" viewBox="0 0 400 420" preserveAspectRatio="xMidYMax meet" aria-hidden="true" focusable="false">
	<circle class="sol" cx="320" cy="90" r="46"/>
	<g class="nube"><ellipse cx="40" cy="60" rx="38" ry="12"/><ellipse cx="62" cy="52" rx="22" ry="12"/></g>
	<path class="campo2" d="M0 360 Q120 330 250 345 T400 335 V420 H0Z"/>
	<path class="campo1" d="M0 380 Q150 355 300 372 T400 365 V420 H0Z"/>
	<path class="campo3" d="M0 400 Q200 385 400 398 V420 H0Z"/>
	<path class="molino-cuerpo" d="M168 168 L232 168 L248 372 L152 372Z"/>
	<path class="molino-trazo" d="M168 168 L232 168 L248 372 L152 372Z"/>
	<path class="molino-tejado" d="M160 170 Q200 112 240 170Z"/>
	<path class="molino-puerta" d="M188 372 V330 Q200 318 212 330 V372Z"/>
	<rect class="molino-puerta" x="194" y="230" width="12" height="18" rx="6"/>
	<g class="aspas">
		<g id="ca-aspa">
			<line class="aspa-marco" x1="200" y1="150" x2="200" y2="20"/>
			<rect class="aspa-lona" x="202" y="30" width="22" height="104"/>
			<path class="aspa-marco" d="M202 52 H224 M202 74 H224 M202 96 H224 M202 118 H224"/>
		</g>
		<use href="#ca-aspa" transform="rotate(90 200 150)"/>
		<use href="#ca-aspa" transform="rotate(180 200 150)"/>
		<use href="#ca-aspa" transform="rotate(270 200 150)"/>
		<circle cx="200" cy="150" r="7" class="molino-tejado"/>
	</g>
</svg>
