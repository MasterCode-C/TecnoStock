<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: listar.php");
    exit;
}

$sql = "SELECT id_categoria, nombre, descripcion, estado
        FROM categoria
        WHERE id_categoria = :id
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":id" => $id
]);

$categoria = $stmt->fetch();

if (!$categoria) {
    $_SESSION["mensaje"] = "La categoría solicitada no existe.";
    header("Location: listar.php");
    exit;
}

$error = "";

$nombre = $categoria["nombre"];
$descripcion = $categoria["descripcion"] ?? "";
$estado = (int)$categoria["estado"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $estado = isset($_POST["estado"]) ? 1 : 0;

    if ($nombre === "") {

        $error = "El nombre de la categoría es obligatorio.";

    } elseif (strlen($nombre) > 100) {

        $error = "El nombre no puede superar los 100 caracteres.";

    } elseif (strlen($descripcion) > 255) {

        $error = "La descripción no puede superar los 255 caracteres.";

    } else {

        // Evitar nombres duplicados
        $sqlExiste = "SELECT id_categoria
                      FROM categoria
                      WHERE nombre = :nombre
                      AND id_categoria <> :id
                      LIMIT 1";

        $stmtExiste = $pdo->prepare($sqlExiste);

        $stmtExiste->execute([
            ":nombre" => $nombre,
            ":id" => $id
        ]);

        if ($stmtExiste->fetch()) {

            $error = "Ya existe otra categoría con ese nombre.";

        } else {

            $sqlActualizar = "UPDATE categoria
                              SET nombre = :nombre,
                                  descripcion = :descripcion,
                                  estado = :estado
                              WHERE id_categoria = :id";

            $stmtActualizar = $pdo->prepare($sqlActualizar);

            $stmtActualizar->execute([
                ":nombre" => $nombre,
                ":descripcion" => $descripcion,
                ":estado" => $estado,
                ":id" => $id
            ]);

            $_SESSION["mensaje"] = "Categoría actualizada correctamente.";

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Editar categoría - TecnoStock</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a class="navbar-brand"
           href="../dashboard.php">
            TecnoStock
        </a>

        <a href="listar.php"
           class="btn btn-outline-light">
            Volver a categorías
        </a>

    </div>

</nav>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="mb-4">

                <h1>Editar categoría</h1>

                <p class="text-muted">
                    Modifica los datos o cambia el estado de la categoría.
                </p>

            </div>


            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <form method="POST">

                        <div class="mb-3">

                            <label for="nombre"
                                   class="form-label">
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

                        </div>


                        <div class="mb-3">

                            <label for="descripcion"
                                   class="form-label">
                                Descripción
                            </label>

                            <textarea
                                class="form-control"
                                id="descripcion"
                                name="descripcion"
                                maxlength="255"
                                rows="4"
                            ><?= htmlspecialchars($descripcion) ?></textarea>

                        </div>


                        <div class="form-check form-switch mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="estado"
                                name="estado"
                                <?= $estado === 1 ? "checked" : "" ?>
                            >

                            <label
                                class="form-check-label"
                                for="estado"
                            >
                                Categoría activa
                            </label>

                        </div>


                        <div class="d-flex justify-content-end gap-2">

                            <a href="listar.php"
                               class="btn btn-secondary">
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary">
                                Guardar cambios
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>