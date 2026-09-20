# Site AO-Risk Consulting

Site vitrine WordPress en français pour le conseil en risques, la résilience stratégique et la formation des PME et ETI. Le thème à blocs se trouve dans `wp-content/themes/aor-consulting/`.

L’interface s’inspire du portfolio « Strategic Resilience » et du programme « Maîtrise du Contrôle Interne » fournis par le client. Elle reprend les **cinq piliers et dix prestations**, propose des parcours selon la maturité de l’entreprise et présente la formation sur deux jours. La palette ivoire et bleu est complétée par un accent violet pour la formation. Les tarifs ne sont pas affichés ; le contact permet de préciser le format et le périmètre de la demande.

## Utilisation

1. Démarrer l’environnement existant avec `docker compose up -d`, après renseignement des variables du fichier `.env`.
2. Ouvrir WordPress à l’adresse correspondant à `WORDPRESS_BIND` (en local : `http://127.0.0.1:8080`).
3. Activer **AO-Risk Consulting** dans **Apparence → Thèmes**, si nécessaire.
4. Modifier les textes, les compositions et les modèles dans **Apparence → Éditeur**. `front-page.html` fournit directement l’accueil.
5. Pour une page d’expertises distincte, attribuer le modèle **Expertises AO-Risk Consulting** à une page.

Le menu rejoint les sections de l’accueil depuis toutes les pages. Le thème comprend aussi les modèles de pages, d’articles, de publications et de page 404. Aucune donnée éditoriale existante n’est remplacée à l’activation. Un modèle personnalisé enregistré dans WordPress reste prioritaire sur le fichier du thème.

Les prestations utilisent les blocs natifs Détails, utilisables au clavier et sans JavaScript. Le programme de formation comporte deux onglets navigables au clavier ; sans JavaScript, les deux jours sont visibles. Les appels à contact présélectionnent le sujet correspondant. La navigation mobile s’appuie sur le bloc Navigation de WordPress.

## Contact et validation des entrées

Le formulaire `[aor_contact]` utilise `wp_mail()`. Son destinataire est l’**adresse e-mail d’administration** configurée dans **Réglages → Général**. Les messages ne sont pas enregistrés par le thème dans la base WordPress ni dans le stockage du navigateur.

La validation côté serveur contrôle la méthode HTTP, le nonce, les types, les longueurs, l’adresse e-mail et une liste fermée de sujets. Les textes sont nettoyés avant envoi ; aucun contenu utilisateur ne sert de destinataire ou de sujet d’en-tête. L’adresse de réponse est validée et protégée contre les injections d’en-tête. Un champ anti-robot et une limite d’un essai par minute et par IP complètent ces contrôles. Cette limite utilise une empreinte salée temporaire.

Avec JavaScript, les retours s’affichent dans le formulaire : la saisie est conservée en cas d’échec, le bouton est désactivé pendant l’envoi et les messages du serveur sont rendus comme du texte. La requête est interrompue après 20 secondes sans confirmation. Sans JavaScript, le formulaire utilise une soumission POST et une redirection après succès.

Configurer un transport e-mail opérationnel sur l’hébergement et vérifier la réception avant mise en ligne. La confirmation signifie que le service d’envoi a pris en charge la demande ; elle ne prouve pas sa livraison. Exclure les pages contenant le formulaire d’un cache HTML de longue durée pour éviter les nonces expirés.

La protection du formulaire ne constitue pas un audit de sécurité de l’ensemble de WordPress ou de son hébergement. Les mises à jour, les accès d’administration et le déploiement HTTPS relèvent de la configuration du site.

## Contenus et ressources

Les textes sont adaptés des supports client. Les coordonnées et informations légales restent à renseigner avec les données de l’entreprise. Le nom du site dans **Réglages → Général** peut être harmonisé avec « AO-Risk Consulting ». Le lien de confidentialité apparaît si une page publiée est configurée dans **Réglages → Confidentialité**.

L’illustration du premier écran est un SVG léger inspiré du portfolio. Le site ne charge ni les planches du PDF, ni le PNG de formation, ni police distante, ni bibliothèque JavaScript tierce. Les couleurs et familles typographiques sont définies dans `theme.json`, les styles dans `style.css` et les interactions progressives dans `assets/js/site.js`.

## Vérification

```bash
find wp-content/themes/aor-consulting -name '*.php' -exec php -l {} \;
python3 -m json.tool wp-content/themes/aor-consulting/theme.json > /dev/null
node --check wp-content/themes/aor-consulting/assets/js/site.js
```

Tests serveur, avec le thème actif dans WordPress local :

```bash
docker compose exec -T wordpress php /var/www/html/wp-content/themes/aor-consulting/tests/contact.php
```

Ces tests interceptent le service d’envoi : **aucun e-mail n’est envoyé**. Ils couvrent le rendu, les saisies malformées, les injections d’en-tête, les sujets autorisés, les erreurs d’envoi, la redirection et la limitation des envois. La donnée temporaire du test est supprimée en fin d’exécution.
