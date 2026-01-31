<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Cargar tipos de documento desde la BD
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

// Si no hay tipos, insertar valores por defecto y recargar
if (empty($tipos)) {
    $defaults = [
        [1, 'Constancia de inscripción'],
        [2, 'Récord académico'],
        [3, 'Cédula'],
        [4, 'RIF'],
        [5, 'Foto tipo carnet'],
        [6, 'Carnet de la patria'],
        [7, 'Referencia bancaria']
    ];
    $ins = $conn->prepare("INSERT INTO tipo_documento (id_tipo_documento, nombre_documento) VALUES (?, ?)");
    if ($ins) {
        foreach ($defaults as $d) {
            $ins->bind_param('is', $d[0], $d[1]);
            @$ins->execute();
        }
        $ins->close();
    }

    // recargar
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
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subir Documentos</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>

<body class="fondo">

    <div class="contenedor animate-item stagger-1">

        <h2 class="animate-item stagger-2">Subir documentos para la beca</h2>

        <form method="POST" enctype="multipart/form-data" class="animate-item stagger-3">

            <label>Tipo de documento:</label>
            <select name="tipo_documento" required>
                <option value="">Seleccione</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?php echo (int) 
                        $t['id_tipo_documento']; ?>"><?php echo htmlspecialchars($t['nombre_documento']); ?></option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <label>Archivo:</label>
            <input type="file" name="archivo" required>

            <br><br>

            <button type="submit" name="subir" class="btn btn-animated">Subir documento</button>
        </form>

        <div class="botones">
            <button class="btn btn-animated" onclick="window.location.href='Interfaz_estudiante.php'"
                type="button">Volver al
                panel</button>
        </div>

    </div> <!-- .contenedor -->

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

    // Base de uploads (ruta absoluta)
    $baseUploads = __DIR__ . '/../assets/uploads/';

    // Mapear tipos a subcarpetas (nombres con slash final)
    $folders = [
        1 => 'constancia/',
        2 => 'record/',
        3 => 'cedula/',
        4 => 'rif/',
        5 => 'foto/',
        6 => 'carnet/',
        7 => 'referencia_bancaria/'
    ];

    if (!isset($folders[$id_tipo])) {
        echo "Tipo de documento inválido";
        exit;
    }

    $subfolder = $folders[$id_tipo];
    $absDir = $baseUploads . $subfolder;

    // Crear directorio si no existe
    if (!is_dir($absDir)) {
        if (!mkdir($absDir, 0777, true)) {
            echo "No se pudo crear la carpeta de destino";
            exit;
        }
    }

    // Nombre seguro y único
    $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($nombre));
    $uniqueName = time() . '_' . mt_rand(1000, 9999) . '_' . $safeName;

    $destPath = $absDir . $uniqueName; // ruta absoluta para mover
    $dbPath = 'assets/uploads/' . $subfolder . $uniqueName; // ruta relativa a guardar en BD

    // Mover archivo
    if (is_uploaded_file($tmp) && move_uploaded_file($tmp, $destPath)) {

        // Validar que el tipo de documento exista en la tabla tipo_documento
        $tipoId = (int) $id_tipo;
        $chk = $conn->prepare("SELECT id_tipo_documento FROM tipo_documento WHERE id_tipo_documento = ? LIMIT 1");
        if (!$chk) {
            echo "Error en la validación del tipo de documento";
            exit;
        }
        $chk->bind_param('i', $tipoId);
        $chk->execute();
        $resTipo = $chk->get_result();
        $chk->close();

        if (!$resTipo || $resTipo->num_rows === 0) {
            echo "Tipo de documento no válido en la base de datos";
            exit;
        }

        // Guardar con estado inicial 'pendiente'
        // Evitar que se suba si ya existe un documento aprobado del mismo tipo para este estudiante
        $checkApproved = $conn->prepare("SELECT estado FROM documento WHERE id_estudiante = ? AND id_tipo_documento = ? ORDER BY fecha_subida DESC LIMIT 1");
        if ($checkApproved) {
            $checkApproved->bind_param('ii', $id_estudiante, $tipoId);
            $checkApproved->execute();
            $rchk = $checkApproved->get_result();
            if ($rchk && $rchk->num_rows > 0) {
                $rrow = $rchk->fetch_assoc();
                if (isset($rrow['estado']) && $rrow['estado'] === 'aprobado') {
                    echo "No puede subir este documento porque ya existe una versión aprobada.";
                    exit;
                }
            }
            $checkApproved->close();
        }

        $estado = 'pendiente';
        $stmt = $conn->prepare("INSERT INTO documento (ruta_archivo, id_estudiante, id_tipo_documento, estado) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('siis', $dbPath, $id_estudiante, $tipoId, $estado);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = 'Documento subido correctamente.';
                header('Location: Interfaz_estudiante.php');
                exit;
            } else {
                $_SESSION['flash_error'] = 'Error al guardar en la base de datos.';
                header('Location: Interfaz_estudiante.php');
                exit;
            }
            $stmt->close();
        } else {
            echo "Error en la consulta a la base de datos";
        }

    } else {
        $_SESSION['flash_error'] = 'Error al subir el archivo. Verifica permisos y que la carpeta exista.';
        header('Location: Interfaz_estudiante.php');
        exit;
    }
}
?>
<script src="../assets/js/animations.js"></script>