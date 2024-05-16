<?php
global $cnx;
session_start();
require_once '../includes/connection.php';
require_once '../classes/Operation.php';
require_once '../classes/Versement.php';
require_once '../classes/Retrait.php';
require_once '../classes/Agent.php';
require_once '../classes/Compte.php';

// Ensure the user is logged in and has the role of agent or admin
if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['ROLE_AGENT', 'ROLE_ADMIN'])) {
    header('Location: ./login.php');
    exit();
}

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numCpt = $_POST['numCpt'];
    $operationType = $_POST['operationType'];
    $montant = $_POST['montant'];

    try {
        $compte = Compte::findByNumCpt($cnx, $numCpt);
        if (!$compte) {
            throw new Exception("Compte not found");
        }

        $agent = Agent::getAgentByUsername($cnx, $_SESSION['username']);
        $dateOp = (new DateTime())->format('Y-m-d H:i:s');

        if ($operationType === 'DEPOT') {
            $operation = new Versement($cnx, $compte->getNumCpt(), $dateOp, $agent->getAgentId(), 0, $montant);
            $compte->setMontantGlobal($compte->getMontantGlobal() + $montant);
        } elseif ($operationType === 'RETRAIT') {
            if ($compte->getMontantGlobal() >= $montant) {
                $operation = new Retrait($cnx,  $compte->getNumCpt(), $dateOp, $agent->getAgentId(), 0, $montant);
                $compte->setMontantGlobal($compte->getMontantGlobal() - $montant);
            } else {
                throw new Exception("Insufficient funds");
            }
        } else {
            throw new Exception("Invalid operation type");
        }
        $operation->ajout($cnx);
        $compte->update();

        $_SESSION['success'] = "Operation processed successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error processing operation: ' . $e->getMessage();
    }

    header('Location: depot-retrait.php');
    exit();
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

    <form action="depot-retrait.php" method="post">
        <div>
            <p>Numéro du compte</p>
            <input class="form-control" type="number" name="numCpt" placeholder="Numéro du compte" required>
        </div>
        <p>Type d'opération</p>
        <select class="form-control form-control-lg" name="operationType">
            <option value="DEPOT">Dépot</option>
            <option value="RETRAIT">Retrait</option>
        </select>
        <div>
            <p>Montant</p>
            <input class="form-control" type="number" name="montant" placeholder="5 euros minimum" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Valider</button>
    </form>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="js/vendor/modernizr-3.11.2.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/depot-retrait.js"></script>

<script>
    window.ga = function () { ga.q.push(arguments) }; ga.q = []; ga.l = +new Date;
    ga('create', 'UA-XXXXX-Y', 'auto'); ga('set', 'anonymizeIp', true); ga('set', 'transport', 'beacon'); ga('send', 'pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async></script>
</body>
</html>
