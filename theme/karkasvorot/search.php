<?php
/**
 * Search results.
 */
get_header();
?>
<div class="kv-container kv-section">
	<h1 class="kv-title">Поиск: «<?php echo esc_html( get_search_query() ); ?>»</h1>
	<?php get_search_form(); ?>
	<?php get_template_part( 'template-parts/loop' ); ?>
</div>
<?php
get_footer();
