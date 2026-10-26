<?php
session_start();
require_once 'config.php';

// Seuls les joueurs connectés peuvent jouer : sinon, direction la page de connexion
if (!isset($_SESSION['id_joueur'])) {
    header('Location: connexion.php');
    exit;
}

// ===== 1. Nouvelle partie : on arrive depuis l'accueil avec jeu.php?niveau=... =====
if (isset($_GET['niveau'])) {
    $requete = $dbh->prepare('SELECT * FROM niveau WHERE id_niveau = ?');
    $requete->execute([$_GET['niveau']]);
    $niveau = $requete->fetch(PDO::FETCH_ASSOC);

    // Niveau inconnu : retour à l'accueil
    if (!$niveau) {
        header('Location: index.php');
        exit;
    }

    // La partie est gardée dans la session (côté serveur) : le nombre secret n'est jamais écrit dans le HTML
    $_SESSION['niveau'] = $niveau;
    $_SESSION['nombre_secret'] = rand($niveau['nombre_min'], $niveau['nombre_max']);
    $_SESSION['essais'] = [];         // les nombres proposés par le joueur
    $_SESSION['debut'] = time();      // heure de début, en secondes
    $_SESSION['etat'] = 'en_cours';   // en_cours, gagnee ou perdue

    // On recharge jeu.php sans ?niveau=... pour qu'un rafraîchissement (F5) ne relance pas une partie
    header('Location: jeu.php');
    exit;
}

// Aucune partie lancée : retour à l'accueil
if (!isset($_SESSION['nombre_secret'])) {
    header('Location: index.php');
    exit;
}

$niveau = $_SESSION['niveau'];
$message = '';
$erreur = '';

// ===== 2. Le joueur propose un nombre =====
if (isset($_POST['nombre']) && $_SESSION['etat'] == 'en_cours') {
    $nombre = trim($_POST['nombre']);

    // Saisie vérifiée en PHP : une erreur ne coûte pas d'essai
    if (!ctype_digit($nombre)) {
        $erreur = 'Merci de saisir un nombre entier.';
    } elseif ($nombre < $niveau['nombre_min'] || $nombre > $niveau['nombre_max']) {
        $erreur = 'Le nombre doit être compris entre ' . $niveau['nombre_min'] . ' et ' . $niveau['nombre_max'] . '.';
    } else {
        // Saisie valide : l'essai compte
        $nombre = (int) $nombre;
        $_SESSION['essais'][] = $nombre;

        if ($nombre == $_SESSION['nombre_secret']) {
            $_SESSION['etat'] = 'gagnee';
        } elseif (count($_SESSION['essais']) >= $niveau['essais_max']) {
            $_SESSION['etat'] = 'perdue';
        } elseif ($nombre < $_SESSION['nombre_secret']) {
            $message = 'C\'est plus !';
        } else {
            $message = 'C\'est moins !';
        }

        // ===== 3. Partie terminée (gagnée ou perdue) : on l'enregistre en base =====
        if ($_SESSION['etat'] != 'en_cours') {
            $_SESSION['duree'] = time() - $_SESSION['debut'];

            $gagnee = 0;
            if ($_SESSION['etat'] == 'gagnee') {
                $gagnee = 1;
            }

            $requete = $dbh->prepare('INSERT INTO partie (id_joueur, id_niveau, nombre_secret, essais_utilises, gagnee, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $requete->execute([
                $_SESSION['id_joueur'],
                $niveau['id_niveau'],
                $_SESSION['nombre_secret'],
                count($_SESSION['essais']),
                $gagnee,
                date('Y-m-d H:i:s', $_SESSION['debut']),
                date('Y-m-d H:i:s')
            ]);
        }
    }
}

$essais_restants = $niveau['essais_max'] - count($_SESSION['essais']);

require 'header.php';

echo '<div class="titre-jeu">';
echo '<h1>Niveau ' . htmlspecialchars($niveau['nom']) . ' - entre ' . $niveau['nombre_min'] . ' et ' . $niveau['nombre_max'] . '</h1>';
echo '<span class="badge">Essais restants : ' . $essais_restants . ' / ' . $niveau['essais_max'] . '</span>';
echo '</div>';

if ($_SESSION['etat'] == 'gagnee') {
    // Fin de partie : gagnée
    echo '<div class="resultat gagne">';
    echo '<h2>Gagné !</h2>';
    echo '<p>Le nombre était ' . $_SESSION['nombre_secret'] . '.</p>';
    echo '<p>Trouvé en ' . count($_SESSION['essais']) . ' essais et ' . $_SESSION['duree'] . ' secondes.</p>';
    echo '</div>';
} elseif ($_SESSION['etat'] == 'perdue') {
    // Fin de partie : perdue
    echo '<div class="resultat perdu">';
    echo '<h2>Perdu...</h2>';
    echo '<p>Plus d\'essais disponibles.</p>';
    echo '<p>Le nombre secret était ' . $_SESSION['nombre_secret'] . '.</p>';
    echo '</div>';
} else {
    // Partie en cours : message d'erreur ou indice, puis le formulaire
    if ($erreur != '') {
        echo '<p class="erreur">' . $erreur . '</p>';
    }
    if ($message != '') {
        echo '<p class="indice">' . $message . '</p>';
    }
    echo '<form class="proposer" method="post" action="jeu.php">';
    echo '<input type="text" name="nombre" placeholder="Votre nombre" autocomplete="off" autofocus>';
    echo '<button type="submit">Proposer</button>';
    echo '</form>';
}

// Fin de partie : rejouer le même niveau ou en changer
if ($_SESSION['etat'] != 'en_cours') {
    echo '<div class="boutons">';
    echo '<a class="bouton" href="jeu.php?niveau=' . $niveau['id_niveau'] . '">Rejouer</a>';
    echo '<a class="bouton secondaire" href="index.php">Changer de niveau</a>';
    echo '</div>';
}

// Historique des essais : chaque essai est comparé au nombre secret ici, côté serveur
if (count($_SESSION['essais']) > 0) {
    echo '<h3>Vos essais</h3>';
    echo '<ul class="essais">';
    foreach ($_SESSION['essais'] as $essai) {
        if ($essai < $_SESSION['nombre_secret']) {
            echo '<li>' . $essai . ' <span class="plus">C\'est plus</span></li>';
        } elseif ($essai > $_SESSION['nombre_secret']) {
            echo '<li>' . $essai . ' <span class="moins">C\'est moins</span></li>';
        } else {
            echo '<li>' . $essai . ' <span class="trouve">Trouvé</span></li>';
        }
    }
    echo '</ul>';
}

require 'footer.php';
