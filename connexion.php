<?php
session_start();
require_once 'config.php';

$erreur = '';
$pseudo = '';

// Le formulaire a été envoyé
if (isset($_POST['pseudo']) && isset($_POST['mot_de_passe'])) {
    $pseudo = trim($_POST['pseudo']);

    if ($pseudo == '' || $_POST['mot_de_passe'] == '') {
        $erreur = 'Merci de remplir tous les champs.';
    } else {
        // On cherche le joueur avec ce pseudo
        $requete = $dbh->prepare('SELECT * FROM joueur WHERE pseudo = ?');
        $requete->execute([$pseudo]);
        $joueur = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$joueur) {
            $erreur = 'Ce pseudo n\'existe pas.';
        } elseif (!password_verify($_POST['mot_de_passe'], $joueur['mot_de_passe'])) {
            $erreur = 'Mot de passe incorrect.';
        } else {
            // Tout est bon : on garde le joueur en session puis on retourne à l'accueil
            $_SESSION['id_joueur'] = $joueur['id_joueur'];
            $_SESSION['pseudo'] = $joueur['pseudo'];
            header('Location: index.php');
            exit;
        }
    }
}

require 'header.php';
?>

<div class="formulaire">
    <h1>Connexion</h1>

    <?php
    // Message affiché quand on arrive juste après l'inscription
    if (isset($_GET['inscrit'])) {
        echo '<p class="succes">Compte créé ! Vous pouvez maintenant vous connecter.</p>';
    }
    if ($erreur != '') {
        echo '<p class="erreur">' . $erreur . '</p>';
    }
    ?>

    <form method="post" action="connexion.php">
        <label for="pseudo">Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($pseudo); ?>">

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe">

        <button type="submit">Se connecter</button>
    </form>

    <p><a href="inscription.php">Pas encore de compte ? Créer un compte</a></p>
</div>

<?php require 'footer.php'; ?>
