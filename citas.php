<?php
session_start();
require 'conexion.php';

// BLINDAJE: Administradores, Recepción y Médicos pueden acceder
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['administrador', 'recepcion', 'medico'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$mensaje = '';

// 1. EL MOTOR INTELIGENTE: Registrar cita evitando choques
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agendar_cita'])) {
    $paciente = $conexion->real_escape_string(trim($_POST['paciente']));
    $fecha = $conexion->real_escape_string($_POST['fecha_cita']);
    $hora = $conexion->real_escape_string($_POST['hora_cita']);
    $motivo = $conexion->real_escape_string(trim($_POST['motivo']));
    
    // LA MAGIA: Verificamos si ese día y a esa hora exacta ya hay alguien programado
    $sql_choque = "SELECT id_cita FROM citas WHERE fecha_cita = '$fecha' AND hora_cita = '$hora' AND estado = 'Pendiente'";
    $resultado_choque = $conexion->query($sql_choque);
    
    // Nota: Si la tabla 'citas' no existe, esto evitará que la página se ponga en blanco
    if (!$resultado_choque && $conexion->errno == 1146) {
        // Si no existe la tabla, la creamos en automático (Magia extra de producción)
        $conexion->query("CREATE TABLE citas (
            id_cita INT AUTO_INCREMENT PRIMARY KEY,
            paciente VARCHAR(150) NOT NULL,
            fecha_cita DATE NOT NULL,
            hora_cita TIME NOT NULL,
            motivo VARCHAR(255),
            estado ENUM('Pendiente', 'Completada', 'Cancelada') DEFAULT 'Pendiente'
        )");
        $resultado_choque = $conexion->query($sql_choque); // Reintentamos
    }

    if ($resultado_choque && $resultado_choque->num_rows > 0) {
        // ¡Alerta! Horario ocupado
        $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #e74c3c;'>
                    ❌ <b>¡Horario no disponible!</b> Ya existe una cita programada para el <b>" . date('d/m/Y', strtotime($fecha)) . "</b> a las <b>" . date('H:i', strtotime($hora)) . "</b>. Por favor, elige otro horario.</div>";
    } else {
        // Horario libre, procedemos a guardar
        $sql_insert = "INSERT INTO citas (paciente, fecha_cita, hora_cita, motivo, estado) VALUES ('$paciente', '$fecha', '$hora', '$motivo', 'Pendiente')";
        if ($conexion->query($sql_insert) === TRUE) {
            $mensaje = "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #27ae60;'>
                        ✅ <b>Cita agendada con éxito.</b> El horario ha quedado bloqueado para este paciente.</div>";
        } else {
            $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>❌ Error: " . $conexion->error . "</div>";
        }
    }
}

// 2. ACTUALIZAR ESTADO (Completar o Cancelar)
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_cita = (int)$_GET['id'];
    $nueva_accion = $_GET['accion'] == 'completar' ? 'Completada' : 'Cancelada';
    $conexion->query("UPDATE citas SET estado = '$nueva_accion' WHERE id_cita = $id_cita");
    echo "<script>window.location.href='citas.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda Inteligente - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --accent-color: #27ae60; --danger-color: #e74c3c; --agenda-color: #e67e22; --info-color: #3498db;}
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
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid var(--agenda-color); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; color: #2c3e50;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-family: inherit;}
        .btn-submit { background-color: var(--agenda-color); color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1.05rem;}
        .btn-submit:hover { background-color: #d35400; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem; background: #fff;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #2c3e50; color: white; }
        
        .badge { padding: 5px 10px; border-radius: 20px; color: white; font-size: 0.8rem; font-weight: bold; text-align: center; display: inline-block; min-width: 80px;}
        .b-pendiente { background-color: var(--info-color); }
        .b-completada { background-color: var(--accent-color); }
        .b-cancelada { background-color: var(--danger-color); }
        
        .btn-accion { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; color: white; margin-right: 5px; display: inline-block;}
        .btn-check { background-color: var(--accent-color); }
        .btn-x { background-color: var(--danger-color); }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Administración</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <?php if($_SESSION['rol'] == 'administrador'): ?>
                <li><a href="panel_admin.php">📊 Panel Principal</a></li>
                <li><a href="medicos.php">👨‍💼 Gestión de Personal</a></li>
                <li><a href="panel_caja.php">💰 Caja y Cobros</a></li>
            <?php endif; ?>
            <li><a href="citas.php" class="active">📅 Agenda de Citas</a></li>
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Inventario Farmacia</a></li>
            <?php if(in_array($_SESSION['rol'], ['administrador', 'laboratorista'])): ?>
                <li><a href="panel_lab.php">🔬 Laboratorio</a></li>
            <?php endif; ?>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Módulo de Agendamiento Inteligente</strong></div>
            <div class="user-info">Usuario: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3 style="color: var(--agenda-color); margin-top: 0;">➕ Programar Nueva Cita</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nombre del Paciente</label>
                            <input type="text" name="paciente" required placeholder="Ej. Adela Damian Figueroa">
                        </div>
                        
                        <div class="grid-2">
                            <div class="form-group">
                                <label>Fecha de la Cita</label>
                                <input type="date" name="fecha_cita" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Hora (Intervalos de 30 min)</label>
                                <select name="hora_cita" required>
                                    <option value="" disabled selected>Seleccione hora...</option>
                                    <?php
                                    // Generador automático de horarios clínicos (De 8:00 AM a 6:00 PM)
                                    $inicio = strtotime('08:00');
                                    $fin = strtotime('18:00');
                                    while ($inicio <= $fin) {
                                        $hora_formato = date('H:i', $inicio);
                                        $hora_am_pm = date('h:i A', $inicio);
                                        echo "<option value='$hora_formato'>🕒 $hora_am_pm</option>";
                                        $inicio = strtotime('+30 minutes', $inicio);
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Motivo de Consulta / Especialidad</label>
                            <input type="text" name="motivo" placeholder="Ej. Chequeo general, Dolor de columna..." required>
                        </div>
                        
                        <button type="submit" name="agendar_cita" class="btn-submit">Bloquear Horario y Agendar</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 500px;">
                    <h3 style="color: var(--agenda-color); margin-top: 0;">📋 Próximas Citas Programadas</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mostramos primero las citas pendientes y ordenadas de más próximas a más lejanas
                            $sql_lista = "SELECT * FROM citas ORDER BY estado DESC, fecha_cita ASC, hora_cita ASC LIMIT 50";
                            $res_lista = $conexion->query($sql_lista);
                            
                            if ($res_lista && $res_lista->num_rows > 0) {
                                while($c = $res_lista->fetch_assoc()) {
                                    // Colores para el estado
                                    $clase_estado = 'b-pendiente';
                                    if ($c['estado'] == 'Completada') $clase_estado = 'b-completada';
                                    if ($c['estado'] == 'Cancelada') $clase_estado = 'b-cancelada';

                                    echo "<tr>";
                                    echo "<td><strong>" . date("d/m/Y", strtotime($c['fecha_cita'])) . "</strong></td>";
                                    echo "<td>" . date("h:i A", strtotime($c['hora_cita'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($c['paciente']) . "</td>";
                                    echo "<td><span class='badge $clase_estado'>" . $c['estado'] . "</span></td>";
                                    echo "<td>";
                                    // Solo mostramos botones de acción si la cita está pendiente
                                    if ($c['estado'] == 'Pendiente') {
                                        echo "<a href='citas.php?accion=completar&id=".$c['id_cita']."' class='btn-accion btn-check' title='Marcar como Atendido'>✔️</a>";
                                        echo "<a href='citas.php?accion=cancelar&id=".$c['id_cita']."' class='btn-accion btn-x' title='Cancelar Cita' onclick=\"return confirm('¿Seguro que deseas cancelar esta cita?');\">❌</a>";
                                    } else {
                                        echo "<span style='color:#7f8c8d; font-size:0.85rem;'>Cerrada</span>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>No hay citas programadas en el sistema.</td></tr>";
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