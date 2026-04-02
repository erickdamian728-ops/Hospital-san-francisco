<?php
session_start();
// require 'conexion.php';

$datos_clinica = [
    'nombre' => 'Hospital San Francisco',
    'direccion' => 'Calle Amado Nervo #1300.',
    'telefonos' => 'Tel: (452) 123 4567 | Urgencias 24/7'
];

$datos_paciente = [
    'nombre' => 'Adela Damian Figueroa',
    'edad' => 'N/D',
    'folio' => '#00004',
    'peso' => '48.00 kg',
    'ta' => '120/80',
    'temp' => '36 °C',
    'sangre' => '___'
];

$datos_consulta = [
    'diagnostico' => 'inflamación de musculo columnal',
    'medico' => 'Dra. Patricia Carrillo Rangel'
];

$receta_medicamentos = [
    [
        'medicamento' => 'Naproxeno',
        'indicacion' => 'para desinflamar. Tomar una tableta cada 8 horas durante 8 días.'
    ],
    [
        'medicamento' => 'Ketorolaco',
        'indicacion' => 'para el dolor. Tomar una tableta cada 8 horas durante 5 días.'
    ]
];

$estudios_solicitados = 'Rayos X de la columna';
$url_logo = "https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=05T1E-27tS8Q7kNvwG4LOQN&_nc_oc=AdpaXbfX6Lg1i-yvz7hLxtKqDGfOp8bpnXwhOdh7YV4tqSIugiPD0s5ZrcsaoESCExE&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=nPfvXaUhldzbzhx8ZZr5Vw&_nc_ss=7a3a8&oh=00_Af3dNsBJZ6uTpJ0WNEb24K-I5vgbMwyqoy3uHkApL54jVw&oe=69D3A5BF";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - Folio <?php echo $datos_paciente['folio']; ?></title>
    <style>
        /* 1. CONFIGURACIÓN DE LA HOJA FÍSICA */
        @page {
            size: auto;   /* Se adapta al tamaño de papel de la impresora automáticamente */
            margin: 15mm; /* Margen físico seguro para que la impresora no corte los bordes */
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333; 
            margin: 0; 
            background-color: #f4f7f6; /* Solo para pantalla */
            padding: 20px;
        }

        /* 2. EL CONTENEDOR FLUIDO */
        .hoja-receta { 
            width: 100%; /* Toma todo el ancho disponible */
            max-width: 800px; /* Límite para que no se vea gigante en monitores anchos */
            margin: 0 auto; 
            background: #fff; 
            padding: 30px; 
            box-sizing: border-box; /* Asegura que el padding no desborde la hoja */
            box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }

        /* Estructura interna usando porcentajes y flexbox */
        .header { 
            display: flex; 
            align-items: center; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
        }
        .logo-img { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 50%; 
            margin-right: 20px;
        }
        .info-clinica h1 { color: #004e92; margin: 0 0 5px 0; font-size: 22px; }
        .info-clinica p { margin: 2px 0; color: #555; font-size: 13px; }

        .caja-paciente { 
            background-color: #f8f9fa; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 15px; 
            margin-bottom: 20px; 
            display: flex; 
            flex-wrap: wrap; /* Si la hoja es angosta, los datos bajan ordenadamente */
            justify-content: space-between;
            font-size: 14px;
        }
        .caja-paciente div { margin-right: 15px; }
        .caja-paciente strong { color: #2c3e50; }

        .signos-vitales {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        .seccion-titulo {
            color: #2c3e50;
            font-size: 15px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .diagnostico { margin-bottom: 30px; font-size: 14px; }

        .simbolo-rx {
            font-family: 'Times New Roman', Times, serif;
            font-size: 32px;
            font-weight: bold;
            font-style: italic;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .medicamento-item { 
            margin-bottom: 20px; 
            page-break-inside: avoid; /* Evita que un medicamento se corte a la mitad entre dos páginas */
        }
        .medicamento-nombre { font-weight: bold; font-size: 15px; color: #333; }
        .medicamento-indicacion { font-size: 14px; color: #555; margin-top: 3px; }

        .area-firmas-estudios {
            margin-top: 50px; 
            display: flex;
            flex-direction: column;
            align-items: center;
            page-break-inside: avoid; /* Evita que la firma se pase sola a otra página */
        }
        
        .firma-medico {
            text-align: center;
            margin-bottom: 40px; 
            width: 100%;
        }
        .linea-firma {
            border-top: 1px solid #000;
            width: 50%; /* Se adapta al tamaño de la hoja */
            max-width: 300px;
            margin: 0 auto 5px auto;
        }
        .nombre-medico { font-weight: bold; font-size: 14px; }

        .orden-estudios {
            width: 100%;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 20px;
        }
        .nota-legal { font-size: 11px; color: #7f8c8d; margin-bottom: 15px; }
        .titulo-estudios { font-weight: bold; color: #2c3e50; margin-bottom: 15px; text-transform: uppercase; font-size: 14px;}
        .detalle-estudios { font-size: 14px; text-align: left; padding: 0 10px; }

        /* 3. REGLAS ESTRICTAS DE IMPRESIÓN */
        @media print {
            body { 
                background-color: transparent; 
                padding: 0; 
            }
            .hoja-receta { 
                box-shadow: none; 
                max-width: 100%; /* Fuerza a ocupar el 100% de la hoja en la que imprimas */
                width: 100%;
                padding: 0; /* Quitamos el padding para aprovechar el margen del @page */
                border: none;
            }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="hoja-receta">
        
        <div class="header">
            <img src="<?php echo $url_logo; ?>" alt="Logo Hospital" class="logo-img">
            <div class="info-clinica">
                <h1><?php echo $datos_clinica['nombre']; ?></h1>
                <p><?php echo $datos_clinica['direccion']; ?></p>
                <p><?php echo $datos_clinica['telefonos']; ?></p>
            </div>
        </div>

        <div class="caja-paciente">
            <div><strong>Paciente:</strong> <?php echo $datos_paciente['nombre']; ?></div>
            <div><strong>Edad:</strong> <?php echo $datos_paciente['edad']; ?></div>
            <div><strong>Folio:</strong> <?php echo $datos_paciente['folio']; ?></div>
        </div>

        <div class="signos-vitales">
            <div><strong>Peso:</strong> <?php echo $datos_paciente['peso']; ?></div>
            <div><strong>TA:</strong> <?php echo $datos_paciente['ta']; ?></div>
            <div><strong>Temp:</strong> <?php echo $datos_paciente['temp']; ?></div>
            <div><strong>Tipo de Sangre:</strong> <?php echo $datos_paciente['sangre']; ?></div>
        </div>

        <div class="seccion-titulo">Diagnóstico Médico</div>
        <div class="diagnostico">
            <?php echo ucfirst($datos_consulta['diagnostico']); ?>
        </div>

        <div class="simbolo-rx">Rx</div>
        
        <div class="lista-medicamentos">
            <?php foreach($receta_medicamentos as $med): ?>
                <div class="medicamento-item">
                    <div class="medicamento-nombre"><?php echo $med['medicamento']; ?></div>
                    <div class="medicamento-indicacion"><?php echo $med['indicacion']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="area-firmas-estudios">
            <div class="firma-medico">
                <div class="linea-firma"></div>
                <div class="nombre-medico">Firma del Médico</div>
                <div style="font-size: 13px; color: #555;"><?php echo $datos_consulta['medico']; ?></div>
            </div>

            <div class="orden-estudios">
                <div class="nota-legal">
                    Documento de uso estrictamente médico. Válido para surtir en farmacia de turno.
                </div>
                <div class="titulo-estudios">
                    🔬 Orden de Estudios de Laboratorio / Gabinete
                </div>
                <div class="detalle-estudios">
                    <?php echo ucfirst($estudios_solicitados); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { 
            setTimeout(function(){ window.print(); }, 800); 
        }
    </script>
</body>
</html>