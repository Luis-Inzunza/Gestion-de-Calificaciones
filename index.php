<?php
require './pages/login/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: ./pages/login/login.php');
  exit;
}

$nombre = $_SESSION['nombre'] ?? 'Usuario Fantasma';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion de Calificaciones</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col items-center justify-center p-4">
  <header class="bg-blue-700 text-white p-4 flex justify-between items-center shadow">
    <div class="text-lg font-semibold">
      Bienvenido, <?= htmlspecialchars($nombre) ?>
    </div>
    <a href="login/logout.php" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
      Cerrar Sesión
    </a>
  </header>
  <div class="bg-white shadow-lg rounded-xl p-6 w-full max-w-xl">
    <h1 class="text-2xl font-bold mb-4 text-center">Sistema Distribuido</h1>
    
    <div id="datos" class="mb-4">
      <!-- Aquí se cargarán los datos desde la capa lógica -->
      <p class="text-gray-600">Cargando datos...</p>
    </div>

    <button onclick="cargarDatos()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Recargar Datos
    </button>
    <a href="./pages/alumnos.php">ir a los alumnos</a>
    <br>
    <a href="./pages/login/login.php">ir a login</a>
    <br>
    <a href="./pages/calificaciones.php">ir a calificaciones</a>
    <br>
    <a href="./pages/asignaturas.php">ir a asignaturas</a>
  </div>

  <script>
    async function cargarDatos() {
      const contenedor = document.getElementById('datos');
      contenedor.innerHTML = '<p class="text-gray-600">Cargando...</p>';

      try {
        const res = await fetch('./api/get_datos.php');
        const data = await res.json();

        contenedor.innerHTML = `
          <ul class="list-disc pl-5 space-y-1">
            ${data.map(item => `<li>${item}</li>`).join('')}
          </ul>
        `;
      } catch (error) {
        contenedor.innerHTML = '<p class="text-red-600">Error al cargar datos</p>';
      }
    }

    window.onload = cargarDatos;
  </script>
</body>
</html>
