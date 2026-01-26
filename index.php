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
            // <a href="views/tipo_becas.php" class="btn">Becas disponibles</a>
            <a href="views/documentos.php" class="btn-secundario">Documentos requeridos</a>
        </div>
    </main>

</body>
</html>