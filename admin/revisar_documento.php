<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: revisar_documentos.php'); exit; }

$stmt = $conn->prepare("SELECT d.id_documento, d.ruta_archivo, d.estado, d.fecha_subida, d.id_tipo_documento, e.id_estudiante, e.nombre, e.apellido, e.correo, td.nombre_documento FROM documento d LEFT JOIN estudiante e ON d.id_estudiante = e.id_estudiante LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento WHERE d.id_documento = ? LIMIT 1");
if (!$stmt) { header('Location: revisar_documentos.php'); exit; }
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) { header('Location: revisar_documentos.php'); exit; }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Revisar documento</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">
<div class="contenedor">
    <h2>Revisar documento</h2>

    <div class="card">
        <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($row['nombre'] . ' ' . ($row['apellido'] ?? '')); ?></p>
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($row['nombre_documento'] ?? ''); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($row['estado'] ?? 'N/A'); ?></p>
        <p><strong>Subido:</strong> <?php echo htmlspecialchars($row['fecha_subida'] ?? ''); ?></p>
        <p><strong>Archivo:</strong> <?php if (!empty($row['ruta_archivo'])): ?><a href="../<?php echo htmlspecialchars($row['ruta_archivo']); ?>" target="_blank">Ver</a><?php endif; ?></p>
    </div>

    <h3 style="margin-top:14px;">Historial de acciones</h3>
    <div class="card" style="margin-bottom:10px;">
        <?php
        $hist = $conn->prepare("SELECT h.accion, h.observacion, h.fecha, a.usuario AS admin_usuario FROM historial_acciones h LEFT JOIN administrador a ON h.admin_id = a.id_admin WHERE h.id_documento = ? ORDER BY h.fecha DESC");
        if ($hist) {
            $hist->bind_param('i', $id);
            $hist->execute();
            $hr = $hist->get_result();
        } else { $hr = null; }
        if ($hr && $hr->num_rows > 0) {
            echo '<ul style="margin:0;padding-left:16px;">';
            while ($hh = $hr->fetch_assoc()) {
                echo '<li><strong>' . htmlspecialchars(ucfirst($hh['accion'])) . '</strong> por ' . htmlspecialchars($hh['admin_usuario'] ?? '#') . ' — ' . htmlspecialchars($hh['fecha']) . '<br>' . htmlspecialchars($hh['observacion'] ?? '') . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="margin:0;color:var(--muted);">No hay historial para este documento.</p>';
        }
        if ($hist) $hist->close();
        ?>
    </div>

    <form action="actualizar_estado.php" method="POST" style="margin-top:12px;">
        <input type="hidden" name="id" value="<?php echo (int)$row['id_documento']; ?>">
        <label>Observación (opcional):</label>
        <textarea name="observacion" rows="4" style="width:100%;padding:8px;border-radius:6px;margin-bottom:8px;"></textarea>

        <div style="display:flex;gap:8px;">
            <input type="hidden" name="estado" value="aprobado">
            <button class="btn" type="submit">✔ Aceptar</button>
        </div>
    </form>
    <div class="botones" style="margin-top:12px;"><a class="btn-secundario" href="estudiante_perfil.php?id=<?php echo (int)$row['id_estudiante']; ?>">⬅ Volver al perfil</a></div>
</div>
</body>
</html>
