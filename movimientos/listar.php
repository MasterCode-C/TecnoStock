<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$buscar = trim($_GET["buscar"] ?? "");
$tipoFiltro = $_GET["tipo"] ?? "";

$condiciones = [];
$parametros = [];

if ($buscar !== "") {

    $condiciones[] = "
        (
            p.codigo LIKE :buscar_codigo
            OR p.nombre LIKE :buscar_producto
        )
    ";

    $parametros[":buscar_codigo"] = "%" . $buscar . "%";
    $parametros[":buscar_producto"] = "%" . $buscar . "%";
}

if (
    $tipoFiltro === "ENTRADA"
    || $tipoFiltro === "SALIDA"
) {

    $condiciones[] = "m.tipo = :tipo";
    $parametros[":tipo"] = $tipoFiltro;
}


$sql = "SELECT
            m.id_movimiento,
            m.fecha_hora,
            m.tipo,
            m.cantidad,
            m.observacion,
            p.codigo,
            p.nombre AS producto,
            u.nombre AS usuario
        FROM movimiento m
        INNER JOIN producto p
            ON m.id_producto = p.id_producto
        INNER JOIN usuario u
            ON m.id_usuario = u.id_usuario";


if (count($condiciones) > 0) {

    $sql .= " WHERE " .
        implode(" AND ", $condiciones);
}


$sql .= "
        ORDER BY
            m.fecha_hora DESC,
            m.id_movimiento DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);

$movimientos = $stmt->fetchAll();

$mensaje = $_SESSION["mensaje"] ?? "";
unset($_SESSION["mensaje"]);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Movimientos - TecnoStock</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand"
            href="../dashboard.php"
        >
            TecnoStock
        </a>

        <div class="d-flex gap-2">

            <a
                href="../dashboard.php"
                class="btn btn-outline-light"
            >
                Panel principal
            </a>

            <a
                href="../auth/logout.php"
                class="btn btn-outline-light"
            >
                Cerrar sesión
            </a>

        </div>

    </div>

</nav>


<div class="container py-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1>
                Movimientos de inventario
            </h1>

            <p class="text-muted mb-0">
                Consulta las entradas y salidas registradas.
            </p>

        </div>

        <a
            href="registrar.php"
            class="btn btn-primary"
        >
            + Nuevo movimiento
        </a>

    </div>


    <?php if ($mensaje): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($mensaje) ?>

        </div>

    <?php endif; ?>


    <!-- BUSCADOR Y FILTROS -->
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row g-2 align-items-end">


                    <div class="col-md-6">

                        <label
                            for="buscar"
                            class="form-label"
                        >
                            Buscar producto
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="buscar"
                            name="buscar"
                            placeholder="Ej: TEC-001 o Notebook"
                            value="<?= htmlspecialchars($buscar) ?>"
                        >

                    </div>


                    <div class="col-md-2">

                        <label
                            for="tipo"
                            class="form-label"
                        >
                            Tipo
                        </label>

                        <select
                            class="form-select"
                            id="tipo"
                            name="tipo"
                        >

                            <option
                                value=""
                                <?= $tipoFiltro === ""
                                    ? "selected"
                                    : "" ?>
                            >
                                Todos
                            </option>

                            <option
                                value="ENTRADA"
                                <?= $tipoFiltro === "ENTRADA"
                                    ? "selected"
                                    : "" ?>
                            >
                                Entradas
                            </option>

                            <option
                                value="SALIDA"
                                <?= $tipoFiltro === "SALIDA"
                                    ? "selected"
                                    : "" ?>
                            >
                                Salidas
                            </option>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Buscar
                        </button>

                    </div>


                    <div class="col-md-2">

                        <a
                            href="listar.php"
                            class="btn btn-outline-secondary w-100"
                        >
                            Limpiar
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <div class="card shadow-sm">

        <div class="card-body">


            <?php if (count($movimientos) === 0): ?>


                <?php if (
                    $buscar !== ""
                    || $tipoFiltro !== ""
                ): ?>

                    <div class="alert alert-warning mb-0">

                        No se encontraron movimientos
                        que coincidan con los criterios
                        seleccionados.

                    </div>

                <?php else: ?>

                    <div class="alert alert-info mb-0">

                        Aún no existen movimientos registrados.

                    </div>

                <?php endif; ?>


            <?php else: ?>


                <?php if (
                    $buscar !== ""
                    || $tipoFiltro !== ""
                ): ?>

                    <div class="alert alert-info">

                        Se encontraron

                        <strong>
                            <?= count($movimientos) ?>
                        </strong>

                        movimiento(s).

                        <?php if ($buscar !== ""): ?>

                            Producto:

                            <strong>
                                <?= htmlspecialchars($buscar) ?>
                            </strong>.

                        <?php endif; ?>


                        <?php if ($tipoFiltro === "ENTRADA"): ?>

                            Mostrando únicamente
                            <strong>entradas</strong>.

                        <?php elseif ($tipoFiltro === "SALIDA"): ?>

                            Mostrando únicamente
                            <strong>salidas</strong>.

                        <?php endif; ?>

                    </div>

                <?php endif; ?>


                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                    >

                        <thead class="table-light">

                            <tr>

                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Usuario</th>
                                <th>Observación</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($movimientos as $movimiento): ?>

                            <tr>


                                <td>

                                    <?= date(
                                        "d-m-Y H:i",
                                        strtotime(
                                            $movimiento["fecha_hora"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $movimiento["codigo"]
                                        ) ?>

                                    </strong>

                                    <br>

                                    <span class="text-muted">

                                        <?= htmlspecialchars(
                                            $movimiento["producto"]
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if (
                                        $movimiento["tipo"]
                                        === "ENTRADA"
                                    ): ?>

                                        <span class="badge bg-success">

                                            ENTRADA

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">

                                            SALIDA

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= (int)$movimiento["cantidad"] ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $movimiento["usuario"]
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        !empty(
                                            $movimiento["observacion"]
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento["observacion"]
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Sin observación

                                        </span>

                                    <?php endif; ?>

                                </td>


                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>