<?php
/**
 * Business data in the Customizer: «Контакты», «Реквизиты», «Аналитика».
 * Stored as theme_mod "kv_<key>".
 */

defined( 'ABSPATH' ) || exit;

/** Fields: key => [section, label, default, sanitize]. Contacts default to the old site. */
function kv_settings_fields(): array {
	return array(
		'phone'               => array( 'kv_contacts', 'Телефон', '+375 44 772-68-23', 'sanitize_text_field' ),
		'viber'               => array( 'kv_contacts', 'Viber (ссылка viber://…)', 'viber://chat?number=%2B375447726823', 'kv_sanitize_viber' ),
		'telegram'            => array( 'kv_contacts', 'Telegram (имя пользователя без @)', 'Maksimus05061981', 'kv_sanitize_telegram' ),
		'email'               => array( 'kv_contacts', 'E-mail для заявок (пусто — e-mail администратора)', '', 'sanitize_email' ),
		'hours'               => array( 'kv_contacts', 'Часы работы', '', 'sanitize_text_field' ),
		'org_name'            => array( 'kv_requisites', 'Наименование (ИП / организация)', '', 'sanitize_text_field' ),
		'unp'                 => array( 'kv_requisites', 'УНП', '', 'kv_sanitize_digits' ),
		'address'             => array( 'kv_requisites', 'Юридический адрес', '', 'sanitize_text_field' ),
		'registration'        => array( 'kv_requisites', 'Регистрация (кем и когда)', '', 'sanitize_text_field' ),
		'metrika_id'          => array( 'kv_analytics', 'ID счётчика Яндекс Метрики', '', 'kv_sanitize_digits' ),
		'yandex_verification' => array( 'kv_analytics', 'Код Яндекс Вебмастера (значение content)', '', 'kv_sanitize_token' ),
		'google_verification' => array( 'kv_analytics', 'Код Google Search Console (значение content)', '', 'kv_sanitize_token' ),
	);
}

function kv_sanitize_viber( $value ) {
	return esc_url_raw( trim( (string) $value ), array( 'viber', 'https' ) );
}

function kv_sanitize_telegram( $value ) {
	$value = preg_replace( '~^(https?://)?(t\.me|telegram\.me)/~i', '', trim( (string) $value ) );
	return preg_replace( '/[^A-Za-z0-9_]/', '', ltrim( $value, '@' ) );
}

function kv_sanitize_digits( $value ) {
	return preg_replace( '/\D/', '', (string) $value );
}

function kv_sanitize_token( $value ) {
	return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $value );
}

/** Setting value; the built-in default while never saved; $default when both are empty. */
function kv_opt( string $key, string $default = '' ): string {
	$fields = kv_settings_fields();
	$value  = (string) get_theme_mod( 'kv_' . $key, isset( $fields[ $key ] ) ? $fields[ $key ][2] : '' );
	return '' !== $value ? $value : $default;
}

/** tel: link from the phone setting, e.g. tel:+375447726823 ('' when no phone). */
function kv_phone_href(): string {
	$digits = preg_replace( '/\D/', '', kv_opt( 'phone' ) );
	return '' === $digits ? '' : 'tel:+' . $digits;
}

/** https://t.me/<user> from the Telegram setting ('' when empty). */
function kv_telegram_href(): string {
	$user = kv_opt( 'telegram' );
	return '' === $user ? '' : 'https://t.me/' . $user;
}

/** True when $key is a known setting the owner has not filled in yet. */
function kv_is_placeholder( string $key ): bool {
	return array_key_exists( $key, kv_settings_fields() ) && '' === kv_opt( $key );
}

add_filter( 'kses_allowed_protocols', function ( $protocols ) {
	$protocols[] = 'viber';
	return $protocols;
} );

add_action( 'customize_register', function ( $wp_customize ) {
	$sections = array(
		'kv_contacts'   => 'Контакты',
		'kv_requisites' => 'Реквизиты',
		'kv_analytics'  => 'Аналитика',
	);
	$priority = 30;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'priority' => $priority++ ) );
	}
	foreach ( kv_settings_fields() as $key => $field ) {
		$wp_customize->add_setting( 'kv_' . $key, array(
			'default'           => $field[2],
			'sanitize_callback' => $field[3],
			'capability'        => 'manage_options',
		) );
		$wp_customize->add_control( 'kv_' . $key, array(
			'label'   => $field[1],
			'section' => $field[0],
			'type'    => 'email' === $key ? 'email' : 'text',
		) );
	}
} );
