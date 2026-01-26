<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Mostrar últimas 500 acciones por defecto
$sql = "SELECT h.id, h.id_documento, h.accion, h.admin_id, h.observacion, h.fecha, d.id_estudiante, td.nombre_documento, e.nombre AS est_nombre, e.apellido AS est_apellido, a.usuario AS admin_usuario FROM historial_acciones h LEFT JOIN documento d ON h.id_documento = d.id_documento LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento LEFT JOIN estudiante e ON d.id_estudiante = e.id_estudiante LEFT JOIN administrador a ON h.admin_id = a.id_admin ORDER BY h.fecha DESC LIMIT 500";
$res = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de acciones</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">
<div class="contenedor">
    <h2>Historial de acciones</h2>

    <div class="table-responsive">
    <table class="table-compact">
        <thead><tr><th>Fecha</th><th>Acción</th><th>Documento</th><th>Estudiante</th><th>Admin</th><th>Observación</th></tr></thead>
        <tbody>
        <?php if ($res && $res->num_rows > 0): while ($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['fecha']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($r['accion'])); ?></td>
                <td><?php echo htmlspecialchars($r['nombre_documento'] ?? 'ID ' . ($r['id_documento'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars(($r['est_nombre'] ?? '') . ' ' . ($r['est_apellido'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars($r['admin_usuario'] ?? ('#' . ($r['admin_id'] ?? ''))); ?></td>
                <td><?php echo htmlspecialchars($r['observacion'] ?? ''); ?></td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6">No hay acciones registradas.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <div class="botones" style="margin-top:12px;"><a class="btn-secundario" href="admin_panel.php">⬅ Volver</a></div>
</div>
</body>
</html>
