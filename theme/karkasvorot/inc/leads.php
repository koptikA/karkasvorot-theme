<?php
/**
 * Leads: order form (once per page), admin_post(_nopriv)_kv_order handler (PRG), lead list columns, cookie banner.
 * No nonce on purpose (page cache breaks it); spam is cut by the kv_hp trap and the kv_ts minimum fill time.
 */

defined( 'ABSPATH' ) || exit;

/** Product options of the form: value = label. */
function kv_lead_products(): array {
	return array( 'Каркас откатных ворот', 'Каркас распашных ворот', 'Калитка', 'Каркас мебели', 'Обувница', 'Забор', 'Навес или беседка', 'Другое' );
}

/** Product preselected by page slug when the caller gave none. */
function kv_lead_product_for_page(): string {
	$map = array(
		'karkas-otkatnyh-vorot'   => 'Каркас откатных ворот',
		'karkas-raspashnyh-vorot' => 'Каркас распашных ворот',
		'kalitki'                 => 'Калитка',
	);
	$post = get_queried_object();
	return ( $post instanceof WP_Post && isset( $map[ $post->post_name ] ) ) ? $map[ $post->post_name ] : '';
}

/**
 * Order form <form id="zayavka">. Idempotent: the second call on the same page prints nothing.
 * $args['product']: one of kv_lead_products() to preselect.
 */
function kv_order_form( array $args = array( 'product' => '' ) ): void {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$status = isset( $_GET['zayavka'] ) ? sanitize_key( wp_unslash( $_GET['zayavka'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$state  = array();
	if ( 'err' === $status && isset( $_GET['kv_req'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$state = get_transient( 'kv_lead_' . kv_lead_req( wp_unslash( $_GET['kv_req'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$sec   = isset( $_COOKIE['kv_lead_sec'] ) ? (string) wp_unslash( $_COOKIE['kv_lead_sec'] ) : '';
		// Entered values go back only to the sender's browser (cookie secret set on POST), not to anyone with the link.
		$state = ( is_array( $state ) && '' !== $sec && isset( $state['sec'] ) && hash_equals( (string) $state['sec'], wp_hash( $sec ) ) ) ? $state : array();
	}
	$product = isset( $args['product'] ) ? (string) $args['product'] : '';
	get_template_part( 'template-parts/order-form', null, array(
		'status' => $status,
		'values' => isset( $state['values'] ) ? $state['values'] : array( 'product' => '' !== $product ? $product : kv_lead_product_for_page() ),
		'errors' => isset( $state['errors'] ) ? $state['errors'] : array(),
	) );
}

// Every public page gets the form right before the footer; templates that called kv_order_form() already keep theirs.
add_action( 'get_footer', function () {
	kv_order_form();
} );

/** Request id: letters, digits, dashes; max 64. */
function kv_lead_req( $raw ): string {
	return substr( preg_replace( '/[^A-Za-z0-9-]/', '', (string) $raw ), 0, 64 );
}

/** Belarus phone → +375XXXXXXXXX, or '' if not recognised. Accepts 80…, 375…, +375…, spaces, brackets, dashes. */
function kv_normalize_phone( string $raw ): string {
	$raw = trim( $raw );
	if ( ! preg_match( '/^\+?[\d\s()\-]+$/', $raw ) ) {
		return '';
	}
	$digits = preg_replace( '/\D/', '', $raw );
	if ( preg_match( '/^80(\d{9})$/', $digits, $m ) || preg_match( '/^375(\d{9})$/', $digits, $m ) ) {
		return '+375' . $m[1];
	}
	return '';
}

/** Error PRG: values + errors in a 10-min transient bound to a per-browser cookie secret; redirect back with zayavka=err. */
function kv_lead_fail( string $req, array $values, array $errors, string $back ) {
	$sec = wp_generate_password( 32, false );
	setcookie( 'kv_lead_sec', $sec, time() + 10 * MINUTE_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	set_transient( 'kv_lead_' . $req, array( 'values' => $values, 'errors' => $errors, 'sec' => wp_hash( $sec ) ), 10 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( array( 'zayavka' => 'err', 'kv_req' => $req ), $back ) . '#zayavka', 303 );
	exit;
}

function kv_lead_handle() {
	$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- no nonce by design, see header.
	$get  = function ( $key, $max ) use ( $post ) {
		return isset( $post[ $key ] ) && is_string( $post[ $key ] ) ? mb_substr( sanitize_textarea_field( $post[ $key ] ), 0, $max ) : '';
	};

	$back = isset( $post['kv_back'] ) && is_string( $post['kv_back'] ) ? $post['kv_back'] : '';
	$back = wp_validate_redirect( esc_url_raw( $back ), home_url( '/' ) );
	$back = remove_query_arg( array( 'zayavka', 'kv_req' ), '' !== $back ? $back : home_url( '/' ) );
	$req  = kv_lead_req( isset( $post['kv_req'] ) ? $post['kv_req'] : '' );
	$req  = '' !== $req ? $req : wp_generate_uuid4();
	$ok   = add_query_arg( 'zayavka', 'ok', $back ) . '#zayavka';

	// Bots: trap filled or form sent faster than 3 s → silent "success", nothing stored.
	$ts = isset( $post['kv_ts'] ) ? (int) $post['kv_ts'] : 0;
	if ( '' !== $get( 'kv_hp', 200 ) || ! $ts || time() - $ts < 3 ) {
		wp_safe_redirect( $ok, 303 );
		exit;
	}

	$values = array(
		'name'    => $get( 'kv_name', 100 ),
		'phone'   => $get( 'kv_phone', 40 ),
		'product' => in_array( $get( 'kv_product', 100 ), kv_lead_products(), true ) ? $get( 'kv_product', 100 ) : '',
		'comment' => $get( 'kv_comment', 2000 ),
		'consent' => '' !== $get( 'kv_consent', 10 ),
	);
	$phone  = kv_normalize_phone( $values['phone'] );
	$errors = array();
	if ( '' === $values['phone'] ) {
		$errors['phone'] = 'Укажите телефон, чтобы мы могли перезвонить';
	} elseif ( '' === $phone ) {
		$errors['phone'] = 'Укажите телефон в формате +375 XX XXX-XX-XX';
	}
	if ( ! $values['consent'] ) {
		$errors['consent'] = 'Отметьте согласие на обработку персональных данных';
	}
	if ( $errors ) {
		kv_lead_fail( $req, $values, $errors, $back );
	}

	// Resubmit = same kv_req AND same phone. kv_req alone is not enough: a cached page gives every visitor the same one.
	$dup = get_posts( array(
		'post_type'   => 'lead',
		'post_status' => 'any',
		'fields'      => 'ids',
		'numberposts' => 1,
		'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array( 'key' => '_kv_req', 'value' => $req ),
			array( 'key' => '_kv_phone', 'value' => $phone ),
		),
	) );
	if ( $dup ) {
		wp_safe_redirect( $ok, 303 );
		exit;
	}

	$time    = current_time( 'mysql' );
	$product = '' !== $values['product'] ? $values['product'] : 'не указано';
	$lines   = array(
		'Имя: ' . ( '' !== $values['name'] ? $values['name'] : 'не указано' ),
		'Телефон: ' . $phone,
		'Изделие: ' . $product,
		'Комментарий: ' . ( '' !== $values['comment'] ? $values['comment'] : '—' ),
		'Страница: ' . $back,
		'Время: ' . $time,
	);
	// Saved BEFORE the mail: a lead survives a failed wp_mail. IP is not stored.
	$id = wp_insert_post( wp_slash( array(
		'post_type'    => 'lead',
		'post_status'  => 'publish',
		'post_title'   => 'Заявка: ' . $phone,
		'post_content' => implode( "\n", $lines ),
		'meta_input'   => array(
			'_kv_name'    => $values['name'],
			'_kv_phone'   => $phone,
			'_kv_product' => $values['product'],
			'_kv_comment' => $values['comment'],
			'_kv_page'    => $back,
			'_kv_time'    => $time,
			'_kv_req'     => $req,
		),
	) ), true );
	if ( is_wp_error( $id ) ) {
		kv_lead_fail( $req, $values, array( 'form' => 'Не удалось отправить заявку. Позвоните нам, пожалуйста.' ), $back );
	}

	$to = kv_opt( 'email' );
	$to = is_email( $to ) ? $to : get_option( 'admin_email' );
	wp_mail( $to, 'Заявка с сайта: ' . $phone . ' — ' . $product, implode( "\n", $lines ) . "\n\nВсе заявки: " . admin_url( 'edit.php?post_type=lead' ) );

	wp_safe_redirect( $ok, 303 );
	exit;
}
add_action( 'admin_post_nopriv_kv_order', 'kv_lead_handle' );
add_action( 'admin_post_kv_order', 'kv_lead_handle' );

// Admin list: Телефон, Изделие, Дата.
add_filter( 'manage_lead_posts_columns', function ( $cols ) {
	return array(
		'cb'         => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'      => 'Заявка',
		'kv_phone'   => 'Телефон',
		'kv_product' => 'Изделие',
		'date'       => 'Дата',
	);
} );
add_action( 'manage_lead_posts_custom_column', function ( $col, $id ) {
	if ( 'kv_phone' === $col ) {
		$phone = get_post_meta( $id, '_kv_phone', true );
		echo '<a href="' . esc_url( 'tel:' . $phone, array( 'tel' ) ) . '">' . esc_html( $phone ) . '</a>';
	} elseif ( 'kv_product' === $col ) {
		echo esc_html( get_post_meta( $id, '_kv_product', true ) ?: '—' );
	}
}, 10, 2 );

add_action( 'wp_footer', function () {
	if ( '' !== kv_opt( 'metrika_id' ) ) { // no Metrika → no cookies to ask about
		get_template_part( 'template-parts/cookie-banner' );
	}
}, 5 );

// ?zayavka=ok|err pages carry one visitor's result: never cache them (headers go out before any output).
add_action( 'template_redirect', function () {
	if ( isset( $_GET['zayavka'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		nocache_headers();
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'kv-form', get_template_directory_uri() . '/assets/form.css', array( 'kv-site' ), KV_VERSION );
} );
