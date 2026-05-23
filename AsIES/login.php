<?php
// COMPROBACIÓN DE SESIÓN INICIAL (Login General)
session_start();

// Si el usuario ya está logueado, lo envía directamente a su panel correspondiente según su rol
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'Técnico' || $_SESSION['rol'] == 'Administrador' || $_SESSION['rol'] == 'Alumno') {
        header("Location: panel_tecnico.php");
        exit();
    } else if ($_SESSION['rol'] == 'Profesor') {
        header("Location: crear_incidencia.php");
        exit();
    }
}

require_once 'config/conexion.php';
$mensaje_error = '';

// PROCESAMIENTO DEL FORMULARIO DE ACCESO GENERAL
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Busca en base de datos si existe el correo enviado
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch();

        // VALIDACIÓN DE USUARIO Y CONTRASEÑA
        if ($usuario) {
            if (password_verify($password, $usuario['password'])) {
                
                // Si es Administrador, no le dejamos entrar por aquí. Tienen que usar su enlace camuflado.
                if ($usuario['rol'] == 'Administrador') {
                    $mensaje_error = "Acceso denegado. Los coordinadores deben usar el portal seguro.";
                } else {
                    // Establecer variables de sesión para el resto de la app
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['rol'] = $usuario['rol'];

                    // Redirecciones post-login según el rol
                    if ($usuario['rol'] == 'Técnico' || $usuario['rol'] == 'Alumno') {
                        header("Location: panel_tecnico.php");
                    } else {
                        header("Location: crear_incidencia.php");
                    }
                    exit();
                }
            } else {
                $mensaje_error = "Contraseña incorrecta.";
            }
        } else {
            $mensaje_error = "No existe ningún usuario con este correo electrónico.";
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
    <title>Iniciar Sesión - AsIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Estilos específicos para la pantalla de Login -->
    <style>
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
                <h4 class="fw-bold text-dark mb-1">Acceso al Sistema</h4>
                <span class="badge bg-light text-dark border">Portal General</span>
            </div>
            
            <?php if (!empty($mensaje_error)): ?>
                <div class="alert alert-danger text-center"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $mensaje_error; ?></div>
            <?php endif; ?>

            <!-- FORMULARIO DE LOGIN NORMAL -->
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@ies.com" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold"><i class="bi bi-key me-1"></i> Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i> Entrar</button>
            </form>
            
            <div class="text-center mt-4">
                <!-- ENLACE OCULTO HACIA EL PORTAL DE COORDINACIÓN -->
                <a href="coordinacion.php" class="text-decoration-none text-muted fw-semibold" style="font-size: 0.9rem; transition: color 0.3s;">
                    <i class="bi bi-shield-lock text-danger me-1"></i> ¿Eres Coordinador ? Entra aquí
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>