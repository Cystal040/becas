<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

// Asegurarse de que la petición es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Acceso no permitido.";
    exit;
}

// Auto-detección por contenido de `user`: si es email -> estudiante, en caso contrario -> admin
$password = $_POST['password'] ?? '';
$userInput = trim($_POST['user'] ?? '');

if ($userInput === '' || $password === '') {
    echo "<h3>Introduce usuario o correo y la contraseña.</h3>";
    exit;
}

if (filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
    // Intento como estudiante por correo
    $stmt = $conn->prepare("SELECT id_estudiante, nombre, password FROM estudiante WHERE correo = ? LIMIT 1");
    if (!$stmt) { error_log('Prepare estudiante failed: '.$conn->error); exit; }
    $stmt->bind_param('s', $userInput);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $usuario = $res->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id_estudiante'] ?? $usuario['id'] ?? null;
            $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? '';
            ini_set('session.gc_maxlifetime', 0);
            ini_set('session.cookie_lifetime', 0);
            header("Location: ../views/dashboard.php");
            $stmt->close();
            exit;
        } else { echo "<h3>Contraseña incorrecta</h3>"; }
    } else { echo "<h3>No existe una cuenta con ese correo</h3>"; }
    $stmt->close();

} else {
    // Intento como admin por usuario
    $stmt = $conn->prepare("SELECT id_admin, usuario, password FROM administrador WHERE usuario = ? LIMIT 1");
    if (!$stmt) { error_log('Prepare admin failed: '.$conn->error); exit; }
    $stmt->bind_param('s', $userInput);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            ini_set('session.gc_maxlifetime', 0);
            ini_set('session.cookie_lifetime', 0);
            header("Location: ../admin/admin_panel.php");
            $stmt->close();
            exit;
        } else { echo "<h3>Contraseña incorrecta</h3>"; }
    } else { echo "<h3>Usuario no encontrado</h3>"; }
    $stmt->close();
}
?>