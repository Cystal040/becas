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
        body.fondo { display:flex; align-items:center; justify-content:center; padding:0; }
        .contenedor { margin:0; width:100%; max-width:520px; }
    </style>
</head>

<body class="fondo">

    <div class="contenedor">
        <div class="login-header" style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
            <a href="../index.php" style="display:inline-block;">
                <img src="../assets/img/icono.png" alt="UNEFA" style="height:58px;object-fit:contain;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.12);">
            </a>
            <div>
                <h2 style="margin:0;">Portal de Becas UNEFA</h2>
                <p style="margin:2px 0 0 0;color:var(--muted);font-size:0.95rem;">Inicia sesión para gestionar tus documentos</p>
            </div>
        </div>

        <?php
        session_start();
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        ?>

        <div class="login-card" style="background:rgba(255,255,255,0.02);padding:18px;border-radius:8px;box-shadow:0 6px 24px rgba(0,0,0,0.08);max-width:420px;">
            <form action="../controllers/login_process.php" method="POST">

                <label>Usuario o correo:</label>
                <input type="text" name="user" placeholder="Usuario o correo" required>

                <label>Contraseña:</label>
                <input type="password" name="password" required>

                <div class="botones" style="display:flex;gap:10px;margin-top:10px;">
                    <button class="btn" type="submit">Entrar</button>
                    <button class="btn-secundario" type="button" onclick="window.location.href='../index.php'">Inicio</button>
                    <a class="btn" href="registro.php" style="background:linear-gradient(90deg,#3498db,#2980b9);">Registrarse</a>
                </div>
            </form>

            <div style="margin-top:12px; display:flex;justify-content:space-between;align-items:center;font-size:0.95rem;">
                <a href="documentos.php">Documentos requeridos</a>
                <a href="#" onclick="alert('Contacto: soporte@unefa.edu.ve')">Ayuda</a>
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
</body>

</html>