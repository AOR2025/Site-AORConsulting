# Site AO-Risk Consulting

Site vitrine WordPress en français dédié au conseil en gestion des risques, à la résilience stratégique et à la formation des PME et ETI.

Le projet repose sur un thème WordPress personnalisé situé dans :

```text
wp-content/themes/aor-consulting/
```

## Stack

* WordPress
* PHP
* HTML / CSS
* JavaScript natif
* `theme.json`
* MariaDB
* Docker / Docker Compose

## Fonctionnalités principales

Le thème comprend notamment :

* une page d’accueil personnalisée ;
* une présentation des expertises et prestations ;
* une section dédiée à la méthodologie ;
* une présentation de l’offre de formation ;
* un formulaire de contact ;
* des templates pour les pages, articles et erreurs 404 ;
* une navigation responsive ;
* un thème clair et un thème sombre avec mémorisation du choix ;
* des patterns WordPress réutilisables ;
* une interface adaptée aux formats desktop, tablette et mobile.

Le site utilise principalement les fonctionnalités natives de WordPress et limite les dépendances externes.

## Structure du thème

```text
wp-content/themes/aor-consulting/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── parts/
├── patterns/
├── templates/
├── tests/
├── functions.php
├── style.css
└── theme.json
```

### `templates/`

Contient les modèles principaux du site :

* page d’accueil ;
* pages standards ;
* publications ;
* articles ;
* page 404 ;
* modèle dédié aux expertises.

### `parts/`

Contient les éléments communs du thème :

* header ;
* footer.

### `patterns/`

Contient les sections réutilisables du site :

* hero ;
* expertises ;
* méthodologie ;
* formation ;
* appels à l’action ;
* header et footer.

### `assets/`

Contient les ressources du thème :

* JavaScript ;
* images ;
* styles complémentaires.

## Installation locale

### 1. Cloner le dépôt

```bash
git clone <repository-url>
cd Site-AORConsulting
```

### 2. Configurer l’environnement

Créer un fichier `.env` à partir du modèle fourni :

```bash
cp .env.example .env
```

Puis renseigner les variables nécessaires dans `.env`.

Le fichier `.env` contient les valeurs propres à l’environnement local et ne doit pas être versionné.

### 3. Démarrer WordPress

```bash
docker compose up -d
```

En développement local, WordPress est accessible à l’adresse configurée dans `WORDPRESS_BIND`.

Exemple :

```text
http://127.0.0.1:8080
```

### 4. Activer le thème

Dans l’administration WordPress :

```text
Apparence → Thèmes → AO-Risk Consulting
```

Activer ensuite le thème.

## Personnalisation

Les principaux réglages graphiques sont centralisés dans :

```text
theme.json
```

Ils comprennent notamment :

* les couleurs ;
* les typographies ;
* les espacements ;
* les largeurs de contenu ;
* les styles globaux des blocs.

Les styles spécifiques sont définis dans :

```text
style.css
```

Les interactions JavaScript sont regroupées dans :

```text
assets/js/site.js
```

### Thème clair et sombre

Le bouton lune / soleil dans l’en-tête permet de changer de thème. Au premier affichage, le site suit la préférence du système. Un choix manuel est conservé dans le navigateur via `localStorage` et reste prioritaire lors des visites suivantes.

Les couleurs des deux modes sont définies par les variables `--aor-*` au début de `style.css`. Le script `assets/js/color-mode.js` applique la préférence dès le chargement de l’en-tête pour éviter un flash du mauvais thème. Si le stockage est indisponible, le bouton fonctionne pour la page courante ; sans JavaScript, le site conserve son thème clair.

## Formulaire de contact

Le thème comprend un formulaire de contact personnalisé.

Les données saisies sont validées côté serveur et plusieurs protections sont mises en place contre les soumissions invalides ou automatisées.

Le formulaire utilise le système d’envoi d’e-mails de WordPress.

Un transport e-mail adapté devra être configuré sur l’environnement de production avant la mise en ligne.

## Sécurité

La [revue sécurité et obligations du site](docs/security-and-compliance.md) décrit les vérifications locales, les corrections et les informations à préparer avant la mise en ligne.

Le dépôt ne contient pas les secrets de l’environnement.

Les fichiers contenant des données sensibles ou générées localement sont exclus grâce au `.gitignore`, notamment :

```text
.env
wp-content/uploads/
fichiers de sauvegarde
logs
bases de données exportées
```

Les identifiants, mots de passe, clés API et autres secrets ne doivent jamais être ajoutés au dépôt Git.

## Vérification du code

### PHP

```bash
find wp-content/themes/aor-consulting -name '*.php' -exec php -l {} \;
```

### JSON

```bash
python3 -m json.tool wp-content/themes/aor-consulting/theme.json > /dev/null
```

### JavaScript

```bash
node --check wp-content/themes/aor-consulting/assets/js/site.js
node --check wp-content/themes/aor-consulting/assets/js/color-mode.js
```

### Tests du formulaire

Avec le thème actif dans l’environnement WordPress local :

```bash
docker compose exec -T wordpress \
php /var/www/html/wp-content/themes/aor-consulting/tests/contact.php
```

Les tests utilisent l’environnement local et n’envoient pas réellement d’e-mail.

## Développement Git

Le projet suit le workflow :

```text
feature/*
    ↓
develop
    ↓
main
```

* `main` : version stable ;
* `develop` : branche d’intégration ;
* `feature/*` : développement des fonctionnalités.

Les modifications passent par des Pull Requests avant intégration dans les branches protégées.

## Contenus

Les contenus du site sont adaptés aux besoins d’AO-Risk Consulting.

Les informations légales, coordonnées définitives et paramètres liés à l’environnement de production doivent être renseignés avant la mise en ligne.
