<?php
session_start();
require_once 'config.php';

// Page réservée au joueur connecté
if (!isset($_SESSION['id_joueur'])) {
    header('Location: connexion.php');
    exit;
}

$id_joueur = $_SESSION['id_joueur'];

// ===== 1. Chiffres globaux : tout est calculé en SQL à partir des parties enregistrées =====
$requete = $dbh->prepare('SELECT COUNT(*) AS nb FROM partie WHERE id_joueur = ?');
$requete->execute([$id_joueur]);
$ligne = $requete->fetch(PDO::FETCH_ASSOC);
$nb_parties = $ligne['nb'];

$requete = $dbh->prepare('SELECT COUNT(*) AS nb FROM partie WHERE id_joueur = ? AND gagnee = 1');
$requete->execute([$id_joueur]);
$ligne = $requete->fetch(PDO::FETCH_ASSOC);
$nb_victoires = $ligne['nb'];

// Pourcentage de réussite (on évite la division par zéro si aucune partie)
$reussite = 0;
if ($nb_parties > 0) {
    $reussite = round($nb_victoires * 100 / $nb_parties);
}

// ===== 2. Les niveaux, pour le détail par niveau =====
$requete = $dbh->prepare('SELECT * FROM niveau ORDER BY id_niveau');
$requete->execute();
$niveaux = $requete->fetchAll(PDO::FETCH_ASSOC);

// ===== 3. Les 10 dernières parties du joueur =====
$requete = $dbh->prepare("SELECT DATE_FORMAT(partie.date_fin, '%d/%m/%Y %H:%i') AS date_partie, niveau.nom, partie.essais_utilises, partie.gagnee
                          FROM partie
                          JOIN niveau ON partie.id_niveau = niveau.id_niveau
                          WHERE partie.id_joueur = ?
                          ORDER BY partie.date_fin DESC
                          LIMIT 10");
$requete->execute([$id_joueur]);
$dernieres_parties = $requete->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';

echo '<h1>Mes statistiques</h1>';

echo '<div class="chiffres">';
echo '<div class="chiffre"><strong>' . $nb_parties . '</strong>parties jouées</div>';
echo '<div class="chiffre"><strong>' . $nb_victoires . '</strong>victoires</div>';
echo '<div class="chiffre"><strong>' . $reussite . ' %</strong>de réussite</div>';
echo '</div>';

// Détail par niveau : pour chaque niveau, deux petites requêtes
echo '<h2>Par niveau</h2>';
echo '<table>';
echo '<tr><th>Niveau</th><th>Parties</th><th>Victoires</th><th>Moy. essais</th><th>Meilleur</th></tr>';
foreach ($niveaux as $niveau) {
    // Toutes les parties du joueur sur ce niveau
    $requete = $dbh->prepare('SELECT COUNT(*) AS nb FROM partie WHERE id_joueur = ? AND id_niveau = ?');
    $requete->execute([$id_joueur, $niveau['id_niveau']]);
    $parties = $requete->fetch(PDO::FETCH_ASSOC);

    // Seulement ses parties gagnées sur ce niveau : nombre, moyenne d'essais, meilleur score
    $requete = $dbh->prepare('SELECT COUNT(*) AS nb, ROUND(AVG(essais_utilises), 1) AS moyenne, MIN(essais_utilises) AS meilleur
                              FROM partie
                              WHERE id_joueur = ? AND id_niveau = ? AND gagnee = 1');
    $requete->execute([$id_joueur, $niveau['id_niveau']]);
    $victoires = $requete->fetch(PDO::FETCH_ASSOC);

    // Aucune victoire sur ce niveau : pas de moyenne ni de meilleur score
    $moyenne = '-';
    $meilleur = '-';
    if ($victoires['nb'] > 0) {
        $moyenne = $victoires['moyenne'];
        $meilleur = $victoires['meilleur'];
    }

    echo '<tr>';
    echo '<td>' . htmlspecialchars($niveau['nom']) . '</td>';
    echo '<td>' . $parties['nb'] . '</td>';
    echo '<td>' . $victoires['nb'] . '</td>';
    echo '<td>' . $moyenne . '</td>';
    echo '<td>' . $meilleur . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '<p class="note">Moyenne et meilleur score calculés sur les parties gagnées.</p>';

// Historique
echo '<h2>Dernières parties</h2>';
if (count($dernieres_parties) == 0) {
    echo '<p>Aucune partie jouée pour l\'instant.</p>';
} else {
    echo '<table>';
    echo '<tr><th>Date</th><th>Niveau</th><th>Essais</th><th>Résultat</th></tr>';
    foreach ($dernieres_parties as $partie) {
        echo '<tr>';
        echo '<td>' . $partie['date_partie'] . '</td>';
        echo '<td>' . htmlspecialchars($partie['nom']) . '</td>';
        echo '<td>' . $partie['essais_utilises'] . '</td>';
        if ($partie['gagnee'] == 1) {
            echo '<td class="gagne">Gagnée</td>';
        } else {
            echo '<td class="perdu">Perdue</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

require 'footer.php';
