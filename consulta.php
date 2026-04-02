<?php
session_start();
require 'conexion.php';

// Seguridad: Solo los médicos entran aquí
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'medico') {
    header("Location: login.php");
    exit();
}

// Verificar que traemos el ID de la cita en la URL
if (!isset($_GET['id'])) {
    header("Location: panel_medico.php");
    exit();
}

$id_cita = (int)$_GET['id'];
$id_medico_actual = $_SESSION['id_usuario'];

// 1. Obtener los datos del paciente
$sql_datos = "SELECT c.id_paciente, c.motivo_consulta, p.nombre_completo, p.fecha_nacimiento, p.tipo_sangre 
              FROM citas c 
              JOIN pacientes p ON c.id_paciente = p.id_paciente 
              WHERE c.id_cita = $id_cita AND c.id_medico = $id_medico_actual AND c.estado = 'pendiente'";

$resultado = $conexion->query($sql_datos);

if ($resultado->num_rows == 0) {
    echo "<script>alert('Esta cita ya fue atendida o no está disponible.'); window.location.href='panel_medico.php';</script>";
    exit();
}

$datos_cita = $resultado->fetch_assoc();
$id_paciente = $datos_cita['id_paciente'];

$edad = "N/D";
if(!empty($datos_cita['fecha_nacimiento']) && $datos_cita['fecha_nacimiento'] != '0000-00-00') {
    $fecha_nac = new DateTime($datos_cita['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y . " años";
}

// 2. LÓGICA CORREGIDA PARA GUARDAR Y MANDAR A IMPRIMIR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_consulta'])) {
    $peso = $_POST['peso'];
    $presion = $_POST['presion'];
    $temperatura = $_POST['temperatura'];
    $sintomas = $conexion->real_escape_string($_POST['sintomas']);
    $diagnostico = $conexion->real_escape_string($_POST['diagnostico']);
    $receta = $conexion->real_escape_string($_POST['receta']);
    $orden = $conexion->real_escape_string($_POST['orden']);

    $sql_expediente = "INSERT INTO expedientes (id_cita, id_paciente, id_medico, peso_kg, presion_arterial, temperatura, sintomas, diagnostico, receta_medicamentos, orden_estudios) 
                       VALUES ($id_cita, $id_paciente, $id_medico_actual, '$peso', '$presion', '$temperatura', '$sintomas', '$diagnostico', '$receta', '$orden')";
    
    if ($conexion->query($sql_expediente) === TRUE) {
        
        // TRUCO SEGURO: Buscamos el ID real que se acaba de crear
        $res_ultimo_id = $conexion->query("SELECT id_expediente FROM expedientes WHERE id_cita = $id_cita ORDER BY id_expediente DESC LIMIT 1");
        $fila_id = $res_ultimo_id->fetch_assoc();
        $id_nuevo_expediente = $fila_id['id_expediente'];

        // Actualizar la cita a "completada"
        $conexion->query("UPDATE citas SET estado='completada' WHERE id_cita=$id_cita");
        
        // Redirigir a imprimir
        echo "<script>alert('Expediente guardado exitosamente. Generando receta...'); window.location.href='imprimir.php?id=$id_nuevo_expediente';</script>";
        exit();
    } else {
        echo "<script>alert('Error al guardar: " . $conexion->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta Médica - Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --sidebar-color: #1a252f; --text-color: #333; --accent-color: #27ae60; --info-color: #3498db; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; background-color: var(--secondary-color);}
        
        .sidebar { width: 250px; background-color: var(--sidebar-color); color: white; display: flex; flex-direction: column; }
        .sidebar-header { padding: 20px; text-align: center; background-color: #0e171e; border-bottom: 1px solid #2c3e50; }
        .sidebar-header h3 { margin: 0; color: var(--info-color); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar-menu a { display: block; padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #2c3e50; }
        .sidebar-menu a:hover { background-color: var(--primary-color); border-left: 4px solid var(--info-color); }
        
        .main-content { flex-grow: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .dashboard-content { padding: 30px; max-width: 900px; margin: 0 auto; width: 100%; box-sizing: border-box;}
        
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid var(--info-color);}
        .patient-header { background-color: #eaf2f8; padding: 15px; border-radius: 5px; margin-bottom: 25px; border-left: 4px solid var(--info-color); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px;}
        .patient-header div { font-size: 0.95rem; }
        .patient-header strong { color: var(--primary-color); }

        .form-section { margin-bottom: 25px; }
        .form-section h4 { color: var(--primary-color); margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        .form-row { display: flex; gap: 20px; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 150px; margin-bottom: 15px; }
        .form-group-full { width: 100%; margin-bottom: 15px; }
        
        label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; color: #555;}
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        textarea { resize: vertical; min-height: 80px; }
        
        .btn-submit { background-color: var(--accent-color); color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.3s;}
        .btn-submit:hover { background-color: #218c4b; }
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-weight: bold;}
        .btn-cancel:hover { color: #e74c3c; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Portal Médico</h3>
            <p style="font-size: 0.8rem; margin-top: 5px; color: #bdc3c7;">San Francisco</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="panel_medico.php">⬅️ Volver a mi Agenda</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <div class="nav-title"><strong>Hoja de Evolución y Receta Médica</strong></div>
            <div class="user-info">Dr(a). <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></div>
        </header>

        <section class="dashboard-content">
            <div class="card">
                
                <div class="patient-header">
                    <div><strong>Paciente:</strong> <?php echo htmlspecialchars($datos_cita['nombre_completo']); ?></div>
                    <div><strong>Edad:</strong> <?php echo $edad; ?></div>
                    <div><strong>Tipo de Sangre:</strong> <span style="color:#e74c3c; font-weight:bold;"><?php echo empty($datos_cita['tipo_sangre']) ? 'Desconocido' : $datos_cita['tipo_sangre']; ?></span></div>
                    <div style="width: 100%;"><strong>Motivo de Consulta:</strong> <?php echo htmlspecialchars($datos_cita['motivo_consulta']); ?></div>
                </div>

                <form method="POST" action="">
                    
                    <div class="form-section">
                        <h4>🩺 Signos Vitales (Triage)</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Peso (kg)</label>
                                <input type="number" step="0.1" name="peso" placeholder="Ej. 75.5" required>
                            </div>
                            <div class="form-group">
                                <label>Presión Arterial</label>
                                <input type="text" name="presion" placeholder="Ej. 120/80" required>
                            </div>
                            <div class="form-group">
                                <label>Temperatura (°C)</label>
                                <input type="number" step="0.1" name="temperatura" placeholder="Ej. 36.5" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>📝 Evaluación Clínica</h4>
                        <div class="form-group-full">
                            <label>Síntomas / Padecimiento Actual</label>
                            <textarea name="sintomas" placeholder="Describa los síntomas que reporta el paciente..." required></textarea>
                        </div>
                        <div class="form-group-full">
                            <label>Diagnóstico</label>
                            <textarea name="diagnostico" placeholder="Escriba su diagnóstico médico..." required></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>💊 Receta y Gabinete</h4>
                        <div class="form-group-full">
                            <label>Receta de Medicamentos (Fármaco, dosis y frecuencia)</label>
                            <textarea name="receta" placeholder="Ej. Paracetamol 500mg, tomar 1 tableta cada 8 horas por 3 días." style="border-left: 4px solid var(--accent-color);"></textarea>
                        </div>
                        <div class="form-group-full">
                            <label>Orden de Estudios de Laboratorio (Opcional)</label>
                            <textarea name="orden" placeholder="Ej. Biometría Hemática completa, Radiografía de Tórax..."></textarea>
                        </div>
                    </div>

                    <button type="submit" name="guardar_consulta" class="btn-submit">💾 Guardar Expediente y Finalizar Cita</button>
                    <a href="panel_medico.php" class="btn-cancel">Cancelar y volver sin guardar</a>
                </form>

            </div>
        </section>
    </main>

</body>
</html>