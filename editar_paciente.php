<?php
session_start();
require 'conexion.php';

// Seguridad: Solo los administradores entran aquí
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

// Verificar que recibimos el ID del paciente
if (!isset($_GET['id'])) {
    header("Location: pacientes.php");
    exit();
}

$id_paciente = (int)$_GET['id'];
$mensaje = '';

// 1. LÓGICA PARA ACTUALIZAR LOS DATOS (Cuando el usuario presiona "Guardar Cambios")
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['actualizar_paciente'])) {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $fecha_nac = $_POST['fecha_nacimiento'];
    $sangre = $_POST['tipo_sangre'];
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    
    // Consulta UPDATE en SQL para sobreescribir los datos
    $sql_update = "UPDATE pacientes SET 
                    nombre_completo = '$nombre', 
                    fecha_nacimiento = '$fecha_nac', 
                    tipo_sangre = '$sangre', 
                    telefono_emergencia = '$telefono' 
                   WHERE id_paciente = $id_paciente";
                   
    if ($conexion->query($sql_update) === TRUE) {
        // Alerta de éxito y redirección automática usando JavaScript
        echo "<script>alert('✅ Datos del paciente actualizados correctamente.'); window.location.href='pacientes.php';</script>";
        exit();
    } else {
        $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ Error al actualizar: " . $conexion->error . "</div>";
    }
}

// 2. OBTENER LOS DATOS ACTUALES DEL PACIENTE PARA LLENAR EL FORMULARIO
$sql_datos = "SELECT * FROM pacientes WHERE id_paciente = $id_paciente";
$resultado = $conexion->query($sql_datos);

if ($resultado->num_rows == 0) {
    echo "<script>alert('Paciente no encontrado.'); window.location.href='pacientes.php';</script>";
    exit();
}

$paciente = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Paciente - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --info-color: #3498db;}
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: var(--accent-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar-menu a:hover { background-color: var(--primary-color); border-left: 4px solid var(--accent-color); }
        
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-back { background-color: #34495e; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9rem;}
        
        .dashboard-content { padding: 30px; display: flex; justify-content: center; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; border-top: 5px solid var(--info-color); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: var(--primary-color); }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-size: 1rem; }
        
        .btn-submit { background-color: var(--info-color); color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; font-size: 1.05rem; cursor: pointer; width: 100%; transition: background 0.3s; }
        .btn-submit:hover { background-color: #2980b9; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Administración</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="pacientes.php">⬅️ Volver a Pacientes</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Actualización de Datos</strong></div>
            <a href="pacientes.php" class="btn-back">Cancelar</a>
        </header>

        <section class="dashboard-content">
            <div class="card">
                <h2 style="color: var(--primary-color); margin-top: 0; text-align: center;">✏️ Editar Paciente</h2>
                <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Folio #<?php echo str_pad($paciente['id_paciente'], 5, "0", STR_PAD_LEFT); ?></p>
                
                <?php echo $mensaje; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($paciente['nombre_completo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Tipo de Sangre</label>
                        <select name="tipo_sangre">
                            <option value="Desconocido" <?php if($paciente['tipo_sangre'] == 'Desconocido') echo 'selected'; ?>>Desconocido</option>
                            <option value="O+" <?php if($paciente['tipo_sangre'] == 'O+') echo 'selected'; ?>>O Positivo (O+)</option>
                            <option value="A+" <?php if($paciente['tipo_sangre'] == 'A+') echo 'selected'; ?>>A Positivo (A+)</option>
                            <option value="B+" <?php if($paciente['tipo_sangre'] == 'B+') echo 'selected'; ?>>B Positivo (B+)</option>
                            <option value="AB+" <?php if($paciente['tipo_sangre'] == 'AB+') echo 'selected'; ?>>AB Positivo (AB+)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Teléfono de Emergencia</label>
                        <input type="tel" name="telefono" value="<?php echo htmlspecialchars($paciente['telefono_emergencia']); ?>" required>
                    </div>
                    
                    <button type="submit" name="actualizar_paciente" class="btn-submit">💾 Guardar Cambios</button>
                </form>
            </div>
        </section>
    </main>

</body>
</html>