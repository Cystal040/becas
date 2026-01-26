<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'] ?? '';

// Cargar tipos de documento
$tipos = [];
$tipo_stmt = $conn->prepare("SELECT id_tipo_documento, nombre_documento FROM tipo_documento ORDER BY id_tipo_documento");
if ($tipo_stmt) {
    $tipo_stmt->execute();
    $res = $tipo_stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $tipos[] = $r;
    }
    $tipo_stmt->close();
}

// Cargar documentos del estudiante (último por tipo)
$status_map = [];
$doc_stmt = $conn->prepare("SELECT id_documento, id_tipo_documento, ruta_archivo, estado, fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC");
if ($doc_stmt) {
    $doc_stmt->bind_param('i', $id_estudiante);
    $doc_stmt->execute();
    $resd = $doc_stmt->get_result();
    while ($row = $resd->fetch_assoc()) {
        $tid = (int) $row['id_tipo_documento'];
        if (!isset($status_map[$tid])) {
            $status_map[$tid] = $row;
        }
    }
    $doc_stmt->close();
}

$faltantes = [];
foreach ($tipos as $t) {
    $tid = (int) $t['id_tipo_documento'];
    if (!isset($status_map[$tid])) {
        $faltantes[] = $t['nombre_documento'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Interfaz Estudiante</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>

<body class="fondo">
    <div class="contenedor">
        <h1>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h1>

        <section style="margin-top:12px;">
            <h3>Documentos faltantes</h3>
            <?php if (empty($faltantes)): ?>
                <p>Has subido todos los documentos requeridos.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($faltantes as $f): ?>
                        <li><?php echo htmlspecialchars($f); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section style="margin-top:14px;">
            <h3>Estado de tus documentos</h3>
            <div class="table-responsive">
                <table class="table-compact">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th>Fecha subida</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <?php foreach ($tipos as $t): ?>
                        <?php $tid = (int) $t['id_tipo_documento'];
                        $info = $status_map[$tid] ?? null; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['nombre_documento']); ?></td>
                            <td>
                                <?php if (!$info): ?>
                                    <span class="status-pendiente">⬜ No enviado</span>
                                <?php else: ?>
                                    <?php $st = $info['estado'];
                                    if ($st === 'aprobado')
                                        echo '<span class="status-aprobado">🟢 Aprobado</span>';
                                    elseif ($st === 'pendiente')
                                        echo '<span class="status-pendiente">🟡 En revisión</span>';
                                    elseif ($st === 'rechazado')
                                        echo '<span class="status-rechazado">❌ Rechazado</span>';
                                    else
                                        echo '<span>' . htmlspecialchars(ucfirst($st)) . '</span>'; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $info ? htmlspecialchars(date('d/m/Y H:i', strtotime($info['fecha_subida']))) : '-'; ?></td>
                            <td>
                                <?php if (!$info): ?>
                                    <a class="btn" href="subir_documentos.php">➕ Subir</a>
                                <?php else: ?>
                                    <?php if (!empty($info['ruta_archivo'])): ?>
                                        <a class="btn-secundario btn-small" href="../<?php echo htmlspecialchars($info['ruta_archivo']); ?>" target="_blank">Ver</a>
                                    <?php endif; ?>
                                    <?php if ($info['estado'] === 'rechazado'): ?>
                                        <a class="btn btn-small" href="subir_documentos.php">🔄 Volver a subir</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>

        <div class="botones" style="margin-top:12px;">
            <a class="btn-secundario" href="dashboard.php">⬅ Volver</a>
            <a class="btn" href="subir_documentos.php">Subir documento</a>
        </div>
    </div>
</body>

</html>
