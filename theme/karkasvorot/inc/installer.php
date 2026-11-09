<?php
/**
 * Installer — STUB (filled by task 02): first content, menus, works import, drafts of the old site.
 */

defined( 'ABSPATH' ) || exit;

/** @return array{created:array,skipped:array,drafted:array} */
function kv_install(): array {
	return array( 'created' => array(), 'skipped' => array(), 'drafted' => array() );
}

add_action( 'after_switch_theme', 'kv_install' );
