<!DOCTYPE HTML>

<html lang="es">

<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP - Meteorología</title>
    <meta name="author" content="David Álvarez Menéndez - UO288705" />
    <meta name="description"
        content="Cronómetro implementado con el lenguaje PHP" />
    <meta name="keywords"
        content="MotoGP, motocicletas, carreras, deportes, velocidad" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="icon" href="multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
    <script src="js/ciudad.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

</head>


<?php

class Cronometro
{
    protected $tiempo;
    protected $inicio;

    public function __construct()
    {
        $this->tiempo = 0;
    }

    public function arrancar()
    {
        $this->inicio = microtime(true);
    }

    public function parar()
    {
        $this->tiempo = microtime(true) - $this->inicio;
    }
    public function mostrar()
    {
        $minutos = floor($this->tiempo / 60);
        $segundos = fmod($this->tiempo, 60);
        return sprintf("%02d:%04.1f", $minutos, $segundos);
    }
}
?>


<body>
    <header>
        <h1> <a href="index.html">MotoGP Desktop </a></h1>
        <nav>
            <a href="index.html" title="Página de inicio">Inicio</a>

            <a href="piloto.html"
                title="Información sobre el piloto MotoGP">Piloto</a>

            <a href="circuito.html"
                title="Información sobre el circuitos MotoGP">Circuito</a>

            <a href="meteorologia.html"
                title="Información sobre meteorología"
                class="active">Meteorología</a>

            <a href="clasificaciones.php"
                title="Información sobre la clasificación MotoGP">Clasificaciones</a>

            <a href="juegos.html"
                title="Sección de juegos de MotoGP Desktop">Juegos</a>

            <a href="ayuda.html"
                title="Sección de ayuda de MotoGP Desktop">Ayuda</a>
        </nav>
    </header>

    <p> Estás en <a href="juegos.html">Juegos</a> -
        <strong>Cronómetro PHP</strong>
    </p>

    <h2> Cronómetro PHP </h2>


    <main>
        <form method='post' action='#'>
            <input type='submit' class='button' name='arrancar' value='Arrancar' />
            <input type='submit' class='button' name='parar' value='Parar' />
            <input type='submit' class='button' name='mostrar' value='Mostrar' />
        </form>

        <?php
        session_start();

        if (!isset($_SESSION['cronometro'])) {
            $_SESSION['cronometro'] = new Cronometro();
        }

        $cronometro = $_SESSION['cronometro'];

        if (count($_POST) > 0) {
            if (isset($_POST['arrancar'])) {
                $cronometro->arrancar();
            } elseif (isset($_POST['parar'])) {
                $cronometro->parar();
            } elseif (isset($_POST['mostrar'])) {
                echo "<p>Tiempo: " . $cronometro->mostrar() . "</p>";
            }
        }
        ?>


    </main>
</body>

</html>