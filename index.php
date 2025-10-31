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
    $reservaExitosa = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $pdo->prepare("INSERT INTO reservas (fecha_reserva, nombre, dni, telefono, numero_personas) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST["fecha_reserva"],
            $_POST["nombre"],
            $_POST["dni"],
            $_POST["telefono"],
            $_POST["numero_personas"]
        ]);
        $reservaExitosa = true;
    }

    // Obtener fechas reservadas
    $stmt = $pdo->query("SELECT DISTINCT fecha_reserva FROM reservas");
    $fechas = $stmt->fetchAll();
    $reservadas = [];
    foreach ($fechas as $f) {
        $fecha = new DateTime($f["fecha_reserva"]);
        $reservadas[] = $fecha->format("Y-m-d");
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
    https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css
    <style>
        .month { margin-bottom: 20px; }
        .month h3 { background: #007bff; color: white; padding: 5px; font-size: 1.2rem; }
        table.calendar { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        table.calendar th, table.calendar td {
            border: 1px solid #ccc;
            width: 14.28%;
            height: 40px;
            text-align: center;
            vertical-align: top;
        }
        .reservado { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Calendario de Reservas 2025</h1>

    <?php
    $anio = 2025;
    $diasSemana = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
    $meses = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];

    foreach ($meses as $mesNum => $mesNombre) {
        echo "<div class='month'><h3>$mesNombre</h3><table class='calendar'>";
        echo "<tr>";
        foreach ($diasSemana as $dia) {
            echo "<th>$dia</th>";
        }
        echo "</tr>";

        $primerDia = new DateTime("$anio-$mesNum-01");
        $primerDiaSemana = (int)$primerDia->format("N"); // 1 (Lun) a 7 (Dom)
        $diasMes = cal_days_in_month(CAL_GREGORIAN, $mesNum, $anio);

        $semana = array_fill(0, 7, 0);
        $diaActual = 1;
        $fila = [];

        // Primera semana
        for ($i = $primerDiaSemana - 1; $i < 7; $i++) {
            $semana[$i] = $diaActual++;
        }
        $fila[] = $semana;

        // Semanas siguientes
        while ($diaActual <= $diasMes) {
            $semana = array_fill(0, 7, 0);
            for ($i = 0; $i < 7 && $diaActual <= $diasMes; $i++) {
                $semana[$i] = $diaActual++;
            }
            $fila[] = $semana;
        }

        foreach ($fila as $semana) {
            echo "<tr>";
            foreach ($semana as $dia) {
                if ($dia == 0) {
                    echo "<td></td>";
                } else {
                    $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mesNum, $dia);
                    if (in_array($fecha_actual, $reservadas)) {
                        echo "<td class='reservado'>$dia ✖</td>";
                    } else {
                        echo "<td>$dia</td>";
                    }
                }
            }
            echo "</tr>";
        }

        echo "</table></div>";
    }
    ?>

    <?php if ($reservaExitosa): ?>
        <div class="alert alert-success">✅ La reserva se ha realizado correctamente.</div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">Formulario de Reserva</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Fecha de reserva</label>
                    <input type="date" name="fecha_reserva" class="form-control" required
                           min="2025-01-01" max="2025-12-31"
                           <?php if (!empty($reservadas)): ?>
                               oninput="validarFecha(this)"
                           <?php endif; ?>>
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

<script>
    const fechasReservadas = <?= json_encode($reservadas) ?>;
    function validarFecha(input) {
        if (fechasReservadas.includes(input.value)) {
            alert("⚠️ Esta fecha ya está reservada. Por favor, elige otra.");
            input.value = "";
        }
    }
</script>
https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js</script>
</body>
</html>
