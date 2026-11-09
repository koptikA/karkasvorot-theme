<?php
/**
 * Archives (incl. /nashi-raboty/).
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title"><?php echo esc_html( is_post_type_archive() ? post_type_archive_title( '', false ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	<?php the_archive_description( '<div class="kv-lead">', '</div>' ); ?>
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
