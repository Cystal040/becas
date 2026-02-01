<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';
$observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : null;

if ($id <= 0 || !in_array($estado, ['aprobado','rechazado'])) {
    header("Location: revisar_documentos.php");
    exit;
}

$cur = $conn->prepare("SELECT estado FROM documento WHERE id_documento = ? LIMIT 1");
if ($cur) {
    $cur->bind_param('i', $id);
    $cur->execute();
    $res = $cur->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $cur->close();
} else {
    $row = null;
}

if ($row && isset($row['estado']) && $row['estado'] === 'aprobado') {
    // No permitir cambios sobre un documento ya aprobado
    header("Location: revisar_documentos.php?error=locked");
    exit;
}

// Asegurar existencia de columnas y tabla de historial
$cols_q = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento'");
$existing = [];
if ($cols_q) {
    while ($c = $cols_q->fetch_assoc()) { $existing[] = $c['COLUMN_NAME']; }
    $cols_q->close();
}

if (!in_array('estado', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN estado VARCHAR(20) DEFAULT 'pendiente'");
}
if (!in_array('id_estudiante', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN id_estudiante INT NULL");
}
if (!in_array('fecha_revision', $existing)) {
    $conn->query("ALTER TABLE documento ADD COLUMN fecha_revision DATETIME NULL");
}

// Crear tabla de historial si no existe
$create_hist = "CREATE TABLE IF NOT EXISTS historial_acciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    admin_id INT NULL,
    observacion TEXT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create_hist);

// Actualizar estado y fecha de revisión
$stmt = $conn->prepare("UPDATE documento SET estado = ?, fecha_revision = NOW() WHERE id_documento = ?");
if ($stmt) {
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();

    // Registrar en historial
    $h = $conn->prepare("INSERT INTO historial_acciones (id_documento, accion, admin_id, observacion) VALUES (?, ?, ?, ?)");
    if ($h) {
        $admin_id = $_SESSION['admin_id'];
        $h->bind_param('isis', $id, $estado, $admin_id, $observacion);
        $h->execute();
        $h->close();
    }
}

header("Location: revisar_documentos.php");
exit;
?>
