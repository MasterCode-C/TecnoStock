<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";

$codigo = "";
$nombre = "";
$descripcion = "";
$precioIngresado = "";
$stockActual = "";
$stockMinimo = "";
$idCategoria = "";


/* =========================
   CATEGORÍAS ACTIVAS
========================= */

$sqlCategorias = "
    SELECT
        id_categoria,
        nombre
    FROM categoria
    WHERE estado = 1
    ORDER BY nombre ASC
";

$stmtCategorias = $pdo->prepare($sqlCategorias);
$stmtCategorias->execute();

$categorias = $stmtCategorias->fetchAll();


/* =========================
   PROCESAR FORMULARIO
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precioIngresado = trim($_POST["precio"] ?? "");

    /*
    Permite ingresar:

    12000
    12.000
    12,000

    y almacenar 12000.
    */

    $precioLimpio = str_replace(
        [".", ",", " "],
        "",
        $precioIngresado
    );

    $stockActual = $_POST["stock_actual"] ?? "";
    $stockMinimo = $_POST["stock_minimo"] ?? "";
    $idCategoria = $_POST["id_categoria"] ?? "";


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

    } elseif (
        $precioLimpio === ""
        || !ctype_digit($precioLimpio)
    ) {

        $error =
            "El precio debe contener solo números. Ejemplo: 12.000.";

    } elseif ((int)$precioLimpio < 0) {

        $error =
            "El precio no puede ser negativo.";

    } elseif (
        filter_var(
            $stockActual,
            FILTER_VALIDATE_INT
        ) === false
        || (int)$stockActual < 0
    ) {

        $error =
            "El stock actual debe ser un número entero mayor o igual a cero.";

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
            SELECT id_categoria
            FROM categoria
            WHERE id_categoria = :id_categoria
              AND estado = 1
            LIMIT 1
        ";

        $stmtCategoria =
            $pdo->prepare($sqlCategoria);

        $stmtCategoria->execute([
            ":id_categoria" => $idCategoria
        ]);


        if (!$stmtCategoria->fetch()) {

            $error =
                "La categoría seleccionada no está disponible.";

        } else {


            /* =========================
               CÓDIGO ÚNICO
            ========================= */

            $sqlExiste = "
                SELECT id_producto
                FROM producto
                WHERE codigo = :codigo
                LIMIT 1
            ";

            $stmtExiste =
                $pdo->prepare($sqlExiste);

            $stmtExiste->execute([
                ":codigo" => $codigo
            ]);


            if ($stmtExiste->fetch()) {

                $error =
                    "Ya existe un producto con ese código.";

            } else {


                /* =========================
                   INSERTAR PRODUCTO
                ========================= */

                $sql = "
                    INSERT INTO producto
                    (
                        codigo,
                        nombre,
                        descripcion,
                        precio,
                        stock_actual,
                        stock_minimo,
                        estado,
                        id_categoria
                    )
                    VALUES
                    (
                        :codigo,
                        :nombre,
                        :descripcion,
                        :precio,
                        :stock_actual,
                        :stock_minimo,
                        1,
                        :id_categoria
                    )
                ";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ":codigo" => $codigo,
                    ":nombre" => $nombre,
                    ":descripcion" => $descripcion,
                    ":precio" => $precioLimpio,
                    ":stock_actual" => $stockActual,
                    ":stock_minimo" => $stockMinimo,
                    ":id_categoria" => $idCategoria
                ]);


                $_SESSION["mensaje"] =
                    "Producto registrado correctamente.";


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
        Nuevo producto - TecnoStock
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
                    Nuevo producto
                </h1>

                <p class="text-muted">
                    Registra un nuevo producto en el inventario.
                </p>

            </div>


            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <?php if (count($categorias) === 0): ?>

                <div class="alert alert-warning">

                    No existen categorías activas.

                    Debes activar o crear una categoría
                    antes de registrar productos.

                    <div class="mt-3">

                        <a
                            href="../categorias/listar.php"
                            class="btn btn-warning"
                        >
                            Ir a categorías
                        </a>

                    </div>

                </div>

            <?php else: ?>


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

                                    <option value="">
                                        Selecciona una categoría
                                    </option>


                                    <?php foreach ($categorias as $categoria): ?>

                                        <option
                                            value="<?= (int)$categoria["id_categoria"] ?>"
                                            <?= (string)$idCategoria ===
                                                (string)$categoria["id_categoria"]
                                                ? "selected"
                                                : "" ?>
                                        >

                                            <?= htmlspecialchars(
                                                $categoria["nombre"]
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                                <div class="invalid-feedback">
                                    Selecciona una categoría.
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
                                            Ingresa un precio válido.
                                        </div>

                                    </div>

                                    <div class="form-text">
                                        Puedes escribir 12000,
                                        12.000 o 12,000.
                                    </div>

                                </div>


                                <div class="col-md-4 mb-3">

                                    <label
                                        for="stock_actual"
                                        class="form-label"
                                    >
                                        Stock actual *
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="stock_actual"
                                        name="stock_actual"
                                        min="0"
                                        step="1"
                                        required
                                        value="<?= htmlspecialchars($stockActual) ?>"
                                    >

                                    <div class="invalid-feedback">
                                        Ingresa un stock igual o mayor a cero.
                                    </div>

                                </div>


                                <div class="col-md-4 mb-4">

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
                                        Ingresa un stock mínimo igual o mayor a cero.
                                    </div>

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
                                    Guardar producto
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

const stockActualInput =
    document.getElementById("stock_actual");

const stockMinimoInput =
    document.getElementById("stock_minimo");

const contadorDescripcion =
    document.getElementById("contadorDescripcion");


/* =========================
   FORMATEAR PRECIO
========================= */

if (precioInput) {

    precioInput.addEventListener(
        "input",
        function () {

            let valor =
                this.value.replace(/\D/g, "");

            if (valor === "") {

                this.value = "";

                return;
            }

            this.value =
                new Intl.NumberFormat(
                    "es-CL"
                ).format(
                    parseInt(valor, 10)
                );
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
   VALIDACIÓN JAVASCRIPT
========================= */

if (formProducto) {

    formProducto.addEventListener(
        "submit",
        function (event) {

            let valido = true;


            /* CÓDIGO */

            if (
                codigoInput.value.trim() === ""
                || codigoInput.value.trim().length > 50
            ) {

                codigoInput.classList.add(
                    "is-invalid"
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
                || nombreInput.value.trim().length > 150
            ) {

                nombreInput.classList.add(
                    "is-invalid"
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

            const precioNumerico =
                precioInput.value.replace(
                    /\D/g,
                    ""
                );


            if (
                precioNumerico === ""
                || Number(precioNumerico) < 0
            ) {

                precioInput.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                precioInput.classList.remove(
                    "is-invalid"
                );

                precioInput.classList.add(
                    "is-valid"
                );
            }


            /* STOCK ACTUAL */

            const stockActual =
                Number(stockActualInput.value);


            if (
                stockActualInput.value === ""
                || !Number.isInteger(stockActual)
                || stockActual < 0
            ) {

                stockActualInput.classList.add(
                    "is-invalid"
                );

                valido = false;

            } else {

                stockActualInput.classList.remove(
                    "is-invalid"
                );

                stockActualInput.classList.add(
                    "is-valid"
                );
            }


            /* STOCK MÍNIMO */

            const stockMinimo =
                Number(stockMinimoInput.value);


            if (
                stockMinimoInput.value === ""
                || !Number.isInteger(stockMinimo)
                || stockMinimo < 0
            ) {

                stockMinimoInput.classList.add(
                    "is-invalid"
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


            /* DETENER FORMULARIO */

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