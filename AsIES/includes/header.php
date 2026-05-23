<!-- includes/header.php -->
<!-- BARRA DE NAVEGACIÓN SUPERIOR GLOBAL -->
<!-- Menú con fondo blanco y sombra suave -->
<nav class="navbar navbar-expand-lg mb-5 shadow-sm" style="background-color: #ffffff;">
    <div class="container">
        
        <!-- LOGO DE AsIES -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="assets/img/logo.png" alt="Logo AsIES" height="45" class="me-2">
        </a>
        
        <!-- Botón para expandir menú en formato móvil -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- LISTADO DE ENLACES DINÁMICOS SEGÚN EL ROL DE SESIÓN -->
            <ul class="navbar-nav me-auto">
                
                <!-- ENLACES PARA PROFESORES (Solo pueden ver la creación y registrar alumnos) -->
                <?php if ($_SESSION['rol'] == 'Profesor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="crear_incidencia.php"><i class="bi bi-plus-circle me-1"></i> Reportar Avería</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestionar_usuarios.php"><i class="bi bi-person-plus me-1"></i> Registrar Alumnos</a>
                    </li>
                <?php endif; ?>

                <!-- ENLACES PARA TÉCNICOS Y ALUMNOS (Solo ven el panel general de resolución) -->
                <?php if ($_SESSION['rol'] == 'Técnico' || $_SESSION['rol'] == 'Alumno'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="panel_tecnico.php"><i class="bi bi-tools me-1"></i> Panel de Averías</a>
                    </li>
                <?php endif; ?>

                <!-- ENLACES PARA ADMINISTRADORES (Acceso total, panel técnico y creación de todo tipo de usuarios) -->
                <?php if ($_SESSION['rol'] == 'Administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="panel_tecnico.php"><i class="bi bi-tools me-1"></i> Panel de Averías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gestionar_usuarios.php"><i class="bi bi-people me-1"></i> Gestión de Usuarios</a>
                    </li>
                <?php endif; ?>

            </ul>
            
            <!-- ZONA DE PERFIL DE USUARIO ACTIVO (Arriba a la derecha) -->
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <span class="text-dark me-4 fw-medium d-flex align-items-center">
                    <i class="bi bi-person-circle fs-5 text-muted me-2"></i> 
                    <!-- Imprime el nombre y el rol con el que se está navegando actualmente -->
                    <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                    <span class="badge bg-light text-dark border ms-2"><?php echo $_SESSION['rol']; ?></span>
                </span>
                <!-- Botón de salir minimalista. Apunta a logout.php para destruir variables -->
                <a href="logout.php" class="btn btn-sm btn-outline-danger fw-bold px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>