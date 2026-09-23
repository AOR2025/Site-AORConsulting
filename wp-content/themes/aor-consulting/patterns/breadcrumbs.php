<?php
/**
 * Title: Fil d’Ariane des expertises
 * Slug: aor-consulting/breadcrumbs
 * Categories: aor-consulting
 * Inserter: no
 */
$items = aor_consulting_breadcrumb_items();
?>
<!-- wp:html -->
<nav class="aor-breadcrumbs" aria-label="Fil d’Ariane"><ol><?php foreach ( $items as $index => $item ) : ?><li><?php if ( $index === count( $items ) - 1 ) : ?><span aria-current="page"><?php echo esc_html( $item['name'] ); ?></span><?php else : ?><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a><?php endif; ?></li><?php endforeach; ?></ol></nav>
<!-- /wp:html -->
