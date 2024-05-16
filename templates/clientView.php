<?php
global $cnx;
require_once '../includes/connection.php';
require_once '../classes/Agent.php';
require_once '../classes/Client.php';
require_once '../classes/Compte.php';
require_once '../classes/Operation.php';
require_once '../classes/Versement.php';
require_once '../classes/Retrait.php';
require_once '../classes/Placement.php';



// Mock authentication for example purposes (replace with actual authentication logic)
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'defaultUser';

$agent = new Agent($cnx);
$client = new Client($cnx);
$compte = new Compte($cnx);

// Get agent details by username
$agentData = $agent->getAgentByUsername($cnx,$username);
if (!$agentData) {
    die("User not found");
}

// Get client details by agent ID
$clientData = $client->getClient($agentData->getAgentId());
if (!$clientData) {
    die("Client details not found");
}

// Get account details by client code
$compteData = $compte->getCompteByClientCode($clientData->getCodeCli());
if (!$compteData) {
    die("Account details not found");
}

// Get operations by account number
$operations = Operation::getOperationsByCompte($cnx, $compteData->getNumCpt());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BankPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet" href="../includes/index.css">
</head>
<body>
<div class="app">
    <header class="app-header">
        <div class="app-header-logo">
            <div class="logo">
                <span class="logo-icon">
                    <img src="/img/logo.svg" alt="Logo">
                </span>
                <h1 class="logo-title">
                    <span>BankPro</span>
                    <span>BankPro</span>
                </h1>
            </div>
        </div>

        <div class="app-header-actions">
            <button class="user-profile">
                <span><?= htmlspecialchars($clientData->getNomCli()) ?> <?= htmlspecialchars($clientData->getPreCli())?></span>
                <span>
                    <img src="https://assets.codepen.io/285131/almeria-avatar.jpeg" alt="User Avatar">
                </span>
            </button>
            <div class="app-header-actions-buttons">
                <button class="icon-button large">
                    <i class="ph-magnifying-glass"></i>
                </button>
                <button class="icon-button large">
                    <i class="ph-bell"></i>
                </button>
            </div>
        </div>
        <div class="app-header-mobile">
            <button class="icon-button large">
                <i class="ph-list"></i>
            </button>
        </div>
    </header>
    <div class="app-body">
        <div class="app-body-main-content">
            <section class="service-section">
                <a href="logout.php">Logout</a>

                <h2>Account Details</h2>
                <div>
                    <p><strong>Account Number:</strong> <span><?= htmlspecialchars($compteData->getNumCpt())?></span></p>
                    <p><strong>Initial Balance:</strong> <span><?= htmlspecialchars(number_format($compteData->getMontantInit(), 2)) ?></span></p>
                    <p><strong>Current Balance:</strong> <span><?= htmlspecialchars(number_format($compteData->getMontantGlobal(), 2)) ?></span></p>
                </div>
            </section>
            <section class="transaction-history">
                <h2>Transaction History</h2>
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($operations as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction->getDateOp()) ?></td>
                            <td><?= htmlspecialchars($transaction->getType()) ?></td>
                            <td><?= htmlspecialchars(number_format($transaction->getMontant(), 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
        <footer class="footer">
            <h1>BankPro<small>©</small></h1>
            <div>
                <br>
                All Rights Reserved 2024
            </div>
        </footer>
    </div>
</div>
<script src='https://unpkg.com/phosphor-icons'></script>
</body>
</html>
