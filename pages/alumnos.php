<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Alumnos</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-center mb-6">Alumnos Registrados</h1>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
          </tr>
        </thead>
        <tbody id="tabla-alumnos" class="bg-white divide-y divide-gray-200">
          <!-- Aquí se van a insertar los alumnos dinámicamente -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    async function cargarAlumnos() {
      try {
        const res = await fetch('../api/get_alumnos.php');
        const alumnos = await res.json();

        const tabla = document.getElementById('tabla-alumnos');
        tabla.innerHTML = '';

        alumnos.forEach(alumno => {
          tabla.innerHTML += `
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">${alumno.matricula}</td>
              <td class="px-6 py-4 whitespace-nowrap">${alumno.nombre}</td>
              <td class="px-6 py-4 whitespace-nowrap">${alumno.grado}</td>
              <td class="px-6 py-4 whitespace-nowrap">${alumno.correo}</td>
            </tr>
          `;
        });
      } catch (error) {
        console.error('Error cargando alumnos:', error);
      }
    }

    window.onload = cargarAlumnos;
  </script>
</body>
</html>
