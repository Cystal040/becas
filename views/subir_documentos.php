<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Documentos</title>
    <link rel="icon" href="img/icono.png">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body>

<h2>Subir documentos para la beca</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Tipo de documento:</label>
    <select name="tipo_documento" required>
        <option value="">Seleccione</option>
        <option value="1">Constancia de inscripción</option>
        <option value="2">Récord académico</option>
        <option value="3">Cédula</option>
        <option value="4">RIF</option>
        <option value="5">Foto tipo carnet</option>
    </select>

    <br><br>

    <label>Archivo:</label>
    <input type="file" name="archivo" required>

    <br><br>

    <button type="submit" name="subir">Subir documento</button>
</form>

<a href="dashboard.php">Volver al panel</a>

</body>
</html>

<?php
if (isset($_POST['subir'])) {

    $id_estudiante = $_SESSION['usuario_id'];
    $id_tipo = $_POST['tipo_documento'];

    $archivo = $_FILES['archivo'];
    $nombre = $archivo['name'];
    $tmp = $archivo['tmp_name'];
    $tamano = $archivo['size'];

    // Extensión del archivo
    $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

    // Formatos permitidos
    $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (!in_array($extension, $permitidos)) {
        echo "Formato no permitido";
        exit;
    }

    // Tamaño máximo (5MB)
    if ($tamano > 5 * 1024 * 1024) {
        echo "El archivo es demasiado grande";
        exit;
    }

    // Carpeta según tipo de documento
    $carpetas = [
        1 => "uploads/constancia/",
        2 => "uploads/record/",
        3 => "uploads/cedula/",
        4 => "uploads/rif/",
        5 => "uploads/foto/"
    ];

    $destino = $carpetas[$id_tipo] . time() . "_" . $nombre;

    // Mover archivo
    if (move_uploaded_file($tmp, $destino)) {

        $sql = "INSERT INTO documento 
                (ruta_archivo, id_estudiante, id_tipo_documento)
                VALUES ('$destino', '$id_estudiante', '$id_tipo')";

        if ($conn->query($sql)) {
            echo "Documento subido correctamente";
        } else {
            echo "Error al guardar en la base de datos";
        }

    } else {
        echo "Error al subir el archivo";
    }
}
?>