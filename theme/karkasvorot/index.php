<?php
/**
 * Fallback template.
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title"><?php echo esc_html( is_front_page() ? get_bloginfo( 'name' ) : wp_get_document_title() ); ?></h1>
	<?php get_template_part( 'template-parts/loop' ); ?>
</div>
<?php
get_footer();
