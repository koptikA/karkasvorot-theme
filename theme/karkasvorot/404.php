<?php
/**
 * Not found.
 */
get_header();
?>
<div class="kv-container kv-section kv-content">
	<h1 class="kv-title">Страница не найдена</h1>
	<p>Такой страницы нет. Перейдите на <a href="<?php echo esc_url( home_url( '/' ) ); ?>">главную</a> или найдите нужное:</p>
	<?php get_search_form(); ?>
</div>
<?php
get_footer();
