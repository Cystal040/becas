<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

// Solo aceptar POST desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['registrar'])) {
    header('Location: ../views/registro.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password_plain = $_POST['password'] ?? '';

if ($nombre === '' || $apellido === '' || $cedula === '' || $correo === '' || $password_plain === '') {
    header('Location: ../views/registro.php?error=empty');
    exit;
}

$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// Inserción segura con prepared statement
// Comprobar duplicados por correo o cédula
$check = $conn->prepare("SELECT correo, cedula FROM estudiante WHERE correo = ? OR cedula = ? LIMIT 1");
if ($check) {
    $check->bind_param('ss', $correo, $cedula);
    $check->execute();
    $res_check = $check->get_result();
    if ($res_check && $res_check->num_rows > 0) {
        $existing = $res_check->fetch_assoc();
        if (!empty($existing['correo']) && $existing['correo'] === $correo) {
            $check->close();
            header('Location: ../views/registro.php?error=correo_exists');
            exit;
        }
        if (!empty($existing['cedula']) && $existing['cedula'] === $cedula) {
            $check->close();
            header('Location: ../views/registro.php?error=cedula_exists');
            exit;
        }
        $check->close();
        header('Location: ../views/registro.php?error=exists');
        exit;
    }
    $check->close();
} else {
    error_log('Prepare failed (check duplicates): ' . $conn->error);
    header('Location: ../views/registro.php?error=db');
    exit;
}

$stmt = $conn->prepare("INSERT INTO estudiante (nombre, apellido, cedula, correo, password) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log('Prepare failed (register): ' . $conn->error);
    header('Location: ../views/registro.php?error=db');
    exit;
}

$stmt->bind_param('sssss', $nombre, $apellido, $cedula, $correo, $password_hashed);
if ($stmt->execute()) {
    $stmt->close();
    // Set flash message and redirect to login
    $_SESSION['flash_success'] = 'Registro completado. Ahora puedes iniciar sesión.';
    header('Location: ../views/login.php');
    exit;
} else {
    error_log('Execute failed (register): ' . $stmt->error);
    $stmt->close();
    header('Location: ../views/registro.php?error=exists');
    exit;
}
?>