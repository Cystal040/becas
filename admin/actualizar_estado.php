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

// Asegurarse de que la columna 'estado' (y columnas relacionadas) existen en la tabla documento.
$cols_q = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento'");
$existing = [];
if ($cols_q) {
    while ($c = $cols_q->fetch_assoc()) { $existing[] = $c['COLUMN_NAME']; }
    $cols_q->close();
}

// Agregar columnas mínimas si faltan
if (!in_array('estado', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN estado VARCHAR(20) DEFAULT 'pendiente'");
}
if (!in_array('id_estudiante', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN id_estudiante INT NULL");
}
if (!in_array('fecha_revision', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN fecha_revision DATETIME NULL");
}

// Actualizar estado y fecha de revisión
$stmt = $conn->prepare("UPDATE documento SET estado = ?, fecha_revision = NOW() WHERE id_documento = ?");
if ($stmt) {
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: revisar_documentos.php");
exit;
?>
