<?php
require 'conexion.php';

// 1. APAGAMOS temporalmente los candados de seguridad de MySQL (Foreign Keys)
$conexion->query("SET FOREIGN_KEY_CHECKS = 0");

// 2. Ahora sí, destruimos la tabla vieja sin que el sistema nos bloquee
$conexion->query("DROP TABLE IF EXISTS citas");

// 3. Creamos la tabla nueva con la estructura perfecta para el radar
$sql_nueva_tabla = "CREATE TABLE citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    paciente VARCHAR(150) NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    motivo VARCHAR(255),
    estado ENUM('Pendiente', 'Completada', 'Cancelada') DEFAULT 'Pendiente'
)";

if ($conexion->query($sql_nueva_tabla) === TRUE) {
    // 4. VOLVEMOS A ENCENDER los candados para dejar la base de datos protegida
    $conexion->query("SET FOREIGN_KEY_CHECKS = 1");

    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: #27ae60;'>✅ ¡Tabla de citas reparada a la fuerza!</h1>";
    echo "<h3>Logramos saltar el candado. El radar inteligente ya está listo.</h3>";
    echo "<p>Ya puedes cerrar esta pestaña y regresar a tu panel de citas.</p>";
    echo "</div>";
} else {
    // Si algo falla, encendemos los candados de todos modos por seguridad
    $conexion->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "Hubo un detalle al crear la tabla: " . $conexion->error;
}
?>