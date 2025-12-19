<?php
// Mostrar mensajes según parámetros en la URL
$msg = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty':
            $msg = 'Por favor completa todos los campos.';
            break;
        case 'db':
            $msg = 'Error interno de base de datos. Intenta más tarde.';
            break;
        case 'exists':
            $msg = 'Ya existe una cuenta con esos datos.';
            break;
        case 'correo_exists':
            $msg = 'El correo ya está afiliado a otro usuario.';
            break;
        case 'cedula_exists':
            $msg = 'La cédula ya está afiliada a otro usuario.';
            break;
        default:
            $msg = 'Ocurrió un error. Intenta nuevamente.';
    }
} elseif (isset($_GET['registered'])) {
    $msg = 'Se ha registrado exitosamente.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" href="../assets/img/icono.png">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="fondo">
    <div class="contenedor">
    <?php if ($msg !== ''): ?>
        <div style="margin-bottom:12px; padding:10px; background:rgba(255,255,255,0.12); border-radius:6px;">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    <form action="../controllers/register_process.php" method="POST">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido" placeholder="Apellido" required>
    <input type="text" name="cedula" placeholder="Cédula" required>
    <input type="email" name="correo" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit" name="registrar">Registrarse</button>
</form>

<div class="botones">
    <button onclick="window.location.href='../index.php'">Volver al inicio</button>
    <button onclick="window.location.href='login.php'">Iniciar sesión</button>
</div>
    </div>
</body>
</html>