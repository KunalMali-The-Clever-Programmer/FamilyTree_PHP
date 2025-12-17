<?php
include 'db.php';
$id = $_GET['id'] ?? 0;
$conn->query("DELETE FROM members WHERE id=$id");
header('Location: index.php');
?>
