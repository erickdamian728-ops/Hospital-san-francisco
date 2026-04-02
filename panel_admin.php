<?php
session_start();
require 'conexion.php';

// Seguridad: Solo el administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

// --- CONSULTAS PARA LAS ESTADÍSTICAS ---
// 1. Total de Pacientes
$res_pacientes = $conexion->query("SELECT COUNT(*) as total FROM pacientes");
$total_pacientes = $res_pacientes->fetch_assoc()['total'];

// 2. Total de Médicos
$res_medicos = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='medico'");
$total_medicos = $res_medicos->fetch_assoc()['total'];

// 3. Ingresos Totales de Caja
$res_ingresos = $conexion->query("SELECT SUM(monto) as total FROM pagos");
$total_ingresos = $res_ingresos->fetch_assoc()['total'];
if ($total_ingresos == null) $total_ingresos = 0;

// 4. Citas Pendientes
$res_citas = $conexion->query("SELECT COUNT(*) as total FROM citas WHERE estado='pendiente'");
$citas_pendientes = $res_citas->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --accent-color: #27ae60; --danger-color: #e74c3c; --info-color: #3498db; --warning-color: #f39c12; --purple-color: #9b59b6;}
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
        
        /* Estilos de las Tarjetas de Estadísticas */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px; transition: transform 0.3s;}
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; color: white; }
        .stat-info h3 { margin: 0; font-size: 24px; color: #333; }
        .stat-info p { margin: 5px 0 0 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;}

        /* Colores de iconos */
        .bg-blue { background-color: var(--info-color); }
        .bg-green { background-color: var(--accent-color); }
        .bg-orange { background-color: var(--warning-color); }
        .bg-purple { background-color: var(--purple-color); }

        .welcome-banner { background: linear-gradient(135deg, var(--primary-color), var(--info-color)); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .welcome-banner h1 { margin: 0 0 10px 0; }
        .welcome-banner p { margin: 0; font-size: 1.1rem; opacity: 0.9; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Administración</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="panel_admin.php" class="active">📊 Panel Principal</a></li>
            <li><a href="citas.php">📅 Agenda de Citas</a></li>
            <li><a href="medicos.php">👨‍💼 Gestión de Personal</a></li>
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Inventario Farmacia</a></li>
            <li><a href="panel_caja.php">💰 Caja y Cobros</a></li>
            <li><a href="panel_lab.php">🔬 Laboratorio</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Centro de Control (Dashboard)</strong></div>
            <div class="user-info">Administrador: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            
            <div class="welcome-banner">
                <h1>¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>! 👋</h1>
                <p>Bienvenido al sistema de gestión integral del Hospital San Francisco. Aquí tienes un resumen del estado actual de tu clínica.</p>
            </div>

            <div class="stats-grid">
                
                <div class="stat-card">
                    <div class="stat-icon bg-blue">🤒</div>
                    <div class="stat-info">
                        <h3><?php echo $total_pacientes; ?></h3>
                        <p>Pacientes Registrados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-green">💰</div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($total_ingresos, 2); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-purple">👨‍⚕️</div>
                    <div class="stat-info">
                        <h3><?php echo $total_medicos; ?></h3>
                        <p>Médicos Activos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-orange">📅</div>
                    <div class="stat-info">
                        <h3><?php echo $citas_pendientes; ?></h3>
                        <p>Citas Pendientes</p>
                    </div>
                </div>

            </div>

        </section>
    </main>
</body>
</html>