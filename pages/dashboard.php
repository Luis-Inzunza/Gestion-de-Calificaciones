<?php
require 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Bienvenido, <?= $_SESSION['profile'] ?></h1>
    <p class="mb-4">Has ingresado correctamente al sistema distribuido. Qué emoción.</p>
    <a href="logout.php" class="text-blue-600 underline">Cerrar sesión</a>
  </div>
</body>
</html>
