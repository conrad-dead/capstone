<?php
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role_id']) || (int)$_SESSION['user_role_id'] !== 2) {
        header('Location: ../login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Hello to clinician dashboard</h1>
    <a href="../logout.php">logout click mo</a>
</body>
</html>