<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Calificaciones</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-center mb-6">Registro de Calificaciones</h1>

    <div class="flex justify-end mb-4">
      <button onclick="mostrarFormulario()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
        Nueva Calificación
      </button>
    </div>

    <!-- Formulario flotante -->
    <div id="formulario-calificacion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 id="form-titulo" class="text-xl font-bold mb-4">Nueva Calificación</h2>
        
        <form id="calificacion-form" class="space-y-4">
          <input type="hidden" id="id_calificacion">
          
          <div>
            <label for="id_alumno" class="block text-sm font-medium text-gray-700">Alumno</label>
            <select id="id_alumno" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option value="">Seleccione un alumno</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Grado del Alumno</label>
            <div id="grado-alumno" class="mt-1 p-2 bg-gray-50 rounded-md">-</div>
          </div>
          
          <div>
            <label for="id_asignatura" class="block text-sm font-medium text-gray-700">Asignatura</label>
            <select id="id_asignatura" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option value="">Seleccione una asignatura</option>
            </select>
          </div>
          
          <div>
            <label for="calificacion" class="block text-sm font-medium text-gray-700">Calificación (0-100)</label>
            <input type="number" id="calificacion" min="0" max="100" step="0.01" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <div class="flex justify-end space-x-3">
            <button type="button" onclick="ocultarFormulario()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
              Cancelar
            </button>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabla de calificaciones -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alumno</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-calificaciones" class="bg-white divide-y divide-gray-200">
          <!-- Datos se cargarán dinámicamente -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Configuración
    const API_BASE_URL = '../api/calificaciones';
    let alumnos = [];
    let asignaturas = [];
    let grados = [];

    // Elementos DOM
    const formulario = document.getElementById('formulario-calificacion');
    const formCalificacion = document.getElementById('calificacion-form');
    const formTitulo = document.getElementById('form-titulo');

    // Cargar datos iniciales
    async function inicializar() {
      await Promise.all([
        cargarGrados(),
        cargarAlumnos(),
        cargarAsignaturas(),
        cargarCalificaciones()
      ]);
    }

    // Cargar grados
    async function cargarGrados() {
      try {
        const res = await fetch('../api/grados');
        grados = await res.json();
      } catch (error) {
        console.error('Error cargando grados:', error);
      }
    }

    // Cargar combos
    async function cargarAlumnos() {
      try {
        const res = await fetch('../api/alumnos');
        alumnos = await res.json();
        const select = document.getElementById('id_alumno');
        
        alumnos.forEach(alumno => {
          const option = document.createElement('option');
          option.value = alumno.id_alumno;
          option.textContent = `${alumno.matricula} - ${alumno.nombre}`;
          option.dataset.grado = alumno.id_grado; // Almacenar el grado del alumno
          select.appendChild(option);
        });

        // Evento para actualizar grado al seleccionar alumno
        select.addEventListener('change', (e) => {
          const idAlumno = e.target.value;
          const alumno = alumnos.find(a => a.id_alumno == idAlumno);
          if (alumno) {
            const grado = grados.find(g => g.id_grado == alumno.id_grado);
            document.getElementById('grado-alumno').textContent = grado ? grado.nombre_grado : 'N/A';
            actualizarAsignaturasPorGrado(alumno.id_grado);
          }
        });
      } catch (error) {
        console.error('Error cargando alumnos:', error);
      }
    }

    // Actualizar combo de asignaturas según grado
    function actualizarAsignaturasPorGrado(idGrado) {
      const select = document.getElementById('id_asignatura');
      select.innerHTML = '<option value="">Seleccione una asignatura</option>';
      
      asignaturas
        .filter(a => a.id_grado == idGrado)
        .forEach(a => {
          const option = document.createElement('option');
          option.value = a.id_asignatura;
          option.textContent = a.nombre;
          select.appendChild(option);
        });
    }

    async function cargarAsignaturas() {
      try {
        const res = await fetch('../api/asignaturas');
        asignaturas = await res.json();
      } catch (error) {
        console.error('Error cargando asignaturas:', error);
      }
    }

    // CRUD Calificaciones
    async function cargarCalificaciones() {
      try {
        const res = await fetch(API_BASE_URL);
        const calificaciones = await res.json();
        const tabla = document.getElementById('tabla-calificaciones');
        tabla.innerHTML = '';

        calificaciones.forEach(calificacion => {
          const alumno = alumnos.find(a => a.id_alumno == calificacion.id_alumno) || {};
          const asignatura = asignaturas.find(a => a.id_asignatura == calificacion.id_asignatura) || {};
          const grado = grados.find(g => g.id_grado == (alumno.id_grado || asignatura.id_grado)) || {};
          
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${alumno.nombre || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${grado.nombre_grado || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${asignatura.nombre || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap">${calificacion.calificacion}</td>
            <td class="px-6 py-4 whitespace-nowrap space-x-2">
              <button onclick="editarCalificacion(${calificacion.id_calificacion})" class="text-blue-500 hover:text-blue-700">
                Editar
              </button>
              <button onclick="eliminarCalificacion(${calificacion.id_calificacion})" class="text-red-500 hover:text-red-700">
                Eliminar
              </button>
            </td>
          `;
          tabla.appendChild(row);
        });
      } catch (error) {
        console.error('Error cargando calificaciones:', error);
      }
    }

    // Resto del código (mostrarFormulario, ocultarFormulario, enviar formulario, editar, eliminar) 
    // se mantiene igual que en la versión anterior...
    
    // Inicializar
    window.onload = inicializar;
  </script>
</body>
</html>