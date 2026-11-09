<?php
/**
 * SEO — STUB (filled by task 04): head meta, JSON-LD, robots, sitemap, noindex, redirects.
 */

defined( 'ABSPATH' ) || exit;

/** @return array<int,array{name:string,url:string}> */
function kv_breadcrumbs(): array {
	return array();
}

function kv_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}
