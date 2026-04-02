<?php
session_start();
require 'conexion.php';

// Seguridad
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit();
}

$mensaje = '';

// 1. AGREGAR NUEVO MEDICAMENTO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_medicamento'])) {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $presentacion = $conexion->real_escape_string($_POST['presentacion']);
    $stock = (int)$_POST['stock'];
    $stock_min = (int)$_POST['stock_minimo'];
    $caducidad = $_POST['fecha_caducidad'];
    
    $sql_insert = "INSERT INTO medicamentos (nombre_medicamento, presentacion, stock_actual, stock_minimo, fecha_caducidad) 
                   VALUES ('$nombre', '$presentacion', $stock, $stock_min, '$caducidad')";
                   
    if ($conexion->query($sql_insert) === TRUE) {
        $mensaje = "<div class='alerta exito'>✅ Medicamento agregado al inventario.</div>";
    }
}

// 2. SURTIR MEDICAMENTO (Resta 1 al stock actual)
if(isset($_GET['surtir'])) {
    $id_surtir = (int)$_GET['surtir'];
    // Solo resta si hay stock disponible
    $conexion->query("UPDATE medicamentos SET stock_actual = stock_actual - 1 WHERE id_medicamento=$id_surtir AND stock_actual > 0");
    header("Location: farmacia.php");
    exit();
}

// 3. ELIMINAR MEDICAMENTO
if(isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM medicamentos WHERE id_medicamento=$id_eliminar");
    header("Location: farmacia.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Farmacia - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --danger-color: #e74c3c; --info-color: #3498db; --warning-color: #f39c12;}
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        
        /* Menú Lateral */
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
        
        /* Tarjetas y Formularios */
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-top: 4px solid var(--primary-color);}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem;}
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background-color: var(--primary-color); color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; width: 100%;}
        
        /* Alertas */
        .alerta { padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .exito { background-color: #d4edda; color: #155724; }
        
        /* Tabla de Inventario */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.95rem;}
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #2c3e50; color: white; }
        
        /* Badges de Estado */
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; font-size: 0.8rem; }
        .badge-ok { background-color: var(--accent-color); }
        .badge-warning { background-color: var(--warning-color); }
        .badge-danger { background-color: var(--danger-color); }
        
        /* Botones de acción */
        .btn-accion { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; color: white; margin-right: 3px; display: inline-block;}
        .btn-surtir { background-color: var(--accent-color); }
        .btn-delete { background-color: var(--danger-color); }
        .btn-surtir:hover, .btn-delete:hover { opacity: 0.8; }
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
            <li><a href="pacientes.php">🤒 Pacientes</a></li>
            <li><a href="farmacia.php" class="active">💊 Farmacia</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Control de Inventario y Farmacia</strong></div>
            <div class="user-info">Administrador: <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <?php echo $mensaje; ?>
            
            <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3 style="color: var(--primary-color); margin-top: 0;">📦 Registrar Entrada</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Nombre del Fármaco</label>
                            <input type="text" name="nombre" placeholder="Ej. Paracetamol 500mg" required>
                        </div>
                        <div class="form-group">
                            <label>Presentación</label>
                            <input type="text" name="presentacion" placeholder="Ej. Caja con 20 tabletas" required>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Cantidad (Stock)</label>
                                <input type="number" name="stock" required>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Stock Mínimo</label>
                                <input type="number" name="stock_minimo" value="5" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Fecha de Caducidad</label>
                            <input type="date" name="fecha_caducidad" required>
                        </div>
                        <button type="submit" name="agregar_medicamento" class="btn-submit">Guardar en Almacén</button>
                    </form>
                </div>

                <div class="card" style="flex: 2; min-width: 600px;">
                    <h3 style="color: var(--primary-color); margin-top: 0;">💊 Inventario Actual</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Stock</th>
                                <th>Estado / Alerta</th>
                                <th>Caducidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $resultado = $conexion->query("SELECT * FROM medicamentos ORDER BY nombre_medicamento ASC");

                            $hoy = new DateTime(); // Fecha actual para comparar caducidades

                            if ($resultado->num_rows > 0) {
                                while($fila = $resultado->fetch_assoc()) {
                                    
                                    // LÓGICA 1: ALERTAS DE STOCK
                                    $stock = $fila['stock_actual'];
                                    $minimo = $fila['stock_minimo'];
                                    if($stock <= 0) {
                                        $alerta_stock = "<span class='badge badge-danger'>Agotado</span>";
                                    } elseif($stock <= $minimo) {
                                        $alerta_stock = "<span class='badge badge-warning'>Bajo Stock</span>";
                                    } else {
                                        $alerta_stock = "<span class='badge badge-ok'>Óptimo</span>";
                                    }

                                    // LÓGICA 2: ALERTAS DE CADUCIDAD
                                    $caducidad = new DateTime($fila['fecha_caducidad']);
                                    $diferencia_dias = $hoy->diff($caducidad)->format("%r%a"); // %r da el signo (negativo si ya pasó)
                                    
                                    $fecha_formateada = $caducidad->format('d/m/Y');

                                    if($diferencia_dias < 0) {
                                        $alerta_cad = "<span style='color: #e74c3c; font-weight: bold;'>¡CADUCADO!</span>";
                                    } elseif ($diferencia_dias <= 30) {
                                        $alerta_cad = "<span style='color: #f39c12; font-weight: bold;'>Vence pronto ($fecha_formateada)</span>";
                                    } else {
                                        $alerta_cad = "<span style='color: #555;'>$fecha_formateada</span>";
                                    }

                                    // IMPRIMIR FILA
                                    echo "<tr>";
                                    echo "<td><strong>" . htmlspecialchars($fila['nombre_medicamento']) . "</strong><br><small style='color:#777;'>" . htmlspecialchars($fila['presentacion']) . "</small></td>";
                                    
                                    // Colorear el número del stock si está crítico
                                    $color_numero = ($stock <= $minimo) ? "color: red; font-weight: bold;" : "";
                                    echo "<td style='$color_numero; font-size: 1.1rem;'>" . $stock . "</td>";
                                    
                                    echo "<td>" . $alerta_stock . "</td>";
                                    echo "<td>" . $alerta_cad . "</td>";
                                    
                                    echo "<td>";
                                    // Botón para surtir rápido (solo si hay stock y no está caducado)
                                    if($stock > 0 && $diferencia_dias >= 0) {
                                        echo "<a href='farmacia.php?surtir=".$fila['id_medicamento']."' class='btn-accion btn-surtir' title='Entregar a paciente (Resta 1)'>💊 Surtir</a>";
                                    }
                                    echo "<a href='farmacia.php?eliminar=".$fila['id_medicamento']."' onclick=\"return confirm('¿Seguro que deseas eliminar este medicamento del catálogo?');\" class='btn-accion btn-delete' title='Eliminar registro'>🗑️</a>";
                                    echo "</td>";
                                    
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center; padding: 20px; color: #7f8c8d;'>El inventario está vacío.</td></tr>";
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