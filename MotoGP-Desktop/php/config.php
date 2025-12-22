<?php
class Configuracion {

    private mysqli $conn;
    private string $bd = "UO288705_DB";

    public function __construct() {
        $this->conn = new mysqli("localhost", "DBUSER2025", "DBPSWD2025", "");

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    private function ejecutarScript(string $rutaScript): void {
        if (!file_exists($rutaScript)) {
            echo "<p>Error: No se encuentra el script $rutaScript</p>";
            return;
        }

        $sql = file_get_contents($rutaScript);

        if ($this->conn->multi_query($sql)) {
            do {
                if ($resultado = $this->conn->store_result()) {
                    $resultado->free();
                }
            } while ($this->conn->next_result());

            echo "<p>Operación realizada correctamente.</p>";
        } else {
            echo "<p>Error al ejecutar el script: {$this->conn->error}</p>";
        }
    }

    public function crearBD(): void {
        $this->ejecutarScript("create.sql");
    }

    public function reiniciarBD(): void {
        $this->ejecutarScript("delete.sql");
    }

    public function eliminarBD(): void {
        $this->ejecutarScript("drop.sql");
    }

public function exportarCSV(): void {
    $this->conn->select_db($this->bd);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="UO288705_DB.csv"');

    $output = fopen('php://output', 'w');

    // Cabecera de la CSV combinada
    $cabecera = [
        // Campos usuario
        'id_usuario','profesion','edad','genero','pericia_informatica',
        // Campos resultado
        'id_resultado','dispositivo','tiempo_segundos','completado',
        'comentarios_usuario','propuestas_mejora','valoracion',
        'pregunta_1','pregunta_2','pregunta_3','pregunta_4','pregunta_5',
        'pregunta_6','pregunta_7','pregunta_8','pregunta_9','pregunta_10',
        // Campos observacion
        'id_observacion','comentarios_facilitador'
    ];
    fputcsv($output, $cabecera);

    // Consulta con JOIN
    $sql = "
        SELECT u.id_usuario, u.profesion, u.edad, u.genero, u.pericia_informatica,
                r.id_resultado, r.dispositivo, r.tiempo_segundos, r.completado,
                r.comentarios_usuario, r.propuestas_mejora, r.valoracion,
                r.pregunta_1, r.pregunta_2, r.pregunta_3, r.pregunta_4, r.pregunta_5,
                r.pregunta_6, r.pregunta_7, r.pregunta_8, r.pregunta_9, r.pregunta_10,
                o.id_observacion, o.comentarios_facilitador
        FROM usuario u
        LEFT JOIN resultado r ON u.id_usuario = r.id_usuario
        LEFT JOIN observacion o ON r.id_resultado = o.id_resultado
        ORDER BY u.id_usuario, r.id_resultado, o.id_observacion
    ";

    $resultado = $this->conn->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            fputcsv($output, $fila);
        }
    }

    fclose($output);
    exit;
}

}

$configuracion = new Configuracion();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {
    switch ($_POST["accion"]) {
        case "crear":
            $configuracion->crearBD();
            break;
        case "reiniciar":
            $configuracion->reiniciarBD();
            break;
        case "eliminar":
            $configuracion->eliminarBD();
            break;
        case "exportar":
            $configuracion->exportarCSV();
            break;
    }
}
?>
<!DOCTYPE HTML>

<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>MotoGP - Configuración</title>
    <meta name="author" content="David Álvarez Menéndez - UO288705" />
    <meta name="description" content="Configuración de la BDD de las pruebas de usabilidad" />
    <meta name="keywords" content="MotoGP, motocicletas, carreras, deportes, velocidad" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" href="../multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>

<body>

    <h1>Configuración de la base de datos</h1>

    <form method="post">
        <button type="submit" name="accion" value="crear">
            Crear base de datos
        </button>

        <button type="submit" name="accion" value="reiniciar"
            onclick="return confirm('¿Seguro que deseas reiniciar la base de datos? Se perderán todos los datos actuales.');">
            Reiniciar base de datos
        </button>

        <button type="submit" name="accion" value="eliminar"
            onclick="return confirm('¿Seguro que deseas eliminar la base de datos?');">
            Eliminar base de datos
        </button>

        <button type="submit" name="accion" value="exportar">
            Exportar base de datos (CSV)
        </button>
    </form>

</body>

</html>
