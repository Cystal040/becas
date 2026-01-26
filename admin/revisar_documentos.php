<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Obtener documentos (comprobar si la columna 'estado' existe para evitar errores en esquemas distintos)
$has_estado = false;
$col_q = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento' AND COLUMN_NAME = 'estado'");
if ($col_q && $col_q->num_rows > 0) { $has_estado = true; }
if ($col_q) { $col_q->close(); }

// Detectar columna FK en `documento` que apunte al estudiante (si existe)
$doc_student_col = null;
$possible_doc_cols = ['id_estudiante','estudiante_id','id_usuario','usuario_id','user_id','student_id'];
foreach ($possible_doc_cols as $c) {
    $q = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento' AND COLUMN_NAME = '" . $c . "'");
    if ($q && $q->num_rows > 0) { $doc_student_col = $c; }
    if ($q) { $q->close(); }
    if ($doc_student_col) break;
}

// Detectar PK en tabla estudiante
$est_pk = null;
$possible_est_cols = ['id_estudiante','id','usuario_id','id_usuario'];
foreach ($possible_est_cols as $c) {
    $q = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'estudiante' AND COLUMN_NAME = '" . $c . "'");
    if ($q && $q->num_rows > 0) { $est_pk = $c; }
    if ($q) { $q->close(); }
    if ($est_pk) break;
}

$join_student = ($doc_student_col && $est_pk);

$select_cols = "d.id_documento, d.ruta_archivo, d.fecha_subida, td.nombre_documento";
if ($join_student) { $select_cols = "d.id_documento, d.ruta_archivo, d.fecha_subida, e.nombre, e.apellido, td.nombre_documento"; }
if ($has_estado) { $select_cols .= ", d.estado"; }

$sql = "SELECT " . $select_cols . "\n    FROM documento d\n";
if ($join_student) {
    $sql .= "    LEFT JOIN estudiante e ON d." . $doc_student_col . " = e." . $est_pk . "\n";
}
$sql .= "    LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento";
if ($has_estado) {
    $sql .= "\n    WHERE d.estado = 'pendiente'";
}
$sql .= "\n    ORDER BY d.fecha_subida DESC";

$resultado = $conn->query($sql);
if (!$resultado) {
    error_log('admin/revisar_documentos.php SQL error: ' . $conn->error);
    // Crear un resultado vacío para que el resto del código no falle
    $resultado = new class {
        public function fetch_assoc() { return false; }
    };
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Revisión de documentos</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>

<body class="fondo">

    <div class="contenedor">
        <h2>Documentos enviados</h2>

        <table>
            <tr>
                <th>Estudiante</th>
                <th>Documento</th>
                <th>Estado</th>
                <th>Archivo</th>
                <th>Acción</th>
            </tr>

            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                            <td><?php echo isset($fila['nombre']) ? htmlspecialchars($fila['nombre'] . ' ' . ($fila['apellido'] ?? '')) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre_documento'] ?? '-'); ?></td>
                            <td><?php if (isset($fila['estado'])) { echo ($fila['estado'] === 'pendiente') ? 'En espera' : ucfirst($fila['estado']); } else { echo 'N/A'; } ?></td>
                    <td><?php if (!empty($fila['ruta_archivo'])): ?><a
                                href="../<?php echo htmlspecialchars($fila['ruta_archivo']); ?>"
                                target="_blank">Ver</a><?php endif; ?></td>
                    <td>
                        <form class="confirm-action-form" action="actualizar_estado.php" method="POST" style="display:inline">
                            <input type="hidden" name="id" value="<?php echo (int) $fila['id_documento']; ?>">
                            <input type="hidden" name="estado" value="aprobado">
                            <button class="btn" type="submit">✔ Aceptar</button>
                        </form>
                        <form class="confirm-action-form" action="actualizar_estado.php" method="POST" style="display:inline; margin-left:8px;">
                            <input type="hidden" name="id" value="<?php echo (int) $fila['id_documento']; ?>">
                            <input type="hidden" name="estado" value="rechazado">
                            <button class="btn-secundario" type="submit">✖ Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>

        </table>

        <div class="botones" style="margin-top:16px;">
            <a class="btn-secundario" href="admin_panel.php">⬅ Volver</a>
        </div>

    </div> <!-- .contenedor -->

</body>

</html>

<script>
// Confirmar acciones en la lista de documentos (sin observación)
document.querySelectorAll('.confirm-action-form').forEach(function(f){
    f.addEventListener('submit', function(e){
        e.preventDefault();
        var estado = f.querySelector('input[name="estado"]').value;
        var msg = (estado === 'aprobado') ? '¿Está seguro que desea APROBAR este documento?' : '¿Está seguro que desea RECHAZAR este documento?';
        if (confirm(msg)) { f.submit(); }
    });
});
</script>