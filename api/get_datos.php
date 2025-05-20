<?php
header('Content-Type: application/json');

// Simular lógica de negocio (capa 2)
$datos = [
    "Nodo 1 operativo",
    "Nodo 2 en mantenimiento",
    "Nodo 3 respondiendo bien"
];

// Aquí iría la lógica de negocio real
echo json_encode($datos);
?>
