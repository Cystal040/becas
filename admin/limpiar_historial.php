<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Eliminar todas las entradas del historial
$conn->query('DELETE FROM historial_acciones');

header('Location: historial.php?cleared=1');
exit;
?>
