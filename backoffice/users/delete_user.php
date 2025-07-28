<?php
require_once '../../includes/db.php';
session_start();

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['user_deleted'] = true;
}

header('Location: users.php');
exit;
