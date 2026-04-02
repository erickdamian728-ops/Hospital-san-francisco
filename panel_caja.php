<?php
session_start();
require 'conexion.php';

// CORRECCIÓN: Ahora deja entrar al Cajero, a la Recepción y al Administrador
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] != 'administrador' && $_SESSION['rol'] != 'cajero' && $_SESSION['rol'] != 'recepcion')) {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 1. REGISTRAR UN NUEVO COBRO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_pago'])) {
    $id_paciente = (int)$_POST['id_paciente'];
    $concepto = $conexion->real_escape_string($_POST['concepto']);
    $monto = (float)$_POST['monto'];
    $metodo = $conexion->real_escape_string($_POST['metodo_pago']);
    
    $sql_insert = "INSERT INTO pagos (id_paciente, concepto, monto, metodo_pago) 
                   VALUES ($id_paciente, '$concepto', $monto, '$metodo')";
                   
    if ($conexion->query($sql_insert) === TRUE) {
        $id_nuevo_pago = $conexion->insert_id;
        // Lanzamos una alerta y abrimos el recibo en una pestaña nueva para imprimir
        echo "<script>
                alert('✅ Pago registrado correctamente. Generando recibo...');
                window.open('recibo.php?id=$id_nuevo_pago', '_blank');
                window.location.href='panel_caja.php';
              </script>";
        exit();
    } else {
        $mensaje = "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>❌ Error: " . $conexion->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Caja y Cobros - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --danger-color: #e74c3c; --info-color: #3498db;}
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
        
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid #f39c12; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: #f39c12; color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1.05rem;}
        .btn-submit:hover { background-color: #e67e22; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #2c3e50; color: white; }
        
        .btn-print { background-color: #34495e; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: bold;}
        .btn-print:hover { background-color: #2c3e50; }
        .monto { color: #27ae60; font-weight: bold; font-size: 1.1rem; }
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
            <?php endif; ?>
            <li><a href="citas.php">📅 Agenda de Citas</a></li>
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php">💊 Inventario Farmacia</a></li>
            <li><a href="panel_caja.php" class="active">💰 Caja y Cobros</a></li>
            <li><a href="panel_lab.php">🔬 Laboratorio</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Módulo de Facturación y Pagos</strong></div>
            <div class="user-info">Cajero/Admin: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3 style="color: #f39c12; margin-top: 0;">💵 Generar Orden de Pago</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Seleccionar Paciente</label>
                            <select name="id_paciente" required>
                                <option value="">Buscar paciente...</option>
                                <?php
                                $res_pacientes = $conexion->query("SELECT id_paciente, nombre_completo FROM pacientes ORDER BY nombre_completo ASC");
                                if($res_pacientes) {
                                    while($p = $res_pacientes->fetch_assoc()) {
                                        echo "<option value='".$p['id_paciente']."'>".$p['nombre_completo']." (ID: ".$p['id_paciente'].")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Concepto del Cobro</label>
                            <input type="text" name="concepto" placeholder="Ej. Consulta General Dra. María / Medicamentos" required>
                        </div>
                        <div class="form-group">
                            <label>Monto a Cobrar ($ MXN)</label>
                            <input type="number" step="0.01" name="monto" placeholder="Ej. 500.00" required>
                        </div>
                        <div class="form-group">
                            <label>Método de Pago</label>
                            <select name="metodo_pago" required>
                                <option value="Efectivo">💵 Efectivo</option>
                                <option value="Tarjeta de Crédito/Débito">💳 Tarjeta (Terminal)</option>
                                <option value="Transferencia">📱 Transferencia SPEI</option>
                            </select>
                        </div>
                        <button type="submit" name="registrar_pago" class="btn-submit">Registrar Pago e Imprimir</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 500px;">
                    <h3 style="color: #f39c12; margin-top: 0;">🧾 Historial de Ingresos</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_pagos = "SELECT p.*, pac.nombre_completo 
                                          FROM pagos p 
                                          JOIN pacientes pac ON p.id_paciente = pac.id_paciente 
                                          ORDER BY p.id_pago DESC LIMIT 50";
                            $res_pagos = $conexion->query($sql_pagos);

                            if ($res_pagos && $res_pagos->num_rows > 0) {
                                while($fila = $res_pagos->fetch_assoc()) {
                                    $fecha = date("d/m/Y H:i", strtotime($fila['fecha_pago']));
                                    echo "<tr>";
                                    echo "<td>#" . str_pad($fila['id_pago'], 5, "0", STR_PAD_LEFT) . "</td>";
                                    echo "<td>" . $fecha . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['nombre_completo']) . "</td>";
                                    echo "<td>" . htmlspecialchars($fila['concepto']) . "</td>";
                                    echo "<td class='monto'>$" . number_format($fila['monto'], 2) . "</td>";
                                    echo "<td><a href='recibo.php?id=".$fila['id_pago']."' target='_blank' class='btn-print'>🖨️ Recibo</a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align: center; color: #7f8c8d;'>No hay pagos registrados aún.</td></tr>";
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