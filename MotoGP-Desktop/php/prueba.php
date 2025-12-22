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
$fase = 'inicio';

if (isset($_SESSION['fase_observador']) && $_SESSION['fase_observador'] === true) {
    $fase = 'observador';
} elseif (isset($_SESSION['id_usuario']) && !isset($_POST['enviar_observacion'])) {
    $fase = 'preguntas';
}

// Manejo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getConexion();

    if (isset($_POST['iniciar'])) {
        $profesion = $conn->real_escape_string($_POST['profesion']);
        $edad = intval($_POST['edad']);
        $pericia = intval($_POST['pericia']);
        $genero = $conn->real_escape_string($_POST['genero']);

        $conn->query("INSERT INTO usuario (profesion, edad, genero, pericia_informatica)
                      VALUES ('$profesion', $edad, '$genero', $pericia)");
        $_SESSION['id_usuario'] = $conn->insert_id;

        $cronometro->arrancar();
        $_SESSION['cronometro'] = $cronometro;

        $fase = 'preguntas';
    }

    if (isset($_POST['enviar_respuestas'])) {
        $id_usuario = $_SESSION['id_usuario'];
        $tiempo_segundos = microtime(true) - ($_SESSION['cronometro_inicio'] ?? microtime(true));

        $preguntas = [];
        for ($i = 1; $i <= 10; $i++) {
            $preguntas[$i] = $conn->real_escape_string($_POST["pregunta$i"]);
        }

        $comentarios_usuario = $conn->real_escape_string($_POST['comentarios_usuario'] ?? '');
        $propuestas_mejora = $conn->real_escape_string($_POST['propuestas_mejora'] ?? '');
        $valoracion = intval($_POST['valoracion']);
        $dispositivo = $conn->real_escape_string($_POST['dispositivo']);

        $campos = implode(',', array_map(fn($n) => "pregunta_$n", range(1,10)));
        $valores = implode(',', array_map(fn($v) => "'$v'", $preguntas));

        $conn->query("INSERT INTO resultado (id_usuario, dispositivo, tiempo_segundos, completado, $campos,
                      comentarios_usuario, propuestas_mejora, valoracion)
                      VALUES ($id_usuario, '$dispositivo', $tiempo_segundos, 1, $valores,
                      '$comentarios_usuario', '$propuestas_mejora', $valoracion)");

        $_SESSION['id_resultado'] = $conn->insert_id;
        $_SESSION['fase_observador'] = true;
        $fase = 'observador';
    }

    if (isset($_POST['enviar_observacion'])) {
        $id_resultado = $_SESSION['id_resultado'];
        $comentario = $conn->real_escape_string($_POST['comentarios']);

        $conn->query("INSERT INTO observacion (id_resultado, comentarios_facilitador)
                      VALUES ($id_resultado, '$comentario')");

        session_unset();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>

<h1>Prueba de usabilidad</h1>

<?php if ($fase === 'inicio'): ?>
<form method="post">
    <label>Profesión: <input type="text" name="profesion" required></label><br><br>
    <label>Edad: <input type="number" name="edad" required></label><br><br>
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

<label>Pregunta 1: ¿Quién es el piloto sobre el que versa el sitio?</label><br>
<input type="text" name="pregunta1" required><br><br>

<label>Pregunta 2: ¿En qué equipo compite actualmente?</label><br>
<input type="text" name="pregunta2" required><br><br>

<label>Pregunta 3: ¿Cuál es el circuito sobre el que versa el sitio?</label><br>
<input type="text" name="pregunta3" required><br><br>

<label>Pregunta 4: ¿Qué sensación térmica habrá en la localidad del circuito el día de la carrera?</label><br>
<input type="text" name="pregunta4" required><br><br>

<label>Pregunta 5: ¿Qué temperatura media habrá en la localidad del circuito durante los días de entreno previos a la carrera?</label><br>
<input type="text" name="pregunta5" required><br><br>

<label>Pregunta 6: ¿Cuántos puntos tenía el tercer piloto en la clasificación del campeonato tras la carrera del circuito Ricardo Tormo?</label><br>
<input type="text" name="pregunta6" required><br><br>

<label>Pregunta 7: ¿En qué apartado del sitio se encuentra la definición de tacómetro?</label><br>
<input type="text" name="pregunta7" required><br><br>

<label>Pregunta 8: ¿Cuántos enlaces aparecen en el apartado "juegos"?</label><br>
<input type="text" name="pregunta8" required><br><br>

<label>Pregunta 9: ¿Quién fue el ganador de la carrera del circuito Ricardo Tormo esta temporada?</label><br>
<input type="text" name="pregunta9" required><br><br>

<label>Pregunta 10: ¿Quién encabezaba la clasificación del campeonato tras la carrera?</label><br>
<input type="text" name="pregunta10" required><br><br>

<label>Comentarios del usuario (opcional)</label><br>
<textarea name="comentarios_usuario"></textarea><br><br>

<label>Propuestas de mejora (opcional)</label><br>
<textarea name="propuestas_mejora"></textarea><br><br>

<label>Valoración (0-10)</label><br>
<input type="number" name="valoracion" min="0" max="10" required><br><br>

<label>Dispositivo</label><br>
<select name="dispositivo" required>
    <option value="" disabled selected>Seleccione un dispositivo</option>
    <option value="Ordenador">Ordenador</option>
    <option value="Tableta">Tableta</option>
    <option value="Teléfono">Teléfono</option>
</select><br><br>

<button type="submit" name="enviar_respuestas">Enviar respuestas</button>
</form>

<?php elseif ($fase === 'observador'): ?>
<form method="post">
    <label>Comentarios del observador</label><br>
    <textarea name="comentarios"></textarea><br><br>
    <button type="submit" name="enviar_observacion">Enviar observación</button>
</form>
<?php endif; ?>

</body>
</html>
