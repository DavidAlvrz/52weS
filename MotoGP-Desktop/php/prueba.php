<?php
session_start();

// Base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'DBUSER2025');
define('DB_PASS', 'DBPSWD2025');
define('DB_NAME', 'UO288705_DB');

// Conexión a BD
function getConexion(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Cronómetro
class Cronometro {
    public float $inicio = 0;
    public float $tiempo = 0;

    public function arrancar() {
        $this->inicio = microtime(true);
        $_SESSION['cronometro_inicio'] = $this->inicio;
    }

    public function parar() {
        $this->tiempo = microtime(true) - ($_SESSION['cronometro_inicio'] ?? microtime(true));
        $_SESSION['cronometro_tiempo'] = $this->tiempo;
    }

    public function mostrar(): string {
        $minutos = floor($this->tiempo / 60);
        $segundos = fmod($this->tiempo, 60);
        return sprintf("%02d:%04.1f", $minutos, $segundos);
    }
}

// Instancia del cronómetro
if (!isset($_SESSION['cronometro'])) {
    $_SESSION['cronometro'] = new Cronometro();
}
$cronometro = $_SESSION['cronometro'];

// Control de fases
$fase = 'inicio'; // Por defecto

if (isset($_SESSION['fase_observador']) && $_SESSION['fase_observador'] === true) {
    $fase = 'observador';
} elseif (isset($_SESSION['id_usuario']) && !isset($_POST['enviar_observacion'])) {
    $fase = 'preguntas';
}

// Manejo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getConexion();

    // Iniciar prueba
    if (isset($_POST['iniciar'])) {
        $profesion = $conn->real_escape_string($_POST['profesion']);
        $edad = intval($_POST['edad']);
        $pericia = intval($_POST['pericia']);
        $genero = $conn->real_escape_string($_POST['genero']);

        // Insertar usuario
        $conn->query("INSERT INTO usuario (profesion, edad, genero, pericia_informatica)
                      VALUES ('$profesion', $edad, '$genero', $pericia)");
        $id_usuario = $conn->insert_id;
        $_SESSION['id_usuario'] = $id_usuario;

        // Iniciar cronómetro
        $cronometro->arrancar();
        $_SESSION['cronometro'] = $cronometro;

        $fase = 'preguntas';
    }

    // Enviar respuestas del usuario
    if (isset($_POST['enviar_respuestas'])) {
        $id_usuario = $_SESSION['id_usuario'];
        $tiempo_segundos = microtime(true) - ($_SESSION['cronometro_inicio'] ?? microtime(true));

        // Recoger respuestas
        $preguntas = [];
        for ($i = 1; $i <= 10; $i++) {
            $preguntas[$i] = $conn->real_escape_string($_POST["pregunta$i"]);
        }

        $comentarios_usuario = $conn->real_escape_string($_POST['comentarios_usuario'] ?? '');
        $propuestas_mejora = $conn->real_escape_string($_POST['propuestas_mejora'] ?? '');
        $valoracion = intval($_POST['valoracion'] ?? 0);
        $dispositivo = $conn->real_escape_string($_POST['dispositivo'] ?? 'Ordenador');

        $campos = implode(',', array_map(fn($n) => "pregunta_$n", range(1,10)));
        $valores = implode(',', array_map(fn($v) => "'$v'", $preguntas));

        // Insertar resultado con comentarios, propuestas, valoración y dispositivo
        $conn->query("INSERT INTO resultado (id_usuario, dispositivo, tiempo_segundos, completado, $campos, comentarios_usuario, propuestas_mejora, valoracion)
                      VALUES ($id_usuario, '$dispositivo', $tiempo_segundos, 1, $valores, '$comentarios_usuario', '$propuestas_mejora', $valoracion)");
        $id_resultado = $conn->insert_id;
        $_SESSION['id_resultado'] = $id_resultado;

        // Pasar a fase observador
        $_SESSION['fase_observador'] = true;
        $fase = 'observador';
    }

    // Enviar observación del facilitador
    if (isset($_POST['enviar_observacion'])) {
        $id_resultado = $_SESSION['id_resultado'];
        $comentario = $conn->real_escape_string($_POST['comentarios']);

        $conn->query("INSERT INTO observacion (id_resultado, comentarios_facilitador)
                      VALUES ($id_resultado, '$comentario')");

        // Reiniciar sesión para nuevo flujo
        unset($_SESSION['id_usuario'], $_SESSION['id_resultado'], $_SESSION['fase_observador'], $_SESSION['cronometro_inicio'], $_SESSION['cronometro']);
        $cronometro = new Cronometro();
        $_SESSION['cronometro'] = $cronometro;
        $fase = 'inicio';

        // Redirigir a GET limpio
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    $conn->close();
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>MotoGP - Prueba de usabilidad</title>
    <meta name="author" content="David Álvarez Menéndez - UO288705" />
    <meta name="description" content="Prueba de usabilidad del proyecto MotoGP" />
    <meta name="keywords" content="MotoGP, prueba, usabilidad, test" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>
<body>
<h1>Prueba de usabilidad</h1>

<?php if ($fase === 'inicio'): ?>
    <form method="post">
        <label>Profesión: <input type="text" name="profesion" required></label><br><br>
        <label>Edad: <input type="number" name="edad" min="0" required></label><br><br>
        <label>Pericia informática (1-10): <input type="number" name="pericia" min="1" max="10" required></label><br><br>
        <label>Género:
            <select name="genero">
                <option value="M">M</option>
                <option value="F">F</option>
                <option value="Otro">Otro</option>
            </select>
        </label><br><br>
        <button type="submit" name="iniciar">Iniciar prueba</button>
    </form>
<?php elseif ($fase === 'preguntas'): ?>
    <form method="post">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <label for="pregunta<?= $i ?>">Pregunta <?= $i ?>: Escribe el enunciado aquí</label><br>
            <input type="text" id="pregunta<?= $i ?>" name="pregunta<?= $i ?>" required><br><br>
        <?php endfor; ?>
        <label for="comentarios_usuario">Comentarios del usuario (opcional):</label><br>
        <textarea id="comentarios_usuario" name="comentarios_usuario" rows="3"></textarea><br><br>
        <label for="propuestas_mejora">Propuestas de mejora (opcional):</label><br>
        <textarea id="propuestas_mejora" name="propuestas_mejora" rows="3"></textarea><br><br>
        <label for="valoracion">Valoración del usuario (0-10):</label><br>
        <input type="number" id="valoracion" name="valoracion" min="0" max="10" required><br><br>
        <label for="dispositivo">Dispositivo desde el que se realiza la prueba:</label><br>
        <select id="dispositivo" name="dispositivo" required>
            <option value="" disabled selected>Selecciona un dispositivo</option>
            <option value="Ordenador">Ordenador</option>
            <option value="Tableta">Tableta</option>
            <option value="Teléfono">Teléfono</option>
        </select><br><br>
        <button type="submit" name="enviar_respuestas">Enviar respuestas</button>
    </form>
<?php elseif ($fase === 'observador'): ?>
    <form method="post">
        <label for="comentarios">Comentarios del observador:</label><br>
        <textarea id="comentarios" name="comentarios" rows="4"></textarea><br><br>
        <button type="submit" name="enviar_observacion">Enviar observación</button>
    </form>
<?php endif; ?>

</body>
</html>
