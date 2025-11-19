<?php
session_start();
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/InicioController.php';

// Validación de administrador
if (!isset($_SESSION["ConsecutivoPerfil"]) || $_SESSION["ConsecutivoPerfil"] != "1") {
    header("Location: InicioSesion.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="/proyecto/ProyectoAmbienteWebG1/public/css/principalAdmin.css" rel="stylesheet">
</head>

<body class="sb-nav-fixed">

    <?php showNavBar(); ?>

    <div id="layoutSidenav">

        <?php showSideBar(); ?>

        <div id="layoutSidenav_content">
            <main class="p-4">

                <!-- TÍTULO -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="page-title">Panel Administrador</h2>
                        <p class="page-sub">Bienvenido, <?php echo $_SESSION["Nombre"]; ?></p>
                    </div>

                    <!-- BOTÓN IR A PRODUCTOS -->
                    <a href="/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php"
                       class="btn btn-products">
                        📦 Ir a Productos
                    </a>
                </div>


                <!-- FILA DE CARDS DE ESTADÍSTICAS -->
                <div class="row g-3 mb-4">

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-title">Productos Registrados</div>
                            <div class="stat-value" id="statProducts">--</div>
                            <div class="stat-sub">Activos en el inventario</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-title">Usuarios</div>
                            <div class="stat-value">2</div>
                            <div class="stat-sub">Activos</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-title">Pedidos del día</div>
                            <div class="stat-value">12</div>
                            <div class="stat-sub">En proceso</div>
                        </div>
                    </div>
                </div>


                <!-- OPCIONES RÁPIDAS -->
                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Gestión de Productos</h5>
                                <p>Administra todo el inventario, equipos, licores y más.</p>
                                <a href="/proyecto/ProyectoAmbienteWebG1/View/Inicio/productos.php"
                                   class="btn btn-outline-primary">
                                   Ir al módulo
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Mi Cuenta</h5>
                                <p>Editá tus datos personales y preferencias.</p>
                                <a href="Perfil.php" class="btn btn-outline-primary">Entrar</a>
                            </div>
                        </div>
                    </div>

                </div>

            </main>

            <?php showFooter(); ?>

        </div>
    </div>

    <?php showJs(); ?>

    <!-- Script para obtener número de productos -->
    <script>
        fetch("/proyecto/ProyectoAmbienteWebG1/Controller/ProductController.php?action=list")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statProducts").innerText = data.data.length;
                }
            })
            .catch(() => {
                document.getElementById("statProducts").innerText = "--";
            });
    </script>

</body>
</html>
