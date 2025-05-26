<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reportes Académicos en PDF</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-center mb-6">Reportes Académicos en PDF</h1>

    <!-- Pestañas de reportes -->
    <div class="flex border-b mb-6">
      <button onclick="mostrarReporte('boleta')" class="report-tab py-2 px-4 font-medium text-sm rounded-t-lg border-b-2 border-blue-500 text-blue-600">
        Boleta Individual
      </button>
      <button onclick="mostrarReporte('asignaturas')" class="report-tab py-2 px-4 font-medium text-sm text-gray-500 hover:text-blue-600">
        Por Asignatura
      </button>
      <button onclick="mostrarReporte('regulares')" class="report-tab py-2 px-4 font-medium text-sm text-gray-500 hover:text-blue-600">
        Estudiantes Regulares
      </button>
      <button onclick="mostrarReporte('irregulares')" class="report-tab py-2 px-4 font-medium text-sm text-gray-500 hover:text-blue-600">
        Estudiantes Irregulares
      </button>
    </div>

    <!-- Controles de reporte -->
    <div id="report-controls" class="bg-white shadow rounded-lg p-6 mb-6">
      <!-- Se llenará dinámicamente según el reporte seleccionado -->
    </div>

    <!-- Vista previa (opcional) -->
    <div class="bg-white shadow rounded-lg p-6 hidden" id="preview-container">
      <h2 class="text-xl font-bold mb-4">Vista Previa del Reporte</h2>
      <div id="report-preview"></div>
      <button onclick="generarPDF()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
        Descargar PDF
      </button>
    </div>
  </div>

  <script>
    // Variables globales
    const { jsPDF } = window.jspdf;
    const API_BASE_URL = 'http://localhost:8081/api';
    let alumnos = [];
    let asignaturas = [];
    let grados = [];
    let calificaciones = [];
    let currentReport = { type: '', data: null };

    // Cargar todos los datos necesarios
    async function cargarDatos() {
      try {
        [alumnos, asignaturas, grados, calificaciones] = await Promise.all([
          fetchData('/alumnos'),
          fetchData('/asignaturas'),
          fetchData('/grados'),
          fetchData('/calificaciones')
        ]);
        
        // Mostrar el primer reporte por defecto
        mostrarReporte('boleta');
      } catch (error) {
        console.error('Error cargando datos:', error);
        alert('Error al cargar datos. Por favor recarga la página.');
      }
    }

    // Función genérica para fetch
    async function fetchData(endpoint) {
      const res = await fetch(`${API_BASE_URL}${endpoint}`);
      return await res.json();
    }

    // Mostrar controles para el reporte seleccionado
    function mostrarReporte(tipo) {
      // Actualizar pestañas activas
      document.querySelectorAll('.report-tab').forEach(tab => {
        tab.classList.remove('border-b-2', 'border-blue-500', 'text-blue-600');
        tab.classList.add('text-gray-500');
      });
      //event.target.classList.add('border-b-2', 'border-blue-500', 'text-blue-600');
      //event.target.classList.remove('text-gray-500');

      const container = document.getElementById('report-controls');
      document.getElementById('preview-container').classList.add('hidden');
      
      switch(tipo) {
        case 'boleta':
          container.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Boleta de Calificaciones</h2>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Seleccione un alumno:</label>
              <select id="select-alumno" class="w-full border border-gray-300 rounded-md p-2">
                <option value="">-- Seleccione --</option>
                ${alumnos.map(a => `
                  <option value="${a.id}">${a.matricula} - ${a.nombre} (${getGradoNombre(a.grado.id_grado)})</option>
                `).join('')}
              </select>
            </div>
            <button onclick="prepararBoleta()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
              Generar Vista Previa
            </button>
          `;
          break;

        case 'asignaturas':
          container.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Calificaciones por Asignatura</h2>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Seleccione una asignatura:</label>
              <select id="select-asignatura" class="w-full border border-gray-300 rounded-md p-2">
                <option value="">-- Seleccione --</option>
                ${asignaturas.map(a => `
                  <option value="${a.id_asignatura}">${a.asignatura.nombre} (${getGradoNombre(a.grado.id_grado)})</option>
                `).join('')}
              </select>
            </div>
            <button onclick="prepararReporteAsignatura()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
              Generar Vista Previa
            </button>
          `;
          break;

        case 'regulares':
          container.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Estudiantes Regulares</h2>
            <p class="mb-4">Estudiantes sin materias reprobadas agrupados por grado</p>
            <button onclick="prepararReporteRegulares()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
              Generar Reporte Completo
            </button>
          `;
          break;

        case 'irregulares':
          container.innerHTML = `
            <h2 class="text-xl font-bold mb-4">Estudiantes Irregulares</h2>
            <p class="mb-4">Estudiantes con materias reprobadas agrupados por grado</p>
            <button onclick="prepararReporteIrregulares()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
              Generar Reporte Completo
            </button>
          `;
          break;
      }
    }

    // Obtener nombre del grado
    function getGradoNombre(idGrado) {
      const grado = grados.find(g => g.id_grado == idGrado);
      return grado ? grado.nombre_grado : 'N/A';
    }

    // Preparar datos para boleta individual
    function prepararBoleta() {
      const idAlumno = document.getElementById('select-alumno').value;
      if (!idAlumno) {
        alert('Seleccione un alumno');
        return;
      }

      const alumno = alumnos.find(a => a.id == idAlumno);
      const califsAlumno = calificaciones.filter(c => c.id == idAlumno);
      const promedio = calcularPromedio(califsAlumno);

      // Preparar datos para PDF
      currentReport = {
        type: 'boleta',
        data: {
          alumno,
          calificaciones: califsAlumno.map(c => {
            return {
              asignatura: c.asignatura.nombre || 'N/A',
              calificacion: c.calificacion.toFixed(1),
              estatus: c.calificacion >= 60 ? 'Aprobado' : 'Reprobado'
            };
          }),
          promedio: promedio.toFixed(1),
          grado: getGradoNombre(alumno.grado.id_grado)
        }
      };

      // Mostrar vista previa
      mostrarVistaPrevia();
    }

    // Preparar reporte por asignatura
    function prepararReporteAsignatura() {
      const idAsignatura = document.getElementById('select-asignatura').value;
      if (!idAsignatura) {
        alert('Seleccione una asignatura');
        return;
      }

      const asignatura = asignaturas.find(a => a.id_asignatura == idAsignatura);
      const califsAsignatura = calificaciones.filter(c => c.id_asignatura == idAsignatura);
      const promedio = calcularPromedio(califsAsignatura);

      // Preparar datos para PDF
      currentReport = {
        type: 'asignatura',
        data: {
          asignatura,
          calificaciones: califsAsignatura.map(c => {
            const alumno = alumnos.find(a => a.id_alumno == c.id_alumno) || {};
            return {
              alumno: alumno.nombre || 'N/A',
              calificacion: c.calificacion.toFixed(1),
              estatus: c.calificacion >= 60 ? 'Aprobado' : 'Reprobado'
            };
          }),
          promedio: promedio.toFixed(1),
          grado: getGradoNombre(asignatura.id_grado)
        }
      };

      // Mostrar vista previa
      mostrarVistaPrevia();
    }

    // Preparar reporte de estudiantes regulares
    function prepararReporteRegulares() {
      const reporteData = [];
      const alumnosPorGrado = agruparAlumnosPorGrado();

      Object.keys(alumnosPorGrado).forEach(idGrado => {
        const alumnosGrado = alumnosPorGrado[idGrado];
        const regulares = alumnosGrado.filter(alumno => {
          const califs = calificaciones.filter(c => c.id_alumno == alumno.id_alumno);
          return califs.every(c => c.calificacion >= 60) && califs.length > 0;
        });

        if (regulares.length > 0) {
          reporteData.push({
            grado: getGradoNombre(idGrado),
            alumnos: regulares.map(alumno => {
              const califs = calificaciones.filter(c => c.id_alumno == alumno.id_alumno);
              const promedio = calcularPromedio(califs);
              
              return {
                matricula: alumno.matricula,
                nombre: alumno.nombre,
                promedio: promedio.toFixed(1)
              };
            })
          });
        }
      });

      currentReport = {
        type: 'regulares',
        data: reporteData
      };

      // Mostrar vista previa
      mostrarVistaPrevia();
    }

    // Preparar reporte de estudiantes irregulares
    function prepararReporteIrregulares() {
      const reporteData = [];
      const alumnosPorGrado = agruparAlumnosPorGrado();

      Object.keys(alumnosPorGrado).forEach(idGrado => {
        const alumnosGrado = alumnosPorGrado[idGrado];
        const irregulares = alumnosGrado.filter(alumno => {
          const califs = calificaciones.filter(c => c.id_alumno == alumno.id_alumno);
          return califs.some(c => c.calificacion < 60);
        });

        if (irregulares.length > 0) {
          reporteData.push({
            grado: getGradoNombre(idGrado),
            alumnos: irregulares.map(alumno => {
              const califs = calificaciones.filter(c => c.id_alumno == alumno.id_alumno);
              const reprobadas = califs.filter(c => c.calificacion < 60).length;
              const promedio = calcularPromedio(califs);
              
              return {
                matricula: alumno.matricula,
                nombre: alumno.nombre,
                reprobadas,
                promedio: promedio.toFixed(1)
              };
            })
          });
        }
      });

      currentReport = {
        type: 'irregulares',
        data: reporteData
      };

      // Mostrar vista previa
      mostrarVistaPrevia();
    }

    // Mostrar vista previa del reporte
    function mostrarVistaPrevia() {
      const preview = document.getElementById('report-preview');
      preview.innerHTML = '';
      
      switch(currentReport.type) {
        case 'boleta':
          preview.innerHTML = `
            <h3 class="text-lg font-semibold mb-2">Boleta de Calificaciones</h3>
            <p><strong>Alumno:</strong> ${currentReport.data.alumno.nombre}</p>
            <p><strong>Grado:</strong> ${currentReport.data.grado}</p>
            <table class="min-w-full border mt-4">
              <thead>
                <tr class="bg-gray-100">
                  <th class="border p-2">Asignatura</th>
                  <th class="border p-2">Calificación</th>
                  <th class="border p-2">Estatus</th>
                </tr>
              </thead>
              <tbody>
                ${currentReport.data.calificaciones.map(c => `
                  <tr>
                    <td class="border p-2">${c.asignatura}</td>
                    <td class="border p-2">${c.calificacion}</td>
                    <td class="border p-2">${c.estatus}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
            <p class="mt-4"><strong>Promedio General:</strong> ${currentReport.data.promedio}</p>
          `;
          break;

        case 'asignatura':
          preview.innerHTML = `
            <h3 class="text-lg font-semibold mb-2">Calificaciones por Asignatura</h3>
            <p><strong>Asignatura:</strong> ${currentReport.data.asignatura.nombre}</p>
            <p><strong>Grado:</strong> ${currentReport.data.grado}</p>
            <table class="min-w-full border mt-4">
              <thead>
                <tr class="bg-gray-100">
                  <th class="border p-2">Alumno</th>
                  <th class="border p-2">Calificación</th>
                  <th class="border p-2">Estatus</th>
                </tr>
              </thead>
              <tbody>
                ${currentReport.data.calificaciones.map(c => `
                  <tr>
                    <td class="border p-2">${c.alumno}</td>
                    <td class="border p-2">${c.calificacion}</td>
                    <td class="border p-2">${c.estatus}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
            <p class="mt-4"><strong>Promedio del Grupo:</strong> ${currentReport.data.promedio}</p>
          `;
          break;

        case 'regulares':
          currentReport.data.forEach(grupo => {
            preview.innerHTML += `
              <div class="mb-8">
                <h3 class="text-lg font-semibold mb-2">${grupo.grado}</h3>
                <table class="min-w-full border">
                  <thead>
                    <tr class="bg-gray-100">
                      <th class="border p-2">Matrícula</th>
                      <th class="border p-2">Nombre</th>
                      <th class="border p-2">Promedio</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${grupo.alumnos.map(a => `
                      <tr>
                        <td class="border p-2">${a.matricula}</td>
                        <td class="border p-2">${a.nombre}</td>
                        <td class="border p-2">${a.promedio}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
                <p class="mt-2 text-sm text-gray-500">Total: ${grupo.alumnos.length} estudiantes</p>
              </div>
            `;
          });
          break;

        case 'irregulares':
          currentReport.data.forEach(grupo => {
            preview.innerHTML += `
              <div class="mb-8">
                <h3 class="text-lg font-semibold mb-2">${grupo.grado}</h3>
                <table class="min-w-full border">
                  <thead>
                    <tr class="bg-gray-100">
                      <th class="border p-2">Matrícula</th>
                      <th class="border p-2">Nombre</th>
                      <th class="border p-2">Materias Reprobadas</th>
                      <th class="border p-2">Promedio</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${grupo.alumnos.map(a => `
                      <tr>
                        <td class="border p-2">${a.matricula}</td>
                        <td class="border p-2">${a.nombre}</td>
                        <td class="border p-2">${a.reprobadas}</td>
                        <td class="border p-2">${a.promedio}</td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
                <p class="mt-2 text-sm text-gray-500">Total: ${grupo.alumnos.length} estudiantes</p>
              </div>
            `;
          });
          break;
      }

      document.getElementById('preview-container').classList.remove('hidden');
    }

    // Generar PDF
    function generarPDF() {
      const doc = new jsPDF();
      const fecha = new Date().toLocaleDateString();
      
      // Configuración común
      doc.setFont('helvetica');
      doc.setTextColor(40);

      switch(currentReport.type) {
        case 'boleta':
          // Encabezado
          doc.setFontSize(16);
          doc.text('Boleta de Calificaciones', 105, 20, { align: 'center' });
          doc.setFontSize(12);
          doc.text(`Fecha: ${fecha}`, 15, 30);
          
          // Datos del alumno
          doc.setFontSize(14);
          doc.text(`Alumno: ${currentReport.data.alumno.nombre}`, 15, 40);
          doc.text(`Grado: ${currentReport.data.grado}`, 15, 50);
          doc.text(`Matrícula: ${currentReport.data.alumno.matricula}`, 15, 60);
          
          // Tabla de calificaciones
          doc.autoTable({
            startY: 70,
            head: [['Asignatura', 'Calificación', 'Estatus']],
            body: currentReport.data.calificaciones.map(c => [c.asignatura, c.calificacion, c.estatus]),
            styles: { fontSize: 10 },
            headStyles: { fillColor: [220, 220, 220] }
          });
          
          // Promedio
          const finalY = doc.lastAutoTable.finalY + 10;
          doc.setFontSize(12);
          doc.text(`Promedio General: ${currentReport.data.promedio}`, 15, finalY);
          
          // Pie de página
          doc.setFontSize(10);
          doc.text('Escuela Primaria - Sistema de Gestión Académica', 105, 285, { align: 'center' });
          break;

        case 'asignatura':
          // Encabezado
          doc.setFontSize(16);
          doc.text('Reporte por Asignatura', 105, 20, { align: 'center' });
          doc.setFontSize(12);
          doc.text(`Fecha: ${fecha}`, 15, 30);
          
          // Datos de la asignatura
          doc.setFontSize(14);
          doc.text(`Asignatura: ${currentReport.data.asignatura.nombre}`, 15, 40);
          doc.text(`Grado: ${currentReport.data.grado}`, 15, 50);
          
          // Tabla de calificaciones
          doc.autoTable({
            startY: 60,
            head: [['Alumno', 'Calificación', 'Estatus']],
            body: currentReport.data.calificaciones.map(c => [c.alumno, c.calificacion, c.estatus]),
            styles: { fontSize: 10 },
            headStyles: { fillColor: [220, 220, 220] }
          });
          
          // Promedio
          const finalY2 = doc.lastAutoTable.finalY + 10;
          doc.setFontSize(12);
          doc.text(`Promedio del Grupo: ${currentReport.data.promedio}`, 15, finalY2);
          
          // Pie de página
          doc.setFontSize(10);
          doc.text('Escuela Primaria - Sistema de Gestión Académica', 105, 285, { align: 'center' });
          break;

        case 'regulares':
          // Encabezado
          doc.setFontSize(16);
          doc.text('Estudiantes Regulares', 105, 20, { align: 'center' });
          doc.setFontSize(12);
          doc.text(`Fecha: ${fecha}`, 15, 30);
          
          let yPos = 40;
          currentReport.data.forEach((grupo, index) => {
            if (index > 0) {
              doc.addPage();
              yPos = 20;
            }
            
            doc.setFontSize(14);
            doc.text(`Grado: ${grupo.grado}`, 15, yPos);
            yPos += 10;
            
            doc.autoTable({
              startY: yPos,
              head: [['Matrícula', 'Nombre', 'Promedio']],
              body: grupo.alumnos.map(a => [a.matricula, a.nombre, a.promedio]),
              styles: { fontSize: 10 },
              headStyles: { fillColor: [220, 220, 220] }
            });
            
            yPos = doc.lastAutoTable.finalY + 10;
            doc.setFontSize(10);
            doc.text(`Total estudiantes: ${grupo.alumnos.length}`, 15, yPos);
          });
          
          // Pie de página
          doc.setFontSize(10);
          doc.text('Escuela Primaria - Sistema de Gestión Académica', 105, 285, { align: 'center' });
          break;

        case 'irregulares':
          // Encabezado
          doc.setFontSize(16);
          doc.text('Estudiantes Irregulares', 105, 20, { align: 'center' });
          doc.setFontSize(12);
          doc.text(`Fecha: ${fecha}`, 15, 30);
          
          let yPos2 = 40;
          currentReport.data.forEach((grupo, index) => {
            if (index > 0) {
              doc.addPage();
              yPos2 = 20;
            }
            
            doc.setFontSize(14);
            doc.text(`Grado: ${grupo.grado}`, 15, yPos2);
            yPos2 += 10;
            
            doc.autoTable({
              startY: yPos2,
              head: [['Matrícula', 'Nombre', 'Materias Reprobadas', 'Promedio']],
              body: grupo.alumnos.map(a => [a.matricula, a.nombre, a.reprobadas, a.promedio]),
              styles: { fontSize: 10 },
              headStyles: { fillColor: [220, 220, 220] }
            });
            
            yPos2 = doc.lastAutoTable.finalY + 10;
            doc.setFontSize(10);
            doc.text(`Total estudiantes: ${grupo.alumnos.length}`, 15, yPos2);
          });
          
          // Pie de página
          doc.setFontSize(10);
          doc.text('Escuela Primaria - Sistema de Gestión Académica', 105, 285, { align: 'center' });
          break;
      }

      // Guardar PDF
      doc.save(`reporte_${currentReport.type}_${fecha.replace(/\//g, '-')}.pdf`);
    }

    // Funciones auxiliares
    function calcularPromedio(calificaciones) {
      if (calificaciones.length === 0) return 0;
      const suma = calificaciones.reduce((total, c) => total + parseFloat(c.calificacion), 0);
      return suma / calificaciones.length;
    }

    function agruparAlumnosPorGrado() {
      return alumnos.reduce((grupos, alumno) => {
        if (!grupos[alumno.id_grado]) {
          grupos[alumno.id_grado] = [];
        }
        grupos[alumno.id_grado].push(alumno);
        return grupos;
      }, {});
    }

    // Inicializar
    window.onload = cargarDatos;
  </script>
</body>
</html>