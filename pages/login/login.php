<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pass = $_POST['password'] ?? '';

  if ($pass === PASSWORD) {
    $_SESSION['logged_in'] = true;
    $_SESSION['profile'] = PROFILE;
    header('Location: ../../index.php');
    exit;
  } else {
    $error = "Contraseña incorrecta. Tú no pasas.";
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 h-screen flex items-center justify-center">
  <form method="POST" class="bg-white p-8 rounded shadow-md w-full max-w-sm">
    <h2 class="text-2xl font-bold mb-4 text-center">Acceso al Sistema</h2>

    <?php if (!empty($error)): ?>
      <div class="mb-4 text-red-600 text-sm"><?= $error ?></div>
    <?php endif; ?>

    <input
      type="password"
      name="password"
      placeholder="Contraseña"
      class="w-full px-4 py-2 border rounded mb-4"
      required
    />
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
      Entrar
    </button>
  </form>
</body>
</html>
