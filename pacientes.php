<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 1. REGISTRAR PACIENTE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_paciente'])) {
    $nombre = $_POST['nombre'];
    $fecha_nac = $_POST['fecha_nacimiento'];
    $sangre = $_POST['tipo_sangre'];
    $telefono = $_POST['telefono'];
    
    $sql_insert = "INSERT INTO pacientes (nombre_completo, fecha_nacimiento, tipo_sangre, telefono_emergencia) 
                   VALUES ('$nombre', '$fecha_nac', '$sangre', '$telefono')";
    if ($conexion->query($sql_insert) === TRUE) {
        $mensaje = "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>✅ Paciente registrado exitosamente.</div>";
    }
}

// 2. ELIMINAR PACIENTE (Con borrado en cascada para evitar el error)
if(isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    
    // 1. Primero borramos los expedientes y recetas del paciente
    $conexion->query("DELETE FROM expedientes WHERE id_paciente=$id_eliminar");
    // 2. Luego borramos sus citas agendadas
    $conexion->query("DELETE FROM citas WHERE id_paciente=$id_eliminar");
    // 3. Finalmente, ahora sí podemos borrar al paciente sin que MySQL marque error
    $conexion->query("DELETE FROM pacientes WHERE id_paciente=$id_eliminar");
    
    header("Location: pacientes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pacientes - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --danger-color: #e74c3c; --info-color: #3498db;}
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: var(--accent-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; transition: background 0.3s;}
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--primary-color); border-left: 4px solid var(--accent-color); }
        .btn-logout { background-color: var(--danger-color); color: white; padding: 15px; text-decoration: none; font-weight: bold; display: block;}
        
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .dashboard-content { padding: 30px; }
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: var(--primary-color); color: white; }
        
        /* Ajuste de botones para que se vean todos iguales y bonitos */
        .btn-accion { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; color: white; margin-right: 3px; display: inline-block;}
        .btn-carnet { background-color: #8e44ad; } /* Morado para el Carnet */
        .btn-historial { background-color: #f39c12; } /* Naranja para el Historial */
        .btn-edit { background-color: var(--info-color); } /* Azul para Editar */
        .btn-delete { background-color: var(--danger-color); } /* Rojo para Eliminar */
        
        .btn-accion:hover { opacity: 0.8; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Administración</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="panel_admin.php">📊 Panel Principal</a></li>
            <li><a href="citas.php">📅 Agenda de Citas</a></li>
            <li><a href="medicos.php">👨‍⚕️ Personal Médico</a></li>
            <li><a href="pacientes.php" class="active">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Farmacia</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Expedientes y Admisión</strong></div>
            <div class="user-info">Administrador: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3 style="color: var(--primary-color); margin-top: 0;">➕ Nuevo Paciente</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento">
                        </div>
                        <div class="form-group">
                            <label>Tipo de Sangre</label>
                            <select name="tipo_sangre">
                                <option value="Desconocido">Desconocido</option>
                                <option value="O+">O Positivo (O+)</option>
                                <option value="A+">A Positivo (A+)</option>
                                <option value="B+">B Positivo (B+)</option>
                                <option value="AB+">AB Positivo (AB+)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" required>
                        </div>
                        <button type="submit" name="registrar_paciente" class="btn-submit">Guardar Paciente</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 500px;">
                    <h3 style="color: var(--primary-color); margin-top: 0;">📋 Padrón de Pacientes</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Sangre</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $resultado = $conexion->query("SELECT * FROM pacientes ORDER BY id_paciente DESC");

                            if ($resultado->num_rows > 0) {
                                while($fila = $resultado->fetch_assoc()) {
                                    $edad = "N/D";
                                    if(!empty($fila['fecha_nacimiento']) && $fila['fecha_nacimiento'] != '0000-00-00') {
                                        $fecha_nac = new DateTime($fila['fecha_nacimiento']);
                                        $hoy = new DateTime();
                                        $edad = $hoy->diff($fecha_nac)->y . " años";
                                    }

                                    $sangre = empty($fila['tipo_sangre']) ? 'Desconocido' : $fila['tipo_sangre'];

                                    echo "<tr>";
                                    echo "<td><strong>#" . $fila['id_paciente'] . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($fila['nombre_completo']) . "</td>";
                                    echo "<td>" . $edad . "</td>";
                                    echo "<td><span style='background:#e74c3c; color:white; padding:2px 6px; border-radius:3px; font-size:0.8rem;'>" . htmlspecialchars($sangre) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($fila['telefono_emergencia']) . "</td>";
                                    
                                    // AQUÍ ESTÁN LOS 4 BOTONES INTEGRADOS
                                    echo "<td>";
                                    echo "<a href='carnet.php?id=".$fila['id_paciente']."' class='btn-accion btn-carnet' title='Imprimir Carnet Médico'>🪪</a>";
                                    echo "<a href='ver_expediente.php?id=".$fila['id_paciente']."' class='btn-accion btn-historial' title='Ver Historial Clínico'>📁</a>";
                                    echo "<a href='editar_paciente.php?id=".$fila['id_paciente']."' class='btn-accion btn-edit' title='Editar Paciente'>✏️</a>";
                                    echo "<a href='pacientes.php?eliminar=".$fila['id_paciente']."' onclick=\"return confirm('¿Seguro que deseas eliminar este paciente?');\" class='btn-accion btn-delete' title='Eliminar Paciente'>🗑️</a>";
                                    echo "</td>";
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center;'>No hay pacientes registrados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>