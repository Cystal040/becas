<?php
$conexion = new mysqli("localhost", "root", "", "sistema_becas");

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
?>