<?php
// CONTROL DE ACCESO AL PANEL TÉCNICO
session_start();
require_once 'config/conexion.php';

// Verificación obligatoria de inicio de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Filtro de roles: Los profesores NO tienen acceso aquí, los devuelve a "crear_incidencia"
if ($_SESSION['rol'] != 'Técnico' && $_SESSION['rol'] != 'Administrador' && $_SESSION['rol'] != 'Alumno') {
    header("Location: crear_incidencia.php");
    exit();
}

// CARGA DE DATOS PARA EL DASHBOARD ESTADÍSTICO
try {
    // Cuenta el total absoluto de incidencias en toda la plataforma
    $stmt_total = $pdo->query("SELECT COUNT(*) as total FROM incidencias");
    $total_incidencias = $stmt_total->fetch()['total'];

    // Cuenta cuántas están actualmente en estado "Pendiente"
    $stmt_pendientes = $pdo->query("SELECT COUNT(*) as pendientes FROM incidencias WHERE estado = 'Pendiente'");
    $total_pendientes = $stmt_pendientes->fetch()['pendientes'];

    // Cuenta cuántas están actualmente resueltas
    $stmt_resueltas = $pdo->query("SELECT COUNT(*) as resueltas FROM incidencias WHERE estado = 'Resuelto'");
    $total_resueltas = $stmt_resueltas->fetch()['resueltas'];

    // CONSULTA GLOBAL DE INCIDENCIAS PARA LA TABLA INFERIOR
    // Se cruzan los datos de incidencias con los datos de usuarios (quién la creó) usando INNER JOIN
    $sql = "SELECT 
                incidencias.id, 
                incidencias.tipo_equipo, 
                incidencias.descripcion, 
                incidencias.estado, 
                incidencias.fecha_creacion, 
                usuarios.nombre AS nombre_profesor 
            FROM incidencias
            INNER JOIN usuarios ON incidencias.usuario_id = usuarios.id
            ORDER BY incidencias.fecha_creacion DESC"; 
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $incidencias = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error al obtener los datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AsIES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- LIBRERÍA DE ICONOS (Bootstrap Icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- NUESTRO CSS PERSONALIZADO -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-speedometer2 me-2 text-muted"></i> Dashboard Técnico</h3>
    </div>
    
    <!-- TARJETAS DE ESTADÍSTICAS CON ICONOS (Arriba de la tabla) -->
    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body text-center py-4 position-relative overflow-hidden">
                    <!-- Icono de fondo translúcido -->
                    <i class="bi bi-hdd-stack position-absolute opacity-25" style="font-size: 6rem; right: -10px; bottom: -20px;"></i>
                    <h6 class="card-title text-uppercase opacity-75 fw-semibold letter-spacing-1">Total Averías</h6>
                    <h1 class="display-3 fw-bold mb-0 position-relative"><?php echo $total_incidencias; ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #d9534f;">
                <div class="card-body text-center py-4 position-relative overflow-hidden">
                    <i class="bi bi-exclamation-octagon position-absolute opacity-25" style="font-size: 6rem; right: -10px; bottom: -20px;"></i>
                    <h6 class="card-title text-uppercase opacity-75 fw-semibold letter-spacing-1">Pendientes</h6>
                    <h1 class="display-3 fw-bold mb-0 position-relative"><?php echo $total_pendientes; ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white h-100 border-0 shadow-sm" style="background-color: #5cb85c;">
                <div class="card-body text-center py-4 position-relative overflow-hidden">
                    <i class="bi bi-check2-circle position-absolute opacity-25" style="font-size: 6rem; right: -10px; bottom: -20px;"></i>
                    <h6 class="card-title text-uppercase opacity-75 fw-semibold letter-spacing-1">Resueltas</h6>
                    <h1 class="display-3 fw-bold mb-0 position-relative"><?php echo $total_resueltas; ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA GLOBAL DE INCIDENCIAS (Bandeja de entrada del soporte técnico) -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2"></i> Listado de Incidencias</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Profesor</th>
                            <th>Equipo</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="pe-4 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Recorre todos los registros en base de datos previamente sacados e imprime filas -->
                        <?php if (count($incidencias) > 0): ?>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?php echo $incidencia['id']; ?></td>
                                    <td class="fw-semibold">
                                        <i class="bi bi-person text-muted me-1"></i> <?php echo htmlspecialchars($incidencia['nombre_profesor']); ?>
                                    </td>
                                    <td>
                                        <!-- Icono dinámico según el tipo de equipo -->
                                        <?php if($incidencia['tipo_equipo'] == 'Portátil'): ?>
                                            <i class="bi bi-laptop text-muted me-1"></i>
                                        <?php else: ?>
                                            <i class="bi bi-pc-display text-muted me-1"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($incidencia['tipo_equipo']); ?>
                                    </td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 250px;"><?php echo htmlspecialchars($incidencia['descripcion']); ?></span></td>
                                    <td><i class="bi bi-calendar3 text-muted me-1"></i> <?php echo date('d/m/Y', strtotime($incidencia['fecha_creacion'])); ?></td>
                                    <td>
                                        <!-- Lógica de badges visuales dependiendo del estado -->
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
                                    <td class="pe-4 text-end">
                                        <!-- Botón de acción que manda el ID a gestionar_incidencia -->
                                        <a href="gestionar_incidencia.php?id=<?php echo $incidencia['id']; ?>" class="btn btn-sm btn-primary fw-bold">
                                            <i class="bi bi-pencil-square me-1"></i> Gestionar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Tabla vacía en caso de que no haya registros en todo el sistema -->
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                    No hay incidencias registradas en este momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>