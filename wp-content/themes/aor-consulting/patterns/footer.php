<?php
/**
 * Title: Pied de page AO-Risk Consulting
 * Slug: aor-consulting/footer
 * Categories: aor-consulting
 * Inserter: no
 */
?>
<!-- wp:group {"className":"aor-footer-inner aor-shell","layout":{"type":"default"}} -->
<div class="wp-block-group aor-footer-inner aor-shell">
	<!-- wp:group {"className":"aor-footer-top","layout":{"type":"flex","justifyContent":"space-between"}} -->
	<div class="wp-block-group aor-footer-top">
		<!-- wp:html -->
		<a class="aor-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="AO-Risk Consulting — Accueil"><span class="aor-brand-name">AO<span class="aor-brand-dot">-</span>Risk</span><span class="aor-brand-caption">CONSULTING</span></a>
		<!-- /wp:html -->
		<!-- wp:paragraph {"className":"aor-footer-motto"} --><p class="aor-footer-motto">Anticiper. Protéger. Construire la suite.</p><!-- /wp:paragraph -->
		<!-- wp:paragraph {"className":"aor-back-top"} --><p class="aor-back-top"><a href="#contenu">Retour en haut ↑</a></p><!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
	<!-- wp:group {"className":"aor-footer-bottom","layout":{"type":"flex","justifyContent":"space-between"}} -->
	<div class="wp-block-group aor-footer-bottom">
		<!-- wp:paragraph --><p>© AO-Risk Consulting</p><!-- /wp:paragraph -->
		<!-- wp:paragraph --><p><a href="<?php echo esc_url( home_url( '/#expertises' ) ); ?>">Expertises</a> · <a href="<?php echo esc_url( home_url( '/#approche' ) ); ?>">Notre approche</a> · <a href="<?php echo esc_url( home_url( '/#formation' ) ); ?>">Formation</a> · <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>">Contact</a><?php if ( get_privacy_policy_url() ) : ?> · <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">Confidentialité</a><?php endif; ?></p><!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
