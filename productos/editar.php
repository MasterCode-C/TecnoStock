<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";


/* =========================
   VALIDAR ID
========================= */

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {
    header("Location: listar.php");
    exit;
}


/* =========================
   OBTENER PRODUCTO
========================= */

$sqlProducto = "
    SELECT
        id_producto,
        codigo,
        nombre,
        descripcion,
        precio,
        stock_actual,
        stock_minimo,
        estado,
        id_categoria
    FROM producto
    WHERE id_producto = :id
    LIMIT 1
";

$stmtProducto = $pdo->prepare($sqlProducto);

$stmtProducto->execute([
    ":id" => $id
]);

$producto = $stmtProducto->fetch();

if (!$producto) {

    $_SESSION["mensaje"] =
        "El producto solicitado no existe.";

    header("Location: listar.php");
    exit;
}


/* Categoría original del producto */

$idCategoriaOriginal =
    (int)$producto["id_categoria"];


/* =========================
   OBTENER CATEGORÍAS
========================= */

$sqlCategorias = "
    SELECT
        id_categoria,
        nombre,
        estado
    FROM categoria
    ORDER BY nombre ASC
";

$stmtCategorias =
    $pdo->prepare($sqlCategorias);

$stmtCategorias->execute();

$categorias =
    $stmtCategorias->fetchAll();


/* =========================
   VALORES INICIALES
========================= */

$error = "";

$codigo =
    $producto["codigo"];

$nombre =
    $producto["nombre"];

$descripcion =
    $producto["descripcion"] ?? "";

$precioIngresado =
    number_format(
        (float)$producto["precio"],
        0,
        ",",
        "."
    );

$stockActual =
    (int)$producto["stock_actual"];

$stockMinimo =
    $producto["stock_minimo"];

$estado =
    (int)$producto["estado"];

$idCategoria =
    $producto["id_categoria"];


/* =========================
   PROCESAR FORMULARIO
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo =
        trim($_POST["codigo"] ?? "");

    $nombre =
        trim($_POST["nombre"] ?? "");

    $descripcion =
        trim($_POST["descripcion"] ?? "");

    $precioIngresado =
        trim($_POST["precio"] ?? "");


    /* =========================
       NORMALIZAR PRECIO
    ========================= */

    $precioLimpio =
        str_replace(
            [".", ",", " "],
            "",
            $precioIngresado
        );


    $stockMinimo =
        $_POST["stock_minimo"] ?? "";

    $idCategoria =
        $_POST["id_categoria"] ?? "";

    $estado =
        isset($_POST["estado"])
            ? 1
            : 0;


    /* =========================
       VALIDACIONES PHP
    ========================= */

    if ($codigo === "") {

        $error =
            "El código del producto es obligatorio.";

    } elseif (strlen($codigo) > 50) {

        $error =
            "El código no puede superar los 50 caracteres.";

    } elseif ($nombre === "") {

        $error =
            "El nombre del producto es obligatorio.";

    } elseif (strlen($nombre) > 150) {

        $error =
            "El nombre no puede superar los 150 caracteres.";

    } elseif (strlen($descripcion) > 255) {

        $error =
            "La descripción no puede superar los 255 caracteres.";

    } elseif ($precioLimpio === "") {

        $error =
            "El precio del producto es obligatorio.";

    } elseif (!ctype_digit($precioLimpio)) {

        $error =
            "El precio debe contener solo números. Ejemplo: 12.000.";

    } elseif ((int)$precioLimpio <= 0) {

        $error =
            "El precio debe ser mayor que $0.";

    } elseif (
        filter_var(
            $stockMinimo,
            FILTER_VALIDATE_INT
        ) === false
        || (int)$stockMinimo < 0
    ) {

        $error =
            "El stock mínimo debe ser un número entero mayor o igual a cero.";

    } elseif (
        filter_var(
            $idCategoria,
            FILTER_VALIDATE_INT
        ) === false
    ) {

        $error =
            "Debes seleccionar una categoría válida.";

    } else {


        /* =========================
           VALIDAR CATEGORÍA
        ========================= */

        $sqlCategoria = "
            SELECT
                id_categoria,
                nombre,
                estado
            FROM categoria
            WHERE id_categoria = :id_categoria
            LIMIT 1
        ";

        $stmtCategoria =
            $pdo->prepare($sqlCategoria);

        $stmtCategoria->execute([
            ":id_categoria" => $idCategoria
        ]);

        $categoriaSeleccionada =
            $stmtCategoria->fetch();


        if (!$categoriaSeleccionada) {

            $error =
                "La categoría seleccionada no existe.";

        } elseif (
            (int)$categoriaSeleccionada["estado"] === 0
            &&
            (int)$idCategoria !== $idCategoriaOriginal
        ) {

            $error =
                "No puedes asignar el producto a una categoría inactiva.";

        } else {


            /* =========================
               CÓDIGO ÚNICO
            ========================= */

            $sqlExiste = "
                SELECT id_producto
                FROM producto
                WHERE codigo = :codigo
                  AND id_producto <> :id
                LIMIT 1
            ";

            $stmtExiste =
                $pdo->prepare($sqlExiste);

            $stmtExiste->execute([
                ":codigo" => $codigo,
                ":id" => $id
            ]);


            if ($stmtExiste->fetch()) {

                $error =
                    "Ya existe otro producto con ese código.";

            } else {


                /* =========================
                   ACTUALIZAR PRODUCTO
                ========================= */

                $sqlActualizar = "
                    UPDATE producto
                    SET
                        codigo = :codigo,
                        nombre = :nombre,
                        descripcion = :descripcion,
                        precio = :precio,
                        stock_minimo = :stock_minimo,
                        estado = :estado,
                        id_categoria = :id_categoria
                    WHERE id_producto = :id
                ";

                $stmtActualizar =
                    $pdo->prepare($sqlActualizar);

                $stmtActualizar->execute([
                    ":codigo" => $codigo,
                    ":nombre" => $nombre,
                    ":descripcion" => $descripcion,
                    ":precio" => $precioLimpio,
                    ":stock_minimo" => $stockMinimo,
                    ":estado" => $estado,
                    ":id_categoria" => $idCategoria,
                    ":id" => $id
                ]);


                $_SESSION["mensaje"] =
                    "Producto actualizado correctamente.";


                header("Location: listar.php");
                exit;
            }
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
        Editar producto - TecnoStock
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
            Volver a productos
        </a>

    </div>

</nav>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">


            <div class="mb-4">

                <h1>
                    Editar producto
                </h1>

                <p class="text-muted">

                    Modifica los datos del producto.

                    Las existencias se controlan
                    mediante movimientos de inventario.

                </p>

            </div>


            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <div class="card shadow-sm">

                <div class="card-body p-4">


                    <form
                        method="POST"
                        id="formProducto"
                        novalidate
                    >


                        <!-- CÓDIGO Y NOMBRE -->

                        <div class="row">


                            <div class="col-md-4 mb-3">

                                <label
                                    for="codigo"
                                    class="form-label"
                                >
                                    Código *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="codigo"
                                    name="codigo"
                                    maxlength="50"
                                    required
                                    value="<?= htmlspecialchars($codigo) ?>"
                                >

                                <div class="invalid-feedback">
                                    Ingresa un código válido.
                                </div>

                            </div>


                            <div class="col-md-8 mb-3">

                                <label
                                    for="nombre"
                                    class="form-label"
                                >
                                    Nombre *
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombre"
                                    name="nombre"
                                    maxlength="150"
                                    required
                                    value="<?= htmlspecialchars($nombre) ?>"
                                >

                                <div class="invalid-feedback">
                                    Ingresa el nombre del producto.
                                </div>

                            </div>


                        </div>


                        <!-- CATEGORÍA -->

                        <div class="mb-3">

                            <label
                                for="id_categoria"
                                class="form-label"
                            >
                                Categoría *
                            </label>

                            <select
                                class="form-select"
                                id="id_categoria"
                                name="id_categoria"
                                required
                            >


                                <?php foreach ($categorias as $categoria): ?>

                                    <?php

                                    $categoriaInactiva =
                                        (int)$categoria["estado"] === 0;

                                    $esCategoriaActual =
                                        (int)$categoria["id_categoria"]
                                        ===
                                        $idCategoriaOriginal;

                                    ?>

                                    <option
                                        value="<?= (int)$categoria["id_categoria"] ?>"

                                        <?= (string)$idCategoria ===
                                            (string)$categoria["id_categoria"]
                                            ? "selected"
                                            : "" ?>

                                        <?= $categoriaInactiva
                                            && !$esCategoriaActual
                                            ? "disabled"
                                            : "" ?>
                                    >

                                        <?= htmlspecialchars(
                                            $categoria["nombre"]
                                        ) ?>

                                        <?= $categoriaInactiva
                                            ? " (Inactiva)"
                                            : "" ?>

                                    </option>

                                <?php endforeach; ?>


                            </select>

                            <div class="invalid-feedback">
                                Selecciona una categoría válida.
                            </div>

                            <div class="form-text">

                                No se puede trasladar un producto
                                a una categoría inactiva.

                            </div>

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="mb-3">

                            <label
                                for="descripcion"
                                class="form-label"
                            >
                                Descripción
                            </label>

                            <textarea
                                class="form-control"
                                id="descripcion"
                                name="descripcion"
                                maxlength="255"
                                rows="3"
                            ><?= htmlspecialchars($descripcion) ?></textarea>

                            <div class="form-text">

                                Máximo 255 caracteres.

                                <span id="contadorDescripcion">
                                    0
                                </span>/255

                            </div>

                        </div>


                        <!-- PRECIO Y STOCK -->

                        <div class="row">


                            <!-- PRECIO -->

                            <div class="col-md-4 mb-3">

                                <label
                                    for="precio"
                                    class="form-label"
                                >
                                    Precio *
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        $
                                    </span>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="precio"
                                        name="precio"
                                        inputmode="numeric"
                                        maxlength="15"
                                        required
                                        placeholder="Ej: 12.000"
                                        value="<?= htmlspecialchars($precioIngresado) ?>"
                                    >

                                    <div class="invalid-feedback">
                                        El precio es obligatorio y debe ser mayor que $0.
                                    </div>

                                </div>

                                <div class="form-text">

                                    Puedes escribir 12000,
                                    12.000 o 12,000.

                                </div>

                            </div>


                            <!-- STOCK ACTUAL -->

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Stock actual
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    value="<?= (int)$stockActual ?>"
                                    disabled
                                >

                                <div class="form-text">

                                    Se modifica mediante
                                    entradas y salidas.

                                </div>

                            </div>


                            <!-- STOCK MÍNIMO -->

                            <div class="col-md-4 mb-3">

                                <label
                                    for="stock_minimo"
                                    class="form-label"
                                >
                                    Stock mínimo *
                                </label>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="stock_minimo"
                                    name="stock_minimo"
                                    min="0"
                                    step="1"
                                    required
                                    value="<?= htmlspecialchars($stockMinimo) ?>"
                                >

                                <div class="invalid-feedback">

                                    Ingresa un stock mínimo
                                    igual o mayor a cero.

                                </div>

                            </div>


                        </div>


                        <!-- ESTADO -->

                        <div class="form-check form-switch mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="estado"
                                name="estado"
                                <?= $estado === 1
                                    ? "checked"
                                    : "" ?>
                            >

                            <label
                                class="form-check-label"
                                for="estado"
                            >
                                Producto activo
                            </label>

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
                                Guardar cambios
                            </button>

                        </div>


                    </form>

                </div>

            </div>


        </div>

    </div>

</div>


<script>

/* =========================
   ELEMENTOS
========================= */

const formProducto =
    document.getElementById("formProducto");

const codigoInput =
    document.getElementById("codigo");

const nombreInput =
    document.getElementById("nombre");

const categoriaInput =
    document.getElementById("id_categoria");

const descripcionInput =
    document.getElementById("descripcion");

const precioInput =
    document.getElementById("precio");

const stockMinimoInput =
    document.getElementById("stock_minimo");

const contadorDescripcion =
    document.getElementById("contadorDescripcion");


/* =========================
   VALIDAR PRECIO
========================= */

function validarPrecio() {

    if (!precioInput) {
        return false;
    }

    const precioNumerico =
        precioInput.value.replace(/\D/g, "");

    if (
        precioNumerico === ""
        ||
        Number(precioNumerico) <= 0
    ) {

        precioInput.classList.add(
            "is-invalid"
        );

        precioInput.classList.remove(
            "is-valid"
        );

        return false;
    }

    precioInput.classList.remove(
        "is-invalid"
    );

    precioInput.classList.add(
        "is-valid"
    );

    return true;
}


/* =========================
   FORMATEAR Y VALIDAR PRECIO
========================= */

if (precioInput) {

    precioInput.addEventListener(
        "input",
        function () {

            let valor =
                this.value.replace(/\D/g, "");

            if (valor === "") {

                this.value = "";

                validarPrecio();

                return;
            }

            this.value =
                new Intl.NumberFormat(
                    "es-CL"
                ).format(
                    parseInt(valor, 10)
                );

            validarPrecio();
        }
    );
}


/* =========================
   CONTADOR DESCRIPCIÓN
========================= */

function actualizarContador() {

    if (
        !descripcionInput
        || !contadorDescripcion
    ) {
        return;
    }

    contadorDescripcion.textContent =
        descripcionInput.value.length;
}


if (descripcionInput) {

    descripcionInput.addEventListener(
        "input",
        actualizarContador
    );

    actualizarContador();
}


/* =========================
   VALIDACIONES JAVASCRIPT
========================= */

if (formProducto) {

    formProducto.addEventListener(
        "submit",
        function (event) {

            let valido = true;


            /* CÓDIGO */

            if (
                codigoInput.value.trim() === ""
                ||
                codigoInput.value.trim().length > 50
            ) {

                codigoInput.classList.add(
                    "is-invalid"
                );

                codigoInput.classList.remove(
                    "is-valid"
                );

                valido = false;

            } else {

                codigoInput.classList.remove(
                    "is-invalid"
                );

                codigoInput.classList.add(
                    "is-valid"
                );
            }


            /* NOMBRE */

            if (
                nombreInput.value.trim() === ""
                ||
                nombreInput.value.trim().length > 150
            ) {

                nombreInput.classList.add(
                    "is-invalid"
                );

                nombreInput.classList.remove(
                    "is-valid"
                );

                valido = false;

            } else {

                nombreInput.classList.remove(
                    "is-invalid"
                );

                nombreInput.classList.add(
                    "is-valid"
                );
            }


            /* CATEGORÍA */

            if (categoriaInput.value === "") {

                categoriaInput.classList.add(
                    "is-invalid"
                );

                categoriaInput.classList.remove(
                    "is-valid"
                );

                valido = false;

            } else {

                categoriaInput.classList.remove(
                    "is-invalid"
                );

                categoriaInput.classList.add(
                    "is-valid"
                );
            }


            /* PRECIO */

            if (!validarPrecio()) {

                valido = false;
            }


            /* STOCK MÍNIMO */

            const stockMinimo =
                Number(stockMinimoInput.value);


            if (
                stockMinimoInput.value === ""
                ||
                !Number.isInteger(stockMinimo)
                ||
                stockMinimo < 0
            ) {

                stockMinimoInput.classList.add(
                    "is-invalid"
                );

                stockMinimoInput.classList.remove(
                    "is-valid"
                );

                valido = false;

            } else {

                stockMinimoInput.classList.remove(
                    "is-invalid"
                );

                stockMinimoInput.classList.add(
                    "is-valid"
                );
            }


            /* DESCRIPCIÓN */

            if (
                descripcionInput.value.length > 255
            ) {

                descripcionInput.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                descripcionInput.classList.remove(
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