<?php
/**
 * Home: offer, price cards of all services, latest works, how to order, order form.
 */
get_header();
?>
<div class="kv-container kv-section kv-home">
	<section class="kv-hero">
		<h1 class="kv-hero__title">Изготовление и монтаж каркасов ворот и металлоконструкций в Минске и области</h1>
		<?php
		while ( have_posts() ) {
			the_post();
			echo '<div class="kv-hero__lead">';
			the_content();
			echo '</div>';
		}
		?>
		<a class="kv-button" href="#zayavka">Оставить заявку</a>
	</section>

	<?php $kv_services = kv_service_pages(); ?>
	<?php if ( $kv_services ) : ?>
		<section class="kv-prices" aria-labelledby="kv-prices-title">
			<h2 id="kv-prices-title">Цены</h2>
			<div class="kv-prices__grid">
				<?php
				foreach ( $kv_services as $kv_i => $kv_page ) {
					get_template_part( 'template-parts/price-card', null, array( 'page' => $kv_page, 'link' => true, 'first' => 0 === $kv_i ) );
				}
				?>
			</div>
			<?php get_template_part( 'template-parts/price-note' ); ?>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/works', null, array( 'works' => kv_works( 0, 6 ), 'title' => 'Наши работы', 'more' => true ) ); ?>
	<?php get_template_part( 'template-parts/how-to-order' ); ?>
	<?php kv_order_form(); ?>
</div>
<?php
get_footer();
