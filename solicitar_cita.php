<?php
// require 'conexion.php'; // Descomenta esto cuando lo subas a tu proyecto
$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Atrapamos los datos del paciente (público)
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $telefono = $conexion->real_escape_string(trim($_POST['telefono']));
    $especialidad = $conexion->real_escape_string($_POST['especialidad']);
    $fecha = $conexion->real_escape_string($_POST['fecha']);
    $hora = $conexion->real_escape_string($_POST['hora']);
    
    // Juntamos la especialidad y el teléfono en el motivo para que lo veas en tu panel
    $motivo_completo = "Especialidad: $especialidad | Tel: $telefono";

    // EL RADAR DE CHOQUES PÚBLICO
    $sql_choque = "SELECT id_cita FROM citas WHERE fecha_cita = '$fecha' AND hora_cita = '$hora' AND estado != 'Cancelada'";
    $resultado_choque = $conexion->query($sql_choque);

    if ($resultado_choque && $resultado_choque->num_rows > 0) {
        $mensaje = "<div class='alerta error'>❌ Lo sentimos, ese horario ya fue reservado por alguien más. Por favor elige otra hora u otro día.</div>";
    } else {
        // Horario libre, lo guardamos como Pendiente
        $sql_insert = "INSERT INTO citas (paciente, fecha_cita, hora_cita, motivo, estado) VALUES ('$nombre', '$fecha', '$hora', '$motivo_completo', 'Pendiente')";
        if ($conexion->query($sql_insert) === TRUE) {
            $mensaje = "<div class='alerta exito'>✅ <b>¡Tu cita ha sido reservada!</b> Te esperamos el ".date('d/m/Y', strtotime($fecha))." a las ".date('h:i A', strtotime($hora)).".</div>";
        } else {
            $mensaje = "<div class='alerta error'>❌ Hubo un error de conexión. Intenta de nuevo.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar Consulta - Hospital San Francisco</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #004e92; /* El azul de tu diseño */
            display: flex; 
            flex-direction: column;
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
            box-sizing: border-box;
        }
        h1 { color: white; margin-bottom: 30px; text-align: center;}
        
        .form-container { 
            background-color: white; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 600px; 
        }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 14px;}
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 15px;
        }
        
        .checkbox-group {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #004e92;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #555;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .btn-submit { 
            background-color: #27ae60; 
            color: white; 
            padding: 15px; 
            border: none; 
            border-radius: 4px; 
            font-weight: bold; 
            font-size: 16px; 
            cursor: pointer; 
            width: 100%; 
            transition: 0.3s;
        }
        .btn-submit:hover { background-color: #219a52; }
        
        .alerta { padding: 15px; border-radius: 4px; margin-bottom: 25px; font-size: 15px; text-align: center;}
        .exito { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    </style>
</head>
<body>

    <h1>Solicitar Consulta Médica en Línea</h1>

    <div class="form-container">
        <?php echo $mensaje; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" required placeholder="Ej. Juan Pérez García">
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Teléfono de Contacto (10 dígitos)</label>
                    <input type="tel" name="telefono" required pattern="[0-9]{10}" placeholder="Ej. 5512345678">
                </div>
                <div class="form-group">
                    <label>Especialidad</label>
                    <select name="especialidad" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <option value="Medicina General">Medicina General</option>
                        <option value="Pediatría">Pediatría</option>
                        <option value="Traumatología">Traumatología</option>
                        <option value="Laboratorio">Estudios de Laboratorio</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha Deseada</label>
                    <input type="date" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Hora Deseada</label>
                    <select name="hora" required>
                        <option value="" disabled selected>Seleccione hora...</option>
                        <?php
                        // Generamos los mismos bloques de 30 min que en tu panel interno
                        $inicio = strtotime('08:00');
                        $fin = strtotime('18:00');
                        while ($inicio <= $fin) {
                            echo "<option value='".date('H:i', $inicio)."'>".date('h:i A', $inicio)."</option>";
                            $inicio = strtotime('+30 minutes', $inicio);
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" required id="privacidad">
                <label for="privacidad" style="font-weight: normal; margin:0; cursor: pointer;">
                    He leído y otorgo mi consentimiento expreso para el tratamiento de mis datos, de conformidad con el <a href="#">Aviso de Privacidad</a> vigente.
                </label>
            </div>

            <button type="submit" class="btn-submit">Confirmar Solicitud</button>
        </form>
    </div>

</body>
</html>