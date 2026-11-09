<?php
/**
 * Stand seed (local Playground only): fixtures into the media library + legacy content like on the live site.
 * Runs before the theme is activated, so after_switch_theme sees the media.
 */
require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dir   = '/kv-fixtures';
$media = json_decode( file_get_contents( $dir . '/media.json' ), true );
$base  = wp_upload_dir()['basedir'];
$first = '';
foreach ( $media as $m ) {
	$dest = $base . '/' . $m['path'];
	wp_mkdir_p( dirname( $dest ) );
	copy( $dir . '/uploads/' . $m['path'], $dest );
	$id = wp_insert_attachment(
		array( 'post_title' => $m['title'], 'post_mime_type' => $m['mime'], 'post_status' => 'inherit' ),
		$dest
	);
	update_post_meta( $id, '_wp_attachment_image_alt', $m['alt'] );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) );
	if ( '' === $first ) {
		$first = wp_get_attachment_url( $id );
	}
}

// Legacy slider posts: two with empty titles, "Слайд2", "Слайд4".
foreach ( array( '', '', 'Слайд2', 'Слайд4' ) as $title ) {
	wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => '<img src="' . esc_url( $first ) . '" alt="">',
		'post_status'  => 'publish',
		'post_type'    => 'post',
	) );
}

$sample = get_page_by_path( 'sample-page' );
$page   = array( 'post_title' => 'Пример страницы', 'post_name' => 'sample-page', 'post_type' => 'page', 'post_status' => 'publish' );
if ( $sample ) {
	$page['ID'] = $sample->ID;
	wp_update_post( $page );
} else {
	$page['post_content'] = 'Это пример страницы.';
	wp_insert_post( $page );
}
echo count( $media ) . " media seeded\n";
