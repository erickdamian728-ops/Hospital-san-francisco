<?php
require 'conexion.php';

// Limpiamos errores anteriores por si acaso
$conexion->query("DELETE FROM usuarios WHERE email IN ('admin@hospital.com', 'caja@hospital.com', 'lab@hospital.com')");

$password = password_hash("123", PASSWORD_DEFAULT); // Todos tendrán la contraseña "123"

$sql = "INSERT INTO usuarios (nombre, email, password, rol, especialidad) VALUES 
('Jefe Admin', 'admin@hospital.com', '$password', 'administrador', 'N/A'),
('Cajero Central', 'caja@hospital.com', '$password', 'cajero', 'N/A'),
('Técnico Lab', 'lab@hospital.com', '$password', 'laboratorista', 'N/A')";

if ($conexion->query($sql) === TRUE) {
    echo "<h1>¡Cuentas de prueba creadas con éxito!</h1>";
    echo "<p>Cuentas listas para usar:</p>";
    echo "<ul>
            <li>Admin: <b>admin@hospital.com</b> (Clave: 123)</li>
            <li>Caja: <b>caja@hospital.com</b> (Clave: 123)</li>
            <li>Laboratorio: <b>lab@hospital.com</b> (Clave: 123)</li>
          </ul>";
    echo "<a href='login.php'>Ir al Login</a>";
} else {
    echo "Error: " . $conexion->error;
}
?>