<?php
/**
 * Price card of a service page: illustration, «от N BYN» (empty → «цена по запросу»), «Что входит».
 * $args: page (WP_Post), heading (tag), title (text), link (bool), first (bool — above the fold: no lazy, high priority).
 */
$kv_page  = $args['page'];
$kv_price = kv_service_price( $kv_page->ID );
$kv_ill   = (int) get_post_meta( $kv_page->ID, '_kv_illustration', true );
$kv_tag   = isset( $args['heading'] ) ? $args['heading'] : 'h3';
$kv_title = isset( $args['title'] ) ? $args['title'] : get_the_title( $kv_page );
$kv_url   = ! empty( $args['link'] ) ? get_permalink( $kv_page ) : '';
?>
<div class="kv-price">
	<?php if ( $kv_ill && wp_attachment_is_image( $kv_ill ) ) : ?>
		<div class="kv-price__media">
			<?php
			$kv_alt = (string) get_post_meta( $kv_ill, '_wp_attachment_image_alt', true );
			$kv_att = array( 'class' => 'kv-price__img', 'alt' => '' !== $kv_alt ? $kv_alt : 'Чертёж: ' . get_the_title( $kv_page ) );
			$kv_att += ! empty( $args['first'] ) ? array( 'loading' => false, 'fetchpriority' => 'high' ) : array( 'loading' => 'lazy' );
			echo wp_get_attachment_image( $kv_ill, 'kv-card', false, $kv_att );
			?>
		</div>
	<?php endif; ?>
	<<?php echo tag_escape( $kv_tag ); ?> class="kv-price__title">
		<?php if ( $kv_url ) : ?>
			<a href="<?php echo esc_url( $kv_url ); ?>"><?php echo esc_html( $kv_title ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $kv_title ); ?>
		<?php endif; ?>
	</<?php echo tag_escape( $kv_tag ); ?>>
	<p class="kv-price__value">
		<?php if ( '' !== $kv_price['from'] ) : ?>
			от <strong><?php echo esc_html( $kv_price['from'] ); ?></strong> BYN
		<?php else : ?>
			Цена по запросу
		<?php endif; ?>
	</p>
	<?php if ( $kv_price['includes'] ) : ?>
		<p class="kv-price__label">Что входит:</p>
		<ul class="kv-price__list">
			<?php foreach ( $kv_price['includes'] as $kv_line ) : ?>
				<li><?php echo esc_html( $kv_line ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if ( $kv_url ) : ?>
		<a class="kv-price__more" href="<?php echo esc_url( $kv_url ); ?>" aria-hidden="true" tabindex="-1">Подробнее</a>
	<?php endif; ?>
</div>
