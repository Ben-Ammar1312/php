<?php
global $cnx;
session_start();
require_once '../includes/connection.php';
require_once '../classes/Client.php';
require_once '../classes/Compte.php';

// Ensure the user is logged in and has the role of agent or admin
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['ROLE_AGENT', 'ROLE_ADMIN'])) {
    header('Location: ./login.php');
    exit();
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomCli = $_POST['nomCli'];
    $preCli = $_POST['preCli'];
    $dateNaissance = $_POST['dateNaissance'];
    $numTel = $_POST['numTel'];
    $emailCli = $_POST['emailCli'];
    $adrCli = $_POST['adrCli'];
    $montant = $_POST['montant'];

    // Validate and parse the date
    $dateObj = DateTime::createFromFormat('Y-m-d', $dateNaissance);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $dateNaissance) {
        $error = 'Invalid date format. Please use YYYY-MM-DD.';
    } else {
        try {
            $cnx->beginTransaction();

            // Insert the new client
            $client = new Client($cnx, 0, $nomCli, $preCli, $dateNaissance, $numTel, $emailCli, $adrCli);
            $client->ajout($cnx);

            // Insert the new account
            $compte = new Compte($cnx, 0, $montant, $montant, $client->getCodeCli());
            $compte->ajout();

            $cnx->commit();
            $success = 'Account created successfully!';
        } catch (Exception $e) {
            $cnx->rollBack();
            $error = 'Error creating account: ' . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BankPro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="../includes/main.css">
</head>
<body>

<nav>
    <ul class="nav nav-pills nav-fill">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Operations</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../templates/depot-retrait.php">Dépot / Retrait</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../templates/virement.php">Virement</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../templates/placement.php">Placement</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../templates/paiementCredit.php">Paiement Credit</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Compte</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../templates/creerCompte.php">Créer un compte</a>
                <a class="dropdown-item" href="../templates/supprimer.php">Supprimer un compte</a>
                <a class="dropdown-item" href="../templates/demandeCredit.php">Demande de credit</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Accueil</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="../templates/agentView.php">Accueil</a>
                <a class="dropdown-item" href="../templates/logout.php">Deconnection</a>
            </div>
        </li>
    </ul>
</nav>

<div class="app-body">
    <main class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <form action="creerCompte.php" method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nomCli">Nom</label>
                    <input type="text" id="nomCli" name="nomCli" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="preCli">Prenom</label>
                    <input type="text" id="preCli" name="preCli" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="dateNaissance">Date de naissance</label>
                <input type="date" id="dateNaissance" name="dateNaissance" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="numTel">Numero Tel</label>
                <input type="text" id="numTel" name="numTel" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="emailCli">Email</label>
                <input type="email" id="emailCli" name="emailCli" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="adrCli">Adresse</label>
                <input type="text" id="adrCli" name="adrCli" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="montant">Montant à déposer sur votre nouveau compte</label>
                <input type="number" id="montant" name="montant" class="form-control" placeholder="50 euros minimum" required>
            </div>
            <button type="submit" class="btn btn-primary">Valider</button>
        </form>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
        $('.dropdown-toggle').dropdown();
    });
</script>
</body>
</html>
