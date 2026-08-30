<?php
session_start();

if (isset($_SESSION["usuario_id"])) {
    header("Location: ../dashboard.php");
    exit;
}

$error = $_SESSION["error_login"] ?? "";
unset($_SESSION["error_login"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio de sesión - TecnoStock</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">

        <div class="col-lg-9">

            <div class="row shadow rounded overflow-hidden">

                <div class="col-md-6 bg-primary text-white p-5 d-flex flex-column justify-content-center">

                    <h1 class="fw-bold">TecnoStock</h1>

                    <p class="mt-3">
                        Sistema de gestión de inventario para el control de productos,
                        categorías y movimientos de existencias.
                    </p>

                    <hr>

                    <p class="mb-2">Con TecnoStock podrás:</p>

                    <ul>
                        <li>Registrar y consultar productos.</li>
                        <li>Controlar entradas y salidas de stock.</li>
                        <li>Buscar productos rápidamente.</li>
                        <li>Visualizar alertas de stock mínimo.</li>
                    </ul>

                </div>

                <div class="col-md-6 bg-white p-5">

                    <h3 class="mb-2">Iniciar sesión</h3>

                    <p class="text-muted mb-4">
                        Ingresa tus credenciales para acceder al sistema.
                    </p>

                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error) ?>
                        </div>

                    <?php endif; ?>

                    <form action="procesar_login.php" method="POST">

                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                Correo electrónico
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="correo"
                                name="correo"
                                required
                                maxlength="150"
                                autocomplete="username"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="clave" class="form-label">
                                Contraseña
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="clave"
                                name="clave"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Iniciar sesión
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                ¿Problemas para ingresar? Contacta al administrador del sistema.
                            </small>
                        </div>

                    </form>

                </div>

            </div>

            <p class="text-center text-muted mt-3">
                TecnoStock · Sistema de Inventario
            </p>

        </div>

    </div>
</div>

</body>
</html>