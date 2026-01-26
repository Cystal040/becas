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

    <!-- Historial de acciones ocultado en esta vista (se mantiene en admin/historial.php) -->
        <hr style="margin:14px 0;">
        <h3>Enviar</h3>
        <form id="form-observacion" action="agregar_observacion.php" method="POST" style="margin-top:8px;">
            <input type="hidden" name="id" value="<?php echo (int)$row['id_documento']; ?>">
            <label>Observación:</label>
            <textarea id="obs-only" name="observacion" rows="4" style="width:100%;padding:8px;border-radius:6px;margin-bottom:8px;"></textarea>
            <div style="display:flex;gap:8px;"><button class="btn" type="submit">✉ Enviar observación</button></div>
        </form>
    <div class="botones" style="margin-top:12px;"><a class="btn-secundario" href="estudiante_perfil.php?id=<?php echo (int)$row['id_estudiante']; ?>">⬅ Volver al perfil</a></div>
</div>
</body>
</html>

<script>
// Confirmación para envío de observación (incluye preview)
document.getElementById('form-observacion').addEventListener('submit', function(e){
    e.preventDefault();
    var obs = document.getElementById('obs-only').value.trim();
    if (!obs) {
        if (!confirm('Enviar observación vacía. ¿Desea continuar?')) return;
    } else {
        var msg = 'Observación a enviar:\n\n' + obs + '\n\nConfirmar envío?';
        if (!confirm(msg)) return;
    }
    e.target.submit();
});
</script>
