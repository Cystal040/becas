<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Obtener solo documentos pendientes unidos a estudiante y tipo
$sql = "SELECT d.id_documento, d.ruta_archivo, d.fecha_subida,
        e.nombre, e.apellido, td.nombre_documento
    FROM documento d
    LEFT JOIN estudiante e ON d.id_estudiante = e.id_estudiante
    LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento
    WHERE d.estado = 'pendiente'
    ORDER BY d.fecha_subida DESC";
$resultado = $conn->query($sql);
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
                    <td><?php echo htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                    <td><?php echo htmlspecialchars($fila['nombre_documento']); ?></td>
                    <td><?php echo ($fila['estado'] === 'pendiente') ? 'En espera' : ucfirst($fila['estado']); ?></td>
                    <td><?php if (!empty($fila['ruta_archivo'])): ?><a
                                href="../<?php echo htmlspecialchars($fila['ruta_archivo']); ?>"
                                target="_blank">Ver</a><?php endif; ?></td>
                    <td>
                        <form action="actualizar_estado.php" method="POST" style="display:inline">
                            <input type="hidden" name="id" value="<?php echo (int) $fila['id_documento']; ?>">
                            <input type="hidden" name="estado" value="aprobado">
                            <button class="btn" type="submit">✔ Aprobar</button>
                        </form>
                        <form action="actualizar_estado.php" method="POST" style="display:inline; margin-left:8px;">
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