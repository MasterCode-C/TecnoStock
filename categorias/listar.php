<?php

require_once "../auth/verificar_sesion.php";
require_once "../config/conexion.php";

$sql = "SELECT id_categoria, nombre, descripcion, estado
        FROM categoria
        ORDER BY nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$categorias = $stmt->fetchAll();

$mensaje = $_SESSION["mensaje"] ?? "";
unset($_SESSION["mensaje"]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Categorías - TecnoStock</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">

        <a class="navbar-brand" href="../dashboard.php">
            TecnoStock
        </a>

        <div>
            <a href="../dashboard.php"
               class="btn btn-outline-light me-2">
                Panel principal
            </a>

            <a href="../auth/logout.php"
               class="btn btn-outline-light">
                Cerrar sesión
            </a>
        </div>

    </div>
</nav>


<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1>Gestión de categorías</h1>

            <p class="text-muted mb-0">
                Administra las categorías utilizadas para clasificar los productos.
            </p>
        </div>

        <a href="crear.php"
           class="btn btn-primary">
            + Nueva categoría
        </a>

    </div>


    <?php if ($mensaje): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($mensaje) ?>
        </div>

    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body">

            <?php if (count($categorias) === 0): ?>

                <div class="alert alert-info mb-0">
                    No existen categorías registradas.
                    Utiliza el botón <strong>Nueva categoría</strong>
                    para crear la primera.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($categorias as $categoria): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($categoria["nombre"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($categoria["descripcion"] ?? "") ?>
                                </td>

                                <td>

                                    <?php if ((int)$categoria["estado"] === 1): ?>

                                        <span class="badge bg-success">
                                            Activa
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactiva
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="text-end">

                                    <a
                                        href="editar.php?id=<?= (int)$categoria["id_categoria"] ?>"
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