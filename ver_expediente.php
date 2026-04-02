<?php
session_start();
require 'conexion.php';

// Seguridad: Administradores o Médicos pueden ver esto
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: pacientes.php");
    exit();
}

$id_paciente = (int)$_GET['id'];

// 1. Obtener los datos generales del paciente
$sql_paciente = "SELECT * FROM pacientes WHERE id_paciente = $id_paciente";
$res_paciente = $conexion->query($sql_paciente);

if ($res_paciente->num_rows == 0) {
    echo "Paciente no encontrado.";
    exit();
}
$paciente = $res_paciente->fetch_assoc();

// Calcular edad
$edad = "N/D";
if(!empty($paciente['fecha_nacimiento']) && $paciente['fecha_nacimiento'] != '0000-00-00') {
    $fecha_nac = new DateTime($paciente['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y . " años";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente Clínico - <?php echo htmlspecialchars($paciente['nombre_completo']); ?></title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f4f7f6; --text-color: #333; --accent-color: #27ae60; --info-color: #3498db; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background-color: var(--secondary-color); color: var(--text-color); }
        
        .top-navbar { background-color: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .btn-back { background-color: #34495e; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.9rem;}
        
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        
        /* Tarjeta principal del paciente */
        .patient-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid var(--primary-color); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;}
        .patient-info h2 { margin: 0 0 10px 0; color: var(--primary-color); }
        .patient-info p { margin: 5px 0; color: #555; }
        
        /* Línea de tiempo de consultas */
        .historial-title { color: var(--primary-color); border-bottom: 2px solid var(--info-color); padding-bottom: 10px; margin-bottom: 20px; }
        
        .consulta-card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px; overflow: hidden; }
        .consulta-header { background-color: #eaf2f8; padding: 15px 20px; display: flex; justify-content: space-between; border-bottom: 1px solid #dcdde1; flex-wrap: wrap; gap: 10px;}
        .consulta-header .fecha { font-weight: bold; color: var(--primary-color); font-size: 1.1rem; }
        .consulta-header .medico { color: #555; font-size: 0.95rem; }
        
        .consulta-body { padding: 20px; }
        .vitales-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #eee;}
        .vital-item span { display: block; font-size: 0.8rem; color: #7f8c8d; text-transform: uppercase; }
        .vital-item strong { font-size: 1.1rem; color: var(--primary-color); }

        .clinico-section { margin-bottom: 15px; }
        .clinico-section h4 { margin: 0 0 5px 0; color: var(--info-color); font-size: 0.95rem; text-transform: uppercase; }
        .clinico-section p { margin: 0; background: #fdfdfd; padding: 10px; border-left: 3px solid #ccc; border-radius: 0 4px 4px 0; white-space: pre-wrap; font-size: 0.95rem; }

        .btn-print { display: inline-block; background: var(--accent-color); color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>

    <header class="top-navbar">
        <div class="nav-title"><strong>Expediente Clínico Electrónico</strong></div>
        <a href="javascript:history.back()" class="btn-back">⬅️ Volver a Pacientes</a>
    </header>

    <div class="container">
        <div class="patient-card">
            <div class="patient-info">
                <h2><?php echo htmlspecialchars($paciente['nombre_completo']); ?></h2>
                <p><strong>ID Expediente:</strong> #<?php echo str_pad($paciente['id_paciente'], 5, "0", STR_PAD_LEFT); ?></p>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($paciente['telefono_emergencia']); ?></p>
            </div>
            <div class="patient-info" style="text-align: right;">
                <p><strong>Edad:</strong> <?php echo $edad; ?></p>
                <p><strong>Tipo de Sangre:</strong> <span style="background:#e74c3c; color:white; padding:2px 8px; border-radius:10px; font-weight:bold;"><?php echo empty($paciente['tipo_sangre']) ? 'N/D' : $paciente['tipo_sangre']; ?></span></p>
            </div>
        </div>

        <h3 class="historial-title">📋 Historial de Consultas Médicas</h3>

        <?php
        // 2. Obtener TODAS las consultas de este paciente ordenadas de la más reciente a la más antigua
        $sql_historial = "SELECT e.*, u.nombre AS medico, u.especialidad 
                          FROM expedientes e
                          JOIN usuarios u ON e.id_medico = u.id_usuario
                          WHERE e.id_paciente = $id_paciente
                          ORDER BY e.fecha_consulta DESC";
        
        $res_historial = $conexion->query($sql_historial);

        if ($res_historial->num_rows > 0) {
            while($consulta = $res_historial->fetch_assoc()) {
                $fecha_formato = date("d/m/Y - h:i A", strtotime($consulta['fecha_consulta']));
                ?>
                
                <div class="consulta-card">
                    <div class="consulta-header">
                        <div class="fecha">📅 <?php echo $fecha_formato; ?></div>
                        <div class="medico">Atendido por: <strong>Dr(a). <?php echo htmlspecialchars($consulta['medico']); ?></strong> (<?php echo htmlspecialchars($consulta['especialidad']); ?>)</div>
                    </div>
                    
                    <div class="consulta-body">
                        <div class="vitales-grid">
                            <div class="vital-item"><span>Peso</span><strong><?php echo htmlspecialchars($consulta['peso_kg']); ?> kg</strong></div>
                            <div class="vital-item"><span>Presión (TA)</span><strong><?php echo htmlspecialchars($consulta['presion_arterial']); ?></strong></div>
                            <div class="vital-item"><span>Temp.</span><strong><?php echo htmlspecialchars($consulta['temperatura']); ?> °C</strong></div>
                        </div>

                        <div class="clinico-section">
                            <h4>Síntomas Reportados</h4>
                            <p><?php echo htmlspecialchars($consulta['sintomas']); ?></p>
                        </div>
                        
                        <div class="clinico-section">
                            <h4>Diagnóstico</h4>
                            <p style="border-left-color: var(--accent-color); font-weight: bold;"><?php echo htmlspecialchars($consulta['diagnostico']); ?></p>
                        </div>

                        <?php if(!empty(trim($consulta['receta_medicamentos']))): ?>
                        <div class="clinico-section">
                            <h4>💊 Receta Emitida</h4>
                            <p><?php echo htmlspecialchars($consulta['receta_medicamentos']); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty(trim($consulta['orden_estudios']))): ?>
                        <div class="clinico-section">
                            <h4>🔬 Orden de Estudios</h4>
                            <p><?php echo htmlspecialchars($consulta['orden_estudios']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="text-align: right; border-top: 1px solid #eee; padding-top: 10px; margin-top: 15px;">
                            <a href="imprimir.php?id=<?php echo $consulta['id_expediente']; ?>" target="_blank" class="btn-print">🖨️ Reimprimir Receta</a>
                        </div>
                    </div>
                </div>

                <?php
            }
        } else {
            // Si el paciente es nuevo y nunca ha entrado a consulta
            echo "<div style='background: white; padding: 30px; text-align: center; border-radius: 8px; color: #7f8c8d; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'>";
            echo "<h3>Sin historial médico</h3>";
            echo "<p>Este paciente aún no ha tenido consultas médicas registradas en el sistema.</p>";
            echo "</div>";
        }
        ?>

    </div>
</body>
</html>