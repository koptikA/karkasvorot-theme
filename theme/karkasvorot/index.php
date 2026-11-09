<?php
/**
 * Fallback template.
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title"><?php echo esc_html( is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title() ); ?></h1>
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
