<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['rol'], ['administrador', 'laboratorista'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_resultado'])) {
    // Aquí atrapamos los datos nuevos
    $id_paciente = $conexion->real_escape_string($_POST['id_paciente']);
    $medico = $conexion->real_escape_string($_POST['medico_solicitante']);
    $analisis = $conexion->real_escape_string($_POST['tipo_analisis']);
    $resultados = $conexion->real_escape_string($_POST['resultados']);
    $estado = $conexion->real_escape_string($_POST['estado']);
    $prioridad = $conexion->real_escape_string($_POST['prioridad']);
    
    // Por ahora simulamos que guarda con un mensaje bonito
    $mensaje = "<div style='background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>✅ Resultados guardados exitosamente. Estado: <b>" . strtoupper($estado) . "</b></div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Laboratorio - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --accent-color: #27ae60; --danger-color: #e74c3c; --purple-color: #9b59b6; --warning-color: #f39c12;}
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: var(--accent-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; transition: background 0.3s;}
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--primary-color); border-left: 4px solid var(--accent-color); }
        .btn-logout { background-color: var(--danger-color); color: white; padding: 15px; text-decoration: none; font-weight: bold; display: block; text-align: center;}
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .dashboard-content { padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid var(--purple-color); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;}
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; font-family: inherit;}
        .btn-submit { background-color: var(--purple-color); color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1.05rem;}
        .btn-submit:hover { background-color: #8e44ad; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #2c3e50; color: white; }
        .btn-print { background-color: var(--purple-color); color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;}
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 0.8rem; font-weight: bold;}
        .b-pendiente { background-color: var(--warning-color); }
        .b-proceso { background-color: var(--info-color); }
        .b-finalizado { background-color: var(--accent-color); }
        .b-urgente { background-color: var(--danger-color); }
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
            <li><a href="citas.php">📅 Agenda de Citas</a></li>
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Inventario Farmacia</a></li>
            <li><a href="panel_lab.php" class="active">🔬 Laboratorio</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Módulo de Análisis y Laboratorio Clínico</strong></div>
            <div class="user-info">Laboratorio/Admin: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>
        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 350px;">
                    <h3 style="color: var(--purple-color); margin-top: 0;">🔬 Capturar / Actualizar Muestra</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="grid-2">
                            <div class="form-group">
                                <label>ID / Nombre del Paciente</label>
                                <input type="text" name="id_paciente" required>
                            </div>
                            <div class="form-group">
                                <label>Médico Solicitante</label>
                                <input type="text" name="medico_solicitante" required>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label>Tipo de Análisis</label>
                                <select name="tipo_analisis" required>
                                    <option value="Biometría Hemática">🩸 Biometría Hemática</option>
                                    <option value="Química Sanguínea">🧪 Química Sanguínea (6 elementos)</option>
                                    <option value="Examen General de Orina">🧫 Examen General de Orina</option>
                                    <option value="Perfil Lipídico">📊 Perfil Lipídico</option>
                                    <option value="Cultivo">🦠 Cultivo Microbiológico</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Prioridad</label>
                                <select name="prioridad" required>
                                    <option value="Rutina">🟢 Rutina</option>
                                    <option value="Urgente">🔴 URGENTE (Emergencia)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Estado Actual de la Muestra</label>
                            <select name="estado" required>
                                <option value="Pendiente">Muestra Recibida (Pendiente)</option>
                                <option value="En Proceso">En Procesamiento (Equipos)</option>
                                <option value="Finalizado">Resultados Listos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Valores y Resultados / Observaciones Clínicas</label>
                            <textarea name="resultados" rows="4" placeholder="Ej. Glucosa: 95 mg/dL (Normal). Sin alteraciones observadas." required></textarea>
                        </div>

                        <div class="form-group">
                            <label>📎 Subir Archivo PDF / Imagen (Opcional)</label>
                            <input type="file" name="archivo_resultado" accept=".pdf, .jpg, .png">
                        </div>

                        <button type="submit" name="registrar_resultado" class="btn-submit">Actualizar Sistema LIS</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 500px;">
                    <h3 style="color: var(--purple-color); margin-top: 0;">📋 Seguimiento de Estudios</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Paciente</th>
                                <th>Análisis</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date("d/m/Y H:i"); ?></td>
                                <td>Griselda Damian</td>
                                <td>Química Sanguínea</td>
                                <td><span class="badge b-finalizado">Finalizado</span></td>
                                <td>Rutina</td>
                                <td><a href="imprimir_lab.php" target="_blank" class="btn-print">🖨️ Reporte</a></td>
                            </tr>
                            <tr>
                                <td><?php echo date("d/m/Y", strtotime("-1 hours")); ?></td>
                                <td>Paciente Emergencia</td>
                                <td>Biometría Hemática</td>
                                <td><span class="badge b-proceso" style="background-color: #3498db;">En Proceso</span></td>
                                <td><span class="badge b-urgente">URGENTE</span></td>
                                <td><span style="color:#7f8c8d; font-size: 0.8rem;">Procesando...</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>