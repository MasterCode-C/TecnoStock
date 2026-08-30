<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$buscar = trim($_GET["buscar"] ?? "");
$filtro = $_GET["filtro"] ?? "";
$idCategoria = $_GET["categoria"] ?? "";
$estado = $_GET["estado"] ?? "";

$condiciones = [];
$parametros = [];


/* =========================
   BUSQUEDA
========================= */

if ($buscar !== "") {

    $condiciones[] = "
        (
            p.codigo LIKE :buscar_codigo
            OR p.nombre LIKE :buscar_nombre
        )
    ";

    $parametros[":buscar_codigo"] = "%" . $buscar . "%";
    $parametros[":buscar_nombre"] = "%" . $buscar . "%";
}


/* =========================
   FILTRO STOCK BAJO
========================= */

if ($filtro === "stock_bajo") {

    $condiciones[] = "
        p.stock_actual < p.stock_minimo
    ";
}


/* =========================
   FILTRO POR CATEGORIA
========================= */

if (
    $idCategoria !== ""
    && ctype_digit((string)$idCategoria)
) {

    $condiciones[] = "p.id_categoria = :id_categoria";

    $parametros[":id_categoria"] = (int)$idCategoria;
}


/* =========================
   FILTRO POR ESTADO EFECTIVO
========================= */

/*
Un producto se considera ACTIVO solamente si:

p.estado = 1
Y
c.estado = 1

Si el producto o su categoría están inactivos,
el producto se considera INACTIVO.
*/

if ($estado === "1") {

    $condiciones[] = "
        p.estado = 1
        AND c.estado = 1
    ";

} elseif ($estado === "0") {

    $condiciones[] = "
        (
            p.estado = 0
            OR c.estado = 0
        )
    ";
}


/* =========================
   CONSULTA PRODUCTOS
========================= */

$sql = "SELECT 
            p.id_producto,
            p.codigo,
            p.nombre,
            p.descripcion,
            p.precio,
            p.stock_actual,
            p.stock_minimo,
            p.estado,
            p.id_categoria,

            c.nombre AS categoria,
            c.estado AS categoria_estado

        FROM producto p

        INNER JOIN categoria c 
            ON p.id_categoria = c.id_categoria";


if (count($condiciones) > 0) {

    $sql .= " WHERE " . implode(" AND ", $condiciones);
}


$sql .= " ORDER BY p.nombre ASC";


$stmt = $pdo->prepare($sql);

$stmt->execute($parametros);

$productos = $stmt->fetchAll();


/* =========================
   CONSULTA CATEGORIAS
========================= */

$sqlCategorias = "
    SELECT
        id_categoria,
        nombre,
        estado
    FROM categoria
    ORDER BY nombre ASC
";

$stmtCategorias = $pdo->prepare($sqlCategorias);

$stmtCategorias->execute();

$categorias = $stmtCategorias->fetchAll();


/* =========================
   MENSAJE
========================= */

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

    <title>Productos - TecnoStock</title>

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

        <div>

            <a
                href="../dashboard.php"
                class="btn btn-outline-light me-2"
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
                Gestión de productos
            </h1>

            <p class="text-muted mb-0">
                Consulta y administra los productos del inventario.
            </p>

        </div>

        <a
            href="crear.php"
            class="btn btn-primary"
        >
            + Nuevo producto
        </a>

    </div>


    <?php if ($mensaje): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($mensaje) ?>

        </div>

    <?php endif; ?>


    <!-- BUSQUEDA Y FILTROS -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row g-2 align-items-end">


                    <!-- BUSCAR -->

                    <div class="col-md-3">

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


                    <!-- CATEGORIA -->

                    <div class="col-md-2">

                        <label
                            for="categoria"
                            class="form-label"
                        >
                            Categoría
                        </label>

                        <select
                            class="form-select"
                            id="categoria"
                            name="categoria"
                        >

                            <option value="">
                                Todas
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

                                    <?php if (
                                        (int)$categoria["estado"] === 0
                                    ): ?>

                                        (Inactiva)

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- STOCK -->

                    <div class="col-md-2">

                        <label
                            for="filtro"
                            class="form-label"
                        >
                            Stock
                        </label>

                        <select
                            class="form-select"
                            id="filtro"
                            name="filtro"
                        >

                            <option
                                value=""
                                <?= $filtro === ""
                                    ? "selected"
                                    : "" ?>
                            >
                                Todos
                            </option>

                            <option
                                value="stock_bajo"
                                <?= $filtro === "stock_bajo"
                                    ? "selected"
                                    : "" ?>
                            >
                                Stock bajo
                            </option>

                        </select>

                    </div>


                    <!-- ESTADO -->

                    <div class="col-md-2">

                        <label
                            for="estado"
                            class="form-label"
                        >
                            Estado
                        </label>

                        <select
                            class="form-select"
                            id="estado"
                            name="estado"
                        >

                            <option
                                value=""
                                <?= $estado === ""
                                    ? "selected"
                                    : "" ?>
                            >
                                Todos
                            </option>

                            <option
                                value="1"
                                <?= $estado === "1"
                                    ? "selected"
                                    : "" ?>
                            >
                                Activos
                            </option>

                            <option
                                value="0"
                                <?= $estado === "0"
                                    ? "selected"
                                    : "" ?>
                            >
                                Inactivos
                            </option>

                        </select>

                    </div>


                    <!-- BUSCAR -->

                    <div class="col-md-1">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Buscar
                        </button>

                    </div>


                    <!-- LIMPIAR -->

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


    <!-- LISTADO -->

    <div class="card shadow-sm">

        <div class="card-body">


            <?php if (count($productos) === 0): ?>


                <?php if (
                    $buscar !== ""
                    || $filtro !== ""
                    || $idCategoria !== ""
                    || $estado !== ""
                ): ?>

                    <div class="alert alert-warning mb-0">

                        No se encontraron productos
                        que coincidan con los criterios seleccionados.

                    </div>

                <?php else: ?>

                    <div class="alert alert-info mb-0">

                        No existen productos registrados.

                    </div>

                <?php endif; ?>


            <?php else: ?>


                <?php if (
                    $buscar !== ""
                    || $filtro !== ""
                    || $idCategoria !== ""
                    || $estado !== ""
                ): ?>

                    <div class="alert alert-info">

                        Se encontraron

                        <strong>
                            <?= count($productos) ?>
                        </strong>

                        producto(s).

                        <?php if ($estado === "1"): ?>

                            Mostrando productos
                            <strong>activos</strong>.

                        <?php elseif ($estado === "0"): ?>

                            Mostrando productos
                            <strong>inactivos</strong>.

                        <?php endif; ?>

                    </div>

                <?php endif; ?>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Stock mínimo</th>
                                <th>Estado</th>

                                <th class="text-end">
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($productos as $producto): ?>

                            <?php

                            /*
                            ESTADO EFECTIVO DEL PRODUCTO
                            */

                            $productoActivo =
                                (int)$producto["estado"] === 1
                                &&
                                (int)$producto["categoria_estado"] === 1;

                            ?>

                            <tr>


                                <td>

                                    <?= htmlspecialchars(
                                        $producto["codigo"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $producto["nombre"]
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $producto["categoria"]
                                    ) ?>

                                    <?php if (
                                        (int)$producto["categoria_estado"] === 0
                                    ): ?>

                                        <br>

                                        <small class="text-danger">
                                            Categoría inactiva
                                        </small>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    $<?= number_format(
                                        $producto["precio"],
                                        0,
                                        ",",
                                        "."
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        $producto["stock_actual"]
                                        < $producto["stock_minimo"]
                                    ): ?>

                                        <span class="badge bg-danger">

                                            <?= (int)$producto["stock_actual"] ?>

                                            - Stock bajo

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">

                                            <?= (int)$producto["stock_actual"] ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= (int)$producto["stock_minimo"] ?>

                                </td>


                                <!-- ESTADO EFECTIVO -->

                                <td>

                                    <?php if ($productoActivo): ?>

                                        <span class="badge bg-success">

                                            Activo

                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            Inactivo

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td class="text-end">

                                    <a
                                        href="editar.php?id=<?= (int)$producto["id_producto"] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Editar
                                    </a>

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