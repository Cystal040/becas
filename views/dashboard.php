<?php
session_start();
include("conexion.php");

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
    <link rel="icon" href="img/icono.png">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

<h2>Bienvenido, <?php echo $nombre; ?></h2>

<p>Desde este panel puedes subir y revisar tus documentos para la beca.</p>

<hr>

<h3>Documentos solicitados</h3>
<ul>
    <li>Constancia de inscripción (PDF)</li>
    <li>Récord académico (PDF)</li>
    <li>Cédula (PDF / JPG / PNG)</li>
    <li>RIF (PDF / DOC)</li>
    <li>Foto tipo carnet</li>
</ul>

<a href="subir_documentos.php">Subir documento</a>

<hr>

<h3>Mis documentos</h3>

<table border="1" cellpadding="8">
    <tr>
        <th>Documento</th>
        <th>Estado</th>
        <th>Fecha de subida</th>
    </tr>

    <?php if ($resultado->num_rows > 0) { ?>
        <?php while ($fila = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $fila['nombre_documento']; ?></td>
                <td><?php echo ucfirst($fila['estado']); ?></td>
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

<a href="logout.php">Cerrar sesión</a>

</body>
</html>