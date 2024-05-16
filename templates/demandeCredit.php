<?php
global $cnx;
session_start();
require_once '../includes/connection.php';
require_once '../classes/Credit.php';
require_once '../classes/Client.php';

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
    $codeCli = $_POST['codeCli'];
    $montCre = $_POST['montCre'];
    $file = $_FILES['file'];

    // Check if client exists
    $client = (new Client($cnx))->getClient($codeCli);
    if (!$client) {
        $error = 'Client not found';
    } else {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            $fileName = basename($file['name']);
            $uploadFilePath = $uploadDir . $fileName;

            // Check if the file is a PDF
            if (mime_content_type($file['tmp_name']) !== 'application/pdf') {
                $error = 'Only PDF files are allowed';
            } else {
                if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                    $currentDateTime = new DateTime();
                    $credit = new Credit(0,$currentDateTime->format('Y-m-d H:i:s') , $montCre, $codeCli, $numCpt, $fileName, 'En attente');
                    try {
                        $credit->save($cnx);
                        $success = 'Credit application submitted successfully!';
                    } catch (Exception $e) {
                        $error = 'Failed to save credit application: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Failed to move uploaded file';
                }
            }
        } else {
            $error = 'Please select a file to upload';
        }
    }

    if ($error) {
        $_SESSION['error'] = $error;
    } else {
        $_SESSION['success'] = $success;
    }

    header('Location: demandeCredit.php');
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BankPro</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">
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

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="numCpt">Numero Compte</label>
                    <input type="number" id="numCpt" name="numCpt" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="codeCli">Code client</label>
                    <input type="number" id="codeCli" name="codeCli" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="montCre">Montant</label>
                <input type="number" id="montCre" name="montCre" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="file">Demande PDF</label>
                <input type="file" id="file" name="file" accept="application/pdf" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Valider</button>
        </form>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="js/vendor/modernizr-3.11.2.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/creerCompte.js"></script>
<script>
    window.ga = function () { ga.q.push(arguments) }; ga.q = []; ga.l = +new Date;
    ga('create', 'UA-XXXXX-Y', 'auto'); ga('set', 'anonymizeIp', true); ga('set', 'transport', 'beacon'); ga('send', 'pageview')
</script>
<script src="https://www.google-analytics.com/analytics.js" async></script>
</body>

</html>
