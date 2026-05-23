<?php
// INICIO DE SESIÓN Y CONTROL DE ACCESO
// Verifica si ya hay una sesión activa. Si es así, redirige al usuario a su panel correspondiente según su rol,
// evitando que vuelva a ver la pantalla de login.
session_start();

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'Administrador') {
        header("Location: panel_tecnico.php");
        exit();
    } else {
        header("Location: crear_incidencia.php");
        exit();
    }
}

// CONEXIÓN A BASE DE DATOS
require_once 'config/conexion.php';
$mensaje_error = '';

// PROCESAMIENTO DEL FORMULARIO DE LOGIN
// Solo se ejecuta si el usuario ha enviado datos mediante el método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Busca al usuario en la base de datos a partir de su correo electrónico.
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch();

        // VALIDACIÓN DE CREDENCIALES Y ROL
        if ($usuario) {
            if (password_verify($password, $usuario['password'])) {
                
                // CANDADO ESTRICTO PARA ADMINS (La lógica interna no cambia)
                // Solo permite el acceso si el rol del usuario es 'Administrador'.
                if ($usuario['rol'] != 'Administrador') {
                    $mensaje_error = "Acceso denegado. Este portal es exclusivo para Coordinación.";
                } else {
                    // Si todo es correcto, guarda los datos en la sesión y redirige al panel técnico.
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['rol'] = $usuario['rol'];

                    header("Location: panel_tecnico.php");
                    exit();
                }
            } else {
                $mensaje_error = "Credenciales incorrectas.";
            }
        } else {
            $mensaje_error = "Credenciales incorrectas.";
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error de conexión con la base de datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- TÍTULO CAMUFLADO -->
    <title>Coordinación  - AsIES</title>
    <!-- CARGA DE ESTILOS Y FUENTES (Bootstrap e Iconos) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ESTILOS ESPECÍFICOS DEL LOGIN */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #F7F5F0 !important;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        .logo-login {
            max-width: 160px;
            height: auto;
            margin-bottom: 1.5rem;
        }
        .card-login {
            border-top: 4px solid #464543 !important; 
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="text-center mb-3">
        <img src="assets/img/logo.png" alt="Logo AsIES" class="logo-login">
    </div>

    <div class="card card-login shadow-lg border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <!-- TEXTOS CAMUFLADOS -->
                <h4 class="fw-bold text-dark mb-1">Área de Coordinación</h4>
                <span class="badge bg-dark"><i class="bi bi-shield-lock me-1"></i> Zona Restringida</span>
            </div>
            
            <!-- MOSTRAR MENSAJES DE ERROR SI EXISTEN -->
            <?php if (!empty($mensaje_error)): ?>
                <div class="alert alert-danger text-center"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <!-- FORMULARIO APUNTANDO AL NUEVO ARCHIVO -->
            <!-- Envia los datos a este mismo archivo para que el bloque PHP de arriba los procese -->
            <form action="coordinacion.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold"><i class="bi bi-envelope-paper me-1"></i> Correo de Coordinación</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold"><i class="bi bi-key-fill me-1"></i> Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-shield-check me-2"></i> Acceso Seguro</button>
            </form>
            
            <!-- ENLACE PARA VOLVER AL LOGIN GENERAL -->
            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none text-muted fw-semibold" style="font-size: 0.9rem; transition: color 0.3s;">
                    <i class="bi bi-arrow-left me-1"></i> Volver al acceso general
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>