<?php include("../config/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>

<body class="fondo">

<div class="contenedor">
    <h2>Iniciar sesión</h2>
    <?php
    session_start();
    if (isset($_SESSION['flash_success'])) {
        echo '<div class="flash" style="background:#e6ffed;border:1px solid #b7f0c6;padding:8px;margin:8px 0;">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        echo '<div class="flash" style="background:#ffecec;border:1px solid #f0b7b7;padding:8px;margin:8px 0;">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    ?>

    <form action="../controllers/login_process.php" method="POST">

        <label>Usuario o correo:</label>
        <input type="text" name="user" placeholder="Usuario o correo" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <div class="botones">
            <button class="btn" type="submit">Entrar</button>
            <button class="btn-secundario" type="button" onclick="window.location.href='../index.php'">Volver al inicio</button>
        </div>
    </form>

    <div style="margin-top:12px; text-align:center;">
        <a href="registro.php">Crear cuenta</a>
    </div>
</div>

</body>
</html>