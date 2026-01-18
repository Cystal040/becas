<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Panel Administrador</title>
    <link rel="icon" href="../assets/img/icono.png">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="fondo">
    <div class="contenedor">
        <header style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <img src="../assets/img/icono.png" alt="Logo" style="width:48px;height:48px;border-radius:8px;">
            <div>
                <h2 style="margin:0;">Panel Administrativo</h2>
                <p style="margin:2px 0 0;color:var(--muted);">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario'] ?? ''); ?></p>
            </div>
        </header>

        <main>
            <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
                <a class="card" href="revisar_documentos.php" style="text-decoration:none;">
                    <h3 style="color:#fff;margin-bottom:8px;">Revisar documentos</h3>
                    <p style="color:var(--muted);margin:0;">Ver y gestionar los documentos pendientes de los estudiantes.</p>
                </a>

                <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                    <a class="btn-secundario" href="../logout.php">Cerrar sesión</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>