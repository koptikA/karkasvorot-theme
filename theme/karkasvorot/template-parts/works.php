<?php
/**
 * Works block: heading + grid. Nothing when there are no works. $args: works (WP_Post[]), title, heading (tag), more (bool).
 */
if ( empty( $args['works'] ) ) {
	return;
}
$kv_tag = isset( $args['heading'] ) ? $args['heading'] : 'h2';
?>
<section class="kv-works">
	<<?php echo tag_escape( $kv_tag ); ?> class="kv-works__title"><?php echo esc_html( $args['title'] ); ?></<?php echo tag_escape( $kv_tag ); ?>>
	<div class="kv-works__grid">
		<?php
		foreach ( $args['works'] as $kv_work ) {
			get_template_part( 'template-parts/work-card', null, array( 'work' => $kv_work ) );
		}
		?>
	</div>
	<?php if ( ! empty( $args['more'] ) ) : ?>
		<p><a class="kv-works__more" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>">Все работы</a></p>
	<?php endif; ?>
</section>
