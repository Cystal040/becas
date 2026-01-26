<?php include("../config/conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>

<body class="fondo">

<div class="contenedor">
    <h2>Iniciar sesión</h2>
    <?php
    session_start();
    $flash_success = $_SESSION['flash_success'] ?? null;
    $flash_error = $_SESSION['flash_error'] ?? null;
    unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    ?>

    <form action="../controllers/login_process.php" method="POST">

        <label>Usuario o correo:</label>
        <input type="text" name="user" placeholder="Usuario o correo" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <div class="botones">
            <button class="btn" type="submit">Entrar</button>
            <button class="btn-secundario" type="button" onclick="window.location.href='../index.php'">Volver al inicio</button>
        </div>
    </form>

    <div style="margin-top:12px; text-align:center;">
        <a href="registro.php">Crear cuenta</a>
    </div>
</div>

    <!-- Toast container -->
    <div id="toast-container" aria-live="polite" style="position:fixed;right:16px;bottom:16px;z-index:9999"></div>
    <style>
    .toast { background:#333;color:#fff;padding:10px 14px;border-radius:6px;margin-top:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);max-width:320px;opacity:0;transform:translateY(10px);transition:all .25s ease; }
    .toast.show { opacity:1; transform:translateY(0); }
    .toast.success { background: linear-gradient(90deg,#2ecc71,#27ae60); }
    .toast.error { background: linear-gradient(90deg,#e74c3c,#c0392b); }
    </style>
    <script>
    (function(){
        function showToast(msg, type){
            if(!msg) return;
            var c = document.getElementById('toast-container');
            var t = document.createElement('div');
            t.className = 'toast ' + (type==='error' ? 'error' : 'success');
            t.textContent = msg;
            c.appendChild(t);
            void t.offsetWidth;
            t.classList.add('show');
            setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ c.removeChild(t); },300); }, 4200);
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