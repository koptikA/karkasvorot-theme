<?php
/**
 * SEO: title, description, canonical, Open Graph, JSON-LD, verification, robots meta, sitemap, redirects.
 * With Yoast / Rank Math active the theme keeps quiet about title/description/canonical/OG/JSON-LD.
 */

defined( 'ABSPATH' ) || exit;

define( 'KV_BRAND', 'KARKASVOROT.BY' );

function kv_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/** Pages that get our meta (everything else is core's or noindex). */
function kv_seo_indexable(): bool {
	return is_front_page() || is_singular() || is_post_type_archive( 'work' );
}

/** Absolute canonical without query; '' outside indexable pages. */
function kv_seo_canonical(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_post_type_archive( 'work' ) ) {
		$url   = get_post_type_archive_link( 'work' );
		$paged = (int) get_query_var( 'paged' );
		return $paged > 1 ? user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged ) : $url;
	}
	return is_singular() ? (string) wp_get_canonical_url() : '';
}

/** Post title; works with a caption already used by an earlier work get « — фото N» so titles stay unique. */
function kv_seo_name( int $id ): string {
	$name = trim( get_post_field( 'post_title', $id ) );
	if ( 'work' !== get_post_type( $id ) ) {
		return $name;
	}
	if ( '' === $name ) {
		return 'Работа №' . $id;
	}
	$same = get_posts( array( 'post_type' => 'work', 'post_status' => 'publish', 'title' => $name, 'fields' => 'ids', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
	$n    = array_search( $id, array_map( 'intval', $same ), true );
	return $n ? $name . ' — фото ' . ( $n + 1 ) : $name;
}

/** First number of the «Цена от» field: «1000 (проём 3×1,5 м)» → 1000, «800–1200» → 800, «1 000» → 1000; '' if none. */
function kv_seo_price( int $id ): string {
	if ( ! kv_public_prices_enabled() ) { return ''; }
	return preg_match( '/\d[\d\x{00A0} ]*(?:[.,]\d+)?/u', kv_service_price( $id )['from'], $m ) ? str_replace( ',', '.', preg_replace( '/[\x{00A0} ]/u', '', $m[0] ) ) : '';
}

/** Plain-text post title for JSON-LD and breadcrumbs (no tags, no HTML entities). */
function kv_seo_plain_title( int $id ): string {
	return html_entity_decode( wp_strip_all_tags( get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' );
}

/** Raw (unescaped) title; '' lets core build it. */
function kv_seo_title(): string {
	$tail = ' | ' . KV_BRAND;
	if ( is_front_page() ) {
		return 'Каркасы ворот на заказ — Минск и вся Беларусь' . $tail;
	}
	if ( is_post_type_archive( 'work' ) ) {
		$paged = (int) get_query_var( 'paged' );
		return 'Наши работы — фото каркасов ворот, калиток и навесов' . ( $paged > 1 ? ' — страница ' . $paged : '' ) . $tail;
	}
	if ( ! is_singular() ) {
		return '';
	}
	$id     = get_queried_object_id();
	$custom = trim( (string) get_post_meta( $id, '_kv_seo_title', true ) );
	if ( '' !== $custom ) {
		return $custom;
	}
	$name = kv_seo_name( $id );
	if ( 'work' === get_post_type( $id ) ) {
		return $name . ' — наши работы' . $tail;
	}
	if ( 'page-service.php' === get_page_template_slug( $id ) ) {
		$from = kv_seo_price( $id );
		$city = false === mb_stripos( $name, 'в Минске' ) ? ' в Минске и по Беларуси' : '';
		return $name . $city . ( '' !== $from ? ' — цена от ' . $from . ' BYN' : '' ) . $tail;
	}
	if ( 'kontakty' === get_post_field( 'post_name', $id ) ) {
		return 'Контакты — заказать каркас ворот в Минске и по Беларуси' . $tail;
	}
	return $name . $tail;
}

/** Raw description: meta → excerpt → first ~155 chars of the text → a fallback with the title. */
function kv_seo_description(): string {
	if ( is_post_type_archive( 'work' ) ) {
		return 'Фото реальных работ ' . KV_BRAND . ': каркасы откатных и распашных ворот, калитки, навесы, заборы и мебель. Изготовление в Минске, доставка каркасов по всей Беларуси.';
	}
	$id   = is_singular() || is_front_page() ? get_queried_object_id() : 0;
	$text = $id ? trim( (string) get_post_meta( $id, '_kv_seo_description', true ) ) : '';
	if ( '' === $text && $id ) {
		$text = has_excerpt( $id ) ? get_post_field( 'post_excerpt', $id ) : get_post_field( 'post_content', $id );
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $text ) ) ) ) );
		if ( mb_strlen( $text ) > 155 ) {
			$text = preg_replace( '/\s+\S*$/u', '', mb_substr( $text, 0, 155 ) ) . '…';
		}
	}
	if ( '' !== $text ) {
		return $text;
	}
	if ( is_front_page() ) {
		return 'Изготовление каркасов откатных и распашных ворот, калиток, мебели и навесов в Минске. Монтаж в Минске и Минской области, доставка каркасов по всей Беларуси.';
	}
	return $id ? kv_seo_name( $id ) . '. ' . KV_BRAND . ' — каркасы ворот и калиток: изготовление в Минске, доставка по всей Беларуси.' : '';
}

/** @return array{url:string,w:int,h:int} og:image. */
function kv_seo_image(): array {
	$id  = is_singular() ? get_queried_object_id() : 0;
	$img = $id ? (int) get_post_thumbnail_id( $id ) : 0;
	if ( ! $img && $id ) {
		$img = (int) get_post_meta( $id, '_kv_illustration', true );
	}
	$src = $img ? wp_get_attachment_image_src( $img, 'full' ) : false;
	return $src ? array( 'url' => $src[0], 'w' => (int) $src[1], 'h' => (int) $src[2] ) : array( 'url' => get_template_directory_uri() . '/assets/og.jpg', 'w' => 1200, 'h' => 630 );
}

/** @return array<int,array{name:string,url:string}> Главная → (Наши работы) → текущая; [] on the front page. */
function kv_breadcrumbs(): array {
	if ( is_front_page() ) {
		return array();
	}
	$crumbs = array( array( 'name' => 'Главная', 'url' => home_url( '/' ) ) );
	if ( is_singular( 'work' ) || is_post_type_archive( 'work' ) ) {
		$crumbs[] = array( 'name' => 'Наши работы', 'url' => get_post_type_archive_link( 'work' ) );
	}
	if ( is_singular() ) {
		$crumbs[] = array( 'name' => kv_seo_plain_title( get_queried_object_id() ), 'url' => kv_seo_canonical() );
	} elseif ( is_search() ) {
		$crumbs[] = array( 'name' => 'Поиск', 'url' => '' );
	} elseif ( is_404() ) {
		$crumbs[] = array( 'name' => 'Страница не найдена', 'url' => '' );
	} elseif ( is_archive() && ! is_post_type_archive( 'work' ) ) {
		$crumbs[] = array( 'name' => html_entity_decode( wp_strip_all_tags( get_the_archive_title() ), ENT_QUOTES, 'UTF-8' ), 'url' => '' );
	}
	return count( $crumbs ) > 1 ? $crumbs : array(); // no current item (e.g. no main query) → no trail

}

/** areaServed for the organization and services (story 19). */
function kv_seo_area(): array {
	return array(
		array( '@type' => 'City', 'name' => 'Минск' ),
		array( '@type' => 'AdministrativeArea', 'name' => 'Минская область' ),
		array( '@type' => 'Country', 'name' => 'Беларусь' ),
	);
}

/** @return array[] JSON-LD blocks for the current page, real data only. */
function kv_seo_schema(): array {
	$out = array();
	$org = home_url( '/#org' );
	if ( is_front_page() ) {
		$node = array( '@context' => 'https://schema.org', '@type' => 'HomeAndConstructionBusiness', '@id' => $org, 'name' => KV_BRAND, 'url' => home_url( '/' ) );
		$map  = array( 'telephone' => 'phone', 'email' => 'email', 'openingHours' => 'hours', 'taxID' => 'unp', 'legalName' => 'org_name' );
		foreach ( $map as $prop => $key ) {
			if ( '' !== kv_opt( $key ) ) {
				$node[ $prop ] = kv_opt( $key );
			}
		}
		if ( '' !== kv_opt( 'address' ) ) {
			$node['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => kv_opt( 'address' ), 'addressCountry' => 'BY' );
		}
		$node['areaServed'] = kv_seo_area();
		$out[]              = $node;
	}
	$id = is_singular( 'page' ) ? get_queried_object_id() : 0;
	if ( $id && ! is_front_page() && 'page-service.php' === get_page_template_slug( $id ) ) {
		$from = kv_seo_price( $id );
		if ( '' !== $from ) {
			$out[] = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'Service',
				'name'       => kv_seo_plain_title( $id ),
				'url'        => kv_seo_canonical(),
				'provider'   => array( '@id' => $org ),
				'areaServed' => kv_seo_area(),
				'offers'     => array(
					'@type'              => 'Offer',
					'priceCurrency'      => 'BYN',
					'priceSpecification' => array( '@type' => 'PriceSpecification', 'minPrice' => (float) $from, 'priceCurrency' => 'BYN' ),
				),
			);
		}
	}
	$items = array();
	foreach ( kv_breadcrumbs() as $i => $crumb ) {
		$item = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $crumb['name'] ) );
		if ( '' !== $crumb['url'] ) {
			$item['item'] = $crumb['url'];
		}
		$items[] = $item;
	}
	if ( $items ) {
		$out[] = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}
	return $out;
}

add_filter( 'pre_get_document_title', function ( $title ) {
	$own = kv_seo_plugin_active() ? '' : kv_seo_title();
	return '' !== $own ? esc_html( $own ) : $title;
} );

add_action( 'wp_head', function () {
	foreach ( array( 'yandex-verification' => 'yandex_verification', 'google-site-verification' => 'google_verification' ) as $name => $key ) {
		if ( '' !== kv_opt( $key ) ) {
			echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( kv_opt( $key ) ) . '">' . "\n";
		}
	}
	if ( kv_seo_plugin_active() ) {
		return;
	}
	remove_action( 'wp_head', 'rel_canonical' );
	foreach ( kv_seo_schema() as $block ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . "</script>\n";
	}
	if ( ! kv_seo_indexable() ) {
		return;
	}
	$title = kv_seo_title();
	if ( '' === $title ) {
		$title = wp_get_document_title();
		$title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
	}
	$desc = kv_seo_description();
	$url  = kv_seo_canonical();
	$img  = kv_seo_image();
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	$og = array(
		'og:type'         => is_singular( 'work' ) ? 'article' : 'website',
		'og:site_name'    => KV_BRAND,
		'og:locale'       => 'ru_RU',
		'og:title'        => $title,
		'og:description'  => $desc,
		'og:url'          => $url,
		'og:image'        => $img['url'],
		'og:image:width'  => (string) $img['w'],
		'og:image:height' => (string) $img['h'],
	);
	foreach ( $og as $prop => $value ) {
		echo '<meta property="' . esc_attr( $prop ) . '" content="' . esc_attr( $value ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 2 );

// noindex for search, 404 and taxonomy/date/author archives; core adds max-image-preview:large elsewhere.
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_search() || is_404() || is_date() || is_tag() || is_category() || is_author() || is_tax() ) {
		unset( $robots['max-image-preview'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );

// Sitemap: pages and works only (core robots.txt already has Disallow /wp-admin/, admin-ajax and Sitemap).
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return in_array( $name, array( 'users', 'taxonomies' ), true ) ? false : $provider;
}, 10, 2 );

add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	if ( ! wp_count_posts( 'post' )->publish ) {
		unset( $types['post'] );
	}
	return $types;
} );

// Duplicates: author archives and ?author=N → home, attachment pages → the file. Before redirect_canonical.
add_action( 'template_redirect', function () {
	if ( is_author() || isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_attachment() ) { // reserve: the installer sets wp_attachment_pages_enabled=0; this covers =1 set later
		$file = wp_get_attachment_url( get_queried_object_id() );
		wp_safe_redirect( $file ? $file : home_url( '/' ), 301 );
		exit;
	}
}, 1 );

// REST user list: guests get 401.
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( 0 === stripos( $request->get_route(), '/wp/v2/users' ) && ! is_user_logged_in() ) {
		return new WP_Error( 'rest_user_cannot_view', 'Нет доступа.', array( 'status' => 401 ) );
	}
	return $result;
}, 10, 3 );

// «SEO» meta box on pages and works.
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'kv_seo', 'SEO', 'kv_seo_metabox', array( 'page', 'work' ), 'normal', 'low' );
} );

function kv_seo_metabox( $post ) {
	wp_nonce_field( 'kv_seo_meta', 'kv_seo_nonce' );
	echo '<p><label for="kv_seo_title"><strong>Title</strong> — пусто: собирается автоматически</label><input class="widefat" type="text" id="kv_seo_title" name="kv_seo_title" value="' . esc_attr( (string) get_post_meta( $post->ID, '_kv_seo_title', true ) ) . '"></p>';
	echo '<p><label for="kv_seo_description"><strong>Description</strong> — 120–160 символов; пусто: начало текста страницы</label><textarea class="widefat" rows="3" id="kv_seo_description" name="kv_seo_description">' . esc_textarea( (string) get_post_meta( $post->ID, '_kv_seo_description', true ) ) . '</textarea></p>';
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['kv_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['kv_seo_nonce'] ) ), 'kv_seo_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = wp_unslash( $_POST );
	kv_set_meta( $post_id, '_kv_seo_title', isset( $in['kv_seo_title'] ) && is_string( $in['kv_seo_title'] ) ? sanitize_text_field( $in['kv_seo_title'] ) : '' );
	kv_set_meta( $post_id, '_kv_seo_description', isset( $in['kv_seo_description'] ) && is_string( $in['kv_seo_description'] ) ? sanitize_textarea_field( $in['kv_seo_description'] ) : '' );
} );
