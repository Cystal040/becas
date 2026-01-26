<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$q_where = [];
$params = [];
$types = '';

// Filtros de búsqueda
$nombre = trim($_GET['nombre'] ?? '');
$cedula = trim($_GET['cedula'] ?? '');
$correo = trim($_GET['correo'] ?? '');

if ($nombre !== '') { $q_where[] = "(nombre LIKE ? OR apellido LIKE ? OR CONCAT(nombre,' ',apellido) LIKE ?)"; $params[] = "%$nombre%"; $params[] = "%$nombre%"; $params[] = "%$nombre%"; $types .= 'sss'; }
if ($cedula !== '') { $q_where[] = "cedula LIKE ?"; $params[] = "%$cedula%"; $types .= 's'; }
if ($correo !== '') { $q_where[] = "correo LIKE ?"; $params[] = "%$correo%"; $types .= 's'; }

$sql = "SELECT id_estudiante, nombre, apellido, cedula, correo FROM estudiante";
if (!empty($q_where)) { $sql .= ' WHERE ' . implode(' AND ', $q_where); }
$sql .= ' ORDER BY nombre, apellido LIMIT 500';

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estudiantes</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
 </head>
<body class="fondo">
<div class="contenedor">
    <h2>Listado de estudiantes</h2>

    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
        <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($nombre); ?>">
        <input type="text" name="cedula" placeholder="Cédula" value="<?php echo htmlspecialchars($cedula); ?>">
        <input type="text" name="correo" placeholder="Correo" value="<?php echo htmlspecialchars($correo); ?>">
        <button class="btn" type="submit">Buscar</button>
        <a class="btn-secundario" href="estudiantes.php">Limpiar</a>
    </form>

    <div class="table-responsive">
    <table class="table-compact">
        <thead>
            <tr><th>Nombre</th><th>Cédula</th><th>Correo</th><th>Acción</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                <td><?php echo htmlspecialchars($row['correo']); ?></td>
                <td><a class="btn-small" href="estudiante_perfil.php?id=<?php echo (int)$row['id_estudiante']; ?>">Ver perfil</a></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <div class="botones" style="margin-top:12px;"><a class="btn-secundario" href="admin_panel.php">⬅ Volver</a></div>
</div>
</body>
</html>
