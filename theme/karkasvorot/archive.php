<?php
/**
 * Archives (incl. /nashi-raboty/).
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title"><?php echo esc_html( is_post_type_archive() ? post_type_archive_title( '', false ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	<?php the_archive_description( '<div class="kv-lead">', '</div>' ); ?>
	<?php get_template_part( 'template-parts/loop' ); ?>
</div>
<?php
get_footer();
