<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Obtener valores para selects
$carreras = [];
$semestres = [];
$r = $conn->query("SELECT DISTINCT carrera FROM estudiante ORDER BY carrera");
if ($r) {
    while ($row = $r->fetch_assoc()) { $carreras[] = $row['carrera']; }
    $r->close();
}
$r = $conn->query("SELECT DISTINCT semestre FROM estudiante ORDER BY semestre");
if ($r) {
    while ($row = $r->fetch_assoc()) { $semestres[] = $row['semestre']; }
    $r->close();
}

// Construir query dinámico con filtros GET
$conditions = [];
$params = [];
$types = '';
if (!empty($_GET['carrera'])) { $conditions[] = 'e.carrera = ?'; $params[] = $_GET['carrera']; $types .= 's'; }
if (!empty($_GET['semestre'])) { $conditions[] = 'e.semestre = ?'; $params[] = $_GET['semestre']; $types .= 's'; }

$sql = "SELECT e.id_estudiante, e.nombre, e.apellido, e.cedula, e.carrera, e.semestre, "
    . "CASE WHEN SUM(documento.estado='aprobado')>0 THEN 'aprobado' "
    . "WHEN SUM(documento.estado='pendiente')>0 THEN 'pendiente' "
    . "WHEN SUM(documento.estado='rechazado')>0 THEN 'rechazado' "
    . "ELSE 'sin_documentos' END AS estado_solicitud "
    . "FROM estudiante e LEFT JOIN documento ON documento.id_estudiante = e.id_estudiante";
if (!empty($conditions)) { $sql .= ' WHERE ' . implode(' AND ', $conditions); }
$sql .= ' GROUP BY e.id_estudiante';
if (!empty($_GET['estado']) && $_GET['estado'] !== 'todos') { $sql .= ' HAVING estado_solicitud = ?'; $params[] = $_GET['estado']; $types .= 's'; }

$students = [];
if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $bind_names = [];
        $bind_names[] = $types;
        foreach ($params as $p) { $bind_names[] = $p; }

        // Crear referencias (requisito para bind_param con call_user_func_array)
        $refs = [];
        foreach ($bind_names as $key => $value) { $refs[$key] = & $bind_names[$key]; }
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $students[] = $row; }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Exportar estudiantes</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="fondo">
    <div class="contenedor">
        <header style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
            <img src="../assets/img/icono.png" alt="Logo" style="width:48px;height:48px;border-radius:8px;">
            <div>
                <h2 style="margin:0;">Exportar estudiantes</h2>
                <p style="margin:2px 0 0;color:var(--muted);">Filtra y exporta la lista a PDF</p>
            </div>
        </header>

        <section class="card" style="padding:14px;margin-bottom:12px;">
            <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <label>Semestre:
                    <select name="semestre">
                        <option value="">Todos</option>
                        <?php foreach ($semestres as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php if(isset($_GET['semestre']) && $_GET['semestre']==$s) echo 'selected'; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>Carrera:
                    <select name="carrera">
                        <option value="">Todas</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>" <?php if(isset($_GET['carrera']) && $_GET['carrera']==$c) echo 'selected'; ?>><?php echo htmlspecialchars($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>Estado solicitud:
                    <select name="estado">
                        <option value="todos">Todos</option>
                        <option value="aprobado" <?php if(isset($_GET['estado']) && $_GET['estado']=='aprobado') echo 'selected'; ?>>Aprobado</option>
                        <option value="pendiente" <?php if(isset($_GET['estado']) && $_GET['estado']=='pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="rechazado" <?php if(isset($_GET['estado']) && $_GET['estado']=='rechazado') echo 'selected'; ?>>Rechazado</option>
                        <option value="sin_documentos" <?php if(isset($_GET['estado']) && $_GET['estado']=='sin_documentos') echo 'selected'; ?>>Sin documentos</option>
                    </select>
                </label>

                <button class="btn-principal" type="submit">Filtrar</button>
                <a class="btn-secundario" href="admin_panel.php">Volver</a>
            </form>
        </section>

        <section id="exportArea" class="card" style="padding:14px;">
            <h3 style="margin-top:0;">Resultados (<?php echo count($students); ?>)</h3>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);">
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Carrera</th>
                        <th>Semestre</th>
                        <th>Estado solicitud</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $st): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($st['apellido'] . ' ' . $st['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($st['cedula']); ?></td>
                            <td><?php echo htmlspecialchars($st['carrera']); ?></td>
                            <td><?php echo htmlspecialchars($st['semestre']); ?></td>
                            <td><?php echo htmlspecialchars($st['estado_solicitud']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <div style="margin-top:12px;display:flex;gap:8px;">
            <button class="btn-principal" id="btnExport">Exportar PDF</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('btnExport').addEventListener('click', function(){
            const element = document.getElementById('exportArea');
            const opt = {
                margin:       10,
                filename:     'estudiantes_export.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>
