<?php
session_start();
include("config/conexion.php");

// Validar si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = $conn->real_escape_string($_POST['correo']);
    $password = $_POST['password'];

    // Buscar usuario
    $sql = "SELECT * FROM usuario_estudiante WHERE correo='$correo' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $usuario['password'])) {

            // Crear sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            // Mantener sesión activa SIN timeout
            ini_set('session.gc_maxlifetime', 0);
            ini_set('session.cookie_lifetime', 0);

            header("Location: dashboard.php");
            exit;
        } else {
            echo "<h3>❌ Contraseña incorrecta</h3>";
        }

    } else {
        echo "<h3>❌ No existe una cuenta con ese correo</h3>";
    }

} else {
    echo "Acceso no permitido.";
}
?>