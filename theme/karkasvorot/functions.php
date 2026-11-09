<?php
/**
 * KARKASVOROT theme bootstrap. PHP 7.4+, WP 6.4+.
 */

defined( 'ABSPATH' ) || exit;

define( 'KV_VERSION', '1.0.0' );
define( 'KV_DIR', get_template_directory() );

foreach ( array( 'settings', 'content-model', 'installer', 'leads', 'seo' ) as $kv_module ) {
	require_once KV_DIR . '/inc/' . $kv_module . '.php';
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'kv-card', 640, 480, true );
	add_image_size( 'kv-wide', 1280, 720, true );
	register_nav_menus( array(
		'primary' => 'Главное меню',
		'footer'  => 'Меню в подвале',
	) );
} );

// New uploads: JPEG/PNG sub-sizes as WebP (WP keeps the original format when the editor lacks WebP).
add_filter( 'image_editor_output_format', function ( $formats ) {
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

// «Наши работы» has no pagination (archive-work.php lists all works): /nashi-raboty/page/N/ is a 404, not a duplicate.
add_action( 'template_redirect', function () {
	global $wp_query;
	if ( is_post_type_archive( 'work' ) && (int) get_query_var( 'paged' ) > 1 ) {
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, 0 );

add_action( 'wp_enqueue_scripts', function () {
	$uri = get_template_directory_uri() . '/assets/';
	wp_enqueue_style( 'kv-site', $uri . 'site.css', array(), KV_VERSION );
	wp_enqueue_script( 'kv-site', $uri . 'site.js', array(), KV_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
} );

add_action( 'wp_head', function () {
	// Discover the first-screen photograph before styles finish loading.
	if ( is_front_page() ) {
		echo '<link rel="preload" as="image" fetchpriority="high" href="' . esc_url( get_template_directory_uri() . '/assets/work-gates.webp' ) . '">' . "\n";
	}
	$font = get_template_directory_uri() . '/assets/fonts/manrope-cyrillic.woff2';
	echo '<link rel="preload" href="' . esc_url( $font ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
}, 1 );

/**
 * Contact link: $type phone|viber|telegram. Empty setting → ''. Carries data-goal for Metrika.
 */
function kv_contact_link( string $type, string $class, string $label = '' ): string {
	$links = array(
		'phone'    => array( kv_phone_href(), kv_opt( 'phone' ), 'phone_click' ),
		'viber'    => array( kv_opt( 'viber' ), 'Viber', 'viber_click' ),
		'telegram' => array( kv_telegram_href(), 'Telegram', 'telegram_click' ),
	);
	if ( ! isset( $links[ $type ] ) || '' === $links[ $type ][0] ) {
		return '';
	}
	list( $href, $text, $goal ) = $links[ $type ];
	$external = 'telegram' === $type ? ' target="_blank" rel="noopener"' : '';
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $href, array( 'tel', 'viber', 'https' ) ) . '" data-goal="' . esc_attr( $goal ) . '"' . $external . '>' . esc_html( '' !== $label ? $label : $text ) . '</a>';
}

/**
 * Image alt: the media library alt, else $fallback (the work / page name).
 */
function kv_image_alt( int $attachment_id, string $fallback ): string {
	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	return '' !== $alt ? $alt : wp_strip_all_tags( $fallback );
}

/**
 * Menu fallback: service pages + works + contacts, so the header is never empty before menus exist.
 */
function kv_menu_fallback() {
	$links = array();
	foreach ( kv_service_pages() as $page ) {
		$links[ get_permalink( $page ) ] = get_the_title( $page );
	}
	$links[ get_post_type_archive_link( 'work' ) ] = 'Наши работы';
	$contacts = get_page_by_path( 'kontakty' );
	if ( $contacts ) {
		$links[ get_permalink( $contacts ) ] = get_the_title( $contacts );
	}
	echo '<ul class="kv-menu">';
	foreach ( $links as $url => $title ) {
		echo '<li class="kv-menu__item"><a class="kv-menu__link" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Footer field: value for visitors; a hint for admins when the setting is empty; nothing otherwise.
 */
function kv_footer_field( string $key, string $label ) {
	$value = kv_opt( $key );
	if ( '' !== $value ) {
		$out = 'email' === $key ? '<a href="' . esc_url( 'mailto:' . antispambot( $value ) ) . '">' . esc_html( antispambot( $value ) ) . '</a>' : esc_html( $value );
		echo '<li class="kv-footer__item"><span class="kv-footer__label">' . esc_html( $label ) . ':</span> ' . $out . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
	} elseif ( kv_is_placeholder( $key ) && current_user_can( 'manage_options' ) ) {
		$fields  = kv_settings_fields();
		$section = 'kv_contacts' === $fields[ $key ][0] ? 'Контакты' : 'Реквизиты';
		echo '<li class="kv-footer__item kv-footer__item--empty">' . esc_html( $label . ' — Заполните: Внешний вид → Настроить → ' . $section ) . '</li>';
	}
}
