<?php
session_start();
header('Content-Type: application/json');
include_once __DIR__ . '/../config/conexion.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    $data = $_POST;
}

$ids = [];
if (!empty($data['ids']) && is_array($data['ids'])) {
    $ids = array_map('intval', $data['ids']);
}

if (!isset($_SESSION['vistas_docs']) || !is_array($_SESSION['vistas_docs'])) {
    $_SESSION['vistas_docs'] = [];
}

foreach ($ids as $i) {
    if (!in_array($i, $_SESSION['vistas_docs'])) {
        $_SESSION['vistas_docs'][] = $i;
    }
}

echo json_encode(['ok' => true, 'seen' => $_SESSION['vistas_docs']]);
exit;
?>