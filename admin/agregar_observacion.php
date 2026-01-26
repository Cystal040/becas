<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : null;

if ($id <= 0 || $observacion === null || $observacion === '') {
    header("Location: revisar_documento.php?id=" . $id);
    exit;
}

// Crear tabla historial si no existe (defensivo)
$create_hist = "CREATE TABLE IF NOT EXISTS historial_acciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    admin_id INT NULL,
    observacion TEXT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create_hist);

$h = $conn->prepare("INSERT INTO historial_acciones (id_documento, accion, admin_id, observacion) VALUES (?, ?, ?, ?)");
if ($h) {
    $accion = 'observacion';
    $admin_id = $_SESSION['admin_id'];
    $h->bind_param('isis', $id, $accion, $admin_id, $observacion);
    $h->execute();
    $h->close();
}

header("Location: revisar_documento.php?id=" . $id . "&msg=obs_sent");
exit;
?>
