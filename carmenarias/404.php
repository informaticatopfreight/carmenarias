<?php
/**
 * Página no encontrada.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="pagina-404">
	<div class="wrap pagina-404-in">
		<div class="pagina-404-molino" aria-hidden="true"><?php ca_molino(); ?></div>
		<div>
			<p class="antetitulo">Error 404</p>
			<h1>Esta página se la ha llevado el viento.</h1>
			<p>Puede que el enlace haya cambiado con la nueva web. Busca lo que necesitas o vuelve al inicio.</p>
			<?php get_search_form(); ?>
			<p><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Volver al inicio</a></p>
		</div>
	</div>
</section>
<?php
get_footer();
