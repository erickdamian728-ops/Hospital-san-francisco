<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "hospital_san_francisco";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}
