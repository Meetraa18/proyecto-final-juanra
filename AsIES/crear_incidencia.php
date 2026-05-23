<?php
// CONTROL DE ACCESO
// Comprueba si el usuario está logueado. Si no es así, lo expulsa a la pantalla de login.
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/conexion.php';

$mensaje_exito = '';
$mensaje_error = '';
$usuario_id = $_SESSION['usuario_id']; 

// PROCESAMIENTO DE CREACIÓN DE NUEVA INCIDENCIA
// Solo entra aquí si el usuario envía el formulario para reportar una avería.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_equipo = trim($_POST['tipo_equipo']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validación básica de campos vacíos
    if (empty($tipo_equipo) || empty($descripcion)) {
        $mensaje_error = "Por favor, rellena todos los campos obligatorios.";
    } else {
        try {
            // Inserta la nueva incidencia en la base de datos asociándola al usuario actual
            $sql = "INSERT INTO incidencias (usuario_id, tipo_equipo, descripcion) VALUES (:usuario_id, :tipo_equipo, :descripcion)";
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_equipo', $tipo_equipo, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $mensaje_exito = "Incidencia reportada correctamente.";
            } else {
                $mensaje_error = "Hubo un problema al guardar la incidencia.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Error de base de datos: No se pudo registrar la incidencia.";
        }
    }
}

// OBTENCIÓN DEL HISTORIAL DE INCIDENCIAS DEL USUARIO ACTUAL
// Carga todas las incidencias creadas por el usuario que tiene iniciada la sesión para mostrarlas en la tabla.
try {
    $sql_mis_incidencias = "SELECT id, tipo_equipo, descripcion, estado, solucion, fecha_creacion 
                            FROM incidencias 
                            WHERE usuario_id = :usuario_id 
                            ORDER BY fecha_creacion DESC";
    $stmt_mis = $pdo->prepare($sql_mis_incidencias);
    $stmt_mis->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt_mis->execute();
    
    $mis_incidencias = $stmt_mis->fetchAll();
} catch (PDOException $e) {
    die("Error al cargar el historial de incidencias.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Incidencias - AsIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- LIBRERÍA DE ICONOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- INCLUSIÓN DEL MENÚ DE NAVEGACIÓN GLOBAL -->
<?php include 'includes/header.php'; ?>

<div class="container-fluid px-4 mb-5">
    <div class="row">
        
        <!-- COLUMNA IZQUIERDA: Formulario para crear incidencia -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-square me-2 text-primary"></i> Reportar Nueva Avería</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Mensajes de feedback (Éxito o error) al enviar el formulario -->
                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>

                    <!-- Formulario de creación -->
                    <form action="crear_incidencia.php" method="POST" id="formIncidencia">
                        <div class="mb-3">
                            <label for="tipo_equipo" class="form-label fw-semibold"><i class="bi bi-pc-display me-1"></i> Tipo de Equipo <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                                <option value="">Seleccione una opción...</option>
                                <option value="Portátil">Portátil</option>
                                <option value="Torre">Torre</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold"><i class="bi bi-card-text me-1"></i> Descripción del problema <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="5" placeholder="Ej: El equipo no enciende, la pantalla parpadea..." required></textarea>
                            <div id="errorDescripcion" class="text-danger mt-1" style="display: none; font-size: 0.9em;"><i class="bi bi-x-circle me-1"></i> La descripción debe tener al menos 10 caracteres.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-send me-2"></i> Enviar Incidencia</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Historial de incidencias del usuario -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2"></i> Mi Historial de Averías</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Equipo</th>
                                    <th>Descripción</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="pe-4">Respuesta del Técnico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recorre el array con las incidencias del usuario y genera una fila por cada una -->
                                <?php if (count($mis_incidencias) > 0): ?>
                                    <?php foreach ($mis_incidencias as $incidencia): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted">#<?php echo $incidencia['id']; ?></td>
                                            <td>
                                                <!-- Icono dinámico según el equipo -->
                                                <?php if($incidencia['tipo_equipo'] == 'Portátil'): ?>
                                                    <i class="bi bi-laptop text-muted me-1"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-pc-display text-muted me-1"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($incidencia['tipo_equipo']); ?>
                                            </td>
                                            <td><span class="text-truncate d-inline-block" style="max-width: 200px;"><?php echo htmlspecialchars($incidencia['descripcion']); ?></span></td>
                                            <td><i class="bi bi-calendar3 text-muted me-1"></i> <?php echo date('d/m/Y', strtotime($incidencia['fecha_creacion'])); ?></td>
                                            <td>
                                                <!-- Lógica para aplicar un color diferente al badge dependiendo del estado de la incidencia -->
                                                <?php
                                                $color_badge = 'bg-secondary';
                                                $icono_estado = 'bi-clock';
                                                if ($incidencia['estado'] == 'Pendiente') { $color_badge = 'bg-danger'; $icono_estado = 'bi-exclamation-circle'; }
                                                if ($incidencia['estado'] == 'En proceso') { $color_badge = 'bg-warning text-dark'; $icono_estado = 'bi-gear-wide-connected'; }
                                                if ($incidencia['estado'] == 'Resuelto') { $color_badge = 'bg-success'; $icono_estado = 'bi-check-circle'; }
                                                ?>
                                                <span class="badge <?php echo $color_badge; ?> px-2 py-1">
                                                    <i class="bi <?php echo $icono_estado; ?> me-1"></i> <?php echo $incidencia['estado']; ?>
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <!-- Muestra la solución si existe, si no, un mensaje por defecto -->
                                                <small class="text-muted">
                                                    <?php if(!empty($incidencia['solucion'])): ?>
                                                        <i class="bi bi-chat-left-text me-1"></i> <?php echo htmlspecialchars(substr($incidencia['solucion'], 0, 30)) . '...'; ?>
                                                    <?php else: ?>
                                                        <span class="opacity-50">- Aún sin respuesta -</span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Si no hay registros, muestra un mensaje vacío en la tabla -->
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                            Aún no has reportado ninguna avería.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- SCRIPT DE VALIDACIÓN FRONTAL -->
<!-- Verifica que la descripción de la avería tenga al menos 10 caracteres antes de enviarse al servidor -->
<script>
    const formulario = document.getElementById('formIncidencia');
    formulario.addEventListener('submit', function(evento) {
        const descripcion = document.getElementById('descripcion').value.trim();
        const errorDiv = document.getElementById('errorDescripcion');
        if (descripcion.length < 10) {
            evento.preventDefault();
            errorDiv.style.display = 'block';
            document.getElementById('descripcion').classList.add('is-invalid');
        } else {
            errorDiv.style.display = 'none';
            document.getElementById('descripcion').classList.remove('is-invalid');
        }
    });
</script>

</body>
</html>