<?php
// Recuperar variables de entorno
$dbHost = getenv('DB_HOST');
$dbName = "proyecto";
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

if (!$dbHost || !$dbUser || $dbPass === false) {
    throw new \RuntimeException('Faltan variables de entorno para la conexión a la base de datos.');
}

// DSN con charset utf8mb4
$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/BaltimoreCyberTrustRoot.crt.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

    // Procesar el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fecha = $_POST["fecha_reserva"];
        $nombre = $_POST["nombre"];
        $dni = $_POST["dni"];
        $telefono = $_POST["telefono"];
        $numero_personas = $_POST["numero_personas"];

        $stmt = $pdo->prepare("INSERT INTO reservas (fecha_reserva, nombre, dni, telefono, numero_personas) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fecha, $nombre, $dni, $telefono, $numero_personas]);
    }

    // Obtener días ya reservados
    $stmt = $pdo->query("SELECT DISTINCT fecha_reserva FROM reservas ORDER BY fecha_reserva ASC");
    $fechasReservadas = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Error de conexión PDO: ' . $e->getMessage());
    echo "Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas del Albergue</title>
</head>
<body>
    <h2>Formulario de Reserva</h2>
    <form method="POST">
        <label>Fecha de reserva: <input type="date" name="fecha_reserva" required></label><br>
        <label>Nombre: <input type="text" name="nombre" required></label><br>
        <label>DNI: <input type="text" name="dni" required></label><br>
        <label>Teléfono: <input type="text" name="telefono"></label><br>
        <label>Número de personas: <input type="number" name="numero_personas" min="1" required></label><br>
        <input type="submit" value="Reservar">
    </form>

    <h2>Días ya reservados</h2>
    <table border="1">
        <tr><th>Fecha</th></tr>
        <?php foreach ($fechasReservadas as $fila): ?>
            <tr><td><?= htmlspecialchars($fila["fecha_reserva"]) ?></td></tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
