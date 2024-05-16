<?php
global $cnx;
session_start();
require_once '../includes/connection.php';
require_once '../classes/Agent.php';
require_once '../classes/Client.php';
require_once '../classes/Compte.php';
require_once '../classes/Operation.php';
require_once '../classes/Versement.php';
require_once '../classes/Retrait.php';
require_once '../classes/Placement.php';
require_once '../classes/Credit.php';

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'ROLE_ADMIN') {
    header('Location: ./login.php');
    exit();
}

// Fetch agents, operations, and credits from the database
$agents = Agent::findAll($cnx);
$operations = Operation::getAllOperations($cnx);
$credits = Credit::findAll($cnx);

// Handle adding an agent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addAgent'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $agent = new Agent($cnx, 0, $password, $username, $role);
    $agent->ajout();

    header('Location: adminView.php');
    exit();
}

// Handle deleting an agent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteAgent'])) {
    $agentId = $_POST['agentId'];

    $agent = new Agent($cnx);
    $agent->setAgentId($agentId);
    $agent->delete();

    header('Location: adminView.php');
    exit();
}

// Handle accepting a credit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acceptCredit'])) {
    $creditId = $_POST['creditId'];
    Credit::updateStatus($cnx, $creditId, 'Accepted');
    header('Location: adminView.php');
    exit();
}

// Handle refusing a credit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refuseCredit'])) {
    $creditId = $_POST['creditId'];
    Credit::updateStatus($cnx, $creditId, 'Refused');
    header('Location: adminView.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BankPro Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet" href="../includes/index.css">
</head>
<body>

<div class="app">
    <header class="app-header">
        <div class="app-header-logo">
            <div class="logo">
                <span class="logo-icon">
                    <img src="https://assets.codepen.io/285131/almeria-logo.svg" />
                </span>
                <h1 class="logo-title">
                    <span>BankPro</span>
                    <span>BankPro</span>
                </h1>
            </div>
        </div>
        <div class="app-header-actions">
            <span>Administration de la Banque</span>
            <div class="app-header-actions-buttons"></div>
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
                <a href="./logout.php">Logout</a>
                <br>
                <button class="btn btn-secondary" onclick="location.href='./agentView.php'">Switch to Agent</button>
                <h2>Gestion des utilisateurs</h2>
                <div class="filter-options">
                    <button class="icon-button" onclick="toggleAddAgentForm()">
                        <i class="ph-plus"></i> Add Agent
                    </button>
                </div>
                <div id="add-agent-form" style="display:none;">
                    <form method="post" action="adminView.php">
                        <input type="hidden" name="addAgent" value="true"/>
                        <label for="agentUsername">Agent Username:</label>
                        <input type="text" id="agentUsername" name="username" required><br>
                        <label for="agentPassword">Agent Password:</label>
                        <input type="password" id="agentPassword" name="password" required><br>
                        <label for="agentRole">Agent Role:</label>
                        <select id="agentRole" name="role" required>
                            <option value="ROLE_ADMIN">Admin</option>
                            <option value="ROLE_AGENT">Agent</option>
                            <option value="ROLE_CLIENT">Client</option>
                        </select><br>
                        <button type="submit">Add Agent</button>
                    </form>
                </div>
                <br>
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td><?= htmlspecialchars($agent->getAgentId()) ?></td>
                            <td><?= htmlspecialchars($agent->getUsername()) ?></td>
                            <td>[Encrypted]</td>
                            <td><?= htmlspecialchars($agent->getRole()) ?></td>
                            <td class="action-cell">
                                <form method="post" action="adminView.php">
                                    <input type="hidden" name="deleteAgent" value="true"/>
                                    <input type="hidden" name="agentId" value="<?= $agent->getAgentId() ?>"/>
                                    <button type="submit" class="icon-button delete-button">
                                        <i class="ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="transfer-section">
                <div class="transfer-section-header">
                    <h2>Historique des Transactions</h2>
                </div>
                <br>
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type de Transaction</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Frais</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($operations as $operation): ?>
                        <tr>
                            <td><?= htmlspecialchars($operation->getNumOp()) ?></td>
                            <td><?= htmlspecialchars($operation->getType())?></td>
                            <td><?= htmlspecialchars($operation->getDateOp()) ?></td>
                            <td><?= htmlspecialchars($operation->getMontant()) ?></td>
                            <td><?= htmlspecialchars($operation->getFraisOp()) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="credit-section">
                <div class="credit-section-header">
                    <h2>Gestion des Crédits</h2>
                </div>
                <br>
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Client Code</th>
                        <th>Compte Numéro</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($credits as $credit): ?>
                        <tr>
                            <td><?= htmlspecialchars($credit->getNumCre()) ?></td>
                            <td><?= htmlspecialchars($credit->getDateCre()) ?></td>
                            <td><?= htmlspecialchars($credit->getMontCre()) ?></td>
                            <td><?= htmlspecialchars($credit->getCodeCli()) ?></td>
                            <td><?= htmlspecialchars($credit->getNumCpt()) ?></td>
                            <td><?= htmlspecialchars($credit->getStatus()) ?></td>
                            <td>
                                <?php if ($credit->getStatus() == 'En attente'): ?>
                                    <form method="post" action="adminView.php" style="display:inline;">
                                        <input type="hidden" name="acceptCredit" value="true"/>
                                        <input type="hidden" name="creditId" value="<?= $credit->getNumCre() ?>"/>
                                        <button type="submit" class="icon-button accept-button">
                                            <i class="ph-check"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="adminView.php" style="display:inline;">
                                        <input type="hidden" name="refuseCredit" value="true"/>
                                        <input type="hidden" name="creditId" value="<?= $credit->getNumCre() ?>"/>
                                        <button type="submit" class="icon-button refuse-button">
                                            <i class="ph-x"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <!-- PDF Open Button -->
                                <a href="../uploads/<?= htmlspecialchars($credit->getFilesPath()) ?>" target="_blank">
                                    <button class="icon-button open-button">
                                        <i class="ph-file-pdf"></i> Open PDF
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

        </div>
    </div>

    <footer class="footer">
        <h1>BankPro<small>©</small></h1>
        <div>All Rights Reserved 2024</div>
    </footer>
</div>

<script src='https://unpkg.com/phosphor-icons'></script>
<script>
    function toggleAddAgentForm() {
        var form = document.getElementById('add-agent-form');
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>

</body>
</html>
