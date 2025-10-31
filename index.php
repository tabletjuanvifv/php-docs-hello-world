<?php
// Conexión PDO con variables de entorno
$dbHost = getenv('DB_HOST');
$dbName = "proyecto";
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

if (!$dbHost || !$dbUser || $dbPass === false) {
    throw new \RuntimeException('Faltan variables de entorno para la conexión a la base de datos.');
}

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

    // Procesar formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $pdo->prepare("INSERT INTO reservas (fecha_reserva, nombre, dni, telefono, numero_personas) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST["fecha_reserva"],
            $_POST["nombre"],
            $_POST["dni"],
            $_POST["telefono"],
            $_POST["numero_personas"]
        ]);
    }

    // Obtener fechas reservadas
    $stmt = $pdo->query("SELECT DISTINCT fecha_reserva FROM reservas");
    $fechas = $stmt->fetchAll();
    $reservadas = [];
    foreach ($fechas as $f) {
        $fecha = new DateTime($f["fecha_reserva"]);
        $reservadas[] = [$fecha->format("n"), $fecha->format("j")]; // mes, día
    }

} catch (PDOException $e) {
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
    <style>
        .reservado { color: red; font-weight: bold; }
        table.calendar { border-collapse: collapse; margin-bottom: 40px; }
        table.calendar td, table.calendar th { border: 1px solid #ccc; padding: 5px; text-align: center; width: 40px; height: 40px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Calendario de Reservas 2025</h1>

    <?php
    $anio = 2025;
    $diasSemana = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];

    for ($mes = 1; $mes <= 12; $mes++) {
        echo "<h3>" . ucfirst(strftime('%B', mktime(0, 0, 0, $mes, 1))) . "</h3>";
        echo "<table class='calendar table table-bordered'><tr>";
        foreach ($diasSemana as $dia) {
            echo "<th>$dia</th>";
        }
        echo "</tr><tr>";

        $cal = calendar::monthcalendar($anio, $mes);
        foreach ($cal as $semana) {
            foreach ($semana as $dia) {
                if ($dia == 0) {
                    echo "<td></td>";
                } elseif (in_array([$mes, $dia], $reservadas)) {
                    echo "<td class='reservado'>$dia ✖</td>";
                } else {
                    echo "<td>$dia</td>";
                }
            }
            echo "</tr><tr>";
        }
        echo "</tr></table>";
    }
    ?>

    <div class="card mt-5">
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
