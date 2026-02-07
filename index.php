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

    <header class="animate-item stagger-1"
        style="position:relative;z-index:1;max-width:var(--max-width);margin:18px auto 8px;padding:8px 16px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="assets/img/icono.png" alt="UNEFA" style="height:56px;object-fit:contain;border-radius:6px;">
            <div style="color:#fff !important;">
                <strong style="font-size:1.1rem;">Sistema de Becas UNEFA</strong>
                <div style="font-size:0.9rem;">Portal de gestión de documentos</div>
            </div>
        </div>
        <nav id="top-nav" style="position:absolute;right:16px;top:12px;">
            <a class="btn btn-animated" href="views/login.php">Iniciar sesión</a>
            <a class="btn btn-animated" href="views/registro.php">Registrarse</a>
        </nav>
    </header>

    <main class="contenedor animate-item stagger-2">
        <h2>Bienvenido</h2>
        <p>Bienvenido al Sistema de Becas de la Universidad Nacional Experimental Politécnica de la Fuerza Armada
            Bolivariana (UNEFA).</p>

        <section style="margin-top:12px; background:transparent;">
            <p>
                Este espacio ha sido creado para brindar a los estudiantes la oportunidad de acceder a beneficios
                académicos que apoyen su formación profesional y personal.
            </p>
            <p>
                Aquí encontrarás información sobre los programas de becas disponibles, requisitos, procesos de
                postulación y seguimiento de solicitudes. Nuestro objetivo es facilitar el acceso a recursos que
                impulsen tu desarrollo y fortalezcan tu compromiso con la excelencia y el servicio a la comunidad.
            </p>
            <p>
                La UNEFA reafirma su misión de formar ciudadanos responsables, con valores éticos y disciplina,
                ofreciendo este sistema como un puente hacia nuevas oportunidades de crecimiento.
            </p>
        </section>

        <div class="botones" style="margin-top:18px;">
            <button id="btn-docs" class="btn-secundario btn-animated">Documentos requeridos</button>
        </div>

        <div id="docs-panel" class="collapsed animate-item stagger-3"
            style="margin-top:18px; text-align:left; overflow:hidden;">
            <div class="card" style="padding:18px;">
                <h3>Documentos Solicitados para la Beca</h3>
                <ul style="font-size:18px;">
                    <li>Constancia de inscripción (PDF)</li>
                    <li>Récord académico (PDF)</li>
                    <li>Fotocopia de la cédula (PDF, JPG, PNG)</li>
                    <li>RIF (PDF o DOC)</li>
                    <li>Foto tipo carnet (Imagen, PDF o DOC)</li>
                    <li>Carnet de la patria (Imagen o PDF)</li>
                    <li>Referencia bancaria (PDF o imagen)</li>
                </ul>
                <!-- Información solamente; botones removidos por solicitud -->
            </div>
        </div>

        <style>
            /* Collapsible panel animation */
            #docs-panel.collapsed {
                max-height: 0;
                transition: max-height 450ms ease;
            }

            #docs-panel.open {
                max-height: 800px;
                transition: max-height 450ms ease;
            }
        </style>

        <script>
            (function () {
                var btn = document.getElementById('btn-docs');
                var panel = document.getElementById('docs-panel');
                if (!btn || !panel) return;
                btn.addEventListener('click', function () {
                    var isOpen = panel.classList.contains('open');
                    if (!isOpen) {
                        panel.classList.remove('collapsed');
                        panel.classList.add('open');
                        btn.textContent = 'Ocultar requisitos';
                        setTimeout(function () { panel.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 100);
                    } else {
                        panel.classList.remove('open');
                        panel.classList.add('collapsed');
                        btn.textContent = 'Documentos requeridos';
                    }
                });
            })();
        </script>
        <script src="assets/js/animations.js"></script>
    </main>

</body>

</html>