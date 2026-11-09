<?php
/**
 * Page.
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'kv-container kv-section kv-content' ); ?>>
		<h1 class="kv-title"><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</article>
	<?php
endwhile;
get_footer();
