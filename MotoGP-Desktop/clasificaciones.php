<!DOCTYPE HTML>

<html lang="es">

<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP - Clasificaciones</title>
    <meta name="author" content="David Álvarez Menéndez - UO288705" />
    <meta name="description" content="Información sobre las clasificaciones de MotoGP" />
    <meta name="keywords" content="MotoGP, motocicletas, carreras, deportes, velocidad" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" href="multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />

</head>

<?php

class Clasificacion {
    private $documento;
    private $xmlObject;

    public function __construct() {
        $this->documento = __DIR__ . '/xml/circuitoEsquema.xml';
        $this->xmlObject = null;
    }

    public function consultar() {
        if (!file_exists($this->documento)) {
            echo "<h3>Error: no se encuentra el archivo XML.</h3>";
            return null;
        }

        $xmlString = file_get_contents($this->documento);

        if ($xmlString === false) {
            echo "<h3>Error al leer el archivo XML.</h3>";
            return null;
        }

        $this->xmlObject = simplexml_load_string($xmlString, 'SimpleXMLElement', 0, 'http://www.uniovi.es');
        if ($this->xmlObject === false) {
            echo "<h3>Error al parsear el XML.</h3>";
            return null;
        }

        return $this->xmlObject;
    }

    private function parseTiempoISO($isoTime) {
        $minutos = 0;
        $segundos = 0;

        if (preg_match('/PT(\d+)M([\d\.]+)S/', $isoTime, $matches)) {
            $minutos = (int)$matches[1];
            $segundos = (float)$matches[2];
        }

        $segundosEnteros = floor($segundos);
        $milisegundos = round(($segundos - $segundosEnteros) * 100);

        return sprintf("%02d:%02d.%02d", $minutos, $segundosEnteros, $milisegundos);
    }

    public function mostrarGanador() {
        if ($this->xmlObject === null) {
            $this->consultar();
            if ($this->xmlObject === null) return;
        }

        $vencedor = $this->xmlObject->vencedor;
        if ($vencedor) {
            $nombre = (string)$vencedor->nombreVencedor;
            $tiempoISO = (string)$vencedor->tiempo;
            $tiempo = $this->parseTiempoISO($tiempoISO);

            echo "<h3>Ganador de la carrera</h3>";
            echo "<p>Nombre: " . htmlspecialchars($nombre) . "</p>";
            echo "<p>Tiempo: " . htmlspecialchars($tiempo) . "</p>";
        } else {
            echo "<p>No se encontró información del ganador.</p>";
        }
    }

    public function mostrarClasificacion() {

        if ($this->xmlObject === null) {
            $this->consultar();
            if ($this->xmlObject === null) return;
        }

        echo "<h2>Clasificación del Mundial tras la carrera</h2>";
        echo "<ol>";

        foreach ($this->xmlObject->mundialClasificacion->piloto as $piloto) {

            $nombre = trim((string)$piloto);

            $atributos = $piloto->attributes();
            $equipo = (string)$atributos['equipo'];
            $puntos = (string)$atributos['puntos'];

        echo "<li>";
            echo htmlspecialchars($nombre) .
                " (" . htmlspecialchars($equipo) . "): " .
                htmlspecialchars($puntos) . " ptos";
            echo "</li>";
        }

        echo "</ol>";
}


    
}

$clasi = new Clasificacion();
?>

<body>
    <header>
        <h1> <a href="index.html">MotoGP Desktop </a></h1>
        <nav>
            <a href="index.html" title="Página de inicio">Inicio</a>

            <a href="piloto.html" title="Información sobre el piloto MotoGP">Piloto</a>

            <a href="circuito.html" title="Información sobre el circuitos MotoGP">Circuito</a>

            <a href="meteorologia.html" title="Información sobre meteorología">Meteorología</a>

            <a href="clasificaciones.php" title="Información sobre la clasificación MotoGP" class="active">Clasificaciones</a>

            <a href="juegos.html" title="Sección de juegos de MotoGP Desktop">Juegos</a>

            <a href="ayuda.html" title="Sección de ayuda de MotoGP Desktop">Ayuda</a>
        </nav>
    </header>

    <p> Estás en <a href="index.html">Inicio</a> - <strong>Clasificaciones</strong></p>

    <h2> Clasificaciones </h2>

    <main>
        <?php
        $clasi->mostrarGanador();
        $clasi->mostrarClasificacion();
        ?>
    </main>
</body>

</html>