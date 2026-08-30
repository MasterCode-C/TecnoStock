<?php

session_start();

require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$correo = trim($_POST["correo"] ?? "");
$clave  = $_POST["clave"] ?? "";

if ($correo === "" || $clave === "") {
    $_SESSION["error_login"] = "Debes completar todos los campos.";
    header("Location: login.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["error_login"] = "El correo electrónico no tiene un formato válido.";
    header("Location: login.php");
    exit;
}

$sql = "SELECT id_usuario, nombre, correo, clave, estado
        FROM usuario
        WHERE correo = :correo
        LIMIT 1";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":correo" => $correo
]);

$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION["error_login"] = "Correo o contraseña incorrectos.";
    header("Location: login.php");
    exit;
}

if ((int)$usuario["estado"] !== 1) {
    $_SESSION["error_login"] = "La cuenta se encuentra desactivada.";
    header("Location: login.php");
    exit;
}

if (!password_verify($clave, $usuario["clave"])) {
    $_SESSION["error_login"] = "Correo o contraseña incorrectos.";
    header("Location: login.php");
    exit;
}

session_regenerate_id(true);

$_SESSION["usuario_id"] = $usuario["id_usuario"];
$_SESSION["usuario_nombre"] = $usuario["nombre"];
$_SESSION["usuario_correo"] = $usuario["correo"];

header("Location: ../dashboard.php");
exit;