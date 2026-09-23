# Messagerie du formulaire

Le destinataire et le service d'envoi sont deux réglages distincts. L'adresse du
visiteur reste uniquement dans `Reply-To`, afin de répondre à sa demande sans
usurper son adresse comme expéditeur.

## Destinataire par environnement

Renseigner `AOR_CONTACT_EMAIL` dans le fichier **local** `.env`. Ce fichier est
ignoré par Git ; le modèle `.env.example` ne contient aucune adresse réelle.
Le thème lit cette variable côté serveur, sans la placer dans le formulaire HTML.

Si la variable est absente ou vide, le formulaire utilise l'adresse administrateur
définie dans **Réglages → Général**. Une valeur renseignée mais invalide bloque
l'envoi au lieu de choisir silencieusement un autre destinataire.

Après modification de `.env`, appliquer la variable au conteneur WordPress :

```bash
docker compose up -d --no-deps wordpress
```

Un simple `docker compose restart` ne met pas à jour les variables du conteneur.
Sur un autre hébergement, définir cette variable dans la configuration serveur.

## Envoi avec WP Mail SMTP

Le diagnostic local a relevé un `sendmail_path` pointant vers un exécutable absent
et aucune configuration SMTP. Dans cet état, `wp_mail()` échoue : l'adresse du
destinataire ne suffit pas à activer l'envoi.

Installer **WP Mail SMTP by WPForms** depuis **Extensions → Ajouter une extension**,
puis l'activer. L'extension est disponible dans le
[répertoire officiel WordPress](https://wordpress.org/plugins/wp-mail-smtp/).
Ses fichiers sont exclus de Git comme les autres extensions installées ; ses
réglages sont enregistrés dans la base WordPress de l'environnement concerné.
Il faut donc aussi l'installer et la configurer sur l'hébergement de production.

Dans **WP Mail SMTP → Réglages**, choisir le service d'envoi réellement utilisé.
Pour des tests avec un compte Yahoo, sélectionner **Autre SMTP / Other SMTP** :

| Réglage | Valeur |
| --- | --- |
| E-mail de l'expéditeur | Adresse complète du compte Yahoo utilisé |
| Forcer l'e-mail de l'expéditeur | Activé |
| Nom de l'expéditeur | AO-Risk Consulting |
| Hôte SMTP | `smtp.mail.yahoo.com` |
| Chiffrement | SSL (TLS implicite) |
| Port SMTP | `465` |
| Authentification | Activée |
| Identifiant SMTP | Adresse complète du compte Yahoo utilisé |
| Mot de passe SMTP | Mot de passe d'application généré dans le compte Yahoo |

Le mot de passe d'application est distinct du mot de passe habituel du compte.
Le saisir directement dans l'extension, jamais dans Git ou une conversation.
Voir les [paramètres officiels Yahoo](https://help.yahoo.com/kb/SLN4075.html) et
le [guide Other SMTP de l'extension](https://wpmailsmtp.com/docs/how-to-set-up-the-other-smtp-mailer-in-wp-mail-smtp/).
L'adresse qui reçoit les demandes peut être différente de celle qui les expédie.

Le transport configuré s'applique à tous les e-mails WordPress, dont ceux de
récupération de mot de passe. Ne pas activer simultanément plusieurs extensions
qui configurent SMTP. Éviter la journalisation du contenu des messages clients
si elle n'est pas nécessaire. Restreindre l'accès au back-office et aux sauvegardes
de la base contenant les réglages de messagerie.

## Confirmation et vérification

Après enregistrement des paramètres, envoyer un test depuis **WP Mail SMTP →
Outils → Test d'e-mail**, puis vérifier la boîte destinataire et les indésirables.
Tester ensuite le formulaire et le fonctionnement de « Répondre ».

Le formulaire affiche une réussite uniquement lorsque `wp_mail()` accepte
l'envoi. En cas d'échec, il affiche une erreur et conserve la saisie dans la page.
Les demandes ne sont ni enregistrées en base ni placées dans une file de reprise.
La limite antispam reste d'une tentative par minute, même après une erreur.

L'acceptation par le transport ne garantit pas la réception. Voir la
[documentation WordPress de wp_mail](https://developer.wordpress.org/reference/functions/wp_mail/).
Pour la production, utiliser l'expéditeur autorisé du domaine du client et
configurer SPF/DKIM/DMARC selon le prestataire retenu.

Les tests automatisés interceptent les envois et ne contactent aucun serveur SMTP :

```bash
docker compose exec -T wordpress php /var/www/html/wp-content/themes/aor-consulting/tests/contact.php
```

La réception réelle doit encore être vérifiée après configuration des identifiants.
