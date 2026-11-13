<?php
/**
 * Portada.
 *
 * Todo el texto se edita en Apariencia → Personalizar → Fundación Carmen Arias.
 * Los centros salen de las páginas marcadas como «Centro de la Fundación».
 * Las noticias son las 3 últimas entradas publicadas.
 *
 * @package carmenarias
 */

defined( 'ABSPATH' ) || exit;

get_header();

$centros_pag = get_page_by_path( 'servicios' );
$contacto    = get_page_by_path( 'contacto' );
$noticias    = get_page_by_path( 'noticias' );
$frases      = ca_opt_lineas( 'marquesina' );
?>

<section class="hero">
	<div class="wrap hero-in">
		<div class="hero-texto">
			<p class="antetitulo"><?php echo esc_html( ca_opt( 'hero_antetitulo' ) ); ?></p>
			<h1>
				<span class="ln"><span><?php echo esc_html( ca_opt( 'hero_linea_1' ) ); ?></span></span>
				<span class="ln"><span><em><?php echo esc_html( ca_opt( 'hero_linea_2' ) ); ?></em></span></span>
			</h1>
			<p class="hero-lead"><?php echo esc_html( ca_opt( 'hero_texto' ) ); ?></p>
			<div class="hero-acciones">
				<a class="btn" href="#centros">Conoce nuestros centros <?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></a>
				<?php if ( $contacto ) : ?>
					<a class="btn btn-borde" href="<?php echo esc_url( get_permalink( $contacto ) ); ?>">Pide información</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="escena" data-parallax><?php ca_molino( 'escena' ); ?></div>
	</div>
</section>

<?php if ( $frases ) : ?>
<div class="zocalo" aria-hidden="true">
	<div class="marquesina">
		<?php for ( $i = 0; $i < 2; $i++ ) : ?>
			<?php foreach ( $frases as $f ) : ?>
				<span><?php echo esc_html( $f ); ?><i>✦</i></span>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
<?php endif; ?>

<section class="fundacion" id="fundacion">
	<div class="wrap">
		<div class="intro reveal">
			<div>
				<p class="antetitulo">La Fundación</p>
				<h2><?php echo esc_html( ca_opt( 'intro_titulo' ) ); ?></h2>
			</div>
			<div class="intro-texto"><?php echo wp_kses_post( wpautop( ca_opt( 'intro_texto' ) ) ); ?></div>
		</div>
		<div class="cifras">
			<?php foreach ( ca_opt_pares( 'cifras' ) as $c ) : ?>
				<?php
				$num  = preg_replace( '/\D/', '', $c[0] );
				$mas  = 0 === strpos( $c[0], '+' );
				?>
				<div class="cifra reveal">
					<b <?php echo $num ? 'data-n="' . esc_attr( $num ) . '"' : ''; ?> <?php echo $mas ? 'data-mas' : ''; ?>><?php echo esc_html( $c[0] ); ?></b>
					<span><?php echo esc_html( $c[1] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
$ss_pag = get_page_by_path( 'sector-solidario' );
if ( $ss_pag ) :
	$ss_url = get_permalink( $ss_pag );
	$ss     = array(
		'mision'  => array( 'Misión', 'Atender a las personas mayores más vulnerables, con calidad y calidez.' ),
		'vision'  => array( 'Visión', 'Ser referencia entre las instituciones que sirven a las personas dependientes.' ),
		'valores' => array( 'Valores', 'Dignidad, plenitud, calidad, compromiso ético y solidaridad.' ),
	);
	?>
<section class="solidario">
	<div class="wrap">
		<div class="solidario-cab reveal">
			<p class="antetitulo">Sector solidario</p>
			<h2>Inspirados en el humanismo cristiano, al servicio de las personas.</h2>
		</div>
		<div class="solidario-grid">
			<?php foreach ( $ss as $ancla => $d ) : ?>
				<a class="solidario-item reveal" href="<?php echo esc_url( $ss_url . '#' . $ancla ); ?>">
					<h3><?php echo esc_html( $d[0] ); ?></h3>
					<p><?php echo esc_html( $d[1] ); ?></p>
					<span class="solidario-mas">Leer más <?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="historia">
	<div class="wrap">
		<div class="historia-cab reveal">
			<h2>Nuestra historia</h2>
			<p><?php echo esc_html( ca_opt( 'historia_texto' ) ); ?></p>
		</div>
		<ol class="linea" data-linea>
			<?php foreach ( ca_opt_pares( 'historia' ) as $h ) : ?>
				<li class="reveal"><b><?php echo esc_html( $h[0] ); ?></b><span><?php echo esc_html( $h[1] ); ?></span></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="centros" id="centros">
	<div class="wrap">
		<div class="centros-cab reveal">
			<h2>Nuestros centros</h2>
			<p>Cada persona necesita un apoyo distinto. Por eso la Fundación se organiza en centros y servicios especializados.</p>
		</div>
		<?php get_template_part( 'template-parts/lista-centros' ); ?>
	</div>
</section>

<section class="cita">
	<div class="wrap">
		<figure class="cita-in reveal">
			<?php ca_molino(); ?>
			<div>
				<blockquote data-cita><?php echo esc_html( ca_opt( 'cita_texto' ) ); ?></blockquote>
				<figcaption><?php echo wp_kses( ca_opt( 'cita_autor' ), array( 'i' => array(), 'em' => array() ) ); ?></figcaption>
			</div>
		</figure>
	</div>
</section>

<?php
$ultimas = new WP_Query( array(
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
if ( $ultimas->have_posts() ) :
	?>
<section class="noticias">
	<div class="wrap">
		<div class="noticias-cab reveal">
			<h2>Últimas noticias</h2>
			<a class="btn btn-borde" href="<?php echo esc_url( $noticias ? get_permalink( $noticias ) : home_url( '/' ) ); ?>">Ver todas</a>
		</div>
		<div class="rejilla">
			<?php
			while ( $ultimas->have_posts() ) {
				$ultimas->the_post();
				ca_tarjeta();
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="contacto-banda" id="contacto">
	<div class="wrap contacto-banda-in">
		<div class="reveal">
			<p class="antetitulo">Contacto</p>
			<h2>Ven a conocernos.</h2>
		</div>
		<div class="reveal">
			<dl>
				<dt>Dirección</dt><dd><?php echo esc_html( str_replace( "\n", ', ', ca_opt( 'direccion' ) ) ); ?></dd>
				<dt>Teléfono</dt><dd><a href="<?php echo esc_attr( ca_tel_href( ca_opt( 'telefono' ) ) ); ?>"><?php echo esc_html( ca_opt( 'telefono' ) ); ?></a></dd>
				<?php if ( ca_opt( 'fax' ) ) : ?><dt>Fax</dt><dd><?php echo esc_html( ca_opt( 'fax' ) ); ?></dd><?php endif; ?>
				<dt>Email</dt><dd><a href="mailto:<?php echo esc_attr( ca_opt( 'email' ) ); ?>"><?php echo esc_html( ca_opt( 'email' ) ); ?></a></dd>
			</dl>
			<?php if ( $contacto ) : ?>
				<p><a class="btn btn-oro" href="<?php echo esc_url( get_permalink( $contacto ) ); ?>">Solicitar una visita <?php echo ca_icono( 'flecha' ); // phpcs:ignore ?></a></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
