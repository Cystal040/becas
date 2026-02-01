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

// Valores actuales de filtros
$cedula = trim($_GET['cedula'] ?? '');
$f_carrera = trim($_GET['carrera'] ?? '');
$f_semestre = trim($_GET['semestre'] ?? '');
// Filtro adicional: estado de documentos (todos aprobados / todos rechazados)
$f_docestado = trim($_GET['doc_estado'] ?? '');

// Preparar listas para los selects (valores disponibles en BD)
$carreras = $conn->query("SELECT DISTINCT carrera FROM estudiante WHERE carrera IS NOT NULL AND carrera <> '' ORDER BY carrera");
$semestres = $conn->query("SELECT DISTINCT semestre FROM estudiante WHERE semestre IS NOT NULL AND semestre <> '' ORDER BY CAST(semestre AS UNSIGNED)");

// Búsqueda por cédula (LIKE)
if ($cedula !== '') { $q_where[] = "e.cedula LIKE ?"; $params[] = "%$cedula%"; $types .= 's'; }
// Filtro por carrera (exacto)
if ($f_carrera !== '') { $q_where[] = "e.carrera = ?"; $params[] = $f_carrera; $types .= 's'; }
// Filtro por semestre (exacto)
if ($f_semestre !== '') { $q_where[] = "e.semestre = ?"; $params[] = $f_semestre; $types .= 's'; }
// Filtro por estado de documentos (agregado, no usa parámetros)
if ($f_docestado === 'todos_aprobados') {
    $q_where[] = "COALESCE(d.total_docs,0) > 0 AND COALESCE(d.aprobados,0) = COALESCE(d.total_docs,0)";
} elseif ($f_docestado === 'todos_rechazados') {
    $q_where[] = "COALESCE(d.total_docs,0) > 0 AND COALESCE(d.rechazados,0) = COALESCE(d.total_docs,0)";
}

// Unir conteo de documentos por estudiante para permitir filtros por estado global
$sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.cedula, e.correo, e.carrera, e.semestre, COALESCE(d.total_docs,0) AS total_docs, COALESCE(d.aprobados,0) AS aprobados, COALESCE(d.rechazados,0) AS rechazados FROM estudiante e LEFT JOIN (SELECT id_estudiante, COUNT(*) AS total_docs, SUM(CASE WHEN estado='aprobado' THEN 1 ELSE 0 END) AS aprobados, SUM(CASE WHEN estado='rechazado' THEN 1 ELSE 0 END) AS rechazados FROM documento GROUP BY id_estudiante) d ON e.id_estudiante = d.id_estudiante";
$sql .= ' WHERE ' . (empty($q_where) ? '1' : implode(' AND ', $q_where));
$sql .= ' ORDER BY e.nombre, e.apellido LIMIT 500';

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

    <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;align-items:center;">
        <div class="filters-row" style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="cedula" placeholder="Cédula" value="<?php echo htmlspecialchars($cedula ?? ''); ?>">

            <select name="carrera">
                <option value="">Todas las carreras</option>
                <?php if ($carreras && $carreras->num_rows): while ($c = $carreras->fetch_assoc()): $cv = $c['carrera']; ?>
                    <option value="<?php echo htmlspecialchars($cv); ?>" <?php echo ($f_carrera === $cv) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cv); ?></option>
                <?php endwhile; endif; ?>
            </select>

            <select name="semestre">
                <option value="">Todos los semestres</option>
                <?php if ($semestres && $semestres->num_rows): while ($s = $semestres->fetch_assoc()): $sv = $s['semestre']; ?>
                    <option value="<?php echo htmlspecialchars($sv); ?>" <?php echo ($f_semestre === $sv) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sv); ?></option>
                <?php endwhile; endif; ?>
            </select>
        </div>

        <select name="doc_estado">
            <option value="">Todos los estados</option>
            <option value="todos_aprobados" <?php echo ($f_docestado === 'todos_aprobados') ? 'selected' : ''; ?>>Todos los documentos aprobados</option>
            <option value="todos_rechazados" <?php echo ($f_docestado === 'todos_rechazados') ? 'selected' : ''; ?>>Todos los documentos rechazados</option>
        </select>

        <button class="btn" type="submit">Filtrar</button>
        <a class="btn-secundario" href="estudiantes.php">Limpiar</a>
    </form>

    <div class="table-responsive">
    <table class="table-compact">
        <thead>
            <tr><th>Nombre</th><th>Cédula</th><th>Carrera</th><th>Semestre</th><th>Correo</th><th>Acción</th></tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                <td><?php echo htmlspecialchars($row['carrera'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['semestre'] ?? ''); ?></td>
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