<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plus ou Moins</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="entete">
    <a class="logo" href="index.php">± Plus ou Moins</a>
    <nav>
        <a href="index.php">Jouer</a>
        <a href="classement.php">Classement</a>
        <?php
        // Le menu change selon que le joueur est connecté ou non
        if (isset($_SESSION['id_joueur'])) {
            echo '<a href="statistiques.php">Mes stats</a>';
            echo '<span class="pseudo">' . htmlspecialchars($_SESSION['pseudo']) . '</span>';
            echo '<a href="deconnexion.php">Se déconnecter</a>';
        } else {
            echo '<a href="connexion.php">Connexion</a>';
            echo '<a href="inscription.php">Créer un compte</a>';
        }
        ?>
    </nav>
</header>

<main>
