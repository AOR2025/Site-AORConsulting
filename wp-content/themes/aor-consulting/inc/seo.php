<?php
/** Native metadata for installations without a dedicated SEO plugin. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aor_consulting_native_seo_enabled() {
	$plugin_active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	return (bool) apply_filters( 'aor_consulting_native_seo_enabled', ! $plugin_active );
}

add_filter( 'document_title_parts', function ( $parts ) {
	if ( aor_consulting_native_seo_enabled() && is_front_page() ) {
		$parts['title'] = 'Conseil en gestion des risques et formation | AO-Risk Consulting';
		unset( $parts['tagline'], $parts['site'] );
	}
	return $parts;
} );

function aor_consulting_seo_metadata() {
	if ( ! aor_consulting_native_seo_enabled() || is_feed() || is_preview() || is_404() || is_search() || is_paged() ) {
		return;
	}
	$post = get_queried_object();
	if ( is_singular() && ( ! $post instanceof WP_Post || ! is_post_publicly_viewable( $post ) || $post->post_password ) ) {
		return;
	}
	if ( ! is_front_page() && ! is_singular() ) {
		return;
	}

	$home = home_url( '/' );
	$url = is_front_page() ? $home : wp_get_canonical_url( $post );
	if ( ! $url ) {
		return;
	}
	$description = is_front_page()
		? 'AO-Risk Consulting accompagne les PME et ETI en France : cartographie des risques, gouvernance, continuité d’activité et formation au contrôle interne.'
		: ( $post->post_excerpt ?: $post->post_content );
	$description = wp_strip_all_tags( strip_shortcodes( $description ), true );
	$description = html_entity_decode( $description, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) );
	$description = preg_replace( '/\s+/u', ' ', $description );
	$description = wp_html_excerpt( trim( $description ), 170, '…' );
	$description = apply_filters( 'aor_consulting_meta_description', $description );
	$title = wp_get_document_title();

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	// Core already emits the canonical link for singular posts/pages.
	if ( is_front_page() && ! is_singular() ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
	foreach ( array( 'og:type' => 'website', 'og:title' => $title, 'og:description' => $description, 'og:url' => $url, 'og:site_name' => 'AO-Risk Consulting', 'og:locale' => get_locale() ) as $property => $value ) {
		if ( $value ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $value ) . '">' . "\n";
		}
	}
	$image = is_singular() ? get_the_post_thumbnail_url( $post, 'large' ) : false;
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}

	$graph = array();
	if ( is_front_page() ) {
		$graph[] = array( '@type' => 'Organization', '@id' => $home . '#organization', 'name' => 'AO-Risk Consulting', 'url' => $home );
		$graph[] = array( '@type' => 'WebSite', '@id' => $home . '#website', 'name' => 'AO-Risk Consulting', 'url' => $home, 'inLanguage' => 'fr-FR', 'publisher' => array( '@id' => $home . '#organization' ) );
	} elseif ( is_page() && in_array( get_page_template_slug( $post ), array( 'page-expertise', 'page-services' ), true ) ) {
		$items = array();
		foreach ( aor_consulting_breadcrumb_items() as $index => $item ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url'] );
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}
	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
add_action( 'wp_head', 'aor_consulting_seo_metadata', 5 );
