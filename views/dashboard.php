<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];
$nombre = $_SESSION['usuario_nombre'];
?>

<?php
// Cargar todos los tipos de documento y el estado (si existe) para este estudiante
$tipos = [];
$tipo_stmt = $conn->prepare("SELECT id_tipo_documento, nombre_documento FROM tipo_documento ORDER BY id_tipo_documento");
if ($tipo_stmt) {
    $tipo_stmt->execute();
    $res = $tipo_stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $tipos[] = $row;
    }
    $tipo_stmt->close();
}

// Comprobar si la columna `fecha_revision` existe para evitar errores en instalaciones antiguas
$colCheck = $conn->query("SHOW COLUMNS FROM documento LIKE 'fecha_revision'");
$has_fecha_revision = ($colCheck && $colCheck->num_rows > 0);

// id_tipo => ['estado'=>..., 'fecha_subida'=>..., 'fecha_revision'=>...]
$status_map = [];
if ($has_fecha_revision) {
    $doc_stmt = $conn->prepare("SELECT estado, fecha_subida, fecha_revision FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
} else {
    // Si no existe la columna, seleccionar solo las columnas disponibles
    $doc_stmt = $conn->prepare("SELECT estado, fecha_subida FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
}
if ($doc_stmt) {
    foreach ($tipos as $t) {
        $tipoId = (int)$t['id_tipo_documento'];
        $doc_stmt->bind_param('ii', $id_estudiante, $tipoId);
        $doc_stmt->execute();
        $resd = $doc_stmt->get_result();
        if ($resd && $resd->num_rows > 0) {
            $r = $resd->fetch_assoc();
            $status_map[$tipoId] = [
                'estado' => $r['estado'],
                'fecha_subida' => $r['fecha_subida'],
                'fecha_revision' => $has_fecha_revision ? ($r['fecha_revision'] ?? null) : null,
            ];
        } else {
            $status_map[$tipoId] = null;
        }
    }
    $doc_stmt->close();
}

// Obtener la lista de documentos subidos (para la tabla de abajo)
$stmt = $conn->prepare("SELECT d.id_documento, td.nombre_documento, d.estado, d.fecha_subida
        FROM documento d
        INNER JOIN tipo_documento td 
        ON d.id_tipo_documento = td.id_tipo_documento
        WHERE d.id_estudiante = ?
        ORDER BY d.fecha_subida DESC");
if ($stmt) {
    $stmt->bind_param('i', $id_estudiante);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
} else {
    $resultado = $conexion->query("SELECT d.id_documento, td.nombre_documento, d.estado, d.fecha_subida
        FROM documento d
        INNER JOIN tipo_documento td 
        ON d.id_tipo_documento = td.id_tipo_documento
        WHERE d.id_estudiante = '$id_estudiante'");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Estudiante</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">

<div class="contenedor">

<h2>Bienvenido, <?php echo $nombre; ?></h2>

<p>Desde este panel puedes subir y revisar tus documentos para la beca.</p>

<hr>

<div class="card">
<h3>Documentos solicitados</h3>
<ul>
    <li>Constancia de inscripción (PDF)</li>
    <li>Récord académico (PDF)</li>
    <li>Cédula (PDF / JPG / PNG)</li>
    <li>RIF (PDF / DOC)</li>
    <li>Foto tipo carnet</li>
</ul>

<div class="botones">
    <a class="btn-secundario" href="subir_documentos.php">Subir documento</a>
</div>
</div>

<hr>

<h3>Mis documentos</h3>

<table>
    <tr>
        <th>Documento</th>
        <th>Estado</th>
        <th>Fecha de subida</th>
    </tr>

    <?php if ($resultado->num_rows > 0) { ?>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $fila['nombre_documento']; ?></td>
                <?php
                    $estado_display = ($fila['estado'] === 'pendiente') ? 'En espera' : ucfirst($fila['estado']);
                ?>
                <td><?php echo $estado_display; ?></td>
                <td><?php echo $fila['fecha_subida']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="3">No has subido documentos aún.</td>
        </tr>
    <?php } ?>
    </table>

    <br>

    <hr>

    <div class="card">
    <h3>Estado de envíos</h3>
    <ul>
        <?php foreach ($tipos as $t): ?>
            <?php $tid = (int)$t['id_tipo_documento']; $info = $status_map[$tid] ?? null; ?>
            <li>
                <?php echo htmlspecialchars($t['nombre_documento']); ?>: 
                <?php if ($info === null): ?>
                    <strong style="color:#f39c12;">Falta enviar</strong>
                <?php else: ?>
                    <?php $st = $info['estado'];
                          $fsub = $info['fecha_subida'];
                          $frev = $info['fecha_revision'];
                    ?>
                    <?php if ($st === 'pendiente'): ?>
                        <strong style="color:#3498db;">Enviado (En espera)</strong>
                        <small> — enviado: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fsub))); ?></small>
                    <?php elseif ($st === 'aprobado'): ?>
                        <strong style="color:#2ecc71;">Aprobado</strong>
                        <?php if (!empty($frev)): ?><small> — aprobado: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($frev))); ?></small><?php endif; ?>
                    <?php elseif ($st === 'rechazado'): ?>
                        <strong style="color:#e74c3c;">Rechazado</strong>
                        <?php if (!empty($frev)): ?><small> — rechazado: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($frev))); ?></small><?php endif; ?>
                    <?php else: ?>
                        <strong><?php echo htmlspecialchars($st); ?></strong>
                    <?php endif; ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    </div>

    <div class="botones">
        <a class="btn-secundario" href="../logout.php">Cerrar sesión</a>
    </div>

</div> <!-- .contenedor -->

</body>
</html>