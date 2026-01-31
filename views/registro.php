<?php
// Mostrar mensajes según parámetros en la URL
$msg = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty':
            $msg = 'Por favor completa todos los campos.';
            break;
        case 'db':
            $msg = 'Error interno de base de datos. Intenta más tarde.';
            break;
        case 'exists':
            $msg = 'Ya existe una cuenta con esos datos.';
            break;
        case 'correo_exists':
            $msg = 'El correo ya está afiliado a otro usuario.';
            break;
        case 'cedula_exists':
            $msg = 'La cédula ya está afiliada a otro usuario.';
            break;
        default:
            $msg = 'Ocurrió un error. Intenta nuevamente.';
    }
} elseif (isset($_GET['registered'])) {
    $msg = 'Se ha registrado exitosamente.';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" href="../assets/img/icono.png">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>

<body class="fondo">
    <div class="contenedor animate-item stagger-1">
        <?php if ($msg !== ''): ?>
            <div class="animate-item stagger-2"
                style="margin-bottom:12px; padding:10px; background:rgba(255,255,255,0.12); border-radius:6px;">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>
        <?php
        session_start();
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        ?>
        <form action="../controllers/register_process.php" method="POST" class="animate-item stagger-3">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="text" name="cedula" placeholder="Cédula" required>
            <input type="email" name="correo" placeholder="Correo" required>

            <label>Carrera</label>
            <select name="carrera" id="carrera-select" required>
                <option value="">Seleccione carrera</option>
                <option value="ingenieria_sistemas">Ingeniería de Sistemas</option>
                <option value="ingenieria_mecanica">Ingeniería Mecánica</option>
                <option value="enfermeria">Enfermería</option>
                <option value="ingenieria_naval">Ingeniería Naval</option>
            </select>

            <label>Sección</label>
            <input type="text" name="seccion" id="seccion" placeholder="Sección (ej. A, B)" required>

            <label>Semestre</label>
            <select name="semestre" id="semestre-select" required>
                <option value="">Seleccione semestre</option>
            </select>

            <input type="password" name="password" placeholder="Contraseña" required>
            <button class="btn btn-animated" type="submit" name="registrar">Registrarse</button>
        </form>

        <div class="botones">
            <button type="button" class="btn-secundario btn-animated"
                onclick="window.location.href='../index.php'">Volver al inicio</button>
            <button type="button" class="btn-secundario btn-animated" onclick="window.location.href='login.php'">Iniciar
                sesión</button>
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
        // Mapeo de semestres por carrera
        (function(){
            var mapping = {
                'ingenieria_sistemas': 10,
                'ingenieria_mecanica': 10,
                'ingenieria_naval': 10,
                'enfermeria': 8
            };
            var carrera = document.getElementById('carrera-select');
            var semestre = document.getElementById('semestre-select');
            function populate() {
                var val = carrera.value;
                semestre.innerHTML = '<option value="">Seleccione semestre</option>';
                if (!mapping[val]) return;
                var max = mapping[val];
                for (var i=1;i<=max;i++){
                    var o = document.createElement('option'); o.value = i; o.textContent = i; semestre.appendChild(o);
                }
            }
            if (carrera) carrera.addEventListener('change', populate);
            // inicializar si ya hay valor
            populate();
        })();

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
    <script src="../assets/js/animations.js"></script>
</body>

</html>