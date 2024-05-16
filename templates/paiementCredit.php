<?php
global $cnx;
session_start();
require_once '../includes/connection.php';
require_once '../classes/Credit.php';
require_once '../classes/Agent.php';

// Ensure the user is logged in and has the role of agent or admin
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['ROLE_AGENT', 'ROLE_ADMIN'])) {
    header('Location: ./login.php');
    exit();
}

$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $creditNumber = $_POST['creditNumber'];
    $paymentAmount = $_POST['paymentAmount'];

    try {
        $credit = Credit::findByNumCre($cnx, $creditNumber);
        if (!$credit) {
            throw new Exception("Numéro de crédit invalide");
        }

        if ($paymentAmount <= 0) {
            throw new Exception("Le montant du paiement doit être supérieur à zéro");
        }

        if ($credit->getMontCre() > $paymentAmount) {
            $credit->setMontCre($credit->getMontCre() - $paymentAmount);
            $credit->update($cnx);
            $message = "Paiement réussi. Montant restant du crédit: " . $credit->getMontCre();
        }elseif ($credit->getMontCre() == $paymentAmount) {
            $credit->delete($cnx);
            $message = "vous avez payer la totalite du credit ";
        }

        else {
            throw new Exception("Le montant du paiement dépasse le montant du crédit");
        }
    } catch (Exception $e) {
        $message = 'Erreur: ' . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paiement Credit - BankPro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 20px;
        }
    </style>
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

<div class="container">
    <h2>Paiement Credit</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="creditNumber">Numéro de Crédit</label>
            <input type="text" class="form-control" id="creditNumber" name="creditNumber" required>
        </div>
        <div class="form-group">
            <label for="paymentAmount">Montant à Payer</label>
            <input type="number" class="form-control" id="paymentAmount" name="paymentAmount" required>
        </div>
        <button type="submit" class="btn btn-primary">Payer</button>
    </form>
    <?php if ($message): ?>
        <div class="alert alert-info">
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>
