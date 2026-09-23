<?php
/** CLI-only page provisioning. Default: drafts. Existing pages are never overwritten. */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}
require dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! function_exists( 'aor_consulting_service_pages' ) ) {
	fwrite( STDERR, "Activate the AO-Risk Consulting theme first.\n" );
	exit( 1 );
}
$args = array_slice( $argv, 1 );
if ( array_diff( $args, array( '--publish', '--pretty-permalinks' ) ) ) {
	fwrite( STDERR, "Options: --publish --pretty-permalinks (both limited to localhost/development).\n" );
	exit( 1 );
}
$publish = in_array( '--publish', $args, true );
$pretty = in_array( '--pretty-permalinks', $args, true );
$is_local = in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1', '[::1]' ), true ) || in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
if ( ( $publish || $pretty ) && ! $is_local ) {
	fwrite( STDERR, "Create drafts here; review and publish them in WordPress. Automatic publication/permalink changes are limited to a local development site.\n" );
	exit( 1 );
}
foreach ( aor_consulting_service_pages() as $path => $data ) {
	if ( get_page_by_path( $path ) ) {
		echo 'SKIP existing: ' . $path . "\n";
		continue;
	}
	$parent_path = dirname( $path );
	$parent = '.' === $parent_path ? null : get_page_by_path( $parent_path );
	if ( '.' !== $parent_path && ! $parent ) {
		fwrite( STDERR, 'Missing parent: ' . $parent_path . "\n" );
		exit( 1 );
	}
	$file = dirname( __DIR__ ) . '/content/' . basename( $path ) . '.php';
	if ( ! is_readable( $file ) ) {
		fwrite( STDERR, 'Missing content: ' . $path . "\n" );
		exit( 1 );
	}
	$content = require $file;
	$id = wp_insert_post( wp_slash( array(
		'post_type' => 'page', 'post_status' => $publish ? 'publish' : 'draft',
		'post_title' => $data['title'], 'post_name' => basename( $path ),
		'post_excerpt' => $data['excerpt'], 'post_content' => $content,
		'post_parent' => $parent ? $parent->ID : 0,
		'comment_status' => 'closed', 'ping_status' => 'closed',
		'meta_input' => array( '_wp_page_template' => $data['template'] ?? 'page-expertise' ),
	) ), true );
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}
	echo ( $publish ? 'PUBLISHED locally: ' : 'DRAFT: ' ) . $path . "\n";
}
if ( $pretty ) {
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	flush_rewrite_rules( false );
	// CLI requests do not identify Apache, so update its managed block explicitly.
	// This option targets the repository's local Apache Docker environment.
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! insert_with_markers( get_home_path() . '.htaccess', 'WordPress', $wp_rewrite->mod_rewrite_rules() ) ) {
		fwrite( STDERR, "Permalinks changed, but Apache rules could not be written. Save Permalinks in WordPress before using these URLs.\n" );
		exit( 1 );
	}
	echo "Local permalinks and Apache rules updated: /%postname%/\n";
}
echo "Search-engine visibility was not changed.\n";
