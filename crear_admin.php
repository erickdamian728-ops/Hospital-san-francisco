<?php
require 'conexion.php';

// 1. Encriptamos la contraseña "admin123" para que el login la acepte
$password_segura = password_hash("admin123", PASSWORD_DEFAULT);

// 2. Insertamos al Administrador en la base de datos
// (Nota: Si tu columna se llama 'correo' en lugar de 'email', cámbialo en la línea de abajo)
$sql = "INSERT INTO usuarios (nombre, email, password, rol, especialidad) 
        VALUES ('Administrador Maestro', 'admin@hospital.com', '$password_segura', 'administrador', 'N/A')";

if ($conexion->query($sql) === TRUE) {
    echo "<h1 style='color: #27ae60;'>¡Administrador creado con éxito! 🎉</h1>";
    echo "<h3>Tus datos para entrar son:</h3>";
    echo "<p><b>Correo:</b> admin@hospital.com</p>";
    echo "<p><b>Contraseña:</b> admin123</p>";
    echo "<a href='login.php' style='padding: 10px; background: #004e92; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login para Entrar</a>";
} else {
    echo "Error (Tal vez ya habías creado uno antes): " . $conexion->error;
}
?>