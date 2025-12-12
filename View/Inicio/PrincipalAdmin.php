<?php
/*
    Corrección de la página de administrador (PrincipalAdmin.php)
    - Ajusta rutas relativas para que funcionen independientemente del directorio base.
    - Corrige el enlace "Mi Cuenta" para ir al perfil del usuario.
*/

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
    <!-- Utiliza el CSS del panel admin desde la carpeta View/css -->
    <link href="../css/principalAdmin.css?v=1" rel="stylesheet" />
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
                        <p class="page-sub">Bienvenido, <?php echo $_SESSION["Nombre"] ?? ''; ?></p>
                    </div>
                    <!-- BOTÓN IR A PRODUCTOS -->
                    <a href="productos.php" class="btn btn-products">
                        📦 Ir a Productos
                    </a>
                </div>
                <!-- FILA DE CARDS DE ESTADÍSTICAS -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-title">Productos Registrados</div>
                            <div class="stat-value" id="statProducts">--</div>
                            <div class="stat-sub">Activos en el inventario</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-title">Usuarios</div>
                            <div class="stat-value" id="statUsers">--</div>
                            <div class="stat-sub">Cuentas totales</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-title">Empleados</div>
                            <div class="stat-value" id="statEmployees">--</div>
                            <div class="stat-sub">Activos en planilla</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-title">Pedidos</div>
                            <!-- Cantidad total de pedidos será calculada vía AJAX -->
                            <div class="stat-value" id="statOrders">--</div>
                            <div class="stat-sub">Pedidos totales</div>
                        </div>
                    </div>
                </div>
                <!-- OPCIONES RÁPIDAS -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Gestión de Productos</h5>
                                <p>Administra todo el inventario, equipos, licores y más.</p>
                                <a href="productos.php" class="btn btn-outline-primary">Ir al módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Gestión de Cuentas</h5>
                                <p>Controlá los roles y permisos de cada cuenta.</p>
                                <a href="usuarios.php" class="btn btn-outline-primary">Ir al módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Gestión de Empleados</h5>
                                <p>Gestioná la planilla de colaboradores.</p>
                                <a href="empleados.php" class="btn btn-outline-primary">Ir al módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Gestión de Pedidos</h5>
                                <p>Revisá y actualizá los pedidos de tus clientes.</p>
                                <a href="gestionPedidos.php" class="btn btn-outline-primary">Ir al módulo</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card action-card">
                            <div class="card-body">
                                <h5>Mi Cuenta</h5>
                                <p>Editá tus datos personales y preferencias.</p>
                                <a href="../Usuario/Perfil.php" class="btn btn-outline-primary">Entrar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php showFooter(); ?>
        </div>
    </div>
    <?php showJs(); ?>
    <!-- Scripts para obtener estadísticas dinámicas -->
    <script>
        // Productos
        fetch("../../Controller/ProductController.php?action=list")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statProducts").innerText = data.data.length;
                }
            })
            .catch(() => {
                document.getElementById("statProducts").innerText = "--";
            });
        // Usuarios
        fetch("../../Controller/UserAdminController.php?action=list")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statUsers").innerText = data.data.length;
                }
            })
            .catch(() => {
                document.getElementById("statUsers").innerText = "--";
            });
        // Empleados
        fetch("../../Controller/EmployeeController.php?action=list")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statEmployees").innerText = data.data.length;
                }
            })
            .catch(() => {
                document.getElementById("statEmployees").innerText = "--";
            });

        // Pedidos
        fetch("../../Controller/OrderController.php?action=list")
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("statOrders").innerText = data.data.length;
                }
            })
            .catch(() => {
                document.getElementById("statOrders").innerText = "--";
            });
    </script>
</body>
</html>