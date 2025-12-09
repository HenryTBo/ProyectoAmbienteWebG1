<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';

if (!isset($_SESSION["ConsecutivoUsuario"])) {
    header("Location: InicioSesion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/productos.css?v=1" rel="stylesheet">
</head>

<body class="sb-nav-fixed">

<?php showNavBar(); ?>

<div id="layoutSidenav">
    <?php showSideBar(); ?>

    <div id="layoutSidenav_content">

        <main class="container mt-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="page-title">Productos</h2>
                    <p class="page-sub">Inventario completo de Distribuidora JJ</p>
                </div>

                <a href="PrincipalAdmin.php" class="btn btn-back">
                    ⬅ Regresar
                </a>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 search-bar">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar producto...">
                </div>
            </div>

            <div class="row" id="productList">
                <p>Cargando productos...</p>
            </div>

        </main>

        <?php showFooter(); ?>
    </div>

</div>

<?php showJs(); ?>

<script>
function loadProducts(filter = "") {
    fetch("../../Controller/ProductController.php?action=list")
        .then(r => r.json())
        .then(json => {
            const cont = document.getElementById("productList");
            cont.innerHTML = "";

            if (!json.success || json.data.length === 0) {
                cont.innerHTML = "<p>No hay productos disponibles.</p>";
                return;
            }

            json.data
                .filter(p => p.nombre.toLowerCase().includes(filter.toLowerCase()))
                .forEach(p => {
                    cont.innerHTML += `
                        <div class="col-md-4 mb-4">
                            <div class="product-card">
                                <img src="${p.imagen}" alt="${p.nombre}" class="product-img">
                                <div class="product-body">
                                    <span class="product-category">${p.categoria}</span>
                                    <h5 class="product-title">${p.nombre}</h5>
                                    <div class="product-price">₡${parseFloat(p.precio).toLocaleString()}</div>
                                    <div class="product-stock">Stock: ${p.stock}</div>
                                    <div class="product-actions">
                                        <button class="btn-edit">Editar</button>
                                        <button class="btn-delete">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
        })
        .catch(err => console.error("Error:", err));
}

document.getElementById("searchInput").addEventListener("input", e => {
    loadProducts(e.target.value);
});

loadProducts();
</script>

</body>
</html>
