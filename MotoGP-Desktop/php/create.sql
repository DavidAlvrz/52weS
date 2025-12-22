
CREATE DATABASE IF NOT EXISTS UO288705_DB
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE UO288705_DB;
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    profesion VARCHAR(100) NOT NULL,
    edad INT NOT NULL,
    genero ENUM('M','F','Otro') NOT NULL,
    pericia_informatica INT NOT NULL
);

CREATE TABLE IF NOT EXISTS resultado (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    dispositivo ENUM('Ordenador','Tableta','Teléfono') NOT NULL,
    tiempo_segundos FLOAT NOT NULL,
    completado BOOLEAN NOT NULL,
    comentarios_usuario TEXT,
    propuestas_mejora TEXT,
    valoracion TINYINT CHECK (valoracion BETWEEN 0 AND 10),
    pregunta_1 TEXT,
    pregunta_2 TEXT,
    pregunta_3 TEXT,
    pregunta_4 TEXT,
    pregunta_5 TEXT,
    pregunta_6 TEXT,
    pregunta_7 TEXT,
    pregunta_8 TEXT,
    pregunta_9 TEXT,
    pregunta_10 TEXT,
    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS observacion (
    id_observacion INT AUTO_INCREMENT PRIMARY KEY,
    id_resultado INT NOT NULL,
    comentarios_facilitador TEXT,
    FOREIGN KEY (id_resultado)
        REFERENCES resultado(id_resultado)
        ON DELETE CASCADE
);
