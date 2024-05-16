<?php
global $cnx;
session_start();
require_once '../classes/Operation.php';
require_once '../includes/connection.php';
require_once '../classes/Compte.php';
require_once '../classes/Placement.php';
require_once '../classes/Agent.php';



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
    $montant = $_POST['montant'];

    try {
        $cnx->beginTransaction();

        // Get the logged-in agent's ID
        $agent = Agent::getAgentByUsername($cnx,$_SESSION['username']);
        $agentId = $agent->getAgentId();

        $compte = Compte::findByNumCpt($cnx, $numCpt);
        if (!$compte) {
            throw new Exception("Compte non trouvé");
        }

        if ($compte->getMontantGlobal() < $montant) {
            throw new Exception("Fonds insuffisants");
        }

        // Subtract the placement amount from the account
        $compte->setMontantGlobal($compte->getMontantGlobal() - $montant);
        $compte->update();

        // Create the placement with 7% interest
        $interest = $montant * 0.07;
        $montantPlusInterest = $montant + $interest;
        $placement = new Placement($cnx, 0, date('Y-m-d H:i:s'), $agentId, 0.0, $montant, $interest, $montantPlusInterest);
        $placement->setCompte($numCpt);
        $placement->ajout();

        $cnx->commit();
        $success = 'Placement effectué avec succès!';
    } catch (Exception $e) {
        $cnx->rollBack();
        $error = 'Erreur lors du placement: ' . $e->getMessage();
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

    <form action="" method="post">
        <p>Compte à faire le placement</p>
        <input class="form-control" type="number" name="numCpt" placeholder="Numéro du compte" required>
        <div>
            <p>Montant à placer</p>
            <input id="montant" class="form-control" type="number" name="montant" placeholder="Montant à placer" oninput="calculateInterest()">
        </div>
        <br>
        <p>Montant avec intérêt (7%)</p>
        <input id="montantPlusInterest" class="form-control" type="text" readonly>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Valider le placement</button>
    </form>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="js/vendor/modernizr-3.11.2.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/virement.js"></script>

<script>
    function calculateInterest() {
        var montant = document.getElementById('montant').value;
        var interest = montant * 0.07; // Calculate 7% interest
        var total = parseFloat(montant) + parseFloat(interest); // Sum up the principal and interest
        document.getElementById('montantPlusInterest').value = total.toFixed(2); // Update the read-only field
    }

    window.ga = function () { ga.q.push(arguments) }; ga.q = []; ga.l = +new Date;
    ga('create', 'UA-XXXXX-Y', 'auto'); ga('set', 'anonymizeIp', true); ga('set', 'transport', 'beacon'); ga('send', 'pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async></script>
</body>

</html>
