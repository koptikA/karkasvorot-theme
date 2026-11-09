<?php
/**
 * Template Name: Услуга
 * Service page: crumbs, h1, intro, price card, term/warranty if set, size calculator (gates, wickets), content, works, FAQ, order form.
 * «Прочие изделия»: content is split by <h2 id="anchor"> (anchors of kv_other_sections()); each section gets its price and works.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$kv_id      = get_the_ID();
	$kv_title   = get_the_title();
	$kv_prices  = (array) json_decode( (string) get_post_meta( $kv_id, '_kv_prices', true ), true );
	$kv_is_misc = 'prochie-izdeliya' === get_post_field( 'post_name' ) || (bool) array_filter( $kv_prices, 'strlen' );
	$kv_html    = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core filter.
	$kv_parts   = array( '' => $kv_html );
	if ( $kv_is_misc ) {
		$kv_parts = array( '' => '' );
		$kv_split = '/(?=<h2\b[^>]*\bid="(?:' . implode( '|', array_keys( kv_other_sections() ) ) . ')")/';
		foreach ( preg_split( $kv_split, $kv_html ) as $kv_chunk ) {
			$kv_key              = preg_match( '/^<h2\b[^>]*\bid="([a-z]+)"/', $kv_chunk, $kv_m ) ? $kv_m[1] : '';
			$kv_parts[ $kv_key ] = ( isset( $kv_parts[ $kv_key ] ) ? $kv_parts[ $kv_key ] : '' ) . $kv_chunk;
		}
	}
	$kv_facts = array_filter( array(
		'Срок изготовления' => (string) get_post_meta( $kv_id, '_kv_term', true ),
		'Гарантия'          => (string) get_post_meta( $kv_id, '_kv_warranty', true ),
	), 'strlen' );
	?>
	<article <?php post_class( 'kv-container kv-section kv-service' ); ?>>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1 class="kv-title"><?php echo esc_html( $kv_is_misc ? 'Каркасы мебели, обувницы, заборы и навесы — Минск и вся Беларусь' : $kv_title . ' в Минске и по Беларуси' ); ?></h1>
		<div class="kv-service__top">
			<div class="kv-service__intro kv-content"><?php echo $kv_parts['']; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content. ?></div>
			<div class="kv-service__price">
				<?php get_template_part( 'template-parts/price-card', null, array( 'page' => get_post(), 'heading' => 'h2', 'title' => 'Каркас и комплектация', 'first' => true ) ); ?>
				<?php if ( $kv_facts ) : ?>
					<dl class="kv-facts">
						<?php foreach ( $kv_facts as $kv_label => $kv_value ) : ?>
							<dt><?php echo esc_html( $kv_label ); ?></dt><dd><?php echo esc_html( $kv_value ); ?></dd>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
				<?php get_template_part( 'template-parts/price-note' ); ?>
			</div>
		</div>

		<?php
		$kv_calc = array( 'karkas-otkatnyh-vorot' => 'slide', 'karkas-raspashnyh-vorot' => 'swing', 'kalitki' => 'wicket' );
		if ( isset( $kv_calc[ get_post_field( 'post_name' ) ] ) ) {
			get_template_part( 'template-parts/calc', null, array( 'type' => $kv_calc[ get_post_field( 'post_name' ) ] ) );
		}
		?>
		<?php if ( $kv_is_misc ) : ?>
			<?php foreach ( kv_other_sections() as $kv_anchor => $kv_name ) : ?>
				<?php $kv_from = isset( $kv_prices[ $kv_anchor ] ) ? (string) $kv_prices[ $kv_anchor ] : ''; ?>
				<section class="kv-other" aria-labelledby="<?php echo esc_attr( $kv_anchor ); ?>">
					<div class="kv-content">
						<?php echo isset( $kv_parts[ $kv_anchor ] ) ? $kv_parts[ $kv_anchor ] : '<h2 id="' . esc_attr( $kv_anchor ) . '">' . esc_html( $kv_name ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content. ?>
						<p class="kv-other__price">
							<?php if ( kv_public_prices_enabled() && '' !== $kv_from ) : ?>
								от <strong><?php echo esc_html( $kv_from ); ?></strong> BYN
							<?php else : ?>
								Стоимость по запросу
							<?php endif; ?>
						</p>
					</div>
					<?php get_template_part( 'template-parts/works', null, array( 'works' => kv_works( $kv_id, 12, $kv_anchor ), 'title' => 'Работы — ' . $kv_name, 'heading' => 'h3' ) ); ?>
				</section>
			<?php endforeach; ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/works', null, array( 'works' => kv_works( $kv_id ), 'title' => 'Наши работы — ' . $kv_title ) ); ?>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/how-to-order' ); ?>
		<?php get_template_part( 'template-parts/faq', null, array( 'page_id' => $kv_id ) ); ?>
		<?php kv_order_form(); // product is preselected by page slug in module leads. ?>
	</article>
	<?php
endwhile;
get_footer();
