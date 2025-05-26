<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Alumnos</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-center mb-6">Alumnos Registrados</h1>
    
    <!-- Botón para agregar nuevo alumno -->
    <div class="flex justify-end mb-4">
      <button onclick="mostrarFormulario()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
        Nuevo Alumno
      </button>
    </div>

    <!-- Formulario flotante (oculto por defecto) -->
    <div id="formulario-alumno" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 id="form-titulo" class="text-xl font-bold mb-4">Nuevo Alumno</h2>
        
        <form id="alumno-form" class="space-y-4">
          <input type="hidden" id="id_alumno">
          
          <div>
            <label for="matricula" class="block text-sm font-medium text-gray-700">Matrícula</label>
            <input type="text" id="matricula" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" id="nombre" required
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
          </div>
          
          <div>
            <label for="id_grado" class="block text-sm font-medium text-gray-700">Grado</label>
            <select id="id_grado" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
              <option value="">Seleccione un grado</option>
              <?php for ($i = 1; $i <= 6; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?>° Primaria</option>
              <?php endfor; ?>
            </select>
          </div>
          
          <div>
            <label for="correo_electronico" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
            <input type="email" id="correo_electronico"
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

    <!-- Tabla de alumnos -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grado</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-alumnos" class="bg-white divide-y divide-gray-200">
          <!-- Aquí se van a insertar los alumnos dinámicamente -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Variables globales
    const API_BASE_URL = 'http://localhost:8081/api/alumnos'; // Ajusta esta URL según tu backend
    
    // DOM Elements
    const formulario = document.getElementById('formulario-alumno');
    const alumnoForm = document.getElementById('alumno-form');
    const formTitulo = document.getElementById('form-titulo');
    
    // Mostrar/ocultar formulario
    function mostrarFormulario(alumno = null) {
      if (alumno) {
        formTitulo.textContent = 'Editar Alumno';
        document.getElementById('id_alumno').value = alumno.id;
        document.getElementById('matricula').value = alumno.matricula;
        document.getElementById('nombre').value = alumno.nombre;
        document.getElementById('id_grado').value = alumno.grado.id_grado;
        document.getElementById('correo_electronico').value = alumno.correoElectronico;
      } else {
        formTitulo.textContent = 'Nuevo Alumno';
        alumnoForm.reset();
      }
      formulario.classList.remove('hidden');
    }
    
    function ocultarFormulario() {
      formulario.classList.add('hidden');
    }
    
    // Manejar envío del formulario
    alumnoForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const alumno = {
        matricula: document.getElementById('matricula').value,
        nombre: document.getElementById('nombre').value,
        correoElectronico: document.getElementById('correo_electronico').value,
        id_grado: document.getElementById('id_grado').value
      };
      
      const id = document.getElementById('id_alumno').value;
      const method = id ? 'PUT' : 'POST';
      const url = id ? `${API_BASE_URL}/${id}` : API_BASE_URL;
      
      try {
        const res = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(alumno)
        });
        
        if (!res.ok) throw new Error('Error al guardar el alumno');
        
        const data = await res.json();
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: id ? 'Alumno actualizado correctamente' : 'Alumno creado correctamente'
        });
        
        ocultarFormulario();
        cargarAlumnos();
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message
        });
      }
    });
    
    // Cargar alumnos
    async function cargarAlumnos() {
      try {
        const res = await fetch(API_BASE_URL);
        if (!res.ok) throw new Error('Error al cargar alumnos');
        
        const alumnos = await res.json();
        const tabla = document.getElementById('tabla-alumnos');
        tabla.innerHTML = '';
        
        alumnos.forEach(alumno => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${alumno.matricula}</td>
            <td class="px-6 py-4 whitespace-nowrap">${alumno.nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap">${alumno.grado.id}° Primaria</td>
            <td class="px-6 py-4 whitespace-nowrap">${alumno.correoElectronico || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap space-x-2">
              <button onclick="editarAlumno(${alumno.id})" class="text-blue-500 hover:text-blue-700">
                Editar
              </button>
              <button onclick="eliminarAlumno(${alumno.id})" class="text-red-500 hover:text-red-700">
                Eliminar
              </button>
            </td>
          `;
          tabla.appendChild(row);
        });
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message
        });
      }
    }
    
    // Editar alumno
    async function editarAlumno(id) {
      try {
        const res = await fetch(`${API_BASE_URL}/${id}`);
        if (!res.ok) throw new Error('Error al cargar alumno');
        
        const alumno = await res.json();
        mostrarFormulario(alumno);
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message
        });
      }
    }
    
    // Eliminar alumno
    async function eliminarAlumno(id) {
      try {
        const result = await Swal.fire({
          title: '¿Estás seguro?',
          text: "No podrás revertir esta acción",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e84421',
          cancelButtonColor: '#b6c6d4',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
          const res = await fetch(`${API_BASE_URL}/${id}`, {
            method: 'DELETE'
          });
          
          if (!res.ok) throw new Error('Error al eliminar alumno');
          
          Swal.fire(
            'Eliminado!',
            'El alumno ha sido eliminado.',
            'success'
          );
          
          cargarAlumnos();
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message
        });
      }
    }
    
    // Inicializar
    window.onload = cargarAlumnos;
  </script>
</body>
</html>