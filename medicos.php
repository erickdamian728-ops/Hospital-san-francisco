<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 1. REGISTRAR NUEVO EMPLEADO (BLINDADO)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_personal'])) {
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $correo = $conexion->real_escape_string(trim($_POST['correo']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    // AQUÍ ESTÁ LA MAGIA: Atrapamos el rol, lo limpiamos y lo hacemos minúsculas
    $rol = isset($_POST['rol']) ? strtolower(trim($_POST['rol'])) : '';
    
    // EL SEGURO ANTI-FANTASMAS: Si el rol llega vacío, detenemos todo
    if (empty($rol)) {
        $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ Error: Debes seleccionar un puesto obligatorio para el empleado.</div>";
    } else {
        $especialidad = ($rol == 'medico') ? $conexion->real_escape_string(trim($_POST['especialidad'])) : 'N/A';
        
        $check = $conexion->query("SELECT id_usuario FROM usuarios WHERE email = '$correo'");
        
        if($check && $check->num_rows > 0) {
            $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ Error: Ese correo ya está registrado.</div>";
        } else {
            $sql_insert = "INSERT INTO usuarios (nombre, email, password, rol, especialidad) 
                           VALUES ('$nombre', '$correo', '$password', '$rol', '$especialidad')";
                           
            if ($conexion->query($sql_insert) === TRUE) {
                $mensaje = "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>✅ Personal registrado exitosamente con el puesto de: <b>" . strtoupper($rol) . "</b></div>";
            } else {
                $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ Error en la base de datos: " . $conexion->error . "</div>";
            }
        }
    }
}

// 2. ELIMINAR EMPLEADO
if(isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    if ($id_eliminar != $_SESSION['id_usuario']) {
        $conexion->query("DELETE FROM expedientes WHERE id_medico=$id_eliminar");
        $conexion->query("DELETE FROM citas WHERE id_medico=$id_eliminar");
        $conexion->query("DELETE FROM usuarios WHERE id_usuario=$id_eliminar");
        header("Location: medicos.php");
        exit();
    } else {
        $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ No puedes eliminar tu propia cuenta.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Personal - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --danger-color: #e74c3c; --info-color: #3498db; --warning-color: #f39c12; --purple-color: #9b59b6;}
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; overflow-y: auto;}
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: var(--accent-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; transition: background 0.3s;}
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--primary-color); border-left: 4px solid var(--accent-color); }
        .btn-logout { background-color: var(--danger-color); color: white; padding: 15px; text-decoration: none; font-weight: bold; display: block; text-align: center;}
        
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .dashboard-content { padding: 30px; }
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%;}
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: var(--primary-color); color: white; }
        
        .btn-accion { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; color: white; margin-right: 3px; display: inline-block;}
        .btn-delete { background-color: var(--danger-color); }
        .btn-delete:hover { opacity: 0.8; }
        
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; font-size: 0.8rem; text-transform: capitalize; display: inline-block; min-width: 80px; text-align: center;}
        .badge-admin { background-color: var(--danger-color); }
        .badge-medico { background-color: var(--info-color); }
        .badge-cajero { background-color: var(--warning-color); }
        .badge-recepcion { background-color: #f1c40f; color: #333;}
        .badge-lab { background-color: var(--purple-color); }
        .badge-desconocido { background-color: #95a5a6; } 
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
            <li><a href="medicos.php" class="active">👨‍💼 Gestión de Personal</a></li>
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Inventario Farmacia</a></li>
            <li><a href="panel_caja.php">💰 Caja y Cobros</a></li>
            <li><a href="panel_lab.php">🔬 Laboratorio</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Gestión de Recursos Humanos</strong></div>
            <div class="user-info">Administrador: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px; border-top: 4px solid var(--primary-color);">
                    <h3 style="color: var(--primary-color); margin-top: 0;">➕ Alta de Personal</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" placeholder="Ej. Dr. Juan Pérez / Ana López" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Puesto / Rol en el Sistema</label>
                            <select name="rol" id="selector_rol" onchange="mostrarEspecialidad()" required>
                                <option value="" disabled selected>Seleccione el puesto...</option>
                                <option value="medico">👨‍⚕️ Médico / Doctor</option>
                                <option value="cajero">💰 Cajero</option>
                                <option value="recepcion">📝 Recepción</option>
                                <option value="laboratorista">🔬 Laboratorista / Técnico</option>
                                <option value="administrador">⚙️ Administrador General</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Correo Electrónico (Para Iniciar Sesión)</label>
                            <input type="email" name="correo" placeholder="usuario@hospital.com" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña Temporal</label>
                            <input type="password" name="password" required>
                        </div>
                        
                        <div class="form-group" id="div_especialidad" style="display: none;">
                            <label>Especialidad (Solo para Médicos)</label>
                            <select name="especialidad">
                                <option value="Medicina General">Medicina General</option>
                                <option value="Pediatría">Pediatría</option>
                                <option value="Traumatología">Traumatología</option>
                                <option value="Ginecología">Ginecología</option>
                                <option value="Cardiología">Cardiología</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="registrar_personal" class="btn-submit">Registrar Empleado</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 500px; border-top: 4px solid var(--info-color);">
                    <h3 style="color: var(--primary-color); margin-top: 0;">📋 Plantilla de Empleados Activa</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Puesto</th>
                                <th>Especialidad</th>
                                <th>Correo (Acceso)</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $resultado = $conexion->query("SELECT * FROM usuarios ORDER BY rol ASC, id_usuario DESC");

                            if ($resultado && $resultado->num_rows > 0) {
                                while($fila = $resultado->fetch_assoc()) {
                                    
                                    $rol_badge = "";
                                    $rol_bd = strtolower(trim($fila['rol'])); 
                                    
                                    if ($rol_bd == 'administrador') {
                                        $rol_badge = "<span class='badge badge-admin'>Administrador</span>";
                                    } else if ($rol_bd == 'medico') {
                                        $rol_badge = "<span class='badge badge-medico'>Médico</span>";
                                    } else if ($rol_bd == 'cajero') {
                                        $rol_badge = "<span class='badge badge-cajero'>Cajero</span>";
                                    } else if ($rol_bd == 'recepcion') {
                                        $rol_badge = "<span class='badge badge-recepcion'>Recepción</span>"; 
                                    } else if ($rol_bd == 'laboratorista') {
                                        $rol_badge = "<span class='badge badge-lab'>Laboratorio</span>";
                                    } else {
                                        $rol_badge = "<span class='badge badge-desconocido'>" . ($rol_bd == '' ? 'Sin Puesto' : htmlspecialchars($rol_bd)) . "</span>";
                                    }

                                    $especialidad = ($fila['especialidad'] == 'N/A' || $fila['especialidad'] == '') ? '<span style="color:#aaa;">-</span>' : htmlspecialchars($fila['especialidad']);

                                    echo "<tr>";
                                    echo "<td><strong>" . htmlspecialchars($fila['nombre']) . "</strong></td>";
                                    echo "<td>" . $rol_badge . "</td>";
                                    echo "<td>" . $especialidad . "</td>";
                                    
                                    $email_mostrar = isset($fila['email']) ? $fila['email'] : 'No definido';
                                    echo "<td>" . htmlspecialchars($email_mostrar) . "</td>";
                                    
                                    echo "<td>";
                                    if ($fila['id_usuario'] != $_SESSION['id_usuario']) {
                                        echo "<a href='medicos.php?eliminar=".$fila['id_usuario']."' onclick=\"return confirm('¿Seguro que deseas eliminar a este empleado?');\" class='btn-accion btn-delete'>🗑️ Despedir</a>";
                                    } else {
                                        echo "<span style='color: #7f8c8d; font-size: 0.8rem;'>Tú (Actual)</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center;'>No hay personal registrado en el sistema.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
        function mostrarEspecialidad() {
            var selector = document.getElementById("selector_rol");
            var divEspecialidad = document.getElementById("div_especialidad");
            
            if (selector.value === "medico") {
                divEspecialidad.style.display = "block";
            } else {
                divEspecialidad.style.display = "none";
            }
        }
    </script>
</body>
</html>