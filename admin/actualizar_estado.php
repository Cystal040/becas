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

// Asegurarse de que exista la columna `fecha_revision` para almacenar cuándo se revisó
$colCheck = $conn->query("SHOW COLUMNS FROM documento LIKE 'fecha_revision'");
if ($colCheck && $colCheck->num_rows === 0) {
    // Intentar añadir la columna (silencioso si falla en entornos antiguos)
    @$conn->query("ALTER TABLE documento ADD COLUMN fecha_revision DATETIME NULL");
}

// Actualizar estado y marcar la fecha de revisión
$stmt = $conn->prepare("UPDATE documento SET estado = ?, fecha_revision = NOW() WHERE id_documento = ?");
if ($stmt) {
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: revisar_documentos.php");
exit;
?>
