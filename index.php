<?php
include("config/conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Becas</title>
    <link rel="icon" href="assets/img/icono.png">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body class="fondo">

    <header style="position:relative;z-index:1;max-width:var(--max-width);margin:18px auto 8px;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px 16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="assets/img/icono.png" alt="UNEFA" style="height:48px;object-fit:contain;border-radius:6px;">
            <div>
                <strong style="font-size:1.1rem;color:#fff">Sistema de Becas UNEFA</strong>
                <div style="font-size:0.9rem;color:var(--muted)">Portal de gestión de documentos</div>
            </div>
        </div>
        <nav>
            <a class="btn" href="views/login.php">Iniciar sesión</a>
            <a class="btn" href="views/registro.php">Registrarse</a>
        </nav>
    </header>

    <main class="contenedor">
        <h1>Bienvenido al portal</h1>
        <p>Envía tus documentos para aplicar a las becas de forma rápida y segura.</p>

        <div class="botones">
            <a href="views/documentos.php" class="btn-secundario">Documentos requeridos</a>
        </div>
    </main>

</body>
</html>