<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_estudiante = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'] ?? '';

// Estadísticas: total tipos, enviados (distintos por tipo), última subida
$total = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tipo_documento");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $r = $res->fetch_assoc();
    $total = (int) ($r['total'] ?? 0);
    $stmt->close();
}

$enviados = 0;
$stmt = $conn->prepare("SELECT COUNT(DISTINCT id_tipo_documento) AS enviados FROM documento WHERE id_estudiante = ?");
if ($stmt) {
    $stmt->bind_param('i', $id_estudiante);
    $stmt->execute();
    $res = $stmt->get_result();
    $r = $res->fetch_assoc();
    $enviados = (int) ($r['enviados'] ?? 0);
    $stmt->close();
}

$faltantes = max(0, $total - $enviados);

$ultima = '-';
$stmt = $conn->prepare("SELECT fecha_subida FROM documento WHERE id_estudiante = ? ORDER BY fecha_subida DESC LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $id_estudiante);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $ultima = date('d/m/Y H:i', strtotime($r['fecha_subida']));
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Estudiante</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
    <style>
        .panel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .panel-card {
            background: #fff;
            padding: 12px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .panel-card h4 {
            margin: 0 0 8px 0
        }

        .panel-card p {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600
        }
    </style>
</head>

<body class="fondo">
    <div class="contenedor animate-item stagger-1">
        <h2 class="animate-item stagger-2">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h2>

        <div class="panel-grid">
            <div class="panel-card animate-item stagger-2">
                <h4>Total requeridos</h4>
                <p><?php echo $total; ?></p>
            </div>
            <div class="panel-card animate-item stagger-2">
                <h4>Enviados</h4>
                <p><?php echo $enviados; ?></p>
            </div>
            <div class="panel-card animate-item stagger-2">
                <h4>Faltantes</h4>
                <p><?php echo $faltantes; ?></p>
            </div>
            <div class="panel-card animate-item stagger-2">
                <h4>Última subida</h4>
                <p><?php echo htmlspecialchars($ultima); ?></p>
            </div>
        </div>

        <div class="grid" style="gap:12px; margin-top:18px;">
            <a class="card animate-item stagger-3" href="Interfaz_estudiante.php">Resumen</a>
            <a class="card animate-item stagger-3" href="mis_envios.php">Mis envíos</a>
            <a class="card animate-item stagger-3" href="subir_documentos.php">Subir documento</a>
            <a class="card animate-item stagger-3" href="documentos.php">Documentos requeridos</a>
        </div>

        <div style="margin-top:18px;">
            <a class="btn-secundario btn-animated" href="../logout.php">Cerrar sesión</a>
        </div>
    </div>
    <script src="../assets/js/animations.js"></script>
</body>

</html>