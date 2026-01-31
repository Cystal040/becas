<?php
session_start();
include_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Flash messages
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new_user = trim($_POST['new_usuario'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '') {
        $_SESSION['flash_error'] = 'Introduce la contraseña actual.';
        header('Location: cambiar_credenciales.php');
        exit;
    }

    // Obtener hash actual
    $stmt = $conn->prepare('SELECT usuario, password FROM administrador WHERE id_admin = ? LIMIT 1');
    if (!$stmt) {
        $_SESSION['flash_error'] = 'Error interno.';
        header('Location: cambiar_credenciales.php');
        exit;
    }
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $admin = $res->fetch_assoc();
    $stmt->close();

    if (!$admin || !password_verify($current, $admin['password'])) {
        $_SESSION['flash_error'] = 'Contraseña actual incorrecta.';
        header('Location: cambiar_credenciales.php');
        exit;
    }

    $updated = false;

    // Actualizar usuario si se proporcionó y es distinto
    if ($new_user !== '' && $new_user !== $admin['usuario']) {
        $u_stmt = $conn->prepare('UPDATE administrador SET usuario = ? WHERE id_admin = ?');
        if ($u_stmt) {
            $u_stmt->bind_param('si', $new_user, $admin_id);
            if ($u_stmt->execute()) {
                $_SESSION['admin_usuario'] = $new_user;
                $updated = true;
            }
            $u_stmt->close();
        }
    }

    // Actualizar contraseña si se proporcionó
    if ($new_pass !== '') {
        if ($new_pass !== $confirm) {
            $_SESSION['flash_error'] = 'La nueva contraseña y la confirmación no coinciden.';
            header('Location: cambiar_credenciales.php');
            exit;
        }
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $p_stmt = $conn->prepare('UPDATE administrador SET password = ? WHERE id_admin = ?');
        if ($p_stmt) {
            $p_stmt->bind_param('si', $hash, $admin_id);
            if ($p_stmt->execute()) {
                $updated = true;
            }
            $p_stmt->close();
        }
    }

    if ($updated) {
        $_SESSION['flash_success'] = 'Credenciales actualizadas correctamente.';
    } else {
        $_SESSION['flash_error'] = 'No hubo cambios para guardar.';
    }
    header('Location: admin_panel.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cambiar credenciales</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="icon" href="../assets/img/icono.png">
</head>
<body class="fondo">
    <div class="contenedor">
        <h2>Cambiar usuario y/o contraseña</h2>

        <?php if (!empty($flash_success)): ?>
            <div class="card" style="border-left:4px solid #2ecc71; padding:10px; margin-bottom:12px; color:#fff; background:transparent;">
                <?php echo htmlspecialchars($flash_success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="card" style="border-left:4px solid #e74c3c; padding:10px; margin-bottom:12px; color:#fff; background:transparent;">
                <?php echo htmlspecialchars($flash_error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="cambiar_credenciales.php" style="max-width:520px;">
            <div class="card">
                <label>Contraseña actual</label>
                <input type="password" name="current_password" required>

                <label>Nuevo usuario (opcional)</label>
                <input type="text" name="new_usuario" placeholder="Nuevo usuario">

                <label>Nueva contraseña (opcional)</label>
                <input type="password" name="new_password" placeholder="Nueva contraseña">

                <label>Confirmar nueva contraseña</label>
                <input type="password" name="confirm_password" placeholder="Confirmar contraseña">

                <div style="margin-top:12px;display:flex;gap:8px;">
                    <button class="btn" type="submit">Guardar cambios</button>
                    <a class="btn-secundario" href="admin_panel.php">Volver</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
