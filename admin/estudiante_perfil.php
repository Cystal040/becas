<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: estudiantes.php'); exit; }

$stmt = $conn->prepare("SELECT id_estudiante, nombre, apellido, cedula, correo FROM estudiante WHERE id_estudiante = ? LIMIT 1");
if (!$stmt) { header('Location: estudiantes.php'); exit; }
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$student = $res->fetch_assoc();
$stmt->close();

if (!$student) { header('Location: estudiantes.php'); exit; }

// Obtener documentos del estudiante
$doc_stmt = $conn->prepare("SELECT id_documento, id_tipo_documento, ruta_archivo, estado, fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC");
if ($doc_stmt) { $doc_stmt->bind_param('i', $id); $doc_stmt->execute(); $docs = $doc_stmt->get_result(); } else { $docs = []; }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Perfil estudiante</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">
<div class="contenedor">
    <h2>Perfil del estudiante</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'estado_updated'): ?>
        <div class="alert success">Cambio guardado correctamente.</div>
    <?php endif; ?>

    <div class="card">
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($student['nombre'] . ' ' . $student['apellido']); ?></p>
        <p><strong>Cédula:</strong> <?php echo htmlspecialchars($student['cedula']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($student['correo']); ?></p>
    </div>

    <h3 style="margin-top:14px;">Documentos enviados</h3>
    <div class="table-responsive">
    <table class="table-compact">
        <thead><tr><th>Tipo</th><th>Estado</th><th>Archivo</th><th>Acción</th></tr></thead>
        <tbody>
        <?php if ($docs && $docs->num_rows > 0): while ($d = $docs->fetch_assoc()):
            // obtener nombre del tipo
            $tq = $conn->prepare("SELECT nombre_documento FROM tipo_documento WHERE id_tipo_documento = ? LIMIT 1");
            $tipo_nombre = 'Documento';
            if ($tq) { $tq->bind_param('i', $d['id_tipo_documento']); $tq->execute(); $tr = $tq->get_result(); if ($tr) { $r = $tr->fetch_assoc(); if ($r) $tipo_nombre = $r['nombre_documento']; } $tq->close(); }
        ?>
            <tr>
                <td><?php echo htmlspecialchars($tipo_nombre); ?></td>
                <td><?php echo htmlspecialchars($d['estado'] ?? 'N/A'); ?></td>
                <td><?php if (!empty($d['ruta_archivo'])): ?><a href="../<?php echo htmlspecialchars($d['ruta_archivo']); ?>" target="_blank">Ver</a><?php endif; ?></td>
                <td>
                    <a class="btn-small" href="revisar_documento.php?id=<?php echo (int)$d['id_documento']; ?>">Revisar</a>
                    <?php if (!empty($d['estado']) && $d['estado'] !== 'pendiente'): ?>
                        <a class="btn-small" style="margin-left:8px;" href="editar_documento.php?id=<?php echo (int)$d['id_documento']; ?>">Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4">No hay documentos enviados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <div class="botones" style="margin-top:12px;"><a class="btn-secundario" href="estudiantes.php">⬅ Volver</a></div>
</div>
</body>
</html>
