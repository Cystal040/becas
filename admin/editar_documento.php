<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) { header('Location: revisar_documentos.php'); exit; }

    $observacion = trim($_POST['observacion'] ?? '');
    $estado = $_POST['estado'] ?? null;

    // No se permite subir archivos desde el admin en esta versión.

    // actualizar estado si viene
    if (in_array($estado, ['pendiente','aprobado','rechazado'])) {
        $u = $conn->prepare('UPDATE documento SET estado = ?, fecha_revision = NOW() WHERE id_documento = ?');
        if ($u) { $u->bind_param('si',$estado,$id); $u->execute(); $u->close(); }
    }

    // Registrar en historial la acción usando el nuevo estado como acción
    $create_hist = "CREATE TABLE IF NOT EXISTS historial_acciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_documento INT NOT NULL,
        accion VARCHAR(50) NOT NULL,
        admin_id INT NULL,
        observacion TEXT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($create_hist);

    if (in_array($estado, ['pendiente','aprobado','rechazado'])) {
        $h = $conn->prepare('INSERT INTO historial_acciones (id_documento, accion, admin_id, observacion) VALUES (?, ?, ?, NULL)');
        if ($h) {
            $accion = $estado;
            $admin_id = $_SESSION['admin_id'];
            $h->bind_param('isi',$id,$accion,$admin_id);
            $h->execute();
            $h->close();
        }
    }

    header('Location: estudiante_perfil.php?id=' . ($_POST['estudiante_id'] ?? '0'));
    exit;

}

// GET: mostrar form
$id = (int)$id;
if ($id <= 0) { header('Location: revisar_documentos.php'); exit; }

$stmt = $conn->prepare('SELECT d.*, td.nombre_documento, e.id_estudiante FROM documento d LEFT JOIN tipo_documento td ON d.id_tipo_documento = td.id_tipo_documento LEFT JOIN estudiante e ON d.id_estudiante = e.id_estudiante WHERE d.id_documento = ? LIMIT 1');
if (!$stmt) { header('Location: revisar_documentos.php'); exit; }
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$doc = $res ? $res->fetch_assoc() : null;
$stmt->close();
if (!$doc) { header('Location: revisar_documentos.php'); exit; }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar documento</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
 </head>
<body class="fondo">
<div class="contenedor">
    <h2>Editar documento</h2>
    <div class="card">
        <p><strong>Documento:</strong> <?php echo htmlspecialchars($doc['nombre_documento'] ?? ''); ?></p>
        <p><strong>Estado actual:</strong> <?php echo htmlspecialchars($doc['estado'] ?? 'N/A'); ?></p>
        <p><strong>Archivo actual:</strong> <?php if (!empty($doc['ruta_archivo'])): ?><a href="../<?php echo htmlspecialchars($doc['ruta_archivo']); ?>" target="_blank">Ver</a><?php endif; ?></p>
    </div>

    <form action="editar_documento.php" method="POST" style="margin-top:12px;">
        <input type="hidden" name="id" value="<?php echo (int)$doc['id_documento']; ?>">
        <input type="hidden" name="estudiante_id" value="<?php echo (int)$doc['id_estudiante']; ?>">

        <label>Estado:</label>
        <select name="estado">
            <option value="pendiente" <?php if(($doc['estado'] ?? '')==='pendiente') echo 'selected'; ?>>Pendiente</option>
            <option value="aprobado" <?php if(($doc['estado'] ?? '')==='aprobado') echo 'selected'; ?>>Aprobado</option>
            <option value="rechazado" <?php if(($doc['estado'] ?? '')==='rechazado') echo 'selected'; ?>>Rechazado</option>
        </select>

        <div class="botones"><button class="btn" type="submit">Guardar cambios</button> <a class="btn-secundario" href="estudiante_perfil.php?id=<?php echo (int)$doc['id_estudiante']; ?>">Cancelar</a></div>
    </form>
</div>
</body>
</html>
