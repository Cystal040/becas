<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'] ?? '';

// Mostrar flash messages (si existen)
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

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

        <?php if (!empty($flash_success)): ?>
            <div class="flash flash-success" style="background:#e6ffed;border:1px solid #b7f0c6;padding:8px;margin:8px 0;">
                <?php echo htmlspecialchars($flash_success); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="flash flash-error" style="background:#ffecec;border:1px solid #f0b7b7;padding:8px;margin:8px 0;">
                <?php echo htmlspecialchars($flash_error); ?>
            </div>
        <?php endif; ?>

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
                            <td><?php echo $info ? htmlspecialchars(date('d/m/Y H:i', strtotime($info['fecha_subida']))) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>

        <div class="botones" style="margin-top:12px;">
            <a class="btn-secundario" href="../logout.php">Cerrar sesión</a>
            <a class="btn" href="subir_documentos.php">Subir documento</a>
        </div>
    </div>
</body>

</html>