<?php include("../config/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
    <style>
        /* Centrar el contenedor de login en esta página */
        body.fondo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .contenedor {
            margin: 0;
            width: 100%;
            max-width: 760px;
        }

        /* Agrandar tarjeta y formularios */
        .login-card {
            max-width: 680px;
            margin: 0 auto;
            padding: 28px;
        }

        .login-card label {
            font-size: 15px;
        }

        .login-card input[type="text"],
        .login-card input[type="password"] {
            padding: 12px 14px;
            font-size: 16px;
        }

        .login-card .botones .btn,
        .login-card .botones .btn-secundario {
            padding: 12px 18px;
            font-size: 15px;
        }

        .login-header img {
            height: 78px;
        }

        .login-header h2 {
            font-size: 1.4rem;
        }
    </style>
</head>

<body class="fondo">

    <div class="contenedor">
        <div class="login-header animate-item logo-float stagger-1"
            style="display:flex;align-items:center;gap:16px;margin-bottom:18px;">
            <a href="../index.php" style="display:inline-block;">
                <img src="../assets/img/icono.png" alt="UNEFA"
                    style="height:96px;object-fit:contain;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.18);">
            </a>
            <div>
                <h2 style="margin:0;font-size:1.6rem;">Portal de Becas UNEFA</h2>
                <p style="margin:4px 0 0 0;color:var(--muted);font-size:1rem;">Inicia sesión para gestionar tus
                    documentos</p>
            </div>
        </div>

        <?php
        session_start();
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        ?>

        <div class="login-card animate-item stagger-2"
            style="background:rgba(255,255,255,0.03);padding:36px;border-radius:12px;box-shadow:0 18px 48px rgba(0,0,0,0.25);max-width:720px;margin:0 auto;">
            <form action="../controllers/login_process.php" method="POST">

                <label>Usuario o correo:</label>
                <input type="text" name="user" placeholder="Usuario o correo" required style="font-size:16px;">

                <label>Contraseña:</label>
                <input type="password" name="password" required style="font-size:16px;">

                <div class="botones" style="display:flex;gap:10px;margin-top:10px;">
                    <button class="btn btn-animated" type="submit">Entrar</button>
                    <button class="btn-secundario btn-animated" type="button"
                        onclick="window.location.href='../index.php'">Inicio</button>
                    <a class="btn btn-animated" href="registro.php"
                        style="background:linear-gradient(90deg,#3498db,#2980b9);">Registrarse</a>
                </div>
            </form>

            <div style="margin-top:12px; display:flex;justify-content:flex-end;align-items:center;font-size:0.95rem;">
                <a href="#" onclick="alert('Contacto: soporte@unefa.edu.ve')">Ayuda</a>
            </div>

            <div style="margin-top:12px; text-align:center; font-size:0.98rem;">
                ¿No tienes cuenta? <a href="registro.php" style="color:var(--accent); font-weight:600;">Regístrate</a>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container" aria-live="polite" style="position:fixed;right:16px;bottom:16px;z-index:9999"></div>
    <style>
        .toast {
            background: #333;
            color: #fff;
            padding: 10px 14px;
            border-radius: 6px;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 320px;
            opacity: 0;
            transform: translateY(10px);
            transition: all .25s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .toast.error {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
    </style>
    <script>
        (function () {
            function showToast(msg, type) {
                if (!msg) return;
                var c = document.getElementById('toast-container');
                var t = document.createElement('div');
                t.className = 'toast ' + (type === 'error' ? 'error' : 'success');
                t.textContent = msg;
                c.appendChild(t);
                void t.offsetWidth;
                t.classList.add('show');
                setTimeout(function () { t.classList.remove('show'); setTimeout(function () { c.removeChild(t); }, 300); }, 4200);
            }
            <?php if (!empty($flash_success)): ?>
                showToast(<?php echo json_encode($flash_success); ?>, 'success');
            <?php endif; ?>
            <?php if (!empty($flash_error)): ?>
                showToast(<?php echo json_encode($flash_error); ?>, 'error');
            <?php endif; ?>
        })();
    </script>
    <script>
        // Activar animaciones de entrada con pequeño stagger
        document.addEventListener('DOMContentLoaded', function () {
            var items = document.querySelectorAll('.animate-item');
            items.forEach(function (el, idx) {
                setTimeout(function () { el.classList.add('enter'); }, idx * 120 + 60);
            });
        });
    </script>
</body>

</html>