# Projet de groupe PHP / MySQL - Le jeu du plus ou moins

Membres du groupe : Chady, Abdallah, Matteo, Claude Alex

L'ordinateur choisit un nombre secret. Le joueur propose des nombres et le jeu répond « C'est plus » ou « C'est moins » jusqu'à ce qu'il trouve, avec un nombre d'essais limité selon le niveau. Chaque partie terminée est enregistrée en base, avec des statistiques par joueur et un classement par niveau.

## Installation (Wamp)

1. Placer le dossier du projet dans `C:\wamp64\www\`.
2. Dans phpMyAdmin, onglet **Importer** : choisir `database/rendu.sql`. Le fichier crée la base `plusoumoins`, ses 3 tables et les 4 niveaux.
3. Si besoin, adapter l'utilisateur et le mot de passe MySQL dans `config.php` (par défaut `root`, sans mot de passe).
4. Ouvrir http://localhost/eval_sn1_phpmysql_2/

## Les fichiers

| Fichier | Rôle |
|---|---|
| `config.php` | Connexion à la base dans `$dbh` |
| `header.php` / `footer.php` | Haut de page (menu) et bas de page, inclus dans chaque page |
| `index.php` | Accueil : choix du niveau (liste lue dans la base) |
| `inscription.php` | Création de compte : pseudo unique, email, mot de passe hashé |
| `connexion.php` / `deconnexion.php` | Connexion et déconnexion |
| `jeu.php` | La partie : plus / moins, essais restants, gagné ou perdu, enregistrement en base |
| `statistiques.php` | Mes statistiques : chiffres globaux, détail par niveau, dernières parties |
| `classement.php` | Classement par niveau, visible sans compte |
| `css/style.css` | Mise en forme |
| `database/rendu.sql` | Export de la base |

## La base de données `plusoumoins`

- `joueur` : id_joueur, pseudo (unique), email, mot_de_passe (hashé)
- `niveau` : id_niveau, nom, nombre_min, nombre_max, essais_max
- `partie` : id_partie, id_joueur, id_niveau, nombre_secret, essais_utilises, gagnee, date_debut, date_fin

Ajouter une ligne dans la table `niveau` suffit : le nouveau niveau apparaît sur l'accueil, dans les statistiques et dans le classement.

## Sécurité

- Toutes les requêtes sont préparées (`prepare` puis `execute` avec des `?`).
- `htmlspecialchars()` sur les données affichées qui viennent de la base ou d'un formulaire.
- Mots de passe hashés avec `password_hash()` et vérifiés avec `password_verify()`.
- Le nombre secret reste dans la session, côté serveur : il n'apparaît jamais dans le HTML pendant la partie.
- La saisie du joueur est vérifiée en PHP : une saisie invalide ne coûte pas d'essai.
