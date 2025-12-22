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

        $tablas = ["usuario", "resultado", "observacion"];

        foreach ($tablas as $tabla) {
            fputcsv($output, ["Tabla: $tabla"]);

            $resultado = $this->conn->query("SELECT * FROM $tabla");

            if ($resultado && $resultado->num_rows > 0) {
                // Cabeceras
                $campos = $resultado->fetch_fields();
                $cabecera = [];

                foreach ($campos as $campo) {
                    $cabecera[] = $campo->name;
                }
                fputcsv($output, $cabecera);

                // Datos
                while ($fila = $resultado->fetch_assoc()) {
                    fputcsv($output, $fila);
                }
            } else {
                fputcsv($output, ["(Sin datos)"]);
            }

            fputcsv($output, []); // Línea en blanco entre tablas
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
