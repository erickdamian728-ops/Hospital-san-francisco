<?php
require 'conexion.php';

echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";

// 1. DESTRUIMOS EL CANDADO ENUM: Convertimos la columna a VARCHAR (texto libre)
$sql_alter = "ALTER TABLE usuarios MODIFY COLUMN rol VARCHAR(50) NOT NULL DEFAULT ''";
if ($conexion->query($sql_alter) === TRUE) {
    echo "<h2 style='color: blue;'>🔓 Candado de la base de datos roto con éxito.</h2>";
} else {
    echo "<h2 style='color: orange;'>Nota: La columna ya era libre o hubo un detalle: " . $conexion->error . "</h2>";
}

// 2. CURAMOS A TUS USUARIOS AFECTADOS
$conexion->query("UPDATE usuarios SET rol = 'laboratorista' WHERE email = 'erickarellano980@gmail.com' OR nombre LIKE '%Natividad%'");
$conexion->query("UPDATE usuarios SET rol = 'recepcion' WHERE email = 'greiz_smallgirl1241@gmail.com' OR nombre LIKE '%GRISELDA%'");

echo "<h1 style='color: #27ae60;'>✅ ¡Usuarios actualizados a la fuerza!</h1>";
echo "<h3>Natividad ya es Laboratorista. Griselda ya es Recepción.</h3>";
echo "<p>Ya puedes cerrar esta pestaña e intentar iniciar sesión de nuevo.</p>";
echo "</div>";
?>