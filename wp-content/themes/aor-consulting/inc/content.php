<?php
/** Service-page catalogue and links to published WordPress pages. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aor_consulting_service_pages() {
	return array(
		'expertises' => array(
			'title' => 'Conseil en gestion des risques et résilience',
			'excerpt' => 'Découvrez les expertises AO-Risk Consulting : diagnostic, cartographie des risques, gouvernance, continuité d’activité, accompagnement et formation.',
			'template' => 'page-services',
		),
		'expertises/diagnostic-cartographie-risques' => array(
			'title' => 'Diagnostic et cartographie des risques',
			'excerpt' => 'Identifiez les risques prioritaires de votre organisation et structurez votre plan d’action avec un diagnostic et une cartographie adaptés aux PME et ETI.',
		),
		'expertises/gouvernance-pilotage-risques' => array(
			'title' => 'Gouvernance et pilotage des risques',
			'excerpt' => 'Clarifiez les responsabilités, définissez votre appétence au risque et construisez les indicateurs utiles à la décision avec AO-Risk Consulting.',
		),
		'expertises/gestion-crise-continuite-activite' => array(
			'title' => 'Gestion de crise et continuité d’activité',
			'excerpt' => 'Préparez vos équipes à la crise et structurez vos plans de continuité et de reprise d’activité : analyse d’impact, scénarios, exercices et retour d’expérience.',
		),
		'expertises/risques-fournisseurs-esg' => array(
			'title' => 'Risques fournisseurs et enjeux ESG',
			'excerpt' => 'Identifiez vos dépendances fournisseurs et vos risques environnementaux, sociaux et de gouvernance pour prioriser les actions de maîtrise.',
		),
		'expertises/external-risk-officer' => array(
			'title' => 'External Risk Officer : votre Risk Manager à temps partagé',
			'excerpt' => 'Intégrez une expertise en gestion des risques à temps partagé pour animer votre démarche, accompagner les Risk Owners et suivre vos plans d’action.',
		),
		'formation-controle-interne' => array(
			'title' => 'Formation au contrôle interne : de la théorie à la pratique',
			'excerpt' => 'Un parcours de deux jours pour comprendre les cadres COSO et ISO, concevoir les contrôles, évaluer leur efficacité et piloter les résultats.',
		),
	);
}

/** Keep navigation usable on installations where pages are still drafts or absent. */
function aor_consulting_page_url( $path, $fallback = '/#expertises' ) {
	$page = get_page_by_path( $path );
	return $page && 'publish' === $page->post_status && ! $page->post_password ? get_permalink( $page ) : home_url( $fallback );
}

/** One hierarchy for both the visible breadcrumb and structured data. */
function aor_consulting_breadcrumb_items() {
	$items = array( array( 'name' => 'Accueil', 'url' => home_url( '/' ) ) );
	if ( ! is_page() || is_front_page() ) {
		return $items;
	}
	foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $id ) {
		if ( 'publish' === get_post_status( $id ) && ! get_post_field( 'post_password', $id ) ) {
			$items[] = array( 'name' => get_the_title( $id ), 'url' => get_permalink( $id ) );
		}
	}
	$items[] = array( 'name' => get_the_title( get_queried_object_id() ), 'url' => get_permalink( get_queried_object_id() ) );
	return $items;
}

add_action( 'init', function () {
	add_post_type_support( 'page', 'excerpt' );
} );
