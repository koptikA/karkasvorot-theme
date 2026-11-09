<?php
/**
 * Search results.
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title">Поиск: «<?php echo esc_html( get_search_query() ); ?>»</h1>
	<?php get_search_form(); ?>
	<?php if ( have_posts() ) : ?>
		<div class="kv-grid">
			<?php
			while ( have_posts() ) {
				the_post();
				get_template_part( 'template-parts/card' );
			}
			?>
		</div>
		<?php the_posts_pagination( array( 'class' => 'kv-pagination' ) ); ?>
	<?php else : ?>
		<p>Здесь пока ничего нет.</p>
	<?php endif; ?>
</div>
<?php
get_footer();
