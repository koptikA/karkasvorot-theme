<?php
/** Owner revision: catalogue illustrations, descriptive work names, no public prices. */
defined( 'ABSPATH' ) || exit;

function kv_public_prices_enabled(): bool { return false; }

/** Legacy attachments stay intact; the public derivative has a meaningful filename. */
function kv_catalog_images(): array {
    return array(
        'kovanye-vorota-s-kirpichnymi-stolbami.jpg' => array( 'kovanye-vorota-s-kirpichnymi-stolbami', 'Кованые ворота с калиткой и кирпичными столбами' ),
        'frame-125.jpg' => array( 'vorota-s-kalitkoy-iz-metalloshtaketnika', 'Ворота с калиткой из металлоштакетника' ),
        'raspashnye-vorota-s-kalitkoj.png' => array( 'zelenyy-zabor-iz-profnastila-s-kalitkoy', 'Зелёный забор из профнастила с калиткой' ),
        'zabor-iz-profnastila-bordo-cena.jpg' => array( 'bordovyy-zabor-iz-profnastila', 'Бордовый забор из профнастила' ),
        'naves-dlya-mashin-karkas-vorot-by.jpg' => array( 'metallicheskiy-naves-dlya-avtomobilya', 'Металлический навес для автомобиля' ),
        'sadovaya-bosedka-barbekyu-karkas.jpg' => array( 'sadovaya-besedka-s-mangalom', 'Садовая беседка с мангалом' ),
        'vorota.png' => array( 'karkas-otkatnyh-vorot', 'Иллюстрация каркаса откатных ворот' ),
        'raspashnye-vorota.png' => array( 'karkas-raspashnyh-vorot', 'Иллюстрация каркаса распашных ворот' ),
        'kalitka.png' => array( 'karkas-kalitki', 'Иллюстрация каркаса калитки' ),
        'tumba.png' => array( 'karkasy-mebeli', 'Иллюстрация металлических каркасов мебели' ),
        'slider1.jpg' => array( 'vorota-iz-svetlogo-metalloshtaketnika', 'Ворота из светлого металлоштакетника' ),
        'slider2.jpg' => array( 'metallicheskie-vorota-s-dekorativnymi-pikami', 'Металлические ворота с декоративными пиками' ),
        'slider4.png' => array( 'metallicheskie-vorota-s-vertikalnymi-prutyami', 'Металлические ворота с вертикальными прутьями' ),
        'slider7.jpg' => array( 'geometricheskiy-karkas-mebeli', 'Геометрический металлический каркас мебели' ),
        'slider8.jpg' => array( 'geometricheskiy-karkas-stola', 'Геометрический металлический каркас стола' ),
        'slider9.jpg' => array( 'metallicheskiy-karkas-mebeli', 'Металлический каркас мебели' ),
        'slider10.jpg' => array( 'svarnaya-obuvnitsa', 'Сварная металлическая обувница' ),
    );
}

function kv_catalog_image( int $id ): array {
    $file = wp_basename( (string) get_post_meta( $id, '_wp_attached_file', true ) );
    $map = kv_catalog_images();
    if ( ! isset( $map[ $file ] ) ) { return array(); }
    $rel = '/assets/catalog/' . $map[ $file ][0] . '.webp';
    $path = get_template_directory() . $rel;
    if ( ! is_file( $path ) ) { return array(); }
    $size = getimagesize( $path );
    return $size ? array( 'src' => get_template_directory_uri() . $rel, 'width' => $size[0], 'height' => $size[1], 'alt' => $map[ $file ][1] ) : array();
}

add_filter( 'wp_get_attachment_image_src', function ( $image, $id ) {
    $asset = kv_catalog_image( (int) $id );
    return $asset ? array( $asset['src'], $asset['width'], $asset['height'], false ) : $image;
}, 10, 2 );
add_filter( 'wp_calculate_image_srcset', function ( $sources, $size, $src, $meta, $id ) {
    return kv_catalog_image( (int) $id ) ? false : $sources;
}, 10, 5 );
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
    $asset = kv_catalog_image( (int) $attachment->ID );
    if ( $asset ) { $attr['alt'] = $asset['alt']; }
    return $attr;
}, 10, 2 );

/** Only machine-imported names are replaced; owner-written names remain intact. */
function kv_refresh_imported_work_names() {
    $map = kv_catalog_images();
    foreach ( get_posts( array( 'post_type' => 'work', 'post_status' => 'any', 'numberposts' => -1 ) ) as $work ) {
        $source = (int) get_post_meta( $work->ID, '_kv_source', true );
        $file = wp_basename( (string) get_post_meta( $source, '_wp_attached_file', true ) );
        if ( isset( $map[ $file ] ) && preg_match( '/^slider\d+$/i', trim( $work->post_title ) ) ) {
            // WordPress records _wp_old_slug and redirects existing work links.
            wp_update_post( wp_slash( array( 'ID' => $work->ID, 'post_title' => $map[ $file ][1], 'post_name' => $map[ $file ][0] ) ) );
        }
    }
}
add_action( 'after_switch_theme', 'kv_refresh_imported_work_names', 20 );

/** Remove historic amount-based FAQ answers without destroying the editable source. */
function kv_public_faq_answer( string $answer ): string {
    if ( ! kv_public_prices_enabled() && preg_match( '/\d[\d\s.,–-]*\s*(?:BYN|руб)/iu', $answer ) ) {
        return 'Стоимость рассчитывается индивидуально по размерам, материалам и комплектации. Оставьте заявку — мастер уточнит детали и подготовит расчёт.';
    }
    return $answer;
}

/** Concise display names for legacy imported works; owner-written titles stay intact. */
add_filter( 'the_title', function ( $title, $id ) {
    if ( 'work' !== get_post_type( $id ) ) { return $title; }
    $source = (int) get_post_meta( $id, '_kv_source', true );
    if ( ! $source ) { return $title; }
    $file = wp_basename( (string) get_post_meta( $source, '_wp_attached_file', true ) );
    $names = array(
        'kovanye-vorota-s-kirpichnymi-stolbami.jpg' => 'Кованые ворота с калиткой',
        'frame-125.jpg' => 'Ворота из металлоштакетника',
        'raspashnye-vorota-s-kalitkoj.png' => 'Забор с калиткой',
        'zabor-iz-profnastila-bordo-cena.jpg' => 'Забор из профнастила',
        'naves-dlya-mashin-karkas-vorot-by.jpg' => 'Навес для автомобиля',
        'sadovaya-bosedka-barbekyu-karkas.jpg' => 'Беседка с мангалом',
        'slider1.jpg' => 'Ворота из металлоштакетника',
        'slider2.jpg' => 'Ворота с декоративными пиками',
        'slider4.png' => 'Металлические ворота',
        'slider7.jpg' => 'Каркас мебели',
        'slider8.jpg' => 'Каркас стола',
        'slider9.jpg' => 'Каркас мебели',
        'slider10.jpg' => 'Обувница',
    );
    if ( ! isset( $names[ $file ] ) ) { return $title; }
    $images = kv_catalog_images();
    $legacy = array(
        (string) get_post_field( 'post_title', $source, 'raw' ),
        trim( (string) get_post_meta( $source, '_wp_attachment_image_alt', true ), " \"'«»" ),
        isset( $images[ $file ] ) ? $images[ $file ][1] : '',
    );
    return in_array( (string) get_post_field( 'post_title', $id, 'raw' ), $legacy, true ) ? $names[ $file ] : $title;
}, 10, 2 );

/** Theme-drawn illustration for a service page without an attachment drawing ('' when none). */
function kv_page_drawing( WP_Post $page, string $class, bool $eager = false ): string {
	$map = array( 'navesy-dlya-avto' => array( 'karkas-navesa-dlya-avto', 'Иллюстрация каркаса навеса для авто' ) );
	if ( ! isset( $map[ $page->post_name ] ) ) {
		return '';
	}
	$rel  = '/assets/catalog/' . $map[ $page->post_name ][0] . '.webp';
	$size = is_file( get_template_directory() . $rel ) ? getimagesize( get_template_directory() . $rel ) : false;
	if ( ! $size ) {
		return '';
	}
	return sprintf( '<img class="%s" src="%s" width="%d" height="%d" alt="%s"%s>', esc_attr( $class ), esc_url( get_template_directory_uri() . $rel ), $size[0], $size[1], esc_attr( $map[ $page->post_name ][1] ), $eager ? ' fetchpriority="high"' : ' loading="lazy"' );
}
