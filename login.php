<?php
// LA MAGIA SUPREMA: ob_start() absorbe cualquier espacio fantasma o error invisible 
// antes de que rompa la pantalla. Debe ser la línea 2 exacta.
ob_start(); 
session_start();
require 'conexion.php'; 

// Si ya tiene sesión, lo mandamos a su panel correspondiente
if (isset($_SESSION['id_usuario'])) {
    $rol_actual = $_SESSION['rol'];
    if ($rol_actual == 'administrador') header("Location: panel_admin.php");
    else if ($rol_actual == 'medico') header("Location: panel_medico.php");
    else if ($rol_actual == 'cajero' || $rol_actual == 'recepcion') header("Location: panel_caja.php");
    else if ($rol_actual == 'laboratorista') header("Location: panel_lab.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conexion->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    $sql = "SELECT id_usuario, nombre, rol, password FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultado = $conexion->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        
        // Comparamos la contraseña (encriptada o en texto plano temporal)
        if (password_verify($password, $fila['password']) || $password == $fila['password']) {
            
            $rol_limpio = strtolower(trim($fila['rol']));
            $_SESSION['id_usuario'] = $fila['id_usuario'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['rol'] = $rol_limpio; 

            // Redirección segura usando PHP nativo
            if ($rol_limpio == 'administrador') header("Location: panel_admin.php");
            else if ($rol_limpio == 'medico') header("Location: panel_medico.php");
            else if ($rol_limpio == 'cajero' || $rol_limpio == 'recepcion') header("Location: panel_caja.php");
            else if ($rol_limpio == 'laboratorista') header("Location: panel_lab.php");
            else $error = "Error: El rol '$rol_limpio' no tiene un panel asignado.";
            
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El correo no está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f8f9fa; --text-color: #2c3e50; --error-color: #e74c3c; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--secondary-color); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; border-top: 5px solid var(--primary-color);}
        .login-container h2 { color: var(--primary-color); margin-bottom: 20px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-color); }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 1rem; }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 12px; border: none; border-radius: 5px; width: 100%; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #003666; }
        .btn-back { display: block; margin-top: 15px; color: var(--primary-color); text-decoration: none; font-size: 0.9rem; }
        .error-message { color: var(--error-color); margin-top: 15px; font-weight: bold; background-color: #f8d7da; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Portal Interno</h2>
        <p>Ingrese sus credenciales para continuar</p>
        <form method="POST" action="">
            <div class="input-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="usuario@hospitalsanfrancisco.com">
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>
            <button type="submit" class="btn-submit">Ingresar</button>
            <?php if(!empty($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <a href="index.php" class="btn-back">← Volver a la página principal</a>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>