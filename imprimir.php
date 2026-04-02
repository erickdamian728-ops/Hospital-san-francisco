<?php
session_start();
require 'conexion.php';

// Seguridad
if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$id_expediente = (int)$_GET['id'];

// Obtener todos los datos juntando el expediente, el paciente y el médico
$sql = "SELECT e.*, p.nombre_completo AS paciente, p.fecha_nacimiento, p.tipo_sangre, 
               u.nombre AS medico, u.especialidad 
        FROM expedientes e
        JOIN pacientes p ON e.id_paciente = p.id_paciente
        JOIN usuarios u ON e.id_medico = u.id_usuario
        WHERE e.id_expediente = $id_expediente";

$resultado = $conexion->query($sql);

if ($resultado->num_rows == 0) {
    echo "Error: No se encontró el expediente.";
    exit();
}

$datos = $resultado->fetch_assoc();

// Calcular edad
$edad = "N/D";
if(!empty($datos['fecha_nacimiento']) && $datos['fecha_nacimiento'] != '0000-00-00') {
    $fecha_nac = new DateTime($datos['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y . " años";
}

$fecha_impresion = date("d/m/Y h:i A", strtotime($datos['fecha_consulta']));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - <?php echo htmlspecialchars($datos['paciente']); ?></title>
    <style>
        /* Estilos en pantalla y para impresora */
        body { font-family: 'Arial', sans-serif; background: #e9ecef; display: flex; justify-content: center; padding: 20px; color: #000; }
        
        /* La hoja blanca que simula el papel */
        .hoja { background: white; width: 21cm; min-height: 29.7cm; padding: 2cm; box-sizing: border-box; box-shadow: 0 0 10px rgba(0,0,0,0.2); position: relative; }
        
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #004e92; padding-bottom: 15px; margin-bottom: 20px; }
        .header-hospital h1 { margin: 0; color: #004e92; font-size: 1.8rem; }
        .header-hospital p { margin: 5px 0 0 0; font-size: 0.9rem; color: #555; }
        .header-medico { text-align: right; }
        .header-medico h2 { margin: 0; font-size: 1.2rem; }
        .header-medico p { margin: 5px 0 0 0; font-size: 0.9rem; font-weight: bold; color: #555; }

        .datos-paciente { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.95rem; border: 1px solid #ddd; }
        .datos-paciente table { width: 100%; }
        .datos-paciente td { padding: 5px; }

        .signos-vitales { display: flex; justify-content: space-between; font-size: 0.85rem; padding-bottom: 15px; border-bottom: 1px solid #eee; margin-bottom: 20px; font-weight: bold;}

        .contenido-clinico { margin-bottom: 30px; }
        .contenido-clinico h3 { color: #004e92; font-size: 1.1rem; margin-bottom: 10px; text-transform: uppercase; }
        .contenido-clinico p { line-height: 1.6; margin-top: 0; white-space: pre-wrap; font-size: 1rem; }
        
        .rx-logo { font-size: 2.5rem; font-family: serif; font-style: italic; font-weight: bold; color: #004e92; margin-bottom: 10px;}

        .firma { position: absolute; bottom: 3cm; right: 2cm; text-align: center; width: 250px; }
        .firma-linea { border-top: 1px solid #000; margin-top: 50px; padding-top: 10px; font-weight: bold; }

        .footer { position: absolute; bottom: 1cm; left: 2cm; right: 2cm; text-align: center; font-size: 0.8rem; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }

        /* Botones que NO se imprimen */
        .botones-accion { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; margin: 0 10px; text-decoration: none; display: inline-block;}
        .btn-print { background: #27ae60; color: white; }
        .btn-back { background: #34495e; color: white; }

        /* Reglas exclusivas para cuando la impresora toma el control */
        @media print {
            body { background: white; padding: 0; }
            .hoja { box-shadow: none; width: 100%; padding: 0; min-height: auto;}
            .botones-accion { display: none !important; } /* Ocultamos los botones en el papel */
        }
    </style>
</head>
<body>

    <div>
        <div class="botones-accion">
            <button onclick="window.print()" class="btn btn-print">🖨️ Imprimir Receta</button>
            <a href="panel_medico.php" class="btn btn-back">⬅️ Volver a mi Agenda</a>
        </div>

        <div class="hoja">
            
<div class="header">
                <div class="header-hospital" style="display: flex; align-items: center;">
                    <img src="https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=AZE4JEuDpwUQ7kNvwHrIf4p&_nc_oc=Adq0GhXZmZBpmqCrmpiDSZdF2JeBbReRkAt-Cj3Inl95D9GbBZcoWh1hoO0H4GXFEhU&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=4e4-0sW89pcWusH06XgM_Q&_nc_ss=7a3a8&oh=00_Afws8M2HEAHsbXXObLWY_GvMBPb2UF4h-gwDyzGJWrnFVA&oe=69D0923F" alt="Logo" style="width: 70px; height: 70px; border-radius: 8px; margin-right: 15px; object-fit: cover;">
                    <div>
                        <h1>Hospital San Francisco</h1>
                        <p> Calle Amado Nervo #1300.</p>
                        <p>Tel: (452) 123 4567 | Urgencias 24/7</p>
                    </div>
                </div>
            </div>

            <div class="datos-paciente">
                <table>
                    <tr>
                        <td><strong>Paciente:</strong> <?php echo htmlspecialchars($datos['paciente']); ?></td>
                        <td><strong>Edad:</strong> <?php echo $edad; ?></td>
                        <td><strong>Folio:</strong> #<?php echo str_pad($datos['id_expediente'], 5, "0", STR_PAD_LEFT); ?></td>
                    </tr>
                </table>
            </div>

            <div class="signos-vitales">
                <span>Peso: <?php echo htmlspecialchars($datos['peso_kg']); ?> kg</span>
                <span>TA: <?php echo htmlspecialchars($datos['presion_arterial']); ?></span>
                <span>Temp: <?php echo htmlspecialchars($datos['temperatura']); ?> °C</span>
                <span>Tipo de Sangre: <?php echo htmlspecialchars($datos['tipo_sangre']); ?></span>
            </div>

            <div class="contenido-clinico">
                <h3>Diagnóstico Médico</h3>
                <p><?php echo htmlspecialchars($datos['diagnostico']); ?></p>
            </div>

            <?php if(!empty(trim($datos['receta_medicamentos']))): ?>
            <div class="contenido-clinico" style="margin-top: 40px;">
                <div class="rx-logo">Rx</div>
                <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($datos['receta_medicamentos']); ?></p>
            </div>
            <?php endif; ?>

            <?php if(!empty(trim($datos['orden_estudios']))): ?>
            <div class="contenido-clinico" style="margin-top: 40px; padding-top: 20px; border-top: 1px dashed #ccc;">
                <h3>🔬 Orden de Estudios de Laboratorio / Gabinete</h3>
                <p><?php echo htmlspecialchars($datos['orden_estudios']); ?></p>
            </div>
            <?php endif; ?>

            <div class="firma">
                <div class="firma-linea">
                    Firma del Médico<br>
                    <span style="font-weight: normal; font-size: 0.85rem;"><?php echo htmlspecialchars($datos['medico']); ?></span>
                </div>
            </div>

            <div class="footer">
                Documento de uso estrictamente médico. Válido para surtir en farmacia de turno. <br>
                Generado por Sistema HIS San Francisco.
            </div>

        </div>
    </div>

    <script>
        window.onload = function() {
            // Ponemos un pequeño retraso de medio segundo para asegurar que carguen los estilos
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>