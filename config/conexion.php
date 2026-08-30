<?php

$configFile = __DIR__ . "/config.local.php";

if (!file_exists($configFile)) {
    die("No se encontró el archivo de configuración de la base de datos.");
}

$config = require $configFile;

try {

    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config["user"],
        $config["pass"],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("Error de conexión a la base de datos.");
}