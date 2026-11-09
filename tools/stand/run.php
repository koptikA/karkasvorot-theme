<?php
/**
 * Local Playground stand only (copied by tools/stand/blueprint.json, never shipped in the theme).
 * POST raw PHP code (without "<?php") to /kv-run.php — it runs inside loaded WordPress; output is returned.
 */
if ( ! in_array( isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '', array( '127.0.0.1', '::1' ), true ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
	http_response_code( 403 );
	exit;
}
require __DIR__ . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
eval( file_get_contents( 'php://input' ) );
