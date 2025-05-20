<?php
header('Content-Type: application/json');

$alumnos = [
  [
    'matricula' => 'A001',
    'nombre' => 'Juan Pérez',
    'grado' => '5',
    'correo' => 'juan.perez@escuela.edu.mx'
  ],
  [
    'matricula' => 'A002',
    'nombre' => 'Ana López',
    'grado' => '6',
    'correo' => 'ana.lopez@escuela.edu.mx'
  ],
  [
    'matricula' => 'A003',
    'nombre' => 'Carlos García',
    'grado' => '4',
    'correo' => 'carlos.garcia@escuela.edu.mx'
  ]
];

echo json_encode($alumnos);
