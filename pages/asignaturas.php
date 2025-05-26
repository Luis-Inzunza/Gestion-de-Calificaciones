<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Asignaturas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-center mb-6">Asignaturas Registradas</h1>
    
    <!-- Botón para agregar nueva asignatura -->
    <div class="flex justify-end mb-4">
      <button onclick="mostrarFormulario()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
        Nueva Asignatura
      </button>
    </div>

    <!-- Formulario flotante -->
    <div id="formulario-asignatura" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 id="form-titulo" class="text-xl font-bold mb-4">Nueva Asignatura</h2>
        
        <form id="asignatura-form" class="space-y-4">
          <input type="hidden" id="id_asignatura">
          
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Asignatura</label>
            <input type="text" id="nombre" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <div>
            <label for="id_grado" class="block text-sm font-medium text-gray-700">Grado</label>
            <select id="id_grado" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option value="">Seleccione un grado</option>
              <!-- Se llenará dinámicamente -->
            </select>
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

    <!-- Tabla de asignaturas -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-asignaturas" class="bg-white divide-y divide-gray-200">
          <!-- Datos se cargarán dinámicamente -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Variables globales
    const API_BASE_URL = 'http://localhost:8081/api/asignaturas';
    let grados = [];

    // Elementos DOM
    const formulario = document.getElementById('formulario-asignatura');
    const asignaturaForm = document.getElementById('asignatura-form');
    const formTitulo = document.getElementById('form-titulo');
    const selectGrado = document.getElementById('id_grado');

    // Cargar datos iniciales
    async function inicializar() {
      await cargarGrados();
      await cargarAsignaturas();
    }

    // Cargar grados para el combo
    async function cargarGrados() {
      try {
        const res = await fetch('http://localhost:8081/api/grados');
        if (!res.ok) throw new Error('Error al cargar grados');
        
        grados = await res.json();
        selectGrado.innerHTML = '<option value="">Seleccione un grado</option>';
        
        grados.forEach(grado => {
          const option = document.createElement('option');
          option.value = grado.id;
          option.textContent = grado.nombre_grado;
          selectGrado.appendChild(option);
        });
      } catch (error) {
        Swal.fire('Error', error.message, 'error');
      }
    }

    // Cargar asignaturas
    async function cargarAsignaturas() {
      try {
        const res = await fetch(API_BASE_URL);
        if (!res.ok) throw new Error('Error al cargar asignaturas');
        
        const asignaturas = await res.json();
        const tabla = document.getElementById('tabla-asignaturas');
        tabla.innerHTML = '';

        asignaturas.forEach(asignatura => {
          const grado = grados.find(g => g.id == asignatura.grado.id) || {};
          
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${asignatura.nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap">${grado.nombre_grado || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap space-x-2">
              <button onclick="editarAsignatura(${asignatura.id})" class="text-blue-500 hover:text-blue-700">
                Editar
              </button>
              <button onclick="eliminarAsignatura(${asignatura.id})" class="text-red-500 hover:text-red-700">
                Eliminar
              </button>
            </td>
          `;
          tabla.appendChild(row);
        });
      } catch (error) {
        Swal.fire('Error', error.message, 'error');
      }
    }

    // Mostrar/ocultar formulario
    function mostrarFormulario(asignatura = null) {
      if (asignatura) {
        formTitulo.textContent = 'Editar Asignatura';
        document.getElementById('id_asignatura').value = asignatura.id;
        document.getElementById('nombre').value = asignatura.nombre;
        document.getElementById('id_grado').value = asignatura.grado.id;
      } else {
        formTitulo.textContent = 'Nueva Asignatura';
        asignaturaForm.reset();
      }
      formulario.classList.remove('hidden');
    }

    function ocultarFormulario() {
      formulario.classList.add('hidden');
    }

    // Manejar envío del formulario
    asignaturaForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const asignatura = {
        nombre: document.getElementById('nombre').value,
        id_grado: document.getElementById('id_grado').value
      };
      
      const id = document.getElementById('id_asignatura').value;
      const method = id ? 'PUT' : 'POST';
      const url = id ? `${API_BASE_URL}/${id}` : API_BASE_URL;
      
      try {
        const res = await fetch(url, {
          method: method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(asignatura)
        });
        
        if (!res.ok) throw new Error(await res.text());
        
        Swal.fire('Éxito', 'Asignatura guardada correctamente', 'success');
        ocultarFormulario();
        cargarAsignaturas();
      } catch (error) {
        Swal.fire('Error', error.message, 'error');
      }
    });

    // Editar asignatura
    async function editarAsignatura(id) {
      try {
        const res = await fetch(`${API_BASE_URL}/${id}`);
        const asignatura = await res.json();
        mostrarFormulario(asignatura);
        cargarAsignaturas();
      } catch (error) {
        Swal.fire('Error', 'No se pudo cargar la asignatura', 'error');
      }
    }

    // Eliminar asignatura
    async function eliminarAsignatura(id) {
      try {
        const result = await Swal.fire({
          title: '¿Eliminar asignatura?',
          text: "Esta acción afectará todas sus calificaciones relacionadas",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
          await fetch(`${API_BASE_URL}/${id}`, { method: 'DELETE' });
          Swal.fire('Eliminada', 'La asignatura fue eliminada', 'success');
          cargarAsignaturas();
        }
      } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar', 'error');
      }
    }

    // Inicializar
    window.onload = inicializar;
  </script>
</body>
</html>