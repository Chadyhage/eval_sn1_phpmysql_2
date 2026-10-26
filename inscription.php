<?php
session_start();
require_once 'config.php';

$erreur = '';
$pseudo = '';
$email = '';

// Le formulaire a été envoyé
if (isset($_POST['pseudo']) && isset($_POST['email']) && isset($_POST['mot_de_passe'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifications en PHP, avec un message clair pour chaque problème
    if ($pseudo == '' || $email == '' || $mot_de_passe == '') {
        $erreur = 'Merci de remplir tous les champs.';
    } elseif (strlen($pseudo) > 50) {
        $erreur = 'Le pseudo ne doit pas dépasser 50 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'adresse email n\'est pas valide.';
    } else {
        // Le pseudo doit être unique : on regarde s'il existe déjà
        $requete = $dbh->prepare('SELECT * FROM joueur WHERE pseudo = ?');
        $requete->execute([$pseudo]);

        if ($requete->rowCount() > 0) {
            $erreur = 'Ce pseudo est déjà utilisé.';
        } else {
            // On hashe le mot de passe puis on enregistre le compte
            $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $requete = $dbh->prepare('INSERT INTO joueur (pseudo, email, mot_de_passe) VALUES (?, ?, ?)');
            $requete->execute([$pseudo, $email, $hash]);

            // Compte créé : direction la page de connexion
            header('Location: connexion.php?inscrit=1');
            exit;
        }
    }
}

require 'header.php';
?>

<div class="formulaire">
    <h1>Créer un compte</h1>

    <?php
    if ($erreur != '') {
        echo '<p class="erreur">' . $erreur . '</p>';
    }
    ?>

    <form method="post" action="inscription.php">
        <label for="pseudo">Pseudo</label>
        <input type="text" id="pseudo" name="pseudo" maxlength="50" value="<?php echo htmlspecialchars($pseudo); ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe">

        <button type="submit">Créer mon compte</button>
    </form>

    <p><a href="connexion.php">Déjà un compte ? Se connecter</a></p>
</div>

<?php require 'footer.php'; ?>
