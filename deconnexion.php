<?php
session_start();

// On vide puis on détruit la session : le joueur est déconnecté (une partie en cours est abandonnée)
session_unset();
session_destroy();

header('Location: index.php');
exit;
