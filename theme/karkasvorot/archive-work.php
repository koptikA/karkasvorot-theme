<?php
/**
 * «Наши работы» (/nashi-raboty/): grid with captions, filter by service with plain links (?usluga=<slug>).
 */
get_header();
$kv_services = kv_service_pages();
$kv_current  = isset( $_GET['usluga'] ) ? sanitize_title( wp_unslash( $_GET['usluga'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- read-only filter.
$kv_service  = 0;
foreach ( $kv_services as $kv_page ) {
	if ( $kv_page->post_name === $kv_current ) {
		$kv_service = $kv_page->ID;
	}
}
// ponytail: all works on one page, no pagination; add paging when there are more than ~60 works.
$kv_works = kv_works( $kv_service, -1 );
$kv_base  = get_post_type_archive_link( 'work' );
?>
<div class="kv-container kv-section">
	<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
	<h1 class="kv-title">Наши работы</h1>
	<?php if ( $kv_services ) : ?>
		<nav class="kv-filter" aria-label="Работы по услугам">
			<a class="kv-filter__link" href="<?php echo esc_url( $kv_base ); ?>"<?php echo $kv_service ? '' : ' aria-current="page"'; ?>>Все</a>
			<?php foreach ( $kv_services as $kv_page ) : ?>
				<?php if ( $kv_service !== $kv_page->ID && ! kv_works( $kv_page->ID, 1 ) ) { continue; } // no works yet → no dead-end filter ?>
				<a class="kv-filter__link" href="<?php echo esc_url( add_query_arg( 'usluga', $kv_page->post_name, $kv_base ) ); ?>"<?php echo $kv_service === $kv_page->ID ? ' aria-current="page"' : ''; ?>><?php echo esc_html( get_the_title( $kv_page ) ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<?php if ( $kv_works ) : ?>
		<div class="kv-works__grid">
			<?php
			foreach ( $kv_works as $kv_work ) {
				get_template_part( 'template-parts/work-card', null, array( 'work' => $kv_work ) );
			}
			?>
		</div>
	<?php else : ?>
		<p class="kv-empty">Скоро добавим новые работы. <a href="#zayavka">Оставьте заявку</a> — мастер перезвонит и всё обсудит.</p>
	<?php endif; ?>
	<?php kv_order_form(); ?>
</div>
<?php
get_footer();
