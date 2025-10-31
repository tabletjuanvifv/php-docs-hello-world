
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

        $stmt = $pdo->prepare("INSERT INTO reservas(fecha_reserva, nombre, dni, telefono, numero_personas) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fecha, $nombre, $dni, $telefono, $numero_personas]);
    }

    // Obtener días ya reservados
    $stmt = $pdo->query("SELECT DISTINCT fecha_reserva FROM reservas ORDER BY fecha_reserva ASC");
    $fechasReservadas = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Error de conexión PDO: ' . $e->getMessage());
    echo "<div class='alert alert-danger'>Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas del Albergue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Reservas del Albergue</h1>

    <div class="card mb-5">
        <div class="card-header bg-primary text-white">Formulario de Reserva</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Fecha de reserva</label>
                    <input type="date" name="fecha_reserva" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Número de personas</label>
                    <input type="number" name="numero_personas" class="form-control" min="1" required>
                </div>
                <button type="submit" class="btn btn-success">Reservar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">Días ya reservados</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($fechasReservadas as $fila): ?>
                        <tr><td><?= htmlspecialchars($fila["fecha_reserva"]) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
