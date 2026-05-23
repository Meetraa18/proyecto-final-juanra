<?php
// PROCESO DE CIERRE DE SESIÓN
// 1. Iniciamos la sesión para poder acceder a ella y destruirla
session_start();

// 2. Vaciamos todas las variables de sesión (id, nombre, rol...) para limpiar los datos del usuario logueado
$_SESSION = array();

// 3. Destruimos la sesión en el servidor borrando las cookies de sesión temporal
session_destroy();

// 4. Redirigimos al usuario a la pantalla de login como invitado
header("Location: login.php");
exit();
?>