<?php
/**
 * Page.
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'kv-container kv-section kv-content' ); ?>>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1 class="kv-title"><?php the_title(); ?></h1>
		<?php the_content(); ?>
		<?php kv_order_form(); ?>
	</article>
	<?php
endwhile;
get_footer();
