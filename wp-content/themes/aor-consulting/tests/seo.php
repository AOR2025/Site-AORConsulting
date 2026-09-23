<?php
/** Local integration checks. Temporary pages are removed in the finally block. */
if ( 'cli' !== PHP_SAPI ) {
	exit;
}
require dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! function_exists( 'aor_consulting_seo_metadata' ) ) {
	fwrite( STDERR, "Activate the AO-Risk Consulting theme first.\n" );
	exit( 1 );
}
if ( ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1', '[::1]' ), true ) && ! in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	fwrite( STDERR, "Run these checks on a local development installation.\n" );
	exit( 1 );
}
$checks = 0;
$created = array();
$original_query = $GLOBALS['wp_query'];
$visibility = get_option( 'blog_public' );
$posts_front = static function () { return 'posts'; };
$static_front = static function () { return 'page'; };
$front_id = null;
function aor_seo_assert( $value, $label ) {
	global $checks;
	if ( ! $value ) {
		throw new RuntimeException( 'FAIL: ' . $label );
	}
	++$checks;
	echo 'PASS: ' . $label . "\n";
}
function aor_seo_capture() {
	ob_start();
	aor_consulting_seo_metadata();
	return ob_get_clean();
}
function aor_seo_query( $args ) {
	$GLOBALS['wp_query'] = new WP_Query( $args );
}
try {
	$parent = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Audit SEO parent', 'post_name' => 'aor-seo-fixture-' . wp_generate_uuid4() ), true );
	if ( is_wp_error( $parent ) ) {
		throw new RuntimeException( $parent->get_error_message() );
	}
	$created[] = $parent;
	$child = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $parent, 'post_title' => 'Audit SEO détail', 'post_name' => 'detail', 'post_excerpt' => 'Une description distincte pour la page de contrôle.', 'post_content' => '<!-- wp:paragraph --><p>Contenu public.</p><!-- /wp:paragraph -->', 'meta_input' => array( '_wp_page_template' => 'page-expertise' ) ), true );
	if ( is_wp_error( $child ) ) {
		throw new RuntimeException( $child->get_error_message() );
	}
	$created[] = $child;
	aor_seo_query( array( 'page_id' => $child ) );
	$html = aor_seo_capture();
	aor_seo_assert( str_contains( $html, 'Une description distincte' ), 'Use the editable page excerpt' );
	aor_seo_assert( ! str_contains( $html, 'rel="canonical"' ), 'Leave singular canonical links to WordPress core' );
	preg_match( '~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches );
	$data = json_decode( $matches[1] ?? '', true, 512, JSON_THROW_ON_ERROR );
	$items = $data['@graph'][0]['itemListElement'];
	aor_seo_assert( 3 === count( $items ) && get_permalink( $parent ) === $items[1]['item'] && get_permalink( $child ) === $items[2]['item'], 'Breadcrumb reflects the actual page hierarchy' );
	$unsafe = static function () { return '"><script>alert(1)</script>'; };
	add_filter( 'aor_consulting_meta_description', $unsafe );
	$escaped = aor_seo_capture();
	aor_seo_assert( ! str_contains( $escaped, '<script>alert' ) && str_contains( $escaped, '&lt;script&gt;' ), 'Escape editable descriptions before HTML output' );
	remove_filter( 'aor_consulting_meta_description', $unsafe );

	wp_update_post( array( 'ID' => $child, 'post_password' => 'audit-only' ) );
	aor_seo_query( array( 'page_id' => $child ) );
	aor_seo_assert( '' === aor_seo_capture(), 'Never expose password-protected content through metadata' );
	wp_update_post( array( 'ID' => $child, 'post_password' => '', 'post_status' => 'draft' ) );
	aor_seo_query( array( 'page_id' => $child, 'post_status' => 'draft' ) );
	aor_seo_assert( '' === aor_seo_capture(), 'Do not generate public metadata for drafts' );
	$path = get_page_uri( $child );
	aor_seo_assert( home_url( '/#contact' ) === aor_consulting_page_url( $path, '/#contact' ), 'Draft pages keep the working navigation fallback' );
	wp_update_post( array( 'ID' => $child, 'post_status' => 'publish' ) );
	aor_seo_assert( get_permalink( $child ) === aor_consulting_page_url( $path ), 'Published pages get a real navigation URL' );

	add_filter( 'pre_option_show_on_front', $posts_front );
	aor_seo_query( array( 'posts_per_page' => 1 ) );
	aor_seo_assert( is_front_page(), 'Test a posts-based front page without changing settings' );
	$html = aor_seo_capture();
	aor_seo_assert( 1 === substr_count( $html, 'rel="canonical"' ), 'Provide the missing homepage canonical exactly once' );
	aor_seo_assert( str_contains( $html, '"@type":"WebSite"' ) && str_contains( $html, '"@type":"Organization"' ), 'Homepage identifies the website and organization' );
	remove_filter( 'pre_option_show_on_front', $posts_front );
	$front_id = static function () use ( $child ) { return $child; };
	add_filter( 'pre_option_show_on_front', $static_front );
	add_filter( 'pre_option_page_on_front', $front_id );
	aor_seo_query( array( 'page_id' => $child ) );
	aor_seo_assert( is_front_page() && is_singular(), 'Support a static WordPress homepage too' );
	$html = aor_seo_capture();
	aor_seo_assert( ! str_contains( $html, 'rel="canonical"' ) && str_contains( $html, '"@type":"WebSite"' ), 'Static homepage retains core canonical and website identity' );
	add_filter( 'aor_consulting_native_seo_enabled', '__return_false' );
	aor_seo_assert( '' === aor_seo_capture(), 'Allow a dedicated SEO plugin to own all metadata' );
	$parts = array( 'title' => 'Existing title', 'tagline' => 'Existing tagline' );
	aor_seo_assert( $parts === apply_filters( 'document_title_parts', $parts ), 'Disabling native SEO also leaves document titles untouched' );
	remove_filter( 'aor_consulting_native_seo_enabled', '__return_false' );
	aor_seo_query( array( 's' => 'audit' ) );
	aor_seo_assert( '' === aor_seo_capture(), 'Search pages do not get misleading homepage metadata' );
	aor_seo_assert( $visibility === get_option( 'blog_public' ), 'SEO does not change search-engine visibility' );
	echo "\n{$checks} SEO checks passed.\n";
} finally {
	remove_filter( 'pre_option_show_on_front', $posts_front );
	remove_filter( 'pre_option_show_on_front', $static_front );
	if ( $front_id ) {
		remove_filter( 'pre_option_page_on_front', $front_id );
	}
	remove_filter( 'aor_consulting_native_seo_enabled', '__return_false' );
	if ( isset( $unsafe ) ) {
		remove_filter( 'aor_consulting_meta_description', $unsafe );
	}
	$GLOBALS['wp_query'] = $original_query;
	foreach ( array_reverse( $created ) as $id ) {
		wp_delete_post( $id, true );
	}
}
