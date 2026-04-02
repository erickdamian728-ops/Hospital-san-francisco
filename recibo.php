<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$id_pago = (int)$_GET['id'];

// Obtener datos del pago y del paciente
$sql = "SELECT p.*, pac.nombre_completo 
        FROM pagos p 
        JOIN pacientes pac ON p.id_paciente = pac.id_paciente 
        WHERE p.id_pago = $id_pago";
$resultado = $conexion->query($sql);

if ($resultado->num_rows == 0) {
    echo "Error: Recibo no encontrado.";
    exit();
}

$pago = $resultado->fetch_assoc();
$fecha_formateada = date("d/m/Y - h:i A", strtotime($pago['fecha_pago']));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #<?php echo str_pad($pago['id_pago'], 5, "0", STR_PAD_LEFT); ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #e9ecef; display: flex; justify-content: center; padding: 20px; color: #000; }
        
        /* Formato estilo "Ticket" o Media Carta */
        .recibo-box { background: white; width: 80mm; padding: 5mm; box-shadow: 0 5px 15px rgba(0,0,0,0.2); margin-top: 20px; border: 1px solid #ccc;}
        
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .header img { width: 60px; height: 60px; border-radius: 8px; filter: grayscale(100%); margin-bottom: 5px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; font-family: 'Arial', sans-serif;}
        .header p { font-size: 12px; margin: 2px 0; }

        .detalles { font-size: 13px; line-height: 1.5; margin-bottom: 15px; }
        .detalles strong { display: inline-block; width: 80px; }

        .concepto-box { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 10px 0; margin-bottom: 15px; }
        .concepto-title { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .concepto-text { font-size: 14px; }

        .total-box { text-align: right; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .metodo { font-size: 12px; text-align: right; margin-top: -15px; margin-bottom: 20px;}

        .footer { text-align: center; font-size: 11px; border-top: 2px dashed #000; padding-top: 10px; }
        
        /* Botones que no se imprimen */
        .botones { text-align: center; margin-bottom: 20px; position: absolute; top: 20px; left: 20px;}
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; background: #3498db; color: white; text-decoration: none;}

        @media print {
            body { background: white; padding: 0; align-items: flex-start; justify-content: flex-start;}
            .botones { display: none !important; }
            .recibo-box { box-shadow: none; border: none; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="botones">
        <button onclick="window.print()" class="btn">🖨️ Imprimir Recibo</button>
    </div>

    <div class="recibo-box">
        
        <div class="header">
            <img src="https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=AZE4JEuDpwUQ7kNvwHrIf4p&_nc_oc=Adq0GhXZmZBpmqCrmpiDSZdF2JeBbReRkAt-Cj3Inl95D9GbBZcoWh1hoO0H4GXFEhU&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=4e4-0sW89pcWusH06XgM_Q&_nc_ss=7a3a8&oh=00_Afws8M2HEAHsbXXObLWY_GvMBPb2UF4h-gwDyzGJWrnFVA&oe=69D0923F" alt="Logo">
            <h1>Hosp. San Francisco</h1>
            <p>Nahuatzen, Michoacán</p>
            <p>RFC: HSF-000000-XXX</p>
        </div>

        <div class="detalles">
            <div><strong>FOLIO:</strong> <?php echo str_pad($pago['id_pago'], 6, "0", STR_PAD_LEFT); ?></div>
            <div><strong>FECHA:</strong> <?php echo $fecha_formateada; ?></div>
            <div><strong>PACIENTE:</strong> <?php echo htmlspecialchars($pago['nombre_completo']); ?></div>
            <div><strong>CAJERO:</strong> <?php echo htmlspecialchars($_SESSION['nombre']); ?></div>
        </div>

        <div class="concepto-box">
            <div class="concepto-title">Descripción del Cargo:</div>
            <div class="concepto-text"><?php echo htmlspecialchars($pago['concepto']); ?></div>
        </div>

        <div class="total-box">
            TOTAL: $<?php echo number_format($pago['monto'], 2); ?> MXN
        </div>
        
        <div class="metodo">
            Pago en: <?php echo htmlspecialchars($pago['metodo_pago']); ?>
        </div>

        <div class="footer">
            ¡Gracias por su preferencia!<br>
            Este documento es un comprobante de pago interno, no válido como factura fiscal.
        </div>

    </div>

    <script>
        // Imprimir automáticamente al abrir
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>