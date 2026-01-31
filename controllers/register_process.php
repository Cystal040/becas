<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

// Solo aceptar POST desde el formulario
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['registrar'])) {
    header('Location: ../views/registro.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$cedula = trim($_POST['cedula'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password_plain = $_POST['password'] ?? '';
// Campos opcionales nuevos
$carrera = trim($_POST['carrera'] ?? '');
$seccion = trim($_POST['seccion'] ?? '');
$semestre = isset($_POST['semestre']) ? (int)$_POST['semestre'] : null;

if ($nombre === '' || $apellido === '' || $cedula === '' || $correo === '' || $password_plain === '') {
    header('Location: ../views/registro.php?error=empty');
    exit;
}

$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// Inserción segura con prepared statement
// Comprobar duplicados por correo o cédula
$check = $conn->prepare("SELECT correo, cedula FROM estudiante WHERE correo = ? OR cedula = ? LIMIT 1");
if ($check) {
    $check->bind_param('ss', $correo, $cedula);
    $check->execute();
    $res_check = $check->get_result();
    if ($res_check && $res_check->num_rows > 0) {
        $existing = $res_check->fetch_assoc();
        if (!empty($existing['correo']) && $existing['correo'] === $correo) {
            $check->close();
            header('Location: ../views/registro.php?error=correo_exists');
            exit;
        }
        if (!empty($existing['cedula']) && $existing['cedula'] === $cedula) {
            $check->close();
            header('Location: ../views/registro.php?error=cedula_exists');
            exit;
        }
        $check->close();
        header('Location: ../views/registro.php?error=exists');
        exit;
    }
    $check->close();
} else {
    error_log('Prepare failed (check duplicates): ' . $conn->error);
    header('Location: ../views/registro.php?error=db');
    exit;
}

// Construir INSERT dinámico si la tabla tiene columnas adicionales
$hasCarrera = false; $hasSeccion = false; $hasSemestre = false;
$q = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'estudiante' AND COLUMN_NAME IN ('carrera','seccion','semestre')");
if ($q) {
    $q->execute();
    $resCols = $q->get_result();
    while ($r = $resCols->fetch_assoc()){
        if ($r['COLUMN_NAME'] === 'carrera') $hasCarrera = true;
        if ($r['COLUMN_NAME'] === 'seccion') $hasSeccion = true;
        if ($r['COLUMN_NAME'] === 'semestre') $hasSemestre = true;
    }
    $q->close();
}

$baseCols = ['nombre','apellido','cedula','correo','password'];
$baseTypes = 'sssss';
$baseVals = [$nombre,$apellido,$cedula,$correo,$password_hashed];

// Añadir opcionales si existen en la tabla
if ($hasCarrera) { $baseCols[] = 'carrera'; $baseTypes .= 's'; $baseVals[] = $carrera; }
if ($hasSeccion) { $baseCols[] = 'seccion'; $baseTypes .= 's'; $baseVals[] = $seccion; }
if ($hasSemestre) { $baseCols[] = 'semestre'; $baseTypes .= 'i'; $baseVals[] = $semestre; }

// Construir consulta preparada dinámica
$colList = implode(', ', $baseCols);
$placeholders = implode(', ', array_fill(0, count($baseCols), '?'));
$sql = "INSERT INTO estudiante ($colList) VALUES ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed (register): ' . $conn->error);
    header('Location: ../views/registro.php?error=db');
    exit;
}

// Bind dinámico
$types = $baseTypes;
$bind_names[] = $types;
for ($i=0;$i<count($baseVals);$i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $baseVals[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array(array($stmt, 'bind_param'), $bind_names);
if ($stmt->execute()) {
    $stmt->close();
    // Set flash message and redirect to login
    $_SESSION['flash_success'] = 'Registro completado. Ahora puedes iniciar sesión.';
    header('Location: ../views/login.php');
    exit;
} else {
    error_log('Execute failed (register): ' . $stmt->error);
    $stmt->close();
    header('Location: ../views/registro.php?error=exists');
    exit;
}
?>