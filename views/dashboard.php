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
// Crear mapa id->nombre para acceso por id
$tipos_map = [];
foreach ($tipos as $t) { $tipos_map[(int)$t['id_tipo_documento']] = $t['nombre_documento']; }

// Comprobar si la columna `fecha_revision` existe para evitar errores en instalaciones antiguas
$colCheck = $conn->query("SHOW COLUMNS FROM documento LIKE 'fecha_revision'");
$has_fecha_revision = ($colCheck && $colCheck->num_rows > 0);

// id_tipo => ['estado'=>..., 'fecha_subida'=>..., 'fecha_revision'=>...]
$status_map = [];
if ($has_fecha_revision) {
    $doc_stmt = $conn->prepare("SELECT id_documento, estado, fecha_subida, fecha_revision FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
} else {
    // Si no existe la columna, seleccionar solo las columnas disponibles
    $doc_stmt = $conn->prepare("SELECT id_documento, estado, fecha_subida FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
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
                'id' => (int)$r['id_documento'],
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

<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <div>
        <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?></h2>
        <p style="margin:4px 0 0; color:var(--muted);">Panel de estudiante — gestiona tus envíos y revisiones.</p>
    </div>

    <?php
    // Contar documentos revisados (aprobado o rechazado) y calcular notificaciones no vistas
    $vistas = isset($_SESSION['vistas_docs']) && is_array($_SESSION['vistas_docs']) ? $_SESSION['vistas_docs'] : [];
    $reviewed_list = []; // lista de doc ids revisados
    foreach ($status_map as $s) {
        if (is_array($s) && in_array($s['estado'], ['aprobado','rechazado'])) {
            $reviewed_list[] = $s['id'];
        }
    }
    $reviewed_count = count($reviewed_list);
    $unseen_count = 0;
    foreach ($reviewed_list as $did) {
        if (!in_array($did, $vistas)) { $unseen_count++; }
    }
    // Lista de tipos faltantes
    $faltantes = [];
    foreach ($tipos as $t) {
        $tid = (int)$t['id_tipo_documento'];
        if (!isset($status_map[$tid]) || $status_map[$tid] === null) {
            $faltantes[] = $t['nombre_documento'];
        }
    }
    ?>

    <div style="display:flex;align-items:center;gap:12px;">
        <div style="position:relative;">
            <a href="#" id="notifToggle" style="text-decoration:none;color:inherit;">
                <span style="font-size:22px;">🔔</span>
                <?php if ($unseen_count > 0): ?>
                    <span id="notifBadge" style="background:#e74c3c;color:#fff;border-radius:50%;padding:2px 6px;font-size:12px;margin-left:-10px;"><?php echo $unseen_count; ?></span>
                <?php endif; ?>
            </a>
            <div id="notifDropdown" style="display:none;position:absolute;right:0;top:28px;background:#fff;color:#000;border:1px solid #ddd;border-radius:6px;padding:10px;min-width:260px;z-index:40;box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                <strong>Notificaciones</strong>
                <hr style="margin:8px 0;">
                <?php if (empty($reviewed_list)): ?>
                    <div style="color:#666;">No hay notificaciones nuevas.</div>
                <?php else: ?>
                    <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($status_map as $tpk => $info): ?>
                        <?php if (is_array($info) && in_array($info['estado'], ['aprobado','rechazado'])): ?>
                            <li style="margin-bottom:8px;">
                                <div style="font-weight:600;"><?php echo htmlspecialchars($tipos_map[$tpk] ?? 'Documento'); ?></div>
                                <div style="font-size:13px;color:#333;">Estado: <?php echo htmlspecialchars(ucfirst($info['estado'])); ?> — enviado: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($info['fecha_subida']))); ?></div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align:right;">
            <a class="btn" href="subir_documentos.php">Subir documento</a>
            <a class="btn-secundario" href="mis_envios.php" style="margin-left:8px;">Mis envíos</a>
        </div>
    </div>

</div>

<script>
// Toggle notifications dropdown and mark as seen via AJAX
document.addEventListener('DOMContentLoaded', function(){
    var toggle = document.getElementById('notifToggle');
    var dropdown = document.getElementById('notifDropdown');
    var badge = document.getElementById('notifBadge');
    if (!toggle) return;
    toggle.addEventListener('click', function(e){
        e.preventDefault();
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
            // gather reviewed ids from PHP-rendered list via data attributes (collect from status_map)
            var ids = [];
            <?php foreach ($status_map as $s): if (is_array($s) && in_array($s['estado'], ['aprobado','rechazado'])): ?>
                ids.push(<?php echo (int)$s['id']; ?>);
            <?php endif; endforeach; ?>
            if (ids.length === 0) return;
            // send POST to mark as seen
            fetch('../controllers/mark_notifications.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ids: ids})
            }).then(function(r){ return r.json(); }).then(function(j){
                if (j.ok && badge) { badge.style.display = 'none'; }
            }).catch(function(){ /* no-op */ });
        } else {
            dropdown.style.display = 'none';
        }
    });
    // click outside to close
    document.addEventListener('click', function(e){
        if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});
</script>

<!-- Banner de faltantes convertido en opción en 'Mis envíos' -->

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
                        <small> — fecha: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fsub))); ?></small>
                    <?php elseif ($st === 'rechazado'): ?>
                        <strong style="color:#e74c3c;">Rechazado</strong>
                        <small> — fecha: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($fsub))); ?></small>
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