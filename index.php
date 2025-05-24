<?php
session_start();
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>l3en-Commerce-Project</title>
    <link rel="stylesheet" href="./assets/css/admin.css">
</head>

<body>
    <?php include('./backoffice/layouts/sidebar.php'); ?>
    <div class="main-container">
        <?php include('./backoffice/dashboard.php'); ?>
    </div>
</body>
<footer>
    <p>&copy; 2023 l3enpify. All rights reserved.</p>
</html>
