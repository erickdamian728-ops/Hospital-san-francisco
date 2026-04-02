<?php
session_start();
require 'conexion.php';

// Seguridad: Si NO está logueado o NO es médico, lo pateamos al login
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'medico') {
    header("Location: login.php");
    exit();
}

// Guardamos el ID del doctor que acaba de iniciar sesión
$id_medico_actual = $_SESSION['id_usuario'];

// Nota: Eliminamos la lógica de "completar cita" que estaba aquí, 
// porque ahora de eso se encarga el archivo consulta.php al guardar el expediente.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal Médico - Hospital San Francisco</title>
    <style>
        /* Estilos base del sistema */
        :root { 
            --primary-color: #004e92; 
            --secondary-color: #f4f7f6; 
            --sidebar-color: #1a252f; 
            --text-color: #333; 
            --accent-color: #27ae60; 
            --danger-color: #e74c3c; 
        }
        
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        
        /* Menú Lateral especial para el Médico */
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: #3498db; } 
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--primary-color); border-left: 4px solid #3498db; }
        .btn-logout { background-color: var(--danger-color); color: white; padding: 15px; text-decoration: none; font-weight: bold; text-align: left; display: block;}
        .btn-logout:hover { background-color: #c0392b; }
        
        /* Contenido Principal */
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .dashboard-content { padding: 30px; }
        
        /* Tablas y Tarjetas */
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #3498db; color: white; } 
        
        /* Etiquetas de estado */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.85rem; font-weight: bold; color: white; }
        .badge-pendiente { background-color: #f39c12; }
        .badge-completada { background-color: var(--accent-color); }
        
        /* Botones de acción */
        .btn-check { background-color: var(--accent-color); padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; color: white; display: inline-block; font-weight: bold; transition: background 0.3s;}
        .btn-check:hover { background-color: #218c4b; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Portal Médico</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="panel_medico.php" class="active">📋 Mi Agenda Diaria</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Consultorio Virtual</strong></div>
            <div class="user-info">Dr(a). <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <div class="card">
                <h3 style="color: #3498db; margin-top: 0;">Pacientes Asignados</h3>
                <p>Aquí solo aparecen los pacientes que han solicitado consulta para tu especialidad.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Paciente</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_mis_citas = "SELECT c.id_cita, c.fecha_hora, c.motivo_consulta, c.estado, p.nombre_completo AS paciente 
                                          FROM citas c
                                          JOIN pacientes p ON c.id_paciente = p.id_paciente
                                          WHERE c.id_medico = $id_medico_actual
                                          ORDER BY c.fecha_hora ASC";
                        
                        $resultado = $conexion->query($sql_mis_citas);

                        if ($resultado->num_rows > 0) {
                            while($fila = $resultado->fetch_assoc()) {
                                $clase_badge = ($fila['estado'] == 'completada') ? 'badge-completada' : 'badge-pendiente';
                                $fecha_formateada = date("d/m/Y H:i", strtotime($fila['fecha_hora']));

                                echo "<tr>";
                                echo "<td><strong>" . $fecha_formateada . "</strong></td>";
                                echo "<td>" . htmlspecialchars($fila['paciente']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['motivo_consulta']) . "</td>";
                                echo "<td><span class='badge $clase_badge'>" . strtoupper($fila['estado']) . "</span></td>";
                                
                                echo "<td>";
                                // AQUI ESTÁ EL CAMBIO CLAVE: El botón ahora apunta a consulta.php
                                if($fila['estado'] == 'pendiente') {
                                    echo "<a href='consulta.php?id=".$fila['id_cita']."' class='btn-check'>Atender Paciente 🩺</a>";
                                } else {
                                    echo "<span style='color:#ccc; font-weight:bold;'>Finalizada</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align: center; color: #7f8c8d; padding: 20px;'>No tienes pacientes programados por el momento. ¡Tómate un café! ☕</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>