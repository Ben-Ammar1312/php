<?php
global $cnx;
session_start();
require_once '../includes/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['pswd'];

    // Query the database to find the user
    $stmt = $cnx->prepare("SELECT * FROM agent WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, start the session
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] == 'ROLE_AGENT') {
            header('Location: ./agentView.php');
        } elseif ($user['role'] == 'ROLE_ADMIN') {
            header('Location: ./adminView.php');
        } else header('Location: ./clientView.php'); // Redirect to the client view page
        exit();
    } else {
        // Invalid login
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="../includes/login.css">
</head>
<body>
<div class="main">




    <div class="login">
        <form method="POST" action="">
            <br><br>
            <label for="chk">Login</label>
            <input type="text" name="username" placeholder="username" required="">
            <input type="password" name="pswd" placeholder="Password" required="">
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
