<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$error = "";
$nombre = "";
$descripcion = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    // Validaciones del servidor
    if ($nombre === "") {

        $error = "El nombre de la categoría es obligatorio.";

    } elseif (strlen($nombre) > 100) {

        $error = "El nombre no puede superar los 100 caracteres.";

    } elseif (strlen($descripcion) > 255) {

        $error = "La descripción no puede superar los 255 caracteres.";

    } else {

        // Comprobar que la categoría no exista
        $sqlExiste = "SELECT id_categoria
                      FROM categoria
                      WHERE nombre = :nombre
                      LIMIT 1";

        $stmtExiste = $pdo->prepare($sqlExiste);

        $stmtExiste->execute([
            ":nombre" => $nombre
        ]);

        if ($stmtExiste->fetch()) {

            $error = "Ya existe una categoría con ese nombre.";

        } else {

            $sql = "INSERT INTO categoria
                    (nombre, descripcion, estado)
                    VALUES
                    (:nombre, :descripcion, 1)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":nombre" => $nombre,
                ":descripcion" => $descripcion
            ]);

            $_SESSION["mensaje"] =
                "Categoría registrada correctamente.";

            header("Location: listar.php");
            exit;
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
        Nueva categoría - TecnoStock
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
            Volver a categorías
        </a>

    </div>

</nav>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="mb-4">

                <h1>
                    Nueva categoría
                </h1>

                <p class="text-muted">
                    Registra una categoría para clasificar
                    los productos del inventario.
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
                        id="formCategoria"
                        novalidate
                    >

                        <!-- NOMBRE -->

                        <div class="mb-3">

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
                                maxlength="100"
                                required
                                value="<?= htmlspecialchars($nombre) ?>"
                            >

                            <div
                                id="mensajeNombre"
                                class="invalid-feedback"
                            >
                                Ingresa un nombre válido.
                            </div>

                            <div class="form-text">
                                Ejemplo: Computadores,
                                Periféricos o Accesorios.
                            </div>

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="mb-4">

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
                                rows="4"
                            ><?= htmlspecialchars($descripcion) ?></textarea>

                            <div class="form-text">

                                Máximo

                                <span id="contadorDescripcion">
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
                                Guardar categoría
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

const formCategoria =
    document.getElementById("formCategoria");

const nombreInput =
    document.getElementById("nombre");

const descripcionInput =
    document.getElementById("descripcion");

const mensajeNombre =
    document.getElementById("mensajeNombre");

const contadorDescripcion =
    document.getElementById("contadorDescripcion");


/* =========================
   VALIDAR NOMBRE
========================= */

function validarNombre() {

    const nombre =
        nombreInput.value.trim();

    if (nombre === "") {

        nombreInput.classList.add(
            "is-invalid"
        );

        nombreInput.classList.remove(
            "is-valid"
        );

        mensajeNombre.textContent =
            "El nombre de la categoría es obligatorio.";

        return false;
    }


    if (nombre.length > 100) {

        nombreInput.classList.add(
            "is-invalid"
        );

        nombreInput.classList.remove(
            "is-valid"
        );

        mensajeNombre.textContent =
            "El nombre no puede superar los 100 caracteres.";

        return false;
    }


    nombreInput.classList.remove(
        "is-invalid"
    );

    nombreInput.classList.add(
        "is-valid"
    );

    return true;
}


/* =========================
   CONTADOR DESCRIPCIÓN
========================= */

function actualizarContador() {

    contadorDescripcion.textContent =
        descripcionInput.value.length;
}


/* =========================
   EVENTOS
========================= */

nombreInput.addEventListener(
    "input",
    validarNombre
);


descripcionInput.addEventListener(
    "input",
    actualizarContador
);


actualizarContador();


/* =========================
   VALIDAR FORMULARIO
========================= */

formCategoria.addEventListener(
    "submit",
    function (event) {

        let valido = true;


        if (!validarNombre()) {

            valido = false;
        }


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


        if (!valido) {

            event.preventDefault();

            event.stopPropagation();
        }

    }
);

</script>

</body>
</html>