<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Contadores para el panel resumen
$totalStudents = 0;
$pendientes = 0;
$aprobados = 0;
$rechazados = 0;

$r = $conn->query("SELECT COUNT(*) AS total FROM estudiante");
if ($r) { $t = $r->fetch_assoc(); $totalStudents = (int) ($t['total'] ?? 0); $r->close(); }

$r = $conn->query("SELECT COUNT(*) AS c FROM documento WHERE estado = 'pendiente'");
if ($r) { $t = $r->fetch_assoc(); $pendientes = (int) ($t['c'] ?? 0); $r->close(); }

$r = $conn->query("SELECT COUNT(*) AS c FROM documento WHERE estado = 'aprobado'");
if ($r) { $t = $r->fetch_assoc(); $aprobados = (int) ($t['c'] ?? 0); $r->close(); }

$r = $conn->query("SELECT COUNT(*) AS c FROM documento WHERE estado = 'rechazado'");
if ($r) { $t = $r->fetch_assoc(); $rechazados = (int) ($t['c'] ?? 0); $r->close(); }


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

        <main style="display:flex;gap:18px;align-items:flex-start;">
            <aside style="width:260px;">
                <div class="card" style="padding:14px;">
                    <h3 style="color:#fff;margin-top:0;margin-bottom:10px;">Menú</h3>
                    <nav style="display:flex;flex-direction:column;gap:8px;">
                        <a class="nav-link" href="estudiantes.php" style="text-decoration:none;color:inherit;padding:8px;border-radius:6px;">Estudiantes</a>
                        <a class="nav-link" href="revisar_documentos.php" style="text-decoration:none;color:inherit;padding:8px;border-radius:6px;">Revisar documentos</a>
                        <a class="nav-link" href="historial.php" style="text-decoration:none;color:inherit;padding:8px;border-radius:6px;">Historial</a>
                        <a class="nav-link" href="agregar_observacion.php" style="text-decoration:none;color:inherit;padding:8px;border-radius:6px;">Agregar observación</a>
                        <a class="btn-secundario" href="../logout.php" style="display:inline-block;margin-top:8px;">Cerrar sesión</a>
                    </nav>
                </div>
            </aside>

            <section style="flex:1;">
                <!-- Resumen rápido -->
                <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:18px;">
                    <div class="card">
                        <h3 style="color:#fff;margin-bottom:6px;">Total estudiantes</h3>
                        <p style="font-size:22px;margin:0;color:var(--muted);"><?php echo $totalStudents; ?></p>
                    </div>

                    <div class="card">
                        <h3 style="color:#fff;margin-bottom:6px;">🟡 En espera</h3>
                        <p style="font-size:22px;margin:0;color:var(--muted);"><?php echo $pendientes; ?></p>
                    </div>

                    <div class="card">
                        <h3 style="color:#fff;margin-bottom:6px;">✅ Aprobados</h3>
                        <p style="font-size:22px;margin:0;color:var(--muted);"><?php echo $aprobados; ?></p>
                    </div>

                    <div class="card">
                        <h3 style="color:#fff;margin-bottom:6px;">❌ Rechazados</h3>
                        <p style="font-size:22px;margin:0;color:var(--muted);"><?php echo $rechazados; ?></p>
                    </div>
                </section>

                <section style="margin-top:8px;">
                    <div class="card">
                        <h3 style="color:#fff;margin-bottom:8px;">Accesos rápidos</h3>
                        <p style="color:var(--muted);margin:0;">Usa el menú a la izquierda para navegar entre las secciones administrativas.</p>
                    </div>
                </section>
            </section>
        </main>
    </div>
</body>
</html>