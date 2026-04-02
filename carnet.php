<?php
session_start();
require 'conexion.php';

// Seguridad
if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$id_paciente = (int)$_GET['id'];

$sql = "SELECT * FROM pacientes WHERE id_paciente = $id_paciente";
$resultado = $conexion->query($sql);

if ($resultado->num_rows == 0) {
    echo "Error: Paciente no encontrado.";
    exit();
}

$paciente = $resultado->fetch_assoc();

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
    <title>Carnet Médico - <?php echo htmlspecialchars($paciente['nombre_completo']); ?></title>
    <style>
        /* Variables de color (Usamos el azul de tu hospital) */
        :root { --primary: #004e92; --secondary: #f4f7f6; --text: #333; }
        
        body { font-family: 'Arial', sans-serif; background: #555; display: flex; flex-direction: column; align-items: center; padding: 20px; margin: 0; }
        
        /* Botones de acción en pantalla */
        .botones-accion { margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); width: 279mm; text-align: center; box-sizing: border-box;}
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; margin: 0 5px; text-decoration: none; display: inline-block; color: white;}
        .btn-print { background: #27ae60; }
        .btn-back { background: #34495e; }

        /* Formato de Hoja Tamaño Carta Acostada (Landscape) */
        .hoja { width: 279mm; height: 215mm; background: white; margin-bottom: 30px; display: flex; box-shadow: 0 5px 15px rgba(0,0,0,0.5); overflow: hidden; position: relative;}
        
        /* Cada mitad de la hoja (139.5mm x 215mm) */
        .mitad { width: 50%; height: 100%; box-sizing: border-box; padding: 15mm; display: flex; flex-direction: column; }
        
        /* Línea de doblez imaginaria (solo se ve en pantalla) */
        .linea-doblez { border-right: 1px dashed #ccc; }

        /* --- ESTILOS DE LA PORTADA --- */
        .portada-content { text-align: center; margin-top: auto; margin-bottom: auto; }
        .pill-title { background: var(--primary); color: white; padding: 10px 30px; border-radius: 30px; font-size: 22px; font-weight: bold; display: inline-block; margin-bottom: 30px; border: 2px solid #003666;}
        .logo-hospital { width: 120px; height: 120px; border-radius: 15px; object-fit: cover; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .hosp-name { color: var(--primary); font-size: 28px; margin: 0; }
        .hosp-sub { color: #555; font-size: 16px; margin: 5px 0 20px 0; font-style: italic; }
        .hosp-info { font-size: 12px; color: #777; line-height: 1.5; border-top: 2px solid var(--primary); padding-top: 15px; margin-top: 30px; }

        /* --- ESTILOS DE LA CONTRAPORTADA (Reglamento) --- */
        .contraportada-content { background: #f9f9f9; padding: 20px; border-radius: 10px; border: 1px solid #eee; margin-top: auto; margin-bottom: auto;}
        .contraportada-content h3 { color: var(--primary); text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 10px;}
        .contraportada-content ul { padding-left: 20px; font-size: 13px; line-height: 1.8; color: #444; }
        .emergencia-box { background: var(--primary); color: white; text-align: center; padding: 15px; border-radius: 8px; margin-top: 30px; }
        .emergencia-box h4 { margin: 0 0 5px 0; font-size: 16px; }
        .emergencia-box p { margin: 0; font-size: 20px; font-weight: bold; }

        /* --- ESTILOS DEL INTERIOR --- */
        .datos-paciente { border: 2px solid #333; border-radius: 5px; padding: 10px; margin-bottom: 15px; font-size: 14px; }
        .datos-paciente .fila { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px dotted #ccc; padding-bottom: 5px;}
        .datos-paciente .fila:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
        .datos-paciente strong { color: var(--primary); }

        .tabla-header { background: #555; color: white; text-align: center; font-weight: bold; font-size: 14px; padding: 8px; border-radius: 5px 5px 0 0; text-transform: uppercase; letter-spacing: 1px;}
        .tabla-citas { width: 100%; border-collapse: collapse; text-align: center; font-size: 12px; }
        .tabla-citas th, .tabla-citas td { border: 1px solid #555; padding: 6px; }
        .tabla-citas th { background: #eee; color: #333; }
        .tabla-citas td { height: 28px; } /* Altura de la celda para escribir a mano */

        /* --- REGLAS DE IMPRESIÓN --- */
        @media print {
            @page { size: landscape; margin: 0; }
            body { background: white; padding: 0; align-items: flex-start; }
            .botones-accion { display: none !important; }
            .hoja { box-shadow: none; margin: 0; page-break-after: always; }
            .linea-doblez { border-right: none; } /* Ocultamos la línea punteada al imprimir */
            .pill-title { border: 2px solid #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .emergencia-box, .tabla-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .tabla-citas th { background: #ddd !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="botones-accion">
        <h2 style="margin-top:0;">Configuración de Impresión</h2>
        <p style="font-size:14px; color:#555;">Imprime este documento en una <strong>hoja tamaño carta</strong> configurada en <strong>orientación Horizontal (Landscape)</strong> y por <strong>ambos lados (doble cara)</strong>. Al salir, solo dóblala por la mitad.</p>
        <button onclick="window.print()" class="btn btn-print">🖨️ Imprimir Librito</button>
        <a href="pacientes.php" class="btn btn-back">⬅️ Volver</a>
    </div>

    <div class="hoja">
        
        <div class="mitad linea-doblez">
            <div class="contraportada-content">
                <h3>Reglamento del Paciente</h3>
                <ul>
                    <li>Es obligatorio presentar este carnet en la recepción para ser atendido.</li>
                    <li>Llegar 15 minutos antes de la hora programada para su cita.</li>
                    <li>En caso de no poder asistir, favor de cancelar con 24 hrs de anticipación.</li>
                    <li>Este documento es personal e intransferible.</li>
                    <li>El extravío de este carnet tiene un costo de reposición.</li>
                </ul>

                <div class="emergencia-box">
                    <h4>🚑 Urgencias 24 Horas</h4>
                    <p>(452) 123 4567</p>
                </div>
            </div>
        </div>

        <div class="mitad">
            <div class="portada-content">
                <div class="pill-title">CARNET DE CITAS</div>
                <br>
                <img src="https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=AZE4JEuDpwUQ7kNvwHrIf4p&_nc_oc=Adq0GhXZmZBpmqCrmpiDSZdF2JeBbReRkAt-Cj3Inl95D9GbBZcoWh1hoO0H4GXFEhU&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=4e4-0sW89pcWusH06XgM_Q&_nc_ss=7a3a8&oh=00_Afws8M2HEAHsbXXObLWY_GvMBPb2UF4h-gwDyzGJWrnFVA&oe=69D0923F" alt="Logo" class="logo-hospital">
                
                <h1 class="hosp-name">Hospital San Francisco</h1>
                <p class="hosp-sub">Clínica de Especialidades</p>
                
                <div class="hosp-info">
                    <strong>📍 Dirección:</strong> Calle Amado Nervo #1300, Nahuatzen, Michoacán.<br>
                    <strong>📧 Correo:</strong> contacto@hospitalsanfrancisco.com
                </div>
            </div>
        </div>

    </div>

    <div class="hoja">
        
        <div class="mitad linea-doblez">
            
            <div class="datos-paciente">
                <div class="fila">
                    <span><strong>Folio:</strong> #<?php echo str_pad($paciente['id_paciente'], 5, "0", STR_PAD_LEFT); ?></span>
                    <span><strong>Sangre:</strong> <?php echo empty($paciente['tipo_sangre']) ? 'N/D' : htmlspecialchars($paciente['tipo_sangre']); ?></span>
                </div>
                <div class="fila">
                    <span style="width:100%;"><strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre_completo']); ?></span>
                </div>
                <div class="fila">
                    <span><strong>Edad:</strong> <?php echo $edad; ?></span>
                    <span><strong>Tel:</strong> <?php echo htmlspecialchars($paciente['telefono_emergencia']); ?></span>
                </div>
            </div>

            <div class="tabla-header">Su Próxima Cita Es</div>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th style="width: 25%;">Fecha</th>
                        <th style="width: 20%;">Hora</th>
                        <th style="width: 55%;">Especialidad / Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=0; $i<11; $i++){ echo "<tr><td></td><td></td><td></td></tr>"; } ?>
                </tbody>
            </table>

        </div>

        <div class="mitad">
            
            <div class="tabla-header" style="background: #34495e;">Control de Citas</div>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th style="width: 25%;">Fecha</th>
                        <th style="width: 20%;">Hora</th>
                        <th style="width: 55%;">Especialidad / Firma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i=0; $i<16; $i++){ echo "<tr><td></td><td></td><td></td></tr>"; } ?>
                </tbody>
            </table>

        </div>

    </div>

</body>
</html>