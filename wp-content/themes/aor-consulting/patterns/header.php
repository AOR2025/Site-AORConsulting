<?php
/**
 * Title: En-tête AO-Risk Consulting
 * Slug: aor-consulting/header
 * Categories: aor-consulting
 * Inserter: no
 */
?>
<!-- wp:group {"className":"aor-header-inner aor-shell","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group aor-header-inner aor-shell">
	<!-- wp:html -->
	<a class="aor-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="AO-Risk Consulting — Accueil"><span class="aor-brand-name">AO<span class="aor-brand-dot">-</span>Risk</span><span class="aor-brand-caption">CONSULTING</span></a>
	<!-- /wp:html -->
	<!-- wp:navigation {"overlayMenu":"mobile","className":"aor-navigation","layout":{"type":"flex","justifyContent":"right"}} -->
		<!-- wp:navigation-link {"label":"Expertises","url":<?php echo wp_json_encode( home_url( '/#expertises' ) ); ?>,"kind":"custom"} /-->
		<!-- wp:navigation-link {"label":"Notre approche","url":<?php echo wp_json_encode( home_url( '/#approche' ) ); ?>,"kind":"custom"} /-->
		<!-- wp:navigation-link {"label":"Formation","url":<?php echo wp_json_encode( home_url( '/#formation' ) ); ?>,"kind":"custom"} /-->
		<!-- wp:navigation-link {"label":"Échangeons ↗","url":<?php echo wp_json_encode( home_url( '/#contact' ) ); ?>,"kind":"custom","className":"aor-nav-contact"} /-->
	<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
