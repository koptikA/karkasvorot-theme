<?php
/**
 * Breadcrumbs from kv_breadcrumbs() (module seo); nothing when empty.
 */
$kv_crumbs = kv_breadcrumbs();
if ( ! $kv_crumbs ) {
	return;
}
$kv_last = count( $kv_crumbs ) - 1;
?>
<nav class="kv-crumbs" aria-label="Хлебные крошки">
	<ol class="kv-crumbs__list">
		<?php foreach ( $kv_crumbs as $kv_i => $kv_crumb ) : ?>
			<li class="kv-crumbs__item">
				<?php if ( $kv_i === $kv_last ) : ?>
					<span aria-current="page"><?php echo esc_html( $kv_crumb['name'] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $kv_crumb['url'] ); ?>"><?php echo esc_html( $kv_crumb['name'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
