<?php
/**
 * Work in a grid: photo + caption link. $args: work (WP_Post).
 */
$kv_work = $args['work'];
$kv_img  = get_post_thumbnail_id( $kv_work );
$kv_name = get_the_title( $kv_work );
if ( ! empty( $args['short_caption'] ) || false !== stripos( $kv_name, 'karkasvorot.by' ) ) {
	$kv_asset = kv_catalog_image( (int) $kv_img );
	if ( $kv_asset ) { $kv_name = $kv_asset['alt']; }
	if ( 'Кованые ворота с калиткой и кирпичными столбами' === $kv_name ) { $kv_name = 'Кованые ворота с калиткой'; }
}
?>
<figure class="kv-work">
	<?php if ( $kv_img ) : ?>
		<a class="kv-work__media" href="<?php echo esc_url( get_permalink( $kv_work ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo wp_get_attachment_image( $kv_img, 'kv-card', false, array( 'class' => 'kv-work__img', 'alt' => kv_image_alt( $kv_img, $kv_name ), 'loading' => 'lazy' ) );
			?>
		</a>
	<?php endif; ?>
	<figcaption class="kv-work__caption"><a href="<?php echo esc_url( get_permalink( $kv_work ) ); ?>"><?php echo esc_html( $kv_name ); ?></a></figcaption>
</figure>
