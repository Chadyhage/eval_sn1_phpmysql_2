<?php
session_start();
require_once 'config.php';

// Page visible par tout le monde, même sans compte

// Les niveaux, pour afficher un onglet par niveau
$requete = $dbh->prepare('SELECT * FROM niveau ORDER BY id_niveau');
$requete->execute();
$niveaux = $requete->fetchAll(PDO::FETCH_ASSOC);

// Niveau demandé dans l'adresse (classement.php?niveau=2). Par défaut : le premier niveau.
// On ne garde la valeur que si elle correspond à un vrai niveau de la base.
$niveau_choisi = $niveaux[0];
if (isset($_GET['niveau'])) {
    foreach ($niveaux as $niveau) {
        if ($niveau['id_niveau'] == $_GET['niveau']) {
            $niveau_choisi = $niveau;
        }
    }
}

// Les 10 meilleures parties gagnées du niveau : le moins d'essais, puis le plus rapide
// TIMESTAMPDIFF calcule la durée en secondes entre le début et la fin de la partie
$requete = $dbh->prepare("SELECT joueur.pseudo, partie.essais_utilises,
                                 TIMESTAMPDIFF(SECOND, partie.date_debut, partie.date_fin) AS duree,
                                 DATE_FORMAT(partie.date_fin, '%d/%m/%Y') AS date_partie
                          FROM partie
                          JOIN joueur ON partie.id_joueur = joueur.id_joueur
                          WHERE partie.id_niveau = ? AND partie.gagnee = 1
                          ORDER BY partie.essais_utilises ASC, duree ASC
                          LIMIT 10");
$requete->execute([$niveau_choisi['id_niveau']]);
$parties = $requete->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';

echo '<h1>Classement</h1>';

// Un onglet par niveau ; celui qui est affiché est mis en avant
echo '<div class="onglets">';
foreach ($niveaux as $niveau) {
    if ($niveau['id_niveau'] == $niveau_choisi['id_niveau']) {
        echo '<a class="onglet actif" href="classement.php?niveau=' . $niveau['id_niveau'] . '">' . htmlspecialchars($niveau['nom']) . '</a>';
    } else {
        echo '<a class="onglet" href="classement.php?niveau=' . $niveau['id_niveau'] . '">' . htmlspecialchars($niveau['nom']) . '</a>';
    }
}
echo '</div>';

if (count($parties) == 0) {
    echo '<p>Aucune partie gagnée pour ce niveau.</p>';
} else {
    echo '<table>';
    echo '<tr><th>#</th><th>Joueur</th><th>Essais</th><th>Durée</th><th>Date</th></tr>';
    $rang = 1;
    foreach ($parties as $partie) {
        echo '<tr>';
        echo '<td>' . $rang . '</td>';
        echo '<td>' . htmlspecialchars($partie['pseudo']) . '</td>';
        echo '<td>' . $partie['essais_utilises'] . '</td>';
        echo '<td>' . $partie['duree'] . ' s</td>';
        echo '<td>' . $partie['date_partie'] . '</td>';
        echo '</tr>';
        $rang++;
    }
    echo '</table>';
}

require 'footer.php';
