<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}
// Tu enlace directo oficial
$url_logo = "https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=05T1E-27tS8Q7kNvwG4LOQN&_nc_oc=AdpaXbfX6Lg1i-yvz7hLxtKqDGfOp8bpnXwhOdh7YV4tqSIugiPD0s5ZrcsaoESCExE&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=nPfvXaUhldzbzhx8ZZr5Vw&_nc_ss=7a3a8&oh=00_Af3dNsBJZ6uTpJ0WNEb24K-I5vgbMwyqoy3uHkApL54jVw&oe=69D3A5BF";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Laboratorio - HSF</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; color: #333; }
        .hoja { max-width: 800px; margin: 0 auto; border: 1px solid #eee; padding: 30px; }
        
        /* Encabezado y Logo */
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #9b59b6; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-area { display: flex; align-items: center; gap: 15px; }
        .logo-img { width: 80px; height: 80px; border-radius: 5px; object-fit: cover; }
        .logo-text h1 { color: #004e92; margin: 0; font-size: 24px; }
        .logo-text h3 { color: #9b59b6; margin: 0; font-size: 16px; font-weight: normal; }
        .info-clinica { text-align: right; font-size: 12px; color: #7f8c8d; line-height: 1.4; }
        
        /* Datos del paciente */
        .datos-paciente { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .datos-paciente td { padding: 8px; border: 1px solid #ddd; font-size: 14px; }
        .datos-paciente td.label { background: #f8f9fa; font-weight: bold; width: 150px; color: #2c3e50;}
        
        /* Tabla de resultados */
        .titulo-estudio { text-align: center; background-color: #f8f9fa; border: 1px solid #ccc; padding: 10px; margin: 20px 0; text-transform: uppercase; color: #2c3e50; font-size: 16px; letter-spacing: 1px; font-weight: bold;}
        .resultados { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .resultados th { background: #9b59b6; color: white; padding: 10px; text-align: left; }
        .resultados td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        /* Observaciones */
        .observaciones-titulo { font-weight: bold; margin-bottom: 5px; color: #2c3e50;}
        .observaciones-caja { border: 1px solid #ddd; padding: 15px; border-radius: 5px; min-height: 50px; background-color: #f9f9f9; font-size: 13px; color: #333;}
        
        /* Firmas */
        .firma-area { margin-top: 50px; text-align: center; }
        .linea-firma { border-top: 1px solid #333; width: 250px; margin: 0 auto 5px; }
        
        /* Reglas para impresión */
        @media print { 
            body { padding: 0; background: white;}
            .hoja { border: none; padding: 0; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-imprimir { display: none; } 
        }
    </style>
</head>
<body>
    <div class="hoja">
        
        <div class="header">
            <div class="logo-area">
                <img src="<?php echo $url_logo; ?>" alt="Logo HSF" class="logo-img">
                <div class="logo-text">
                    <h1>Hospital San Francisco</h1>
                    <h3>Laboratorio Clínico Central</h3>
                </div>
            </div>
            <div class="info-clinica">
                Folio de Estudio: <strong>#LAB-<?php echo rand(1000, 9999); ?></strong><br>
                Fecha de Emisión: <?php echo date("d/m/Y H:i"); ?><br>
                 Calle Amado Nervo #1300, Nahuatzen, Michoacán<br>
                Tel: 452 285 23 02
            </div>
        </div>

        <table class="datos-paciente">
            <tr>
                <td class="label">Nombre del Paciente:</td>
                <td><strong>Griselda Damian</strong></td>
                <td class="label">Edad / Sexo:</td>
                <td>28 Años / F</td>
            </tr>
            <tr>
                <td class="label">Médico Solicitante:</td>
                <td>Dr. Juan Pérez</td>
                <td class="label">Prioridad:</td>
                <td>Rutina</td>
            </tr>
        </table>

        <div class="titulo-estudio">Química Sanguínea de 6 Elementos</div>
        
        <table class="resultados">
            <thead>
                <tr>
                    <th>Parámetro</th>
                    <th>Resultado</th>
                    <th>Unidades</th>
                    <th>Valores de Referencia</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Glucosa</td>
                    <td><strong>92.5</strong></td>
                    <td>mg/dL</td>
                    <td>70.0 - 100.0</td>
                </tr>
                <tr>
                    <td>Urea</td>
                    <td><strong>28.4</strong></td>
                    <td>mg/dL</td>
                    <td>15.0 - 45.0</td>
                </tr>
                <tr>
                    <td>Creatinina</td>
                    <td><strong>0.9</strong></td>
                    <td>mg/dL</td>
                    <td>0.6 - 1.2</td>
                </tr>
                <tr>
                    <td>Ácido Úrico</td>
                    <td><strong>4.5</strong></td>
                    <td>mg/dL</td>
                    <td>2.5 - 6.0</td>
                </tr>
                <tr>
                    <td>Colesterol Total</td>
                    <td><strong>160.0</strong></td>
                    <td>mg/dL</td>
                    <td>&lt; 200.0</td>
                </tr>
                <tr>
                    <td>Triglicéridos</td>
                    <td><strong>110.0</strong></td>
                    <td>mg/dL</td>
                    <td>&lt; 150.0</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-bottom: 40px;">
            <div class="observaciones-titulo">Observaciones Clínicas y Notas del Laboratorio:</div>
            <div class="observaciones-caja">
                Muestra procesada sin alteraciones. Hemólisis ausente. Todos los valores se encuentran dentro de los rangos biológicos de referencia esperados para la edad y sexo del paciente. No se requieren estudios confirmatorios adicionales por el momento.
            </div>
        </div>

        <div class="firma-area">
            <div class="linea-firma"></div>
            <strong>Q.F.B. <?php echo htmlspecialchars($_SESSION['nombre']); ?></strong><br>
            <span style="font-size: 12px; color: #7f8c8d;">Responsable de Laboratorio (Céd. Prof. 1234567)</span>
        </div>
        
    </div>

    <script>
        window.onload = function() { 
            // Pausa de 1 segundo exacto para que el logo de Facebook cargue antes de lanzar la impresión
            setTimeout(function(){ window.print(); }, 1000); 
        }
    </script>
</body>
</html>