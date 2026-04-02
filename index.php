<?php
require 'conexion.php';
$alerta = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['solicitar_cita'])) {
    if (!isset($_POST['aviso_privacidad'])) {
        $alerta = "<script>alert('Error: Debe aceptar el Aviso de Privacidad para continuar.'); window.location.href='#agendar';</script>";
    } else {
        // 1. Atrapamos los datos del paciente
        $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
        $telefono = preg_replace('/[^0-9]/', '', $_POST['telefono']);
        $especialidad = $conexion->real_escape_string($_POST['especialidad']);
        $fecha = $conexion->real_escape_string($_POST['fecha']);
        $hora = $conexion->real_escape_string($_POST['hora']); // <-- LA NUEVA HORA
        
        // Juntamos todo en el motivo para que Recepción lo vea clarito
        $motivo_completo = "Consulta web: $especialidad | Tel: $telefono";

        // 2. EL RADAR DE CHOQUES
        $sql_choque = "SELECT id_cita FROM citas WHERE fecha_cita = '$fecha' AND hora_cita = '$hora' AND estado != 'Cancelada'";
        $resultado_choque = $conexion->query($sql_choque);

        if ($resultado_choque && $resultado_choque->num_rows > 0) {
            $alerta = "<script>alert('❌ Lo sentimos, el horario de las " . date('h:i A', strtotime($hora)) . " ya está reservado. Por favor elige otra hora.'); window.location.href='#agendar';</script>";
        } else {
            // 3. GUARDADO INTELIGENTE (Compatible con tu nuevo panel de citas.php)
            $sql_insert = "INSERT INTO citas (paciente, fecha_cita, hora_cita, motivo, estado) VALUES ('$nombre', '$fecha', '$hora', '$motivo_completo', 'Pendiente')";
            
            if ($conexion->query($sql_insert) === TRUE) {
                // Si quieres seguir guardando al paciente en su propia tabla por historial, lo hacemos aquí de forma silenciosa:
                $conexion->query("INSERT IGNORE INTO pacientes (nombre_completo, telefono_emergencia) VALUES ('$nombre', '$telefono')");
                
                $alerta = "<script>alert('✅ ¡Cita solicitada con éxito para las " . date('h:i A', strtotime($hora)) . "! Lo esperamos en la clínica.'); window.location.href='#agendar';</script>";
            } else {
                $alerta = "<script>alert('❌ Error de conexión al guardar la cita. Intente de nuevo.'); window.location.href='#agendar';</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital San Francisco</title>
    <style>
        :root { --primary-color: #004e92; --secondary-color: #f8f9fa; --text-color: #2c3e50; --accent-color: #27ae60; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; color: var(--text-color); background-color: var(--secondary-color); scroll-behavior: smooth; }
        
        /* Encabezado */
        header { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .logo { font-size: 1.5rem; font-weight: bold; color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .logo img { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        nav a { margin-left: 20px; text-decoration: none; color: var(--text-color); font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: var(--primary-color); }
        .btn-login { background-color: var(--primary-color); color: white; padding: 10px 20px; border-radius: 5px; border: none; font-weight: bold; margin-left: 20px; text-decoration: none; }
        .btn-login:hover { background-color: #003666; }
        
        /* Hero */
        .hero { background: linear-gradient(rgba(0, 78, 146, 0.85), rgba(0, 78, 146, 0.85)), url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover; color: white; text-align: center; padding: 120px 20px; }
        .hero h1 { font-size: 3.5rem; margin-bottom: 15px; }
        .hero p.slogan { font-size: 1.5rem; font-style: italic; margin-bottom: 30px; }
        .btn-appointment { background-color: var(--accent-color); color: white; padding: 15px 35px; font-size: 1.1rem; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
        
        /* Sección Nosotros */
        .about-section { padding: 60px 5%; background-color: white; display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 40px; }
        .about-text { flex: 1; min-width: 300px; max-width: 600px; }
        .about-text h2 { color: var(--primary-color); font-size: 2.2rem; margin-top: 0; }
        .about-text p { line-height: 1.6; color: #555; font-size: 1.05rem; margin-bottom: 15px; text-align: justify;}
        
        /* Especialidades */
        .services { padding: 60px 5%; text-align: center; background-color: var(--secondary-color); }
        .services h2 { color: var(--primary-color); font-size: 2.2rem; margin-bottom: 40px; }
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; max-width: 1200px; margin: 0 auto; }
        .service-card { background: white; padding: 30px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s; border-top: 4px solid var(--primary-color);}
        .service-card:hover { transform: translateY(-5px); }
        .service-card h3 { color: var(--primary-color); margin-bottom: 15px; font-size: 1.3rem; }
        .service-card p { color: #555; line-height: 1.6; }

        /* Formulario de Citas */
        .appointment-section { background-color: #004e92; padding: 60px 5%; display: flex; justify-content: center; align-items: center; flex-direction: column; color: white;}
        .appointment-section h2 { font-size: 2.2rem; margin-bottom: 20px; text-align: center; }
        .appointment-form { background: white; color: var(--text-color); padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 100%; max-width: 600px; }
        .form-row { display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap; }
        .form-group { flex: 1; display: flex; flex-direction: column; min-width: 250px; }
        .form-group label { font-weight: bold; margin-bottom: 5px; font-size: 0.9rem; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; }
        
        /* Privacidad */
        .checkbox-group { flex-direction: row; align-items: flex-start; gap: 10px; margin-top: 15px; background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid var(--primary-color); }
        .checkbox-group input { width: auto; margin-top: 3px; cursor: pointer; }
        .checkbox-group label { font-weight: normal; font-size: 0.85rem; color: #555; margin-bottom: 0; line-height: 1.4; }
        .checkbox-group span { color: var(--primary-color); text-decoration: underline; cursor: pointer; font-weight: bold; }
        .btn-submit-appointment { background-color: var(--accent-color); color: white; padding: 12px; border: none; border-radius: 5px; width: 100%; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 20px; transition: background 0.3s;}
        .btn-submit-appointment:hover { background-color: #218c4b; }
        
        /* Footer / Soporte / Redes */
        footer { background-color: #1a252f; color: white; padding: 50px 5% 20px 5%; display: flex; flex-direction: column; align-items: center; }
        .footer-content { display: flex; justify-content: space-between; width: 100%; max-width: 1200px; margin-bottom: 20px; flex-wrap: wrap; gap: 30px; }
        .footer-section { flex: 1; min-width: 250px; }
        .footer-section h4 { color: var(--accent-color); margin-bottom: 15px; font-size: 1.2rem; border-bottom: 2px solid #2c3e50; padding-bottom: 10px;}
        .footer-section p { font-size: 0.95rem; color: #bdc3c7; line-height: 1.6; margin: 8px 0; display: flex; align-items: center; gap: 8px;}
        .support-link { color: #3498db; text-decoration: none; font-weight: bold; }
        .support-link:hover { text-decoration: underline; }
        .social-link { display: inline-block; margin-top: 10px; background: #3b5998; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold;}
        .social-link:hover { background: #2d4373; }
        .footer-bottom { border-top: 1px solid #2c3e50; padding-top: 20px; width: 100%; text-align: center; font-size: 0.8rem; color: #7f8c8d; }

        /* Modal Privacidad */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(3px); }
        .modal-content { background-color: #fff; margin: 8% auto; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); position: relative; max-height: 80vh; overflow-y: auto; }
        .close-btn { color: #aaa; position: absolute; top: 15px; right: 20px; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: #e74c3c; }
        .modal-content h3 { color: var(--primary-color); margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .modal-content p { font-size: 0.9rem; color: #555; line-height: 1.6; text-align: justify; }

        /* Botón Flotante WhatsApp */
        .whatsapp-float { position: fixed; width: 60px; height: 60px; bottom: 40px; right: 40px; background-color: #25d366; color: #FFF; border-radius: 50px; text-align: center; font-size: 30px; box-shadow: 2px 2px 10px rgba(0,0,0,0.2); z-index: 1000; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.3s;}
        .whatsapp-float:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    <?php echo $alerta; ?>

    <header>
        <a href="#" class="logo">
            <img src="https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/333064747_589515563039156_5950082378040788875_n.jpg?_nc_cat=100&ccb=1-7&_nc_sid=1d70fc&_nc_ohc=AZE4JEuDpwUQ7kNvwHrIf4p&_nc_oc=Adq0GhXZmZBpmqCrmpiDSZdF2JeBbReRkAt-Cj3Inl95D9GbBZcoWh1hoO0H4GXFEhU&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=4e4-0sW89pcWusH06XgM_Q&_nc_ss=7a3a8&oh=00_Afws8M2HEAHsbXXObLWY_GvMBPb2UF4h-gwDyzGJWrnFVA&oe=69D0923F" alt="Logo Hospital San Francisco">
            Hospital San Francisco
        </a>
        <nav>
            <a href="#nosotros">Nosotros</a>
            <a href="#servicios">Especialidades</a>
            <a href="#agendar">Citas</a>
            <a href="login.php" class="btn-login">Portal Interno</a>
        </nav>
    </header>

    <section class="hero">
        <h1>Tu salud en las mejores manos</h1>
        <p class="slogan">"¡Calidad y calidez al servicio de tu salud!"</p>
        <a href="#agendar" class="btn-appointment">Agendar Cita Médica</a>
    </section>

    <section id="nosotros" class="about-section">
        <div class="about-text">
            <h2>Acerca del Hospital</h2>
            <p>En el <strong>Hospital San Francisco</strong>, nos enorgullecemos de ser una institución médica líder en la región, comprometida con ofrecer atención médica de la más alta calidad y con un trato humano excepcional.</p>
            <p>Contamos con instalaciones modernas, quirófanos equipados, área de urgencias y un equipo de médicos especialistas certificados listos para atenderte a ti y a tu familia.</p>
            <p><strong>Nuestra Misión:</strong> Brindar servicios de salud integrales, seguros y accesibles, garantizando el bienestar de nuestra comunidad mediante la excelencia clínica.</p>
        </div>
        <img style="width: 100%; max-width: 400px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" src="https://scontent.fupn2-1.fna.fbcdn.net/v/t39.30808-6/476464833_636630345616330_4632333930846874228_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=13d280&_nc_ohc=6GLwB4zyxB0Q7kNvwFe2ONp&_nc_oc=AdqgNhO0IP9A8YcjWyNJa95fuLXlT9iFCv_0VweVshHaYy763Xwg2bxbDeMeqz0OII0&_nc_zt=23&_nc_ht=scontent.fupn2-1.fna&_nc_gid=bp7n7k4ONnhgojwugZz0Qg&_nc_ss=7a3a8&oh=00_Afxw09ns1YASwT8XY3LRDsohCgNXvZJyTKFl_BDZnTA6wg&oe=69D125D0">
    </section>

    <section id="servicios" class="services">
        <h2>Nuestras Especialidades y Servicios</h2>
        <div class="services-grid">
            <div class="service-card">
                <h3>👨‍⚕️ Medicina General</h3>
                <p>Atención primaria, diagnóstico inicial y medicina preventiva para cuidar la salud integral de toda la familia en el día a día.</p>
            </div>
            <div class="service-card">
                <h3>👶 Pediatría</h3>
                <p>Atención médica especializada, control de vacunas y seguimiento del sano desarrollo de recién nacidos, niños y adolescentes.</p>
            </div>
            <div class="service-card">
                <h3>🤰 Ginecología</h3>
                <p>Cuidado de la salud reproductiva, prevención de enfermedades, control de embarazo y atención especializada para la mujer.</p>
            </div>
            <div class="service-card">
                <h3>🦴 Traumatología</h3>
                <p>Diagnóstico, tratamiento y rehabilitación de lesiones, fracturas y enfermedades del sistema musculoesquelético.</p>
            </div>
            <div class="service-card">
                <h3>🚑 Urgencias 24/7</h3>
                <p>Atención médica inmediata para estabilización y cuidado de pacientes en situaciones críticas, disponible de día y noche.</p>
            </div>
            <div class="service-card">
                <h3>🔬 Laboratorio Clínico</h3>
                <p>Análisis de sangre, pruebas de rutina y estudios especializados con resultados precisos y rápidos para un buen diagnóstico.</p>
            </div>
        </div>
    </section>

    <section id="agendar" class="appointment-section">
        <h2>Solicitar Consulta Médica en Línea</h2>
        <form class="appointment-form" method="POST" action="index.php#agendar">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Ej. Juan Pérez García" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono de Contacto (10 dígitos)</label>
                    <input type="tel" name="telefono" pattern="[0-9]{10}" maxlength="10" placeholder="Ej. 5512345678" title="Debe ingresar exactamente 10 números" required>
                </div>
                <div class="form-group">
                    <label>Especialidad</label>
                    <select name="especialidad" required>
                        <option value="">Seleccione...</option>
                        <option value="Medicina General">Medicina General</option>
                        <option value="Pediatría">Pediatría</option>
                        <option value="Traumatología">Traumatología</option>
                        <option value="Ginecología">Ginecología</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Fecha Deseada</label>
                    <input type="date" name="fecha" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Hora Deseada</label>
                    <select name="hora" required>
                        <option value="" disabled selected>Seleccione hora...</option>
                        <?php
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
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="privacidad" name="aviso_privacidad" required>
                <label for="privacidad">
                    He leído y otorgo mi consentimiento expreso para el tratamiento de mis datos, de conformidad con el <span onclick="abrirModal()">Aviso de Privacidad</span> vigente.
                </label>
            </div>

            <button type="submit" name="solicitar_cita" class="btn-submit-appointment">Confirmar Solicitud</button>
        </form>
    </section>

    <footer id="soporte">
        <div class="footer-content">
            <div class="footer-section">
                <h4>🏥 Información de Contacto</h4>
                <p>📍 Calle Amado Nervo #1300, Nahuatzen, Michoacán.</p>
                <p>🕒 Horario: Abierto las 24 horas, los 365 días del año.</p>
                <p>🚑 Urgencias Médicas: Disponibles siempre.</p>
            </div>
            <div class="footer-section">
                <h4>📞 Soporte y Recepción</h4>
                <p>¿Necesitas reprogramar o tienes dudas?</p>
                <p>📱 WhatsApp / Recepción: <a href="tel:452 285 23 02" class="support-link"> 452 285 23 02 </p>
                <p>📧 Email: <a href="mailto:contacto@hospitalsanfrancisco.com" class="support-link">contacto@hospitalsanfrancisco.com</a></p>
            </div>
            <div class="footer-section">
                <h4>Síguenos en Redes</h4>
                <p>Mantente al tanto de nuestras campañas de salud y avisos importantes.</p>
                <p><a href="https://www.facebook.com/hospitalsanfrco" target="_blank" class="social-link">👍 Visitar Facebook Oficial</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Hospital San Francisco. Todos los derechos reservados.</p>
        </div>
    </footer>

    <a href="https://wa.me/524522852302?text=Hola,%20me%20gustaría%20pedir%20información%20sobre%20las%20consultas." class="whatsapp-float" target="_blank">
        <svg style="width:35px; height:35px; fill:white;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.029 18.88c-1.161 0-2.305-.292-3.318-.844l-3.677.964.984-3.595c-.607-1.052-.927-2.246-.926-3.468.001-3.825 3.113-6.937 6.937-6.937 1.856.001 3.598.723 4.907 2.034 1.31 1.311 2.031 3.054 2.03 4.908-.002 3.828-3.116 6.938-6.937 6.938z"/></svg>
    </a>

    <div id="miModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <h3>Aviso de Privacidad (Resumen)</h3>
            <p><strong>Identidad y domicilio del responsable:</strong> El Hospital San Francisco es responsable del tratamiento de sus datos personales y médicos.</p>
            <p><strong>Datos personales que recabamos:</strong> Recabamos su nombre completo, teléfono de contacto e información relacionada con su estado de salud (datos sensibles).</p>
            <p><strong>Finalidad del tratamiento de datos:</strong> Sus datos serán utilizados única y exclusivamente para proveer los servicios médicos solicitados, agendar sus citas, integrar su expediente clínico y contactarlo en caso de emergencias o reprogramaciones.</p>
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="cerrarModal()" style="background:var(--primary-color); color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Entendido, cerrar ventana</button>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("miModal");
        function abrirModal() { modal.style.display = "block"; }
        function cerrarModal() { modal.style.display = "none"; }
        window.onclick = function(event) {
            if (event.target == modal) { modal.style.display = "none"; }
        }
    </script>
</body>
</html>