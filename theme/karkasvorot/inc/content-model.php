<?php
/**
 * Content model: post types work / lead, service-page and work meta boxes, query helpers.
 */

defined( 'ABSPATH' ) || exit;

/** Sections of «Прочие изделия»: anchor => title. */
function kv_other_sections(): array {
	return array(
		'mebel'    => 'Каркасы мебели',
		'obuvnicy' => 'Обувницы',
		'zabory'   => 'Заборы',
		'navesy'   => 'Навесы и беседки',
	);
}

add_action( 'init', function () {
	register_post_type( 'work', array(
		'labels'        => array(
			'name'          => 'Наши работы',
			'singular_name' => 'Работа',
			'menu_name'     => 'Наши работы',
			'all_items'     => 'Все работы',
			'add_new'       => 'Добавить работу',
			'add_new_item'  => 'Новая работа',
			'edit_item'     => 'Редактировать работу',
			'view_item'     => 'Смотреть работу',
			'search_items'  => 'Найти работу',
			'not_found'     => 'Работ не найдено',
		),
		'public'        => true,
		'has_archive'   => 'nashi-raboty',
		'rewrite'       => array( 'slug' => 'nashi-raboty', 'with_front' => false ),
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 21,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'lead', array(
		'labels'              => array(
			'name'          => 'Заявки',
			'singular_name' => 'Заявка',
			'all_items'     => 'Все заявки',
			'edit_item'     => 'Заявка',
			'not_found'     => 'Заявок пока нет',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'query_var'           => false,
		'rewrite'             => false,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 22,
		'supports'            => array( 'title', 'editor' ),
		'map_meta_cap'        => false,
		'capabilities'        => array_fill_keys(
			array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'delete_posts', 'delete_others_posts', 'publish_posts', 'read_private_posts', 'create_posts', 'read' ),
			'manage_options'
		),
	) );
} );

add_action( 'after_switch_theme', function () {
	flush_rewrite_rules( false );
}, 20 );

/** @return WP_Post[] published pages with the «Услуга» template, by menu_order. */
function kv_service_pages(): array {
	return get_posts( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => -1,
		'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => 'page-service.php', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
	) );
}

/** @return array{from:string,includes:string[]} */
function kv_service_price( int $page_id ): array {
	$lines = explode( "\n", (string) get_post_meta( $page_id, '_kv_includes', true ) );
	return array(
		'from'     => (string) get_post_meta( $page_id, '_kv_price_from', true ),
		'includes' => array_values( array_filter( array_map( 'trim', $lines ), 'strlen' ) ),
	);
}

/**
 * @return WP_Post[] published works, optionally of one service page and one «Прочие» section.
 * A work belongs to its main service (_kv_service) and to every page in _kv_also (one meta row per page ID).
 */
function kv_works( int $service_id = 0, int $limit = 12, string $section = '' ): array {
	$meta = array();
	if ( $service_id > 0 ) {
		$meta[] = array(
			'relation' => 'OR',
			array( 'key' => '_kv_service', 'value' => $service_id ),
			array( 'key' => '_kv_also', 'value' => $service_id ),
		); // WP_Query groups by post ID, so a work matching both is returned once
	}
	if ( '' !== $section ) {
		$meta[] = array( 'key' => '_kv_section', 'value' => $section );
	}
	return get_posts( array(
		'post_type'   => 'work',
		'post_status' => 'publish',
		'numberposts' => $limit,
		'meta_query'  => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );
}

/** FAQ editing format: blocks split by an empty line; first line is the question, the rest is the answer. */
function kv_faq_from_text( string $text ): array {
	$items = array();
	foreach ( preg_split( "/\R\s*\R/", trim( $text ) ) as $block ) {
		$lines = preg_split( '/\R/', trim( $block ) );
		$q     = trim( array_shift( $lines ) );
		$a     = trim( implode( "\n", $lines ) );
		if ( '' !== $q && '' !== $a ) {
			$items[] = array( 'q' => $q, 'a' => $a );
		}
	}
	return $items;
}

function kv_faq_to_text( string $json ): string {
	$blocks = array();
	foreach ( (array) json_decode( $json, true ) as $item ) {
		if ( isset( $item['q'], $item['a'] ) ) {
			$blocks[] = $item['q'] . "\n" . $item['a'];
		}
	}
	return implode( "\n\n", $blocks );
}

/** Save or delete one meta value (update_post_meta unslashes, so slash first). */
function kv_set_meta( int $post_id, string $key, string $value ) {
	if ( '' === $value ) {
		delete_post_meta( $post_id, $key );
	} else {
		update_post_meta( $post_id, $key, wp_slash( $value ) );
	}
}

add_action( 'add_meta_boxes_page', function ( $post ) {
	if ( 'page-service.php' === get_page_template_slug( $post ) ) {
		add_meta_box( 'kv_service', 'Услуга: цена и комплектация', 'kv_service_metabox', 'page', 'normal', 'high' );
	}
} );

function kv_service_metabox( $post ) {
	wp_nonce_field( 'kv_service_meta', 'kv_service_nonce' );
	$get    = function ( $key ) use ( $post ) {
		return (string) get_post_meta( $post->ID, $key, true );
	};
	$prices = (array) json_decode( $get( '_kv_prices' ), true );
	$text   = array(
		'kv_price_from' => array( '_kv_price_from', 'Цена от, BYN (пусто — «цена по запросу»)' ),
		'kv_term'       => array( '_kv_term', 'Срок изготовления (пусто — не выводится)' ),
		'kv_warranty'   => array( '_kv_warranty', 'Гарантия (пусто — не выводится)' ),
	);
	echo '<div class="kv-metabox">';
	foreach ( $text as $name => $f ) {
		echo '<p><label for="' . esc_attr( $name ) . '"><strong>' . esc_html( $f[1] ) . '</strong></label><br><input class="widefat" type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $get( $f[0] ) ) . '"></p>';
	}
	echo '<p><label for="kv_includes"><strong>Что входит</strong> — по одному пункту в строке</label><textarea class="widefat" rows="4" id="kv_includes" name="kv_includes">' . esc_textarea( $get( '_kv_includes' ) ) . '</textarea></p>';
	$ill = (int) $get( '_kv_illustration' );
	echo '<p><label for="kv_illustration"><strong>Иллюстрация</strong> — ID картинки из «Медиафайлов» (0 — без картинки)</label><br><input type="number" min="0" id="kv_illustration" name="kv_illustration" value="' . esc_attr( (string) $ill ) . '"> ' . ( $ill ? wp_get_attachment_image( $ill, 'thumbnail' ) : '' ) . '</p>';
	echo '<p><label for="kv_faq"><strong>Вопросы и ответы</strong> — первая строка вопрос, ниже ответ; между парами пустая строка</label><textarea class="widefat" rows="8" id="kv_faq" name="kv_faq">' . esc_textarea( kv_faq_to_text( $get( '_kv_faq' ) ) ) . '</textarea></p>';
	echo '<fieldset><legend><strong>Цены разделов</strong> — только для страницы «Прочие изделия», BYN от (пусто — «по запросу»)</legend>';
	foreach ( kv_other_sections() as $anchor => $title ) {
		$value = isset( $prices[ $anchor ] ) ? (string) $prices[ $anchor ] : '';
		echo '<p><label for="kv_prices_' . esc_attr( $anchor ) . '">' . esc_html( $title ) . '</label><br><input type="text" id="kv_prices_' . esc_attr( $anchor ) . '" name="kv_prices[' . esc_attr( $anchor ) . ']" value="' . esc_attr( $value ) . '"></p>';
	}
	echo '</fieldset></div>';
}

add_action( 'save_post_page', function ( $post_id ) {
	if ( ! isset( $_POST['kv_service_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['kv_service_nonce'] ) ), 'kv_service_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$in  = wp_unslash( $_POST );
	$str = function ( $name ) use ( $in ) {
		return isset( $in[ $name ] ) && is_string( $in[ $name ] ) ? $in[ $name ] : '';
	};
	kv_set_meta( $post_id, '_kv_price_from', sanitize_text_field( $str( 'kv_price_from' ) ) );
	kv_set_meta( $post_id, '_kv_term', sanitize_text_field( $str( 'kv_term' ) ) );
	kv_set_meta( $post_id, '_kv_warranty', sanitize_text_field( $str( 'kv_warranty' ) ) );
	kv_set_meta( $post_id, '_kv_includes', sanitize_textarea_field( $str( 'kv_includes' ) ) );
	$ill = absint( $str( 'kv_illustration' ) );
	kv_set_meta( $post_id, '_kv_illustration', $ill ? (string) $ill : '' );
	$faq = kv_faq_from_text( sanitize_textarea_field( $str( 'kv_faq' ) ) );
	kv_set_meta( $post_id, '_kv_faq', $faq ? wp_json_encode( $faq, JSON_UNESCAPED_UNICODE ) : '' );
	$raw    = isset( $in['kv_prices'] ) && is_array( $in['kv_prices'] ) ? $in['kv_prices'] : array();
	$prices = array();
	foreach ( array_keys( kv_other_sections() ) as $anchor ) {
		$prices[ $anchor ] = isset( $raw[ $anchor ] ) && is_string( $raw[ $anchor ] ) ? sanitize_text_field( $raw[ $anchor ] ) : '';
	}
	kv_set_meta( $post_id, '_kv_prices', array_filter( $prices, 'strlen' ) ? wp_json_encode( $prices, JSON_UNESCAPED_UNICODE ) : '' );
} );

add_action( 'add_meta_boxes_work', function () {
	add_meta_box( 'kv_work', 'Работа: услуги и город', 'kv_work_metabox', 'work', 'side', 'default' );
} );

function kv_work_metabox( $post ) {
	wp_nonce_field( 'kv_work_meta', 'kv_work_nonce' );
	$service = (int) get_post_meta( $post->ID, '_kv_service', true );
	$section = (string) get_post_meta( $post->ID, '_kv_section', true );
	echo '<p><label for="kv_service">Услуга</label><select class="widefat" id="kv_service" name="kv_service"><option value="0">— не выбрана —</option>';
	foreach ( kv_service_pages() as $page ) {
		echo '<option value="' . esc_attr( (string) $page->ID ) . '"' . selected( $service, $page->ID, false ) . '>' . esc_html( get_the_title( $page ) ) . '</option>';
	}
	$also = array_map( 'intval', get_post_meta( $post->ID, '_kv_also' ) );
	echo '</select></p><fieldset><legend>Также показывать в…</legend>';
	foreach ( kv_service_pages() as $page ) {
		echo '<label style="display:block"><input type="checkbox" name="kv_also[]" value="' . esc_attr( (string) $page->ID ) . '"' . checked( in_array( $page->ID, $also, true ), true, false ) . '> ' . esc_html( get_the_title( $page ) ) . '</label>';
	}
	echo '</fieldset><p><label for="kv_section">Раздел «Прочих изделий»</label><select class="widefat" id="kv_section" name="kv_section"><option value="">— нет —</option>';
	foreach ( kv_other_sections() as $anchor => $title ) {
		echo '<option value="' . esc_attr( $anchor ) . '"' . selected( $section, $anchor, false ) . '>' . esc_html( $title ) . '</option>';
	}
	echo '</select></p><p><label for="kv_city">Город / населённый пункт</label><input class="widefat" type="text" id="kv_city" name="kv_city" value="' . esc_attr( (string) get_post_meta( $post->ID, '_kv_city', true ) ) . '"></p>';
}

add_action( 'save_post_work', function ( $post_id ) {
	if ( ! isset( $_POST['kv_work_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['kv_work_nonce'] ) ), 'kv_work_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in      = wp_unslash( $_POST );
	$service = isset( $in['kv_service'] ) ? absint( $in['kv_service'] ) : 0;
	$section = isset( $in['kv_section'] ) && array_key_exists( $in['kv_section'], kv_other_sections() ) ? $in['kv_section'] : '';
	kv_set_meta( $post_id, '_kv_service', $service ? (string) $service : '' );
	kv_set_meta( $post_id, '_kv_section', $section );
	$posted = isset( $in['kv_also'] ) && is_array( $in['kv_also'] ) ? array_map( 'absint', $in['kv_also'] ) : array();
	$also   = array_diff( array_intersect( wp_list_pluck( kv_service_pages(), 'ID' ), $posted ), array( $service ) );
	delete_post_meta( $post_id, '_kv_also' );
	foreach ( $also as $page_id ) {
		add_post_meta( $post_id, '_kv_also', (string) $page_id );
	}
	kv_set_meta( $post_id, '_kv_city', isset( $in['kv_city'] ) && is_string( $in['kv_city'] ) ? sanitize_text_field( $in['kv_city'] ) : '' );
} );
