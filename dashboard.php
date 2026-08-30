<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "config/conexion.php";

$nombreUsuario = $_SESSION["usuario_nombre"];


/* =========================
   TOTAL DE PRODUCTOS
========================= */

$sqlTotalProductos = "
    SELECT COUNT(*) AS total
    FROM producto
";

$stmtTotalProductos = $pdo->prepare($sqlTotalProductos);
$stmtTotalProductos->execute();

$totalProductos =
    (int)$stmtTotalProductos->fetch()["total"];


/* =========================
   PRODUCTOS ACTIVOS EFECTIVOS
========================= */

$sqlProductosActivos = "
    SELECT COUNT(*) AS total
    FROM producto p
    INNER JOIN categoria c
        ON p.id_categoria = c.id_categoria
    WHERE p.estado = 1
      AND c.estado = 1
";

$stmtProductosActivos =
    $pdo->prepare($sqlProductosActivos);

$stmtProductosActivos->execute();

$totalProductosActivos =
    (int)$stmtProductosActivos->fetch()["total"];


/* =========================
   PRODUCTOS INACTIVOS EFECTIVOS
========================= */

$sqlProductosInactivos = "
    SELECT COUNT(*) AS total
    FROM producto p
    INNER JOIN categoria c
        ON p.id_categoria = c.id_categoria
    WHERE p.estado = 0
       OR c.estado = 0
";

$stmtProductosInactivos =
    $pdo->prepare($sqlProductosInactivos);

$stmtProductosInactivos->execute();

$totalProductosInactivos =
    (int)$stmtProductosInactivos->fetch()["total"];


/* =========================
   PRODUCTOS CON STOCK BAJO
========================= */

$sqlStockBajo = "
    SELECT COUNT(*) AS total
    FROM producto p
    INNER JOIN categoria c
        ON p.id_categoria = c.id_categoria
    WHERE p.stock_actual < p.stock_minimo
      AND p.estado = 1
      AND c.estado = 1
";

$stmtStockBajo = $pdo->prepare($sqlStockBajo);
$stmtStockBajo->execute();

$totalStockBajo =
    (int)$stmtStockBajo->fetch()["total"];


/* =========================
   TOTAL DE MOVIMIENTOS
========================= */

$sqlMovimientos = "
    SELECT COUNT(*) AS total
    FROM movimiento
";

$stmtMovimientos = $pdo->prepare($sqlMovimientos);
$stmtMovimientos->execute();

$totalMovimientos =
    (int)$stmtMovimientos->fetch()["total"];


/* =========================
   TOTAL DE ENTRADAS
========================= */

$sqlEntradas = "
    SELECT COUNT(*) AS total
    FROM movimiento
    WHERE tipo = 'ENTRADA'
";

$stmtEntradas = $pdo->prepare($sqlEntradas);
$stmtEntradas->execute();

$totalEntradas =
    (int)$stmtEntradas->fetch()["total"];


/* =========================
   TOTAL DE SALIDAS
========================= */

$sqlSalidas = "
    SELECT COUNT(*) AS total
    FROM movimiento
    WHERE tipo = 'SALIDA'
";

$stmtSalidas = $pdo->prepare($sqlSalidas);
$stmtSalidas->execute();

$totalSalidas =
    (int)$stmtSalidas->fetch()["total"];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Panel principal - TecnoStock</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <span class="navbar-brand">
            TecnoStock
        </span>

        <a
            href="auth/logout.php"
            class="btn btn-outline-light"
        >
            Cerrar sesión
        </a>

    </div>

</nav>


<div class="container py-5">


    <div class="alert alert-success">

        Bienvenido,

        <strong>
            <?= htmlspecialchars($nombreUsuario) ?>
        </strong>

    </div>


    <div class="mb-4">

        <h1>
            Panel principal
        </h1>

        <p class="text-muted">
            Resumen general del inventario.
        </p>

    </div>


    <!-- ALERTA DE STOCK BAJO -->

    <?php if ($totalStockBajo > 0): ?>

        <div
            class="alert alert-danger d-flex justify-content-between align-items-center"
        >

            <div>

                <strong>
                    Atención:
                </strong>

                existen

                <strong>
                    <?= $totalStockBajo ?>
                </strong>

                producto(s) activo(s) con stock bajo.

            </div>

            <a
                href="productos/listar.php?filtro=stock_bajo&estado=1"
                class="btn btn-danger"
            >
                Ver productos
            </a>

        </div>

    <?php endif; ?>


    <!-- INDICADORES -->

    <div class="row g-4 mb-5">


        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Total de productos
                    </h6>

                    <h2 class="fw-bold">
                        <?= $totalProductos ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Productos activos
                    </h6>

                    <h2 class="fw-bold text-success">
                        <?= $totalProductosActivos ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Productos inactivos
                    </h6>

                    <h2 class="fw-bold text-secondary">
                        <?= $totalProductosInactivos ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Productos con stock bajo
                    </h6>

                    <h2 class="fw-bold text-danger">
                        <?= $totalStockBajo ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Movimientos registrados
                    </h6>

                    <h2 class="fw-bold">
                        <?= $totalMovimientos ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Entradas registradas
                    </h6>

                    <h2 class="fw-bold text-success">
                        <?= $totalEntradas ?>
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">
                        Salidas registradas
                    </h6>

                    <h2 class="fw-bold text-danger">
                        <?= $totalSalidas ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>


    <!-- MÓDULOS -->

    <div class="row g-4">

        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h4>
                        Productos
                    </h4>

                    <p class="text-muted">
                        Registra, consulta y modifica los productos disponibles.
                    </p>

                    <a
                        href="productos/listar.php"
                        class="btn btn-primary"
                    >
                        Gestionar productos
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h4>
                        Categorías
                    </h4>

                    <p class="text-muted">
                        Organiza los productos según sus respectivas categorías.
                    </p>

                    <a
                        href="categorias/listar.php"
                        class="btn btn-primary"
                    >
                        Gestionar categorías
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h4>
                        Movimientos
                    </h4>

                    <p class="text-muted">
                        Registra entradas y salidas de productos del inventario.
                    </p>

                    <a
                        href="movimientos/listar.php"
                        class="btn btn-primary"
                    >
                        Ver movimientos
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>