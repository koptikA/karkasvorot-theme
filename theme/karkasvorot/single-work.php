<?php
/**
 * One work: big photo, caption, city, link to its service.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$kv_service = (int) get_post_meta( get_the_ID(), '_kv_service', true );
	$kv_city    = (string) get_post_meta( get_the_ID(), '_kv_city', true );
	$kv_img     = get_post_thumbnail_id();
	$kv_name    = get_the_title() ? get_the_title() : 'Работа';
	?>
	<article <?php post_class( 'kv-container kv-section kv-content kv-single-work' ); ?>>
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1 class="kv-title"><?php echo esc_html( $kv_name ); ?></h1>
		<?php if ( $kv_img ) : ?>
			<figure class="kv-single-work__photo">
				<?php
				echo wp_get_attachment_image( $kv_img, 'large', false, array( 'alt' => kv_image_alt( $kv_img, $kv_name ), 'loading' => false, 'fetchpriority' => 'high', 'sizes' => '(max-width: 48rem) 100vw, 48rem' ) );
				?>
			</figure>
		<?php endif; ?>
		<?php if ( '' !== $kv_city ) : ?>
			<p class="kv-meta"><?php echo esc_html( $kv_city ); ?></p>
		<?php endif; ?>
		<?php the_content(); ?>
		<p class="kv-single-work__links">
			<?php if ( $kv_service && 'publish' === get_post_status( $kv_service ) ) : ?>
				Услуга: <a href="<?php echo esc_url( get_permalink( $kv_service ) ); ?>"><?php echo esc_html( get_the_title( $kv_service ) ); ?></a> ·
			<?php endif; ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Все работы</a>
		</p>
		<?php kv_order_form(); ?>
	</article>
	<?php
endwhile;
get_footer();
