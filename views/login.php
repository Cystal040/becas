<?php include("config/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="img/icono.png">
</head>

<body>

<h2>Iniciar sesión</h2>

<form action="login_process.php" method="POST">

    <label>Correo:</label>
    <input type="email" name="correo" required>

    <label>Contraseña:</label>
    <input type="password" name="password" required>

    <button type="submit">Entrar</button>
</form>

</body>
</html>