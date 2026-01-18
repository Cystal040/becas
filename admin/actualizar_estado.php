<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Aceptar POST para mayor seguridad
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';

if ($id <= 0 || !in_array($estado, ['aprobado','rechazado'])) {
    header("Location: revisar_documentos.php");
    exit;
}

// Actualizar estado (sin modificar esquema de base de datos)
$stmt = $conn->prepare("UPDATE documento SET estado = ? WHERE id_documento = ?");
if ($stmt) {
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: revisar_documentos.php");
exit;
?>
