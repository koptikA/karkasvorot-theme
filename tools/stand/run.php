<?php
/**
 * Local Playground stand only (copied by tools/stand/blueprint.json, never shipped in the theme).
 * POST raw PHP code (without "<?php") to /kv-run.php — it runs inside loaded WordPress; output is returned.
 * Requires header X-KV-Token = contents of tools/stand/.token (written by tools/serve.sh): a cross-site
 * HTML form cannot set custom headers, so a page in the admin's browser cannot reach eval().
 */
$kv_token = is_readable( '/kv-stand/.token' ) ? trim( file_get_contents( '/kv-stand/.token' ) ) : '';
$kv_sent  = isset( $_SERVER['HTTP_X_KV_TOKEN'] ) ? (string) $_SERVER['HTTP_X_KV_TOKEN'] : '';
if ( ! in_array( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '', array( '127.0.0.1', '::1' ), true )
	|| 'POST' !== $_SERVER['REQUEST_METHOD'] || '' === $kv_token || ! hash_equals( $kv_token, $kv_sent ) ) {
	http_response_code( 403 );
	exit;
}
require __DIR__ . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
eval( file_get_contents( 'php://input' ) );
