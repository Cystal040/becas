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
<div class="contenedor" style="display:flex;gap:18px;align-items:flex-start;">

    <!-- Sidebar / Menu -->
    <aside style="width:220px;background:rgba(255,255,255,0.03);padding:12px;border-radius:8px;">
        <div style="margin-bottom:12px;">
            <strong><?php echo htmlspecialchars($nombre); ?></strong><br>
            <small style="color:var(--muted);">Estudiante</small>
        </div>
        <nav style="display:flex;flex-direction:column;gap:8px;">
            <a href="dashboard.php" style="text-decoration:none;">🏠 Resumen</a>
            <a href="mis_envios.php" style="text-decoration:none;">📄 Mis envíos</a>
            <a href="subir_documentos.php" style="text-decoration:none;">➕ Subir documento</a>
            <a href="../logout.php" style="text-decoration:none;">🚪 Cerrar sesión</a>
        </nav>
        <hr style="margin:12px 0;">
        <div>
            <strong>Notificaciones</strong>
            <?php if ($unseen_count > 0): ?> <span style="background:#e74c3c;color:#fff;border-radius:50%;padding:2px 6px;font-size:12px;margin-left:8px;"><?php echo $unseen_count; ?></span><?php endif; ?>
        </div>
    </aside>

    <!-- Main content -->
    <main style="flex:1;">

        <!-- Resumen general -->
        <?php
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

        <div style="display:flex;gap:12px;margin-bottom:12px;">
            <div style="flex:1;padding:12px;background:#ecf9f1;border-radius:8px;">
                <div style="font-size:18px;font-weight:600;">Resumen de documentos</div>
                <div style="margin-top:8px;display:flex;gap:12px;flex-wrap:wrap;">
                    <div>✅ Aprobados: <strong><?php echo $counts['aprobado']; ?></strong></div>
                    <div>🟡 En espera: <strong><?php echo $counts['pendiente']; ?></strong></div>
                    <div>❌ Rechazados: <strong><?php echo $counts['rechazado']; ?></strong></div>
                    <div>⬜ Pendientes: <strong><?php echo $counts['faltante']; ?></strong></div>
                </div>
            </div>

            <div style="width:260px;padding:12px;background:rgba(255,255,255,0.02);border-radius:8px;">
                <div style="font-weight:600;">Mensajes</div>
                <div style="margin-top:8px;color:var(--muted);">Revisa los documentos rechazados y vuelve a subirlos con el formato correcto.</div>
            </div>
        </div>

        <!-- Tabla principal de documentos (compacta) -->
        <div class="card">
            <h3>Documentos requeridos</h3>
            <table style="width:100%;border-collapse:collapse;">
                <tr style="text-align:left;border-bottom:1px solid #eee;padding:8px 0;"><th>Documento</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr>
                <?php foreach ($tipos as $t): ?>
                    <?php $tid = (int)$t['id_tipo_documento']; $info = $status_map[$tid] ?? null; ?>
                    <tr style="border-bottom:1px solid #f3f3f3;">
                        <td style="padding:10px 6px"><?php echo htmlspecialchars($t['nombre_documento']); ?></td>
                        <td style="padding:10px 6px">
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
                        <td style="padding:10px 6px"><?php echo $info ? htmlspecialchars(date('d/m/Y H:i', strtotime($info['fecha_subida']))) : '-'; ?></td>
                        <td style="padding:10px 6px">
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