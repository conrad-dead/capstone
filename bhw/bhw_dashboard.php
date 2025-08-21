<?php
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role_name']) || strtolower($_SESSION['user_role_name']) !== 'bhw') {
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
    <h1>Hello to bhw dashboard</h1>
    <a href="../logout.php">logout click mo</a>
<!--Tangina mo-->
</body>
</html>