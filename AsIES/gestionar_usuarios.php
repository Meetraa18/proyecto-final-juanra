<?php
// CONTROL DE ACCESO
session_start();
require_once 'config/conexion.php';

// Verificación básica de sesión iniciada
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Bloqueo estricto para Técnicos: no pueden gestionar usuarios
if ($_SESSION['rol'] == 'Técnico') {
    header("Location: panel_tecnico.php");
    exit();
}

$mensaje_exito = '';
$mensaje_error = '';

// PROCESAMIENTO DE CREACIÓN DE USUARIO
// Se ejecuta cuando el administrador o el profesor rellenan el formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Asignación de permisos según el creador: Un admin elige el rol, un usuario normal solo crea 'Alumnos'
    if ($_SESSION['rol'] == 'Administrador') {
        $rol_nuevo = $_POST['rol_nuevo'];
    } else {
        $rol_nuevo = 'Alumno';
    }

    // Comprobaciones de seguridad y relleno de campos
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
        $mensaje_error = "Por favor, rellena todos los campos.";
    } elseif ($password !== $confirm_password) {
        $mensaje_error = "Las contraseñas no coinciden.";
    } else {
        // Expresiones regulares para forzar una contraseña segura
        $tiene_longitud = strlen($password) >= 8;
        $tiene_mayuscula = preg_match('/[A-Z]/', $password);
        $tiene_numero = preg_match('/[0-9]/', $password);
        $tiene_especial = preg_match('/[\W_]/', $password);

        if (!$tiene_longitud || !$tiene_mayuscula || !$tiene_numero || !$tiene_especial) {
            $mensaje_error = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.";
        } else {
            try {
                // Comprobación previa para evitar registrar correos duplicados
                $sql_check = "SELECT id FROM usuarios WHERE email = :email";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_check->execute();

                if ($stmt_check->rowCount() > 0) {
                    $mensaje_error = "Este correo electrónico ya está registrado.";
                } else {
                    // Encriptación segura de la contraseña antes de guardarla
                    $password_encriptada = password_hash($password, PASSWORD_DEFAULT);

                    // Inserción del nuevo usuario en la base de datos
                    $sql_insert = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";
                    $stmt_insert = $pdo->prepare($sql_insert);
                    
                    $stmt_insert->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':password', $password_encriptada, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':rol', $rol_nuevo, PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $mensaje_exito = "Usuario ($rol_nuevo) registrado correctamente en el sistema.";
                    } else {
                        $mensaje_error = "Hubo un problema al registrar el usuario.";
                    }
                }
            } catch (PDOException $e) {
                $mensaje_error = "Error de base de datos al registrar.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - AsIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- LIBRERÍA DE ICONOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-5">
    
    <!-- Renderizado condicional del botón de 'volver' dependiendo de quién esté navegando -->
    <?php if ($_SESSION['rol'] == 'Administrador'): ?>
        <a href="panel_tecnico.php" class="btn btn-outline-secondary mb-4 fw-bold"><i class="bi bi-arrow-left me-1"></i> Volver al Panel Admin</a>
    <?php else: ?>
        <a href="crear_incidencia.php" class="btn btn-outline-secondary mb-4 fw-bold"><i class="bi bi-arrow-left me-1"></i> Volver a Mis Incidencias</a>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-person-plus me-2 text-primary"></i>
                        <!-- Título adaptativo según el rol de sesión -->
                        <?php echo ($_SESSION['rol'] == 'Administrador') ? 'Crear Nuevo Usuario (Admin)' : 'Registrar Nuevo Alumno'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Mensajes informativos de proceso -->
                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>

                    <!-- Formulario de creación de usuario -->
                    <form action="gestionar_usuarios.php" method="POST" id="formRegistro">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-semibold"><i class="bi bi-person me-1"></i> Nombre y Apellidos</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <!-- Selector de roles: Solo visible para los Administradores -->
                        <?php if ($_SESSION['rol'] == 'Administrador'): ?>
                            <div class="mb-3">
                                <label for="rol_nuevo" class="form-label fw-semibold"><i class="bi bi-diagram-3 me-1"></i> Rol del Usuario</label>
                                <select class="form-select" id="rol_nuevo" name="rol_nuevo" required>
                                    <option value="Alumno">Alumno</option>
                                    <option value="Profesor">Profesor</option>
                                    <option value="Técnico">Técnico</option>
                                    <option value="Administrador">Administrador</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <!-- Si es profesor, solo ve un aviso informativo de que registrará un 'Alumno' por defecto -->
                            <div class="alert alert-secondary py-2 border-0 d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <span><strong>Rol asignado:</strong> Alumno (Por defecto)</span>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold"><i class="bi bi-key me-1"></i> Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="confirm_password" class="form-label fw-semibold"><i class="bi bi-shield-check me-1"></i> Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <div id="errorCoincidencia" class="text-danger mt-1" style="display: none; font-size: 0.9em;">
                                    <i class="bi bi-x-circle me-1"></i> Las contraseñas no coinciden.
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-text text-muted mb-4" style="font-size: 0.85em;">
                            <i class="bi bi-shield-lock me-1"></i> La contraseña debe contener al menos: 8 caracteres, 1 mayúscula, 1 número y 1 carácter especial.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-person-check me-2"></i> Registrar Usuario</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT DE VALIDACIÓN FRONTAL PARA CONTRASEÑAS -->
<script>
    const formRegistro = document.getElementById('formRegistro');
    const inputPassword = document.getElementById('password');
    const inputConfirm = document.getElementById('confirm_password');
    const errorCoincidencia = document.getElementById('errorCoincidencia');
    
    // Validación de coincidencia de contraseñas en tiempo real antes de enviar formulario al servidor
    formRegistro.addEventListener('submit', function(evento) {
        if (inputPassword.value !== inputConfirm.value) {
            evento.preventDefault();
            errorCoincidencia.style.display = 'block';
            inputConfirm.classList.add('is-invalid'); // Juanra
    // Si las contraseñas coinciden, se oculta el mensaje de error y se permite el envío del formulario
        } else {
            errorCoincidencia.style.display = 'none';
            inputConfirm.classList.remove('is-invalid');
        }
    });
</script>

</body>
</html>