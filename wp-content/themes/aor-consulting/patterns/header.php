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
	<!-- wp:html -->
	<button class="aor-theme-toggle" type="button" data-aor-theme-toggle aria-label="Thème sombre" aria-pressed="false" title="Passer au thème sombre" hidden>
		<svg class="aor-theme-icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.5 13.5A8.5 8.5 0 0 1 10.5 3.5 8.5 8.5 0 1 0 20.5 13.5Z"/></svg>
		<svg class="aor-theme-icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg>
	</button>
	<!-- /wp:html -->
</div>
<!-- /wp:group -->
