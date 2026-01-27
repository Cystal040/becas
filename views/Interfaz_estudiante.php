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
// Estadísticas básicas
$total_tipos = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tipo_documento");
if ($stmt) { $stmt->execute(); $res = $stmt->get_result(); $r = $res->fetch_assoc(); $total_tipos = (int) ($r['total'] ?? 0); $stmt->close(); }
$enviados_count = 0;
$stmt = $conn->prepare("SELECT COUNT(DISTINCT id_tipo_documento) AS enviados FROM documento WHERE id_estudiante = ?");
if ($stmt) { $stmt->bind_param('i', $id_estudiante); $stmt->execute(); $res = $stmt->get_result(); $r = $res->fetch_assoc(); $enviados_count = (int) ($r['enviados'] ?? 0); $stmt->close(); }
$faltantes_count = max(0, $total_tipos - $enviados_count);
$ultima_subida = '-';
$stmt = $conn->prepare("SELECT fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC LIMIT 1");
if ($stmt) { $stmt->bind_param('i', $id_estudiante); $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) { $ultima_subida = date('d/m/Y H:i', strtotime($r['fecha_subida'])); } $stmt->close(); }
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
    <div class="contenedor animate-item stagger-1">
        <h1 class="animate-item stagger-2">Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h1>

        <section style="margin-top:12px;" class="animate-item stagger-2">
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

        <section style="margin-top:14px;" class="animate-item stagger-3">
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
            <a class="btn-secundario btn-animated" href="../logout.php">Cerrar sesión</a>
            <a class="btn btn-animated" href="subir_documentos.php">Subir documento</a>
        </div>
    </div>
    <!-- Toast container -->
    <div id="toast-container" aria-live="polite" style="position:fixed;right:16px;bottom:16px;z-index:9999"></div>

    <style>
        .toast {
            background: #333;
            color: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 320px;
            opacity: 0;
            transform: translateY(10px);
            transition: all .25s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .toast.error {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
    </style>

    <script>
        (function () {
            function showToast(msg, type) {
                if (!msg) return;
                var c = document.getElementById('toast-container');
                var t = document.createElement('div');
                t.className = 'toast ' + (type === 'error' ? 'error' : 'success');
                t.textContent = msg;
                c.appendChild(t);
                // force reflow
                void t.offsetWidth;
                t.classList.add('show');
                setTimeout(function () { t.classList.remove('show'); setTimeout(function () { c.removeChild(t); }, 300); }, 4200);
            }
            // Server-provided flashes
            <?php if (!empty($flash_success)): ?>
                showToast(<?php echo json_encode($flash_success); ?>, 'success');
            <?php endif; ?>
            <?php if (!empty($flash_error)): ?>
                showToast(<?php echo json_encode($flash_error); ?>, 'error');
            <?php endif; ?>
        })();
    </script>
    <script src="../assets/js/animations.js"></script>
</body>

</html>