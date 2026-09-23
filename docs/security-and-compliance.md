# Sécurité et obligations du site — revue du 23 septembre 2026

Périmètre : thème AO-Risk Consulting, dépôt Git et WordPress Docker local. État initial : `main` au commit `f11fae3`, identique en contenu à `develop` (`d3bb346`). Corrections sur `feature/security-seo`, créée depuis `origin/develop`. Aucun domaine de production, compte d’hébergement ou service de messagerie externe n’a été audité.

Contexte confirmé : entreprise en France, prestations et formations destinées aux professionnels **et aux particuliers**, premier contact par formulaire, puis proposition commerciale par e-mail. Les informations juridiques ci-dessous constituent une préparation à faire valider pour la structure et les contrats réels.

## Constats techniques

| Point | Résultat | Suite |
| --- | --- | --- |
| Validation serveur | 28 contrôles réussis : types, tailles, e-mail, injection d’en-têtes, sujet autorisé, nonce, piège antispam, limitation de fréquence et erreurs d’envoi | Conserver ces contrôles à chaque modification du formulaire |
| Envoi JavaScript | Le champ caché `action` masquait `form.action` : la requête visait `/[object HTMLInputElement]` | Corrigé avec `form.getAttribute('action')`, version du thème augmentée pour renouveler le cache |
| Retour utilisateur | Les messages du serveur passent par `textContent` ; une erreur conserve la saisie | Vérifié dans le navigateur, y compris réponse contenant du HTML et erreurs 400/429/503 |
| Secrets et fichiers techniques | `.env`, `.git/HEAD` et `debug.log` inaccessibles par HTTP ; aucun contenu de configuration divulgué ; aucun listing du dossier du thème | Maintenir ces protections lors du déploiement |
| Exposition réseau locale | WordPress lié à `127.0.0.1:8080`, aucun port MariaDB publié | Ne pas réutiliser sans revue cette configuration comme hébergement public |
| WordPress et extensions | WordPress 7.1.1, thème 1.4.0 avant correction ; aucune extension active, deux extensions inactives | Maintenir les mises à jour et retirer les extensions inutilisées après vérification |
| Administration | Inscriptions publiques fermées ; `WP_DEBUG=false` ; `DISALLOW_FILE_EDIT` non défini | Désactiver l’éditeur de fichiers en production ; vérifier les droits des comptes et la double authentification |
| Commentaires | Autorisation ouverte par défaut dans les réglages locaux | Fermer les commentaires si inutiles au site vitrine et vérifier aussi les contenus déjà publiés |
| Transport et en-têtes | HTTP local ; pas de CSP générale ni de `X-Content-Type-Options` sur l’accueil. La connexion WordPress dispose déjà d’une protection contre l’encadrement | Vérifier HTTPS, redirections et en-têtes sur le véritable hébergement ; tester toute CSP avec WordPress avant application |
| Messagerie | Destinataire : adresse administrateur WordPress ; transport réel non vérifié | Choisir une boîte métier, limiter ses accès et vérifier la réception réelle avant ouverture du site |

Les tests serveur interceptent `wp_mail`. Les essais navigateur simulent les réponses, sauf un test réel avec un nonce volontairement invalide, rejeté en 403. Aucun e-mail n’a été envoyé pendant cette revue. Ces contrôles ne constituent pas un test d’intrusion exhaustif.

Complément messagerie : le diagnostic ultérieur a confirmé l'absence de l'exécutable `sendmail` et de configuration SMTP dans le conteneur local. Le destinataire du formulaire est désormais configurable séparément de l'administrateur ; le transport doit être configuré avec WP Mail SMTP dans le back-office. Voir la [configuration et les vérifications encore nécessaires](email.md).

La limitation actuelle repose sur une clé issue de l’adresse IP et expire après une minute. C’est une protection antispam de base : vérifier son comportement derrière le proxy de production. Le nonce public ne constitue ni une authentification ni une preuve qu’un visiteur est humain. Voir les [limites des nonces WordPress](https://developer.wordpress.org/apis/security/nonces/).

Le plan d’exploitation doit couvrir les sauvegardes avec restauration testée, les mises à jour de WordPress/PHP/extensions, les accès administrateurs et les permissions des fichiers. Voir le [guide de sécurisation WordPress](https://developer.wordpress.org/advanced-administration/security/hardening/). La version installée inclut la [mise à jour de sécurité 7.1.1](https://wordpress.org/news/2026/09/wordpress-7-1-1-maintenance-and-security-release/).

## Informations à publier avant ouverture du site

Au début de la revue, la base locale contenait seulement une page d’exemple publiée et une politique de confidentialité en **brouillon**. Aucun lien vers des mentions légales, des CGV ou un médiateur n’était présent dans la page testée. Le lien de confidentialité du formulaire et du pied de page apparaît uniquement lorsqu’une politique publiée est configurée dans WordPress. Les pages d’expertise ajoutées ensuite ne remplacent pas ces documents juridiques.

| Élément | Contenu à préparer |
| --- | --- |
| Mentions légales | Identité juridique, coordonnées, immatriculation et TVA selon le statut, capital si applicable, directeur de publication, identité/adresse/téléphone de l’hébergeur et autres prestataires de stockage concernés |
| Confidentialité | Responsable du traitement, finalités, bases légales, destinataires, conservation, droits et contact pour les exercer, réclamation CNIL, transferts hors UE éventuels |
| Information sous le formulaire | Résumé compréhensible au moment de la collecte, champs obligatoires et lien vers la politique détaillée ; le texte actuel seul ne suffit pas |
| CGV et informations précontractuelles | Conditions adaptées au conseil et à la formation, aux professionnels et aux consommateurs ; à rendre accessibles et à transmettre avant acceptation du devis |
| Médiation de la consommation | Coordonnées et site du médiateur avec lequel l’entreprise a effectivement organisé son dispositif de médiation |

Les mentions d’identification et de publication découlent notamment de la [LCEN, article 1-1](https://www.legifrance.gouv.fr/loda/article_lc/LEGIARTI000049568614) et des [obligations présentées par le ministère de l’Économie](https://www.economie.gouv.fr/entreprises/developper-son-entreprise/innover-et-numeriser-son-entreprise/mentions-sur-votre-site-internet-les-obligations-respecter). Adapter les champs à une société ou à une entreprise individuelle, sans inventer de coordonnées ni de statut.

Pour les données personnelles, couvrir aussi les e-mails reçus, les journaux de l’hébergeur et les éventuels outils de suivi commercial. Fixer les durées selon chaque finalité et appliquer la suppression correspondante. Voir l’[information des personnes selon la CNIL](https://www.cnil.fr/fr/conformite-rgpd-information-des-personnes-et-transparence).

Une demande de devis peut relever des mesures précontractuelles demandées par la personne : une case générale « j’accepte le RGPD » n’est pas une obligation automatique. Une newsletter ou une réutilisation commerciale doit être évaluée séparément. Voir la [base légale du contrat et des mesures précontractuelles](https://www.cnil.fr/fr/les-bases-legales/contrat).

Le fichier clients/prospects doit également être décrit dans le registre interne des traitements ; ce registre n’est pas une page publique du site. Voir la [fiche CNIL sur les clients et prospects](https://www.cnil.fr/fr/cnil-direct/question/gestion-des-clients-et-prospects-que-faire).

## Prestations proposées par e-mail aux particuliers

L’absence de paiement sur le site ne dispense pas des obligations envers les consommateurs. Pour une prestation conclue à distance, préparer avant signature les caractéristiques, le prix TTC ou son mode de calcul, les délais, modalités de paiement, réclamations et informations sur la rétractation. Le délai de principe est de 14 jours ; le démarrage anticipé et les exceptions demandent des conditions précises, à adapter au contrat. Voir les [CGV et informations précontractuelles](https://entreprendre.service-public.gouv.fr/vosdroits/F33527).

Le médiateur doit être identifié sur le site, **même sur un site vitrine** : voir la [fiche officielle sur la médiation pour les professionnels](https://www.economie.gouv.fr/files/files/directions_services/mediation-conso/Fiche%20pratique%20professionnels.pdf). Choisir réellement un médiateur compétent avant de publier ses coordonnées.

Si une interface de conclusion de contrat, de réservation ou de signature en ligne est ajoutée, réexaminer les obligations correspondantes, dont la fonctionnalité de rétractation en ligne applicable depuis le 19 juin 2026 aux contrats concernés. Le parcours prévu ici se limite à la prise de contact.

L’activité de formation professionnelle a aussi ses formalités propres : vérifier la déclaration d’activité et les documents contractuels avant de commercialiser les formations. Voir la [déclaration d’activité des organismes de formation](https://entreprendre.service-public.gouv.fr/vosdroits/F19087). Ne pas afficher de numéro, d’agrément ou de certification non justifié.

## Cookies, traceurs et accessibilité

Sur l’accueil testé, aucun cookie visiteur ni requête vers un tiers n’a été observé. Après une action sur le bouton de thème, seule la préférence `aor-color-mode` est enregistrée dans `localStorage`. Cette observation ne couvre pas l’administration ni de futures extensions.

Le choix d’affichage demandé par le visiteur correspond, dans cette utilisation, à une personnalisation pouvant être exemptée de consentement. Il reste à le décrire dans la politique. Un bandeau n’est donc pas à ajouter pour cette seule préférence ; réévaluer avant d’intégrer des traceurs publicitaires, statistiques non exemptés, vidéos ou widgets tiers. Voir les [exemptions et règles de la CNIL](https://www.cnil.fr/fr/cookies-et-autres-traceurs/regles/cookies/comment-mettre-mon-site-web-en-conformite).

Les obligations formelles d’accessibilité dépendent du statut, de la taille de l’entreprise et des services proposés. Une petite société privée présentant simplement son activité n’est pas automatiquement soumise au même dispositif qu’un organisme public. Vérifier le [champ du RGAA](https://accessibilite.numerique.gouv.fr/obligations/champ-application/) et, si le parcours évolue, les [règles relatives aux services, dont le commerce électronique](https://www.economie.gouv.fr/dgccrf/les-fiches-pratiques/professionnels-vos-produits-et-services-doivent-etre-conformes-la-directive-accessibilite). Les vérifications de clavier, contrastes et mobile déjà réalisées ne constituent pas une certification RGAA.

## Informations encore nécessaires

- Statut, raison sociale, adresse, immatriculation, TVA éventuelle, téléphone et directeur de publication.
- Hébergeur et prestataire de messagerie retenus, lieux de traitement et contacts.
- Médiateur effectivement choisi, conditions des devis, paiement, annulation et démarrage des missions.
- Contact pour les droits RGPD, durées de conservation, personnes habilitées à lire les demandes.
- Informations vérifiées sur l’activité de formation et les droits d’utilisation des contenus et visuels.

Ces éléments sont nécessaires pour finaliser les pages et les contrats ; aucun modèle incomplet n’a été publié en guise de document juridique.
