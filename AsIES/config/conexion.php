<?php
// Iniciamos un bloque try-catch para intentar conectar y capturar posibles errores sin que la web "explote"
try {
    // Comprobamos si estamos trabajando en nuestro ordenador local (localhost)
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        // --- Configuración para entorno LOCAL (XAMPP) ---
        $host = 'localhost'; 
        $dbname = 'helpdesk_db'; 
        $username = 'root'; 
        $password = '';                             //Juanra
    } else {                                
        // --- Configuración para entorno REMOTO (AwardSpace) ---
        // Datos extraídos exactamente de tu captura de pantalla
        $host = 'fdb1032.awardspace.net'; // Host remoto de AwardSpace
        $dbname = '4761038_mibd'; // Nombre de la BD remota
        $username = '4761038_mibd'; // Usuario remoto
        $password = 'AsIESJuanra19!'; // Contraseña remota que creaste
    }

    // Creamos la cadena de conexión (DSN - Data Source Name)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    // Creamos un array con opciones adicionales para configurar PDO
    $opciones =[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Instanciamos el objeto PDO (creamos la conexión real a la base de datos)
    $pdo = new PDO($dsn, $username, $password, $opciones);

} catch (PDOException $e) {
    // En un TFG/Producción NUNCA se debe mostrar $e->getMessage() al usuario final porque revela datos del servidor
    die("Error crítico: No se pudo conectar a la base de datos. Contacte con el administrador.");
}
?>