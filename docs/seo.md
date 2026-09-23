# Référencement naturel — AO-Risk Consulting

Cible confirmée : France entière. Le site présente les prestations puis recueille les demandes de contact ; les devis sont proposés par e-mail. Les contenus ci-dessous sont une base éditoriale à compléter avec l’expérience et les références réelles du cabinet.

## Architecture retenue

Un accueil sur une seule page peut être référencé. Cependant, les ancres comme `/#formation` ne créent pas autant de pages autonomes à positionner. Pour des intentions distinctes, les pages dédiées donnent à chaque sujet un titre, un contenu et une URL propres. C’est le choix retenu ici, avec l’accueil conservé comme vue d’ensemble. Voir les [recommandations Google sur les URL et les fragments](https://developers.google.com/search/docs/crawling-indexing/url-structure).

| URL | Intention principale |
| --- | --- |
| `/` | Comprendre le positionnement et contacter AO-Risk |
| `/expertises/` | Explorer l’offre de conseil en gestion des risques |
| `/expertises/diagnostic-cartographie-risques/` | Diagnostiquer la maturité et cartographier les risques |
| `/expertises/gouvernance-pilotage-risques/` | Structurer la gouvernance et le pilotage |
| `/expertises/gestion-crise-continuite-activite/` | Préparer la crise, le PCA et le PRA |
| `/expertises/risques-fournisseurs-esg/` | Comprendre les dépendances fournisseurs et les risques ESG |
| `/expertises/external-risk-officer/` | Trouver un accompagnement en risques à temps partagé |
| `/formation-controle-interne/` | Comprendre le programme et demander une formation |

Les liens de navigation et les liens dans les sections de l’accueil conduisent aux pages publiées. Tant qu’une page est absente ou en brouillon, le thème conserve un lien vers l’accueil. Les pages utilisent des blocs WordPress éditables et sont rendues côté serveur.

## Création des pages

Le déploiement du thème ne crée pas automatiquement de contenu en base de données. Avec le thème actif, ce script crée les sept pages **en brouillon**, sans écraser une page existante :

```bash
docker compose exec -T wordpress php \
/var/www/html/wp-content/themes/aor-consulting/tools/create-service-pages.php
```

Relire ensuite les textes, compléter les informations métier et publier depuis WordPress. Les contenus sources sont dans `wp-content/themes/aor-consulting/content/`, sous forme de fichiers PHP qui ne rendent aucun contenu en accès HTTP direct ; après création, les modifications éditoriales se font dans les pages WordPress. Relancer le script ne remplace ni les contenus ni les statuts existants.

Pour la prévisualisation dans le Docker local Apache de ce dépôt seulement :

```bash
docker compose exec -T wordpress php \
/var/www/html/wp-content/themes/aor-consulting/tools/create-service-pages.php \
--publish --pretty-permalinks
```

Ces options sont refusées hors de localhost ou d’un environnement déclaré local/development. Elles règlent les permaliens et le bloc WordPress du fichier `.htaccess` local. Sur un autre hébergement, configurer les permaliens et les règles du serveur selon sa documentation. Le script ne modifie jamais la visibilité dans les moteurs de recherche.

## Métadonnées et édition

- Le titre WordPress fournit le titre de chaque page ; l’accueil a un titre décrivant le conseil en risques et la formation.
- Le champ **Extrait** des pages sert de description et de texte d’introduction sur le modèle « Prestation AO-Risk Consulting ». À défaut, un extrait du contenu public est utilisé.
- WordPress conserve la gestion des URL canoniques des pages et articles ; le thème complète celle de l’accueil lorsque celui-ci affiche les publications.
- Les métadonnées Open Graph facilitent le partage. Une image est fournie lorsqu’une image mise en avant existe.
- Le balisage `WebSite` et `Organization` décrit l’identité du site sur l’accueil. Le fil d’Ariane des pages d’expertise est visible et repris en `BreadcrumbList`.
- Les pages protégées, brouillons, prévisualisations, recherches et erreurs 404 ne reçoivent pas de description publique générée par ce module.

Google peut réécrire les descriptions affichées ; leur présence ne garantit ni leur reprise exacte ni un classement. Voir les [recommandations sur les extraits](https://developers.google.com/search/docs/appearance/snippet), le [nom du site](https://developers.google.com/search/docs/appearance/site-names) et les [fils d’Ariane](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb).

Les métadonnées natives se désactivent lorsque le thème détecte Yoast, Rank Math, All in One SEO, SEOPress ou The SEO Framework. Pour un autre outil, un filtre permet de lui laisser la main :

```php
add_filter( 'aor_consulting_native_seo_enabled', '__return_false' );
```

Après installation d’une extension SEO, vérifier les balises effectivement rendues : la détection ne constitue pas un test de compatibilité avec chaque version de ces extensions.

## Avant l’ouverture au référencement

L’installation locale reste volontairement en `noindex`. Cela empêche de valider son indexation réelle dans Google, et ne remplace pas un contrôle d’accès sur un serveur de préproduction public.

1. Finaliser les [informations légales et le parcours commercial](security-and-compliance.md), puis valider les contenus avec le cabinet.
2. Sur le domaine final, vérifier HTTPS, URL WordPress, redirections et permaliens. Retirer ou passer en brouillon les contenus WordPress d’exemple qui ne doivent pas être publics.
3. Autoriser l’indexation dans **Réglages → Lecture** au moment de l’ouverture. Vérifier les balises robots, les en-têtes HTTP et les éventuelles protections de l’hébergeur.
4. Vérifier le sitemap natif `/wp-sitemap.xml` une fois le site public, puis le soumettre dans Google Search Console après validation de propriété du domaine. Le sitemap natif est désactivé lorsque WordPress est réglé pour décourager les moteurs.
5. Vérifier les URL publiées, les liens internes et les données structurées sur le domaine réel. Prévoir des redirections lors de changements futurs de slugs.
6. Mesurer les performances sur l’hébergement final, sur mobile et bureau : LCP, INP et CLS. Les essais locaux de mise en page ne sont pas une mesure des performances réelles des visiteurs.

## Développement éditorial

Compléter progressivement le site avec la présentation du consultant, ses qualifications vérifiables, sa méthode et des cas concrets publiables avec l’accord des clients. Pour les formations, préciser le public, les prérequis, les objectifs, les modalités d’évaluation, l’accessibilité et les conditions pratiques réellement proposées.

Rédiger ensuite des ressources utiles aux questions des clients : préparer une cartographie, distinguer PCA et PRA, construire une matrice de contrôle, animer le rôle de Risk Owner. Relier chaque ressource à l’expertise pertinente et suivre les requêtes, clics et demandes reçues. Voir le [guide SEO de Google](https://developers.google.com/search/docs/fundamentals/seo-starter-guide).

La cible nationale ne justifie pas des pages quasi identiques pour chaque ville. Privilégier des contenus distincts et des références réelles ; éviter l’accumulation de mots-clés, les avis inventés et les liens artificiels. Voir les [règles Google relatives au spam](https://developers.google.com/search/docs/essentials/spam-policies).

## Vérifications

```bash
docker compose exec -T wordpress php \
/var/www/html/wp-content/themes/aor-consulting/tests/seo.php
```

Ce test local vérifie notamment les descriptions, les pages protégées, la hiérarchie des fils d’Ariane, les deux types d’accueil WordPress et la désactivation du module. Les pages temporaires sont supprimées après les tests. Les contrôles navigateur ont également porté sur les huit URL, les titres et descriptions distincts, l’unicité des canoniques, les 404 et l’affichage clair/sombre de 320 à 1 440 px.
