<?php
/**
 * Single post (works use single-work.php).
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'kv-container kv-section kv-content' ); ?>>
		<h1 class="kv-title"><?php echo wp_kses_post( get_the_title() ? get_the_title() : 'Без названия' ); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="kv-content__cover"><?php the_post_thumbnail( 'kv-wide' ); ?></figure>
		<?php endif; ?>
		<?php the_content(); ?>
	</article>
	<?php
endwhile;
get_footer();
