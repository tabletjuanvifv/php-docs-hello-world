<?php
// Recuperar variables de entorno
$dbHost = getenv('DB_HOST');
$dbName = "prueba";
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['accion']) && $_POST['accion'] === 'añadir') {
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            if ($nombre && $email) {
                $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email) VALUES (?, ?)");
                $stmt->execute([$nombre, $email]);
            }
        } elseif (isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
            $id = $_POST['id'] ?? '';
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
                $stmt->execute([$id]);
            }
        }
    }

    echo "<h1>Gestión de Clientes</h1>";
    echo "<form method='post'>";
    echo "<input type='text' name='nombre' placeholder='Nombre' required>";
    echo "<input type='email' name='email' placeholder='Email' required>";
    echo "<button type='submit' name='accion' value='añadir'>Añadir Cliente</button>";
    echo "</form>";

    echo "<h2>Lista de Clientes</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Acción</th></tr>";

    $stmt = $pdo->query("SELECT id, nombre, email FROM clientes");
    while ($cliente = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($cliente['id']) . "</td>";
        echo "<td>" . htmlspecialchars($cliente['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
        echo "<td>";
        echo "<form method='post' style='display:inline;'>";
        echo "<input type='hidden' name='id' value='" . htmlspecialchars($cliente['id']) . "'>";
        echo "<button type='submit' name='accion' value='borrar'>Borrar</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (PDOException $e) {
    error_log('Error de conexión PDO: ' . $e->getMessage());
    echo "Error al conectar con la base de datos: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
