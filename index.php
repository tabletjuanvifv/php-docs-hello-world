<?php
// Recuperar variables de entorno
$dbHost = getenv('DB_HOST');
$dbName = "prueba";
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');


$conn = new mysqli($dbHost, $dbUser,$dbPass, "clientes");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];

    if ($accion == "añadir") {
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $email);
        $stmt->execute();
        $stmt->close();
    } elseif ($accion == "borrar") {
        $stmt = $conn->prepare("DELETE FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        form {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            background-color: #fff;
        }
        th {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <h1>Gestión de Clientes</h1>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="accion" value="añadir">Añadir Cliente</button>
        <button type="submit" name="accion" value="borrar">Borrar Cliente</button>
    </form>

    <h2>Lista de Clientes</h2>
    <table border="1">
        <tr><th>ID</th><th>Nombre</th><th>Email</th></tr>
        <?php
        $resultado = $conn->query("SELECT * FROM clientes");
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr><td>{$fila['id']}</td><td>{$fila['nombre']}</td><td>{$fila['email']}</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
