<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

$idProducto = "";
$tipo = "";
$cantidad = "";
$observacion = "";


/* =========================
   PRODUCTOS DISPONIBLES
========================= */

$sqlProductos = "SELECT
                    p.id_producto,
                    p.codigo,
                    p.nombre,
                    p.stock_actual
                 FROM producto p
                 INNER JOIN categoria c
                    ON p.id_categoria = c.id_categoria
                 WHERE p.estado = 1
                   AND c.estado = 1
                 ORDER BY p.nombre ASC";

$stmtProductos = $pdo->prepare($sqlProductos);
$stmtProductos->execute();

$productos = $stmtProductos->fetchAll();


/* =========================
   REGISTRAR MOVIMIENTO
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $idProducto = $_POST["id_producto"] ?? "";
    $tipo = $_POST["tipo"] ?? "";
    $cantidad = $_POST["cantidad"] ?? "";
    $observacion = trim($_POST["observacion"] ?? "");


    /* =========================
       VALIDACIONES PHP
    ========================= */

    if (
        filter_var(
            $idProducto,
            FILTER_VALIDATE_INT
        ) === false
    ) {

        $error =
            "Debes seleccionar un producto válido.";

    } elseif (
        !in_array(
            $tipo,
            ["ENTRADA", "SALIDA"],
            true
        )
    ) {

        $error =
            "Debes seleccionar un tipo de movimiento válido.";

    } elseif (
        filter_var(
            $cantidad,
            FILTER_VALIDATE_INT
        ) === false
        || (int)$cantidad <= 0
    ) {

        $error =
            "La cantidad debe ser un número entero mayor que cero.";

    } elseif (strlen($observacion) > 255) {

        $error =
            "La observación no puede superar los 255 caracteres.";

    } else {

        try {

            $pdo->beginTransaction();


            /* =========================
               OBTENER Y BLOQUEAR PRODUCTO
            ========================= */

            $sqlProducto = "SELECT
                                p.id_producto,
                                p.nombre,
                                p.stock_actual,
                                p.estado AS producto_estado,
                                c.estado AS categoria_estado,
                                c.nombre AS categoria
                            FROM producto p
                            INNER JOIN categoria c
                                ON p.id_categoria = c.id_categoria
                            WHERE p.id_producto = :id_producto
                            FOR UPDATE";

            $stmtProducto =
                $pdo->prepare($sqlProducto);

            $stmtProducto->execute([
                ":id_producto" => $idProducto
            ]);

            $producto =
                $stmtProducto->fetch();


            if (!$producto) {

                throw new Exception(
                    "El producto seleccionado no existe."
                );
            }


            /* PRODUCTO ACTIVO */

            if (
                (int)$producto["producto_estado"] !== 1
            ) {

                throw new Exception(
                    "El producto seleccionado se encuentra inactivo."
                );
            }


            /* CATEGORÍA ACTIVA */

            if (
                (int)$producto["categoria_estado"] !== 1
            ) {

                throw new Exception(
                    "No es posible registrar movimientos porque la categoría \"" .
                    $producto["categoria"] .
                    "\" se encuentra inactiva."
                );
            }


            /* =========================
               CALCULAR STOCK
            ========================= */

            $stockActual =
                (int)$producto["stock_actual"];

            $cantidadMovimiento =
                (int)$cantidad;


            if ($tipo === "ENTRADA") {

                $nuevoStock =
                    $stockActual
                    + $cantidadMovimiento;

            } else {


                /* =========================
                   EVITAR STOCK NEGATIVO
                ========================= */

                if (
                    $cantidadMovimiento
                    > $stockActual
                ) {

                    throw new Exception(
                        "Stock insuficiente. Actualmente existen " .
                        $stockActual .
                        " unidades disponibles."
                    );
                }


                $nuevoStock =
                    $stockActual
                    - $cantidadMovimiento;
            }


            /* =========================
               ACTUALIZAR STOCK
            ========================= */

            $sqlActualizarStock =
                "UPDATE producto
                 SET stock_actual = :nuevo_stock
                 WHERE id_producto = :id_producto";

            $stmtActualizarStock =
                $pdo->prepare(
                    $sqlActualizarStock
                );

            $stmtActualizarStock->execute([
                ":nuevo_stock" => $nuevoStock,
                ":id_producto" => $idProducto
            ]);


            /* =========================
               REGISTRAR MOVIMIENTO
            ========================= */

            $sqlMovimiento =
                "INSERT INTO movimiento
                (
                    fecha_hora,
                    tipo,
                    cantidad,
                    observacion,
                    id_producto,
                    id_usuario
                )
                VALUES
                (
                    NOW(),
                    :tipo,
                    :cantidad,
                    :observacion,
                    :id_producto,
                    :id_usuario
                )";


            $stmtMovimiento =
                $pdo->prepare(
                    $sqlMovimiento
                );


            $stmtMovimiento->execute([

                ":tipo" =>
                    $tipo,

                ":cantidad" =>
                    $cantidadMovimiento,

                ":observacion" =>
                    $observacion !== ""
                        ? $observacion
                        : null,

                ":id_producto" =>
                    $idProducto,

                ":id_usuario" =>
                    $_SESSION["usuario_id"]
            ]);


            /* =========================
               CONFIRMAR TRANSACCIÓN
            ========================= */

            $pdo->commit();


            $_SESSION["mensaje"] =
                "Movimiento registrado correctamente. " .
                "Stock actualizado: " .
                $nuevoStock .
                " unidades.";


            header("Location: listar.php");
            exit;


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }

            $error =
                $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Nuevo movimiento - TecnoStock
    </title>

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

        <a
            href="listar.php"
            class="btn btn-outline-light"
        >
            Volver a movimientos
        </a>

    </div>

</nav>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">


            <div class="mb-4">

                <h1>
                    Nuevo movimiento
                </h1>

                <p class="text-muted">
                    Registra una entrada o salida de inventario.
                </p>

            </div>


            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <?php if (count($productos) === 0): ?>

                <div class="alert alert-warning">

                    No existen productos activos
                    pertenecientes a categorías activas
                    disponibles para registrar movimientos.

                    <div class="mt-3">

                        <a
                            href="../productos/listar.php"
                            class="btn btn-warning"
                        >
                            Ir a productos
                        </a>

                    </div>

                </div>

            <?php else: ?>


                <div class="card shadow-sm">

                    <div class="card-body p-4">


                        <form
                            method="POST"
                            id="formMovimiento"
                            novalidate
                        >


                            <!-- PRODUCTO -->

                            <div class="mb-3">

                                <label
                                    for="id_producto"
                                    class="form-label"
                                >
                                    Producto *
                                </label>

                                <select
                                    class="form-select"
                                    id="id_producto"
                                    name="id_producto"
                                    required
                                >

                                    <option value="">
                                        Selecciona un producto
                                    </option>


                                    <?php foreach ($productos as $producto): ?>

                                        <option
                                            value="<?= (int)$producto["id_producto"] ?>"
                                            data-stock="<?= (int)$producto["stock_actual"] ?>"
                                            <?= (string)$idProducto ===
                                                (string)$producto["id_producto"]
                                                ? "selected"
                                                : "" ?>
                                        >

                                            <?= htmlspecialchars(
                                                $producto["codigo"]
                                            ) ?>

                                            -

                                            <?= htmlspecialchars(
                                                $producto["nombre"]
                                            ) ?>

                                            (Stock:
                                            <?= (int)$producto["stock_actual"] ?>)

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <div class="invalid-feedback">

                                    Selecciona un producto.

                                </div>

                            </div>


                            <!-- STOCK DISPONIBLE -->

                            <div
                                id="stockDisponible"
                                class="alert alert-info d-none"
                            >
                            </div>


                            <!-- TIPO -->

                            <div class="mb-3">

                                <label
                                    for="tipo"
                                    class="form-label"
                                >
                                    Tipo de movimiento *
                                </label>

                                <select
                                    class="form-select"
                                    id="tipo"
                                    name="tipo"
                                    required
                                >

                                    <option value="">
                                        Selecciona una opción
                                    </option>

                                    <option
                                        value="ENTRADA"
                                        <?= $tipo === "ENTRADA"
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Entrada
                                    </option>

                                    <option
                                        value="SALIDA"
                                        <?= $tipo === "SALIDA"
                                            ? "selected"
                                            : "" ?>
                                    >
                                        Salida
                                    </option>

                                </select>

                                <div class="invalid-feedback">

                                    Selecciona entrada o salida.

                                </div>

                            </div>


                            <!-- CANTIDAD -->

                            <div class="mb-3">

                                <label
                                    for="cantidad"
                                    class="form-label"
                                >
                                    Cantidad *
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="cantidad"
                                    name="cantidad"
                                    min="1"
                                    step="1"
                                    required
                                    value="<?= htmlspecialchars($cantidad) ?>"
                                >

                                <div
                                    id="mensajeCantidad"
                                    class="invalid-feedback"
                                >
                                    La cantidad debe ser mayor que cero.
                                </div>

                                <div class="form-text">

                                    Para una salida, la cantidad
                                    no puede superar el stock disponible.

                                </div>

                            </div>


                            <!-- OBSERVACIÓN -->

                            <div class="mb-4">

                                <label
                                    for="observacion"
                                    class="form-label"
                                >
                                    Observación
                                </label>

                                <textarea
                                    class="form-control"
                                    id="observacion"
                                    name="observacion"
                                    rows="3"
                                    maxlength="255"
                                    placeholder="Ej: Compra a proveedor, venta, ajuste de inventario..."
                                ><?= htmlspecialchars($observacion) ?></textarea>

                                <div class="form-text">

                                    Máximo

                                    <span id="contadorObservacion">
                                        0
                                    </span>/255 caracteres.

                                </div>

                            </div>


                            <!-- BOTONES -->

                            <div class="d-flex justify-content-end gap-2">

                                <a
                                    href="listar.php"
                                    class="btn btn-secondary"
                                >
                                    Cancelar
                                </a>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Registrar movimiento
                                </button>

                            </div>


                        </form>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>


<script>

/* =========================
   ELEMENTOS
========================= */

const formMovimiento =
    document.getElementById("formMovimiento");

const productoSelect =
    document.getElementById("id_producto");

const tipoSelect =
    document.getElementById("tipo");

const cantidadInput =
    document.getElementById("cantidad");

const mensajeCantidad =
    document.getElementById("mensajeCantidad");

const stockDisponible =
    document.getElementById("stockDisponible");

const observacionInput =
    document.getElementById("observacion");

const contadorObservacion =
    document.getElementById("contadorObservacion");


/* =========================
   OBTENER STOCK
========================= */

function obtenerStockDisponible() {

    if (!productoSelect) {
        return 0;
    }

    const opcion =
        productoSelect.options[
            productoSelect.selectedIndex
        ];


    if (
        !opcion
        || opcion.value === ""
    ) {

        return 0;
    }


    return Number(
        opcion.getAttribute("data-stock")
    );
}


/* =========================
   MOSTRAR STOCK
========================= */

function mostrarStock() {

    if (
        !productoSelect
        || !stockDisponible
    ) {
        return;
    }


    const opcion =
        productoSelect.options[
            productoSelect.selectedIndex
        ];


    if (
        !opcion
        || opcion.value === ""
    ) {

        stockDisponible.classList.add(
            "d-none"
        );

        stockDisponible.textContent = "";

        return;
    }


    const stock =
        obtenerStockDisponible();


    stockDisponible.textContent =
        "Stock disponible actualmente: " +
        stock +
        " unidades.";


    stockDisponible.classList.remove(
        "d-none"
    );
}


/* =========================
   VALIDAR CANTIDAD
========================= */

function validarCantidad() {

    if (
        !cantidadInput
        || !tipoSelect
        || !productoSelect
    ) {

        return true;
    }


    const cantidad =
        Number(cantidadInput.value);

    const stock =
        obtenerStockDisponible();


    /* CAMPO VACÍO */

    if (cantidadInput.value === "") {

        cantidadInput.classList.add(
            "is-invalid"
        );

        cantidadInput.classList.remove(
            "is-valid"
        );

        mensajeCantidad.textContent =
            "Debes ingresar una cantidad.";

        return false;
    }


    /* ENTERO MAYOR QUE CERO */

    if (
        !Number.isInteger(cantidad)
        || cantidad <= 0
    ) {

        cantidadInput.classList.add(
            "is-invalid"
        );

        cantidadInput.classList.remove(
            "is-valid"
        );

        mensajeCantidad.textContent =
            "La cantidad debe ser un número entero mayor que cero.";

        return false;
    }


    /* SALIDA SUPERIOR AL STOCK */

    if (
        tipoSelect.value === "SALIDA"
        && cantidad > stock
    ) {

        cantidadInput.classList.add(
            "is-invalid"
        );

        cantidadInput.classList.remove(
            "is-valid"
        );

        mensajeCantidad.textContent =
            "Stock insuficiente. Solo existen " +
            stock +
            " unidades disponibles.";

        return false;
    }


    /* CORRECTO */

    cantidadInput.classList.remove(
        "is-invalid"
    );

    cantidadInput.classList.add(
        "is-valid"
    );

    return true;
}


/* =========================
   CONTADOR OBSERVACIÓN
========================= */

function actualizarContador() {

    if (
        !observacionInput
        || !contadorObservacion
    ) {

        return;
    }


    contadorObservacion.textContent =
        observacionInput.value.length;
}


/* =========================
   EVENTOS
========================= */

if (productoSelect) {

    productoSelect.addEventListener(
        "change",
        function () {

            mostrarStock();

            if (cantidadInput.value !== "") {
                validarCantidad();
            }
        }
    );


    mostrarStock();
}


if (tipoSelect) {

    tipoSelect.addEventListener(
        "change",
        function () {

            if (cantidadInput.value !== "") {
                validarCantidad();
            }
        }
    );
}


if (cantidadInput) {

    cantidadInput.addEventListener(
        "input",
        validarCantidad
    );
}


if (observacionInput) {

    observacionInput.addEventListener(
        "input",
        actualizarContador
    );

    actualizarContador();
}


/* =========================
   VALIDAR FORMULARIO
========================= */

if (formMovimiento) {

    formMovimiento.addEventListener(
        "submit",
        function (event) {

            let valido = true;


            /* PRODUCTO */

            if (productoSelect.value === "") {

                productoSelect.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                productoSelect.classList.remove(
                    "is-invalid"
                );

                productoSelect.classList.add(
                    "is-valid"
                );
            }


            /* TIPO */

            if (
                tipoSelect.value !== "ENTRADA"
                &&
                tipoSelect.value !== "SALIDA"
            ) {

                tipoSelect.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                tipoSelect.classList.remove(
                    "is-invalid"
                );

                tipoSelect.classList.add(
                    "is-valid"
                );
            }


            /* CANTIDAD */

            if (!validarCantidad()) {

                valido = false;
            }


            /* OBSERVACIÓN */

            if (
                observacionInput.value.length > 255
            ) {

                observacionInput.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                observacionInput.classList.remove(
                    "is-invalid"
                );
            }


            /* DETENER ENVÍO */

            if (!valido) {

                event.preventDefault();

                event.stopPropagation();
            }

        }
    );
}

</script>

</body>

</html>