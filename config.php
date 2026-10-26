<?php
// Connexion à la base de données (incluse dans chaque page avec require_once)
try {
    $dbh = new PDO('mysql:host=localhost;dbname=plusoumoins;charset=utf8mb4', 'root', '');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Heure de Paris pour les dates des parties (Wamp est réglé sur UTC par défaut)
date_default_timezone_set('Europe/Paris');
