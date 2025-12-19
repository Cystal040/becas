<?php
include("config/conexion.php");

if (isset($_POST['registrar'])) {

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $correo = $_POST['correo'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO estudiante (nombre, apellido, cedula, correo, password)
            VALUES ('$nombre', '$apellido', '$cedula', '$correo', '$password')";

    if ($conexion->query($sql)) {
        echo "Registro exitoso";
    } else {
        echo "Error: " . $conexion->error;
    }
} else {
    echo "No se ha enviado el formulario de registro.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" href="img/icono.png">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    
<form action="registro.php" method="POST">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido" placeholder="Apellido" required>
    <input type="text" name="cedula" placeholder="Cédula" required>
    <input type="email" name="correo" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit" name="registrar">Registrarse</button>
</form>

<button onclick="window.location.href='index.php'">Volver al inicio</button>
<button onclick="window.location.href='login.php'">Ir a iniciar sesión</button>
</body>
</html>