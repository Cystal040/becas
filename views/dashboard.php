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

$status_map = []; // id_tipo => estado (string) o null
$doc_stmt = $conn->prepare("SELECT estado, fecha_subida FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
if ($doc_stmt) {
    foreach ($tipos as $t) {
        $tipoId = (int)$t['id_tipo_documento'];
        $doc_stmt->bind_param('ii', $id_estudiante, $tipoId);
        $doc_stmt->execute();
        $resd = $doc_stmt->get_result();
        if ($resd && $resd->num_rows > 0) {
            $r = $resd->fetch_assoc();
            $status_map[$tipoId] = $r['estado'];
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

    <div class="botones">
        <a class="btn-secundario" href="../logout.php">Cerrar sesión</a>
    </div>

</div> <!-- .contenedor -->

</body>
</html>