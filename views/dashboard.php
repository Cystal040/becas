<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];
$nombre = $_SESSION['usuario_nombre'];

// Cargar tipos de documento
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

// Obtener último documento por tipo para este estudiante
$status_map = []; // id_tipo => row
$doc_stmt = $conn->prepare("SELECT id_documento, id_tipo_documento, ruta_archivo, estado, fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC");
if ($doc_stmt) {
    $doc_stmt->bind_param('i', $id_estudiante);
    $doc_stmt->execute();
    $resd = $doc_stmt->get_result();
    while ($row = $resd->fetch_assoc()) {
        $tid = (int)$row['id_tipo_documento'];
        if (!isset($status_map[$tid])) {
            $status_map[$tid] = $row;
        }
    }
    $doc_stmt->close();
}

// Map id -> nombre
$tipos_map = [];
foreach ($tipos as $t) { $tipos_map[(int)$t['id_tipo_documento']] = $t['nombre_documento']; }

// Notificaciones (sesión)
$vistas = isset($_SESSION['vistas_docs']) && is_array($_SESSION['vistas_docs']) ? $_SESSION['vistas_docs'] : [];
$reviewed_list = [];
foreach ($status_map as $s) {
    if (is_array($s) && in_array($s['estado'], ['aprobado','rechazado'])) {
        $reviewed_list[] = (int)$s['id_documento'];
    }
}
$unseen_count = 0;
foreach ($reviewed_list as $did) {
    if (!in_array($did, $vistas)) { $unseen_count++; }
}

// Contadores resumen
$counts = ['aprobado'=>0,'pendiente'=>0,'rechazado'=>0,'faltante'=>0];
foreach ($tipos as $t) {
    $tid = (int)$t['id_tipo_documento'];
    $info = $status_map[$tid] ?? null;
    if (!$info) { $counts['faltante']++; }
    else {
        if ($info['estado'] === 'aprobado') $counts['aprobado']++;
        elseif ($info['estado'] === 'pendiente') $counts['pendiente']++;
        elseif ($info['estado'] === 'rechazado') $counts['rechazado']++;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Estudiante</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
    <style>
        /* Pequeños ajustes locales para el panel */
        .sidebar-link { display:block; padding:6px 4px; color:inherit; }
        .card { padding:12px; background:rgba(255,255,255,0.02); border-radius:8px; margin-bottom:12px; }
        table th, table td { padding:8px 6px; }
        table tr { border-bottom:1px solid #f3f3f3; }
        .btn { background:#2d89ef;color:#fff;padding:6px 10px;border-radius:6px;text-decoration:none; }
        .btn-secundario { background:transparent;border:1px solid rgba(255,255,255,0.06);padding:6px 10px;border-radius:6px;text-decoration:none;color:inherit; }
    </style>
</head>
<body class="fondo">

<div class="contenedor" style="display:flex;gap:18px;align-items:flex-start;">

    <!-- Sidebar / Menu -->
    <aside style="width:220px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px;">
        <div style="margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <strong><?php echo htmlspecialchars($nombre); ?></strong><br>
                    <small style="color:var(--muted);">Estudiante</small>
                </div>
                <div>
                    <a class="notif-bell" id="notifBell" href="mis_envios.php" title="Notificaciones">
                        <!-- bell SVG -->
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 005 14h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 18a2.5 2.5 0 002.45-2H9.55A2.5 2.5 0 0012 20z"/></svg>
                        <?php if ($unseen_count > 0): ?>
                            <span class="notif-badge" id="notifCount"><?php echo $unseen_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        <nav style="display:flex;flex-direction:column;gap:8px;">
            <a class="sidebar-link" href="dashboard.php">🏠 Resumen</a>
            <a class="sidebar-link" href="mis_envios.php">📄 Mis envíos</a>
            <a class="sidebar-link" href="subir_documentos.php">➕ Subir documento</a>
            <a class="sidebar-link" href="../logout.php">🚪 Cerrar sesión</a>
        </nav>
        <hr style="margin:12px 0;">
        <div>
            <strong>Notificaciones</strong>
            <?php if ($unseen_count > 0): ?> <span style="background:#e74c3c;color:#fff;border-radius:50%;padding:2px 6px;font-size:12px;margin-left:8px;"><?php echo $unseen_count; ?></span><?php endif; ?>
        </div>
    </aside>

    <!-- Main content -->
    <main style="flex:1;">

        <div style="display:flex;gap:12px;margin-bottom:12px;">
            <div style="flex:1;" class="card">
                <div style="font-size:18px;font-weight:600;">Resumen de documentos</div>
                <div style="margin-top:8px;display:flex;gap:18px;flex-wrap:wrap;">
                    <div>✅ Aprobados: <strong><?php echo $counts['aprobado']; ?></strong></div>
                    <div>🟡 En espera: <strong><?php echo $counts['pendiente']; ?></strong></div>
                    <div>❌ Rechazados: <strong><?php echo $counts['rechazado']; ?></strong></div>
                    <div>⬜ Pendientes: <strong><?php echo $counts['faltante']; ?></strong></div>
                </div>
            </div>

            <div style="width:260px;" class="card">
                <div style="font-weight:600;">Mensajes</div>
                <div style="margin-top:8px;color:var(--muted);">Revisa los documentos rechazados y vuelve a subirlos con el formato correcto.</div>
            </div>
        </div>

        <div class="card">
            <h3>Documentos requeridos</h3>
            <table style="width:100%;border-collapse:collapse;">
                <tr style="text-align:left;"><th>Documento</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr>
                <?php foreach ($tipos as $t): ?>
                    <?php $tid = (int)$t['id_tipo_documento']; $info = $status_map[$tid] ?? null; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['nombre_documento']); ?></td>
                        <td>
                            <?php if (!$info): ?>
                                <span style="color:#7f8c8d;">⬜ No enviado</span>
                            <?php else: ?>
                                <?php $st = $info['estado'];
                                    if ($st === 'aprobado') echo '<span style="color:#27ae60">🟢 Aprobado</span>'; 
                                    elseif ($st === 'pendiente') echo '<span style="color:#f1c40f">🟡 En revisión</span>'; 
                                    elseif ($st === 'rechazado') echo '<span style="color:#c0392b">❌ Rechazado</span>'; 
                                    else echo '<span>'.htmlspecialchars(ucfirst($st)).'</span>'; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $info ? htmlspecialchars(date('d/m/Y H:i', strtotime($info['fecha_subida']))) : '-'; ?></td>
                        <td>
                            <?php if (!$info): ?>
                                <a class="btn" href="subir_documentos.php?tipo=<?php echo $tid; ?>">➕ Subir</a>
                            <?php else: ?>
                                <?php if (!empty($info['ruta_archivo'])): ?>
                                    <a class="btn-secundario" href="../<?php echo htmlspecialchars($info['ruta_archivo']); ?>" target="_blank">Ver</a>
                                <?php endif; ?>
                                <?php if ($info['estado'] === 'rechazado'): ?>
                                    <a class="btn" href="subir_documentos.php?tipo=<?php echo $tid; ?>" style="margin-left:6px;">🔄 Volver a subir</a>
                                <?php elseif ($info['estado'] === 'pendiente'): ?>
                                    <span style="color:#95a5a6;margin-left:6px;">⏳ En espera</span>
                                <?php else: ?>
                                    <span style="color:#95a5a6;margin-left:6px;">🔒 Bloqueado</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </main>

</div> <!-- .contenedor -->

</body>
</html>
<script>
// Marcar notificaciones vistas al hacer click en la campana
document.addEventListener('DOMContentLoaded', function(){
    var bell = document.getElementById('notifBell');
    if (!bell) return;
    bell.addEventListener('click', function(e){
        try {
            // ids para marcar: pasar desde PHP
            var ids = <?php echo json_encode($reviewed_list, JSON_HEX_TAG); ?> || [];
            if (ids.length === 0) return; // nada que marcar
            fetch('../controllers/mark_notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: ids })
            }).then(function(resp){ return resp.json(); }).then(function(data){
                // quitar contador visual si OK
                var badge = document.getElementById('notifCount');
                if (badge) badge.style.display = 'none';
            }).catch(function(){ /* silently fail */ });
        } catch(err){ /* ignore */ }
        // allow navigation to mis_envios.php
    });
});
</script>
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
                    <?php $tid = (int) $t['id_tipo_documento'];
                    $info = $status_map[$tid] ?? null; ?>
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