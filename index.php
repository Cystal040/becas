<?php
include("config/conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Becas</title>
    <link rel="icon" href="assets/img/icono.png">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>

<body class="fondo">

    <header style="position:relative;z-index:1;max-width:var(--max-width);margin:18px auto 8px;padding:8px 16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="assets/img/icono.png" alt="UNEFA" style="height:56px;object-fit:contain;border-radius:6px;">
            <div>
                <strong style="font-size:1.1rem;color:#fff">Sistema de Becas UNEFA</strong>
                <div style="font-size:0.9rem;color:var(--muted)">Portal de gestión de documentos</div>
            </div>
        </div>
        <nav style="position:absolute;right:16px;top:12px;">
            <a class="btn" href="views/login.php">Iniciar sesión</a>
            <a class="btn" href="views/registro.php">Registrarse</a>
        </nav>
    </header>

    <main class="contenedor">
        <h2>Bienvenido</h2>
        <p>Bienvenido al Sistema de Becas de la Universidad Nacional Experimental Politécnica de la Fuerza Armada Bolivariana (UNEFA).</p>

        <section style="margin-top:12px; background:transparent;">
            <p>
                Este espacio ha sido creado para brindar a los estudiantes la oportunidad de acceder a beneficios académicos que apoyen su formación profesional y personal.
            </p>
            <p>
                Aquí encontrarás información sobre los programas de becas disponibles, requisitos, procesos de postulación y seguimiento de solicitudes. Nuestro objetivo es facilitar el acceso a recursos que impulsen tu desarrollo y fortalezcan tu compromiso con la excelencia y el servicio a la comunidad.
            </p>
            <p>
                La UNEFA reafirma su misión de formar ciudadanos responsables, con valores éticos y disciplina, ofreciendo este sistema como un puente hacia nuevas oportunidades de crecimiento.
            </p>
        </section>

        <div class="botones" style="margin-top:18px;">
            <button id="btn-docs" class="btn-secundario" type="button">Documentos requeridos</button>
        </div>

        <div id="docs-panel" style="display:none; margin-top:18px; text-align:left;">
            <div class="card">
                <h3>Documentos Solicitados para la Beca</h3>
                <ul style="font-size:18px;">
                    <li>Constancia de inscripción (PDF)</li>
                    <li>Récord académico (PDF)</li>
                    <li>Fotocopia de la cédula (PDF, JPG, PNG)</li>
                    <li>RIF (PDF o DOC)</li>
                    <li>Foto tipo carnet (Imagen, PDF o DOC)</li>
                </ul>
                <div class="botones" style="justify-content:flex-start;">
                    <a href="views/login.php" class="btn">Iniciar sesión</a>
                    <a href="views/registro.php" class="btn-secundario">Registrarse</a>
                </div>
            </div>
        </div>

        <script>
            (function(){
                var btn = document.getElementById('btn-docs');
                var panel = document.getElementById('docs-panel');
                if(!btn || !panel) return;
                btn.addEventListener('click', function(){
                    if(panel.style.display === 'none' || panel.style.display === ''){
                        panel.style.display = 'block';
                        btn.textContent = 'Ocultar requisitos';
                        panel.scrollIntoView({behavior:'smooth', block:'start'});
                    } else {
                        panel.style.display = 'none';
                        btn.textContent = 'Documentos requeridos';
                    }
                });
            })();
        </script>
    </main>

</body>
</html>