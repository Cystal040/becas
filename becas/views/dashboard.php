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
$sql = "SELECT d.id_documento, td.nombre_documento, d.estado, d.fecha_subida
        FROM documento d
        INNER JOIN tipo_documento td 
        ON d.id_tipo_documento = td.id_tipo_documento
        WHERE d.id_estudiante = '$id_estudiante'";

$resultado = $conexion->query($sql);
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