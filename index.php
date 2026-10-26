<?php
session_start();
require_once 'config.php';

// Les niveaux sont lus dans la base : ajouter une ligne dans la table niveau suffit à créer un nouveau niveau
$requete = $dbh->prepare('SELECT * FROM niveau ORDER BY id_niveau');
$requete->execute();
$niveaux = $requete->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';

echo '<h1>Choisissez votre niveau</h1>';

echo '<div class="niveaux">';
foreach ($niveaux as $niveau) {
    echo '<div class="carte">';
    echo '<h2>' . htmlspecialchars($niveau['nom']) . '</h2>';
    echo '<p>Entre ' . $niveau['nombre_min'] . ' et ' . $niveau['nombre_max'] . '</p>';
    echo '<p>' . $niveau['essais_max'] . ' essais</p>';
    // jeu.php renvoie vers la connexion si le visiteur n'est pas connecté
    echo '<a class="bouton" href="jeu.php?niveau=' . $niveau['id_niveau'] . '">Jouer</a>';
    echo '</div>';
}
echo '</div>';

require 'footer.php';
