<?php
// GESTIÓN DE INCIDENCIAS (EXCLUSIVO PARA ROLES TÉCNICOS/ADMIN)
session_start();
require_once 'config/conexion.php';

$mensaje_exito = '';
$mensaje_error = '';

// VALIDACIÓN DE PARÁMETRO URL
// Protege el archivo asegurándose de que reciba obligatoriamente una ID por GET (ej: gestionar_incidencia.php?id=5)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error crítico: No se ha especificado ninguna incidencia para gestionar.");
}

$id_incidencia = $_GET['id'];

// PROCESAMIENTO DE ACTUALIZACIÓN DE INCIDENCIA
// Captura el nuevo estado y la solución introducida por el técnico para guardarla en la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevo_estado = $_POST['estado'];
    $nueva_solucion = trim($_POST['solucion']);

    try {
        $sql_update = "UPDATE incidencias SET estado = :estado, solucion = :solucion WHERE id = :id";
        $stmt_update = $pdo->prepare($sql_update);
        
        $stmt_update->bindParam(':estado', $nuevo_estado, PDO::PARAM_STR);
        $stmt_update->bindParam(':solucion', $nueva_solucion, PDO::PARAM_STR);
        $stmt_update->bindParam(':id', $id_incidencia, PDO::PARAM_INT);
        
        if ($stmt_update->execute()) {
            $mensaje_exito = "La incidencia se ha actualizado correctamente.";
        } else {
            $mensaje_error = "Hubo un problema al actualizar la incidencia.";
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error de base de datos al actualizar.";
    }
}

// OBTENCIÓN DE DATOS DE LA INCIDENCIA ACTUAL
// Extrae toda la información de la incidencia seleccionada y el nombre del usuario que la creó usando un INNER JOIN
try {
    $sql_select = "SELECT incidencias.*, usuarios.nombre AS nombre_profesor 
                   FROM incidencias 
                   INNER JOIN usuarios ON incidencias.usuario_id = usuarios.id 
                   WHERE incidencias.id = :id";
                   
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->bindParam(':id', $id_incidencia, PDO::PARAM_INT);
    $stmt_select->execute();
    
    $incidencia = $stmt_select->fetch();

    // Si la ID no existe en la base de datos, detiene la carga
    if (!$incidencia) {
        die("Error: La incidencia solicitada no existe.");
    }

} catch (PDOException $e) {
    die("Error al consultar la base de datos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Incidencia - AsIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- LIBRERÍA DE ICONOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <a href="panel_tecnico.php" class="btn btn-outline-secondary mb-4 fw-bold"><i class="bi bi-arrow-left me-1"></i> Volver al Panel</a>
    
    <div class="row">
        <!-- COLUMNA IZQUIERDA: Detalles de solo lectura de la incidencia -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2 text-primary"></i> Detalles de la Avería #<?php echo $incidencia['id']; ?></h5>
                </div>
                <div class="card-body p-4">
                    <!-- Se muestran los datos extraídos previamente de la base de datos -->
                    <div class="mb-3">
                        <span class="text-muted d-block mb-1"><i class="bi bi-person me-1"></i> Profesor Reportador</span>
                        <span class="fw-semibold fs-5"><?php echo htmlspecialchars($incidencia['nombre_profesor']); ?></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <span class="text-muted d-block mb-1"><i class="bi bi-pc-display me-1"></i> Tipo de Equipo</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($incidencia['tipo_equipo']); ?></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block mb-1"><i class="bi bi-calendar3 me-1"></i> Fecha de reporte</span>
                            <span class="fw-semibold"><?php echo date('d/m/Y H:i', strtotime($incidencia['fecha_creacion'])); ?></span>
                        </div>
                    </div>
                    
                    <hr class="my-4 text-muted opacity-25">
                    
                    <p><strong class="text-muted"><i class="bi bi-card-text me-1"></i> Descripción del problema:</strong></p>
                    <div class="p-4 bg-light border rounded text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($incidencia['descripcion'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Formulario para el técnico -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-tools me-2 text-primary"></i> Actualizar Resolución</h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Mensajes de feedback -->
                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $mensaje_error; ?></div>
                    <?php endif; ?>

                    <!-- Formulario que permite cambiar el estado y registrar la solución. Envía datos a este mismo archivo por POST -->
                    <form action="gestionar_incidencia.php?id=<?php echo $incidencia['id']; ?>" method="POST">
                        
                        <div class="mb-4">
                            <label for="estado" class="form-label fw-semibold"><i class="bi bi-activity me-1"></i> Estado de la incidencia</label>
                            <!-- El estado actual de la incidencia queda pre-seleccionado -->
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="Pendiente" <?php echo ($incidencia['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En proceso" <?php echo ($incidencia['estado'] == 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                                <option value="Resuelto" <?php echo ($incidencia['estado'] == 'Resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="solucion" class="form-label fw-semibold"><i class="bi bi-chat-left-text me-1"></i> Solución aplicada (Opcional si está pendiente)</label>
                            <textarea class="form-control" id="solucion" name="solucion" rows="6" placeholder="Escribe aquí las acciones realizadas para solucionar el problema..."><?php echo htmlspecialchars($incidencia['solucion'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-save me-2"></i> Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>