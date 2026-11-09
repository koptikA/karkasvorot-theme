<?php
/**
 * Single post / work.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$kv_service = 'work' === get_post_type() ? (int) get_post_meta( get_the_ID(), '_kv_service', true ) : 0;
	$kv_city    = 'work' === get_post_type() ? (string) get_post_meta( get_the_ID(), '_kv_city', true ) : '';
	?>
	<article <?php post_class( 'kv-container kv-section kv-content' ); ?>>
		<h1 class="kv-title"><?php echo esc_html( get_the_title() ? get_the_title() : 'Без названия' ); ?></h1>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="kv-content__cover"><?php the_post_thumbnail( 'kv-wide' ); ?></figure>
		<?php endif; ?>
		<?php if ( $kv_service || '' !== $kv_city ) : ?>
			<p class="kv-meta">
				<?php if ( $kv_service ) : ?>
					<a href="<?php echo esc_url( get_permalink( $kv_service ) ); ?>"><?php echo esc_html( get_the_title( $kv_service ) ); ?></a>
				<?php endif; ?>
				<?php echo esc_html( $kv_city ); ?>
			</p>
		<?php endif; ?>
		<?php the_content(); ?>
	</article>
	<?php
endwhile;
get_footer();
