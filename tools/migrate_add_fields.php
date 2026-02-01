<?php
// Script de migración: backup básico y aplicar cambios a la BD
chdir(__DIR__ . '/..');
include_once __DIR__ . '/../config/conexion.php';

if (!isset($conn)) {
    echo "No se pudo cargar la conexión desde config/conexion.php\n";
    exit(1);
}

$backupFile = __DIR__ . '/../respaldo_before_migrate.sql';
$fh = fopen($backupFile, 'w');
if (!$fh) {
    echo "No se pudo crear archivo de respaldo: $backupFile\n";
    exit(1);
}

$tables = ['estudiante', 'tipo_documento'];
foreach ($tables as $t) {
    // CREATE TABLE
    $r = $conn->query("SHOW CREATE TABLE `$t`");
    if ($r && $row = $r->fetch_assoc()) {
        fwrite($fh, "-- Tabla: $t\n");
        fwrite($fh, "DROP TABLE IF EXISTS `$t`;\n");
        fwrite($fh, $row['Create Table'] . ";\n\n");
    }
    // INSERT datos
    $res = $conn->query("SELECT * FROM `$t`");
    if ($res && $res->num_rows > 0) {
        while ($rrow = $res->fetch_assoc()) {
            $cols = array_keys($rrow);
            $vals = array_map(function($v) use ($conn){ return "'" . $conn->real_escape_string((string)$v) . "'"; }, array_values($rrow));
            fwrite($fh, "INSERT INTO `$t` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n");
        }
        fwrite($fh, "\n");
    }
}
fclose($fh);
echo "Respaldo generado en: $backupFile\n";

$errors = [];

// Añadir columnas si no existen
$colsToAdd = [
    'carrera' => "VARCHAR(100) NULL",
    'seccion' => "VARCHAR(50) NULL",
    'semestre' => "INT NULL"
];

foreach ($colsToAdd as $col => $def) {
    $q = $conn->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'estudiante' AND COLUMN_NAME = ? LIMIT 1");
    if (!$q) { $errors[] = "Error prepare check column $col: " . $conn->error; continue; }
    $q->bind_param('s', $col);
    $q->execute();
    $res = $q->get_result();
    if ($res && $res->num_rows === 0) {
        $alter = "ALTER TABLE estudiante ADD COLUMN `$col` $def";
        if ($conn->query($alter) === TRUE) {
            echo "Columna añadida: $col\n";
        } else {
            $errors[] = "Error al añadir columna $col: " . $conn->error;
        }
    } else {
        echo "Columna ya existe: $col\n";
    }
    $q->close();
}

// Insertar tipos de documento 6 y 7 si no existen
$docs = [6 => 'Carnet de la patria', 7 => 'Referencia bancaria'];
foreach ($docs as $id => $name) {
    $chk = $conn->prepare("SELECT id_tipo_documento FROM tipo_documento WHERE id_tipo_documento = ? OR nombre_documento = ? LIMIT 1");
    if (!$chk) { $errors[] = "Error prepare check tipo_documento: " . $conn->error; continue; }
    $chk->bind_param('is', $id, $name);
    $chk->execute();
    $res = $chk->get_result();
    if (!$res || $res->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO tipo_documento (id_tipo_documento, nombre_documento) VALUES (?, ?)");
        if ($ins) {
            $ins->bind_param('is', $id, $name);
            if ($ins->execute()) {
                echo "Insertado tipo_documento: $id - $name\n";
            } else {
                $errors[] = "Error insert tipo_documento $id: " . $ins->error;
            }
            $ins->close();
        } else {
            $errors[] = "Error prepare insert tipo_documento: " . $conn->error;
        }
    } else {
        echo "tipo_documento ya existe (id o nombre): $id - $name\n";
    }
    $chk->close();
}

// Añadir índice opcional semestre
$idxChk = $conn->query("SHOW INDEX FROM estudiante WHERE Key_name = 'idx_estudiante_semestre'");
if ($idxChk && $idxChk->num_rows === 0) {
    if ($conn->query("ALTER TABLE estudiante ADD INDEX idx_estudiante_semestre (semestre)") === TRUE) {
        echo "Índice idx_estudiante_semestre creado.\n";
    } else {
        $errors[] = "Error creando índice: " . $conn->error;
    }
} else {
    echo "Índice idx_estudiante_semestre ya existe o no pudo comprobarse.\n";
}

if (!empty($errors)) {
    echo "\nAlgunos errores ocurrieron:\n" . implode("\n", $errors) . "\n";
    exit(1);
}

echo "Migración completada correctamente.\n";
exit(0);

?>
