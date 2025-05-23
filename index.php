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
<body class="">

  <header class="bg-blue-700 text-white p-4 flex shadow justify-between">
    <div class="text-lg font-semibold">
      Bienvenido, <?= htmlspecialchars($nombre) ?>
    </div>
    <a href="login/logout.php" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
      Cerrar Sesión
    </a>
  </header>

<section class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
  <!-- Alumnos -->
  <div class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center">
    <h2 class="text-xl font-bold mb-4">Alumnos</h2>
    <img src="./images/alumno.png" alt="alumno" class="w-32 h-32 mb-4">
    <a href="./pages/alumnos.php" class="text-blue-600 underline">Ir a Alumnos</a>
  </div>

  <!-- Calificaciones -->
  <div class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center">
    <h2 class="text-xl font-bold mb-4">Calificaciones</h2>
    <img src="./images/calificaciones.png" alt="calificaciones" class="w-32 h-32 mb-4">
    <a href="./pages/calificaciones.php" class="text-blue-600 underline">Ir a Calificaciones</a>
  </div>

  <!-- Asignaturas -->
  <div class="bg-white shadow-lg rounded-xl p-6 flex flex-col items-center">
    <h2 class="text-xl font-bold mb-4">Asignaturas</h2>
    <img src="./images/asignaturas.png" alt="asignaturas" class="w-32 h-32 mb-4">
    <a href="./pages/asignaturas.php" class="text-blue-600 underline">Ir a Asignaturas</a>
  </div>
</section>


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
