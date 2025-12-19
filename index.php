<?php
include("config/conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Becas</title>
    <link rel="icon" href="img/icono.png">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="fondo">

    <div class="contenedor">

        <h1>Sistema de Gestión de Becas</h1>
        <p>
            Bienvenido al portal donde podrás enviar tus documentos para aplicar a las becas
            de forma rápida, segura y sin necesidad de entregarlos físicamente.
        </p>

        <div class="botones">
            <a href="registro.php" class="btn">Registrarse</a>
            <a href="login.php" class="btn">Iniciar sesión</a>
            <a href="documentos.php" class="btn-secundario">Documentos requeridos</a>
        </div>

    </div>

</body>
</html>
