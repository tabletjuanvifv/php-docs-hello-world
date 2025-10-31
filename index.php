
<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "usuario", "contraseña", "nombre_base_datos");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha = $_POST["fecha_reserva"];
    $nombre = $_POST["nombre"];
    $dni = $_POST["dni"];
    $telefono = $_POST["telefono"];
    $numero_personas = $_POST["numero_personas"];

    $stmt = $conexion->prepare("INSERT INTO reservas_albergue (fecha_reserva, nombre, dni, telefono, numero_personas) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $fecha, $nombre, $dni, $telefono, $numero_personas);
    $stmt->execute();
    $stmt->close();
}

// Obtener días ya reservados
$resultado = $conexion->query("SELECT DISTINCT fecha_reserva FROM reservas_albergue ORDER BY fecha_reserva ASC");
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
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($fila["fecha_reserva"]) ?></td></tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
$conexion->close();
?>
