<?php
// Logout unificado: limpia sesión de admin y/o estudiante y redirige al login unificado
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Si quieres mantener otros datos en sesión (por ejemplo configuración), solo eliminamos los keys de usuario
unset($_SESSION['admin_id'], $_SESSION['admin_usuario'], $_SESSION['usuario_id'], $_SESSION['usuario_nombre']);

// Regenerar id de sesión para mitigar fijación
session_regenerate_id(true);

// Redirigir al login unificado. Si prefieres la página de inicio usa 'index.php'.
header('Location: views/login.php');
exit;
?>