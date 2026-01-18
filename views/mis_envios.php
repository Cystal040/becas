<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];

// Tipos y faltantes
$tipos = [];
$tipo_stmt = $conn->prepare("SELECT id_tipo_documento, nombre_documento FROM tipo_documento ORDER BY id_tipo_documento");
if ($tipo_stmt) {
    $tipo_stmt->execute();
    $res = $tipo_stmt->get_result();
    while ($r = $res->fetch_assoc()) { $tipos[] = $r; }
    $tipo_stmt->close();
}

$status_map = [];
$doc_stmt = $conn->prepare("SELECT id_documento, id_tipo_documento, estado, fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC");
if ($doc_stmt) {
    $doc_stmt->bind_param('i', $id_estudiante);
    $doc_stmt->execute();
    $resd = $doc_stmt->get_result();
    while ($row = $resd->fetch_assoc()) {
        $tid = (int)$row['id_tipo_documento'];
        // sólo guardar el último por tipo si aún no existe
        if (!isset($status_map[$tid])) {
            $status_map[$tid] = $row;
        }
    }
    $doc_stmt->close();
}

$faltantes = [];
foreach ($tipos as $t) {
    $tid = (int)$t['id_tipo_documento'];
    if (!isset($status_map[$tid])) { $faltantes[] = $t['nombre_documento']; }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis envíos</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">
<div class="contenedor">
    <h2>Mis envíos</h2>

    <div class="card">
        <h3>Documentos pendientes</h3>
        <?php if (empty($faltantes)): ?>
            <div>Has enviado todos los documentos.</div>
        <?php else: ?>
            <ul>
                <?php foreach ($faltantes as $f): ?>
                    <li><?php echo htmlspecialchars($f); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <hr>

    <h3>Historial de envíos (últimos por tipo)</h3>
    <table>
        <tr><th>Documento</th><th>Estado</th><th>Fecha de subida</th></tr>
        <?php foreach ($tipos as $t): ?>
            <?php $tid = (int)$t['id_tipo_documento']; $info = $status_map[$tid] ?? null; ?>
            <tr>
                <td><?php echo htmlspecialchars($t['nombre_documento']); ?></td>
                <td><?php echo $info ? htmlspecialchars(ucfirst($info['estado'])) : 'Sin enviar'; ?></td>
                <td><?php echo $info ? htmlspecialchars($info['fecha_subida']) : '-'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="botones" style="margin-top:12px;">
        <a class="btn-secundario" href="dashboard.php">⬅ Volver</a>
        <a class="btn" href="subir_documentos.php">Subir documento</a>
    </div>
</div>
</body>
</html>
