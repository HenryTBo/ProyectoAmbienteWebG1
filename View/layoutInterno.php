<?php
include_once __DIR__ . '/../Controller/InicioController.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["Nombre"])) {
    header("Location: ../../View/Inicio/InicioSesion.php");
    exit;
}

/* ---------------- CSS ---------------- */
function showCss() {
    echo '
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

        <title>Distribuidora J.J</title>

        <link href="../css/bootstrap.css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <style>
            /*
             * Paleta de colores corporativos de Distribuidora JJ
             * Utiliza variables CSS para permitir cambios centralizados
             */
            :root {
                --jj-gold:        #D9A441;
                --jj-gold-dark:   #A97823;
                --jj-blue-deep:   #18354A;
                --jj-red-warm:    #B02A2E;
                --jj-cream-light: #F1E8D8;
                --jj-graphite:    #302621;
            }

            .marca-titulo {
                font-weight: 600;
                font-size: 20px;
                letter-spacing: 0.5px;
                font-family: Poppins, sans-serif;
                color: var(--jj-gold);
            }

            /* Barra de navegación superior */
            .sb-topnav.navbar-dark {
                /* Barra semi-transparente para mayor elegancia */
                background-color: rgba(24, 53, 74, 0.9) !important;
            }
            .sb-topnav .navbar-brand,
            .sb-topnav .navbar-nav .nav-link,
            .sb-topnav .navbar-nav .dropdown-item {
                color: var(--jj-cream-light) !important;
            }
            .sb-topnav .navbar-brand img {
                /* Aumentamos aún más el tamaño del logo para mayor presencia */
                height: 100px;
                width: auto;
                margin-right: 12px;
            }

            /* Sidebar */
            .sb-sidenav-dark {
                background-color: var(--jj-blue-deep) !important;
            }
            .sb-sidenav-footer {
                background-color: var(--jj-blue-deep) !important;
                color: var(--jj-cream-light);
            }
            .sb-sidenav-menu-heading {
                color: var(--jj-gold-dark) !important;
                font-weight: 700;
            }
            .sb-nav-link-icon {
                color: var(--jj-gold) !important;
            }
            .nav-link {
                color: var(--jj-cream-light) !important;
            }
            .nav-link.active {
                background-color: var(--jj-gold-dark) !important;
                color: var(--jj-cream-light) !important;
                font-weight: 600;
            }
        </style>
    ';
}

/* ---------------- JS ---------------- */
function showJs() {
    echo '
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/scripts.js"></script>
    ';
}

/* ---------------- NAVBAR ---------------- */
function showNavBar() {
    echo '
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow-sm">

            <!-- Marca -->
            <a class="navbar-brand ps-3 marca-titulo" href="../Inicio/Principal.php">
                <!-- Logo ampliado: el tamaño ahora se controla vía CSS (48px de altura) -->
                <img src="../img/Logo_Empresa.png" alt="Logo" class="logo-navbar" />
                Distribuidora J.J
            </a>

            <!-- Botón sidebar -->
            <button class="btn btn-link btn-sm me-4" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Espacio -->
            <div class="flex-grow-1"></div>

            <!-- Menú usuario -->
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Usuario/Perfil.php">Actualizar Perfil</a></li>
                        <li><a class="dropdown-item" href="../Usuario/Seguridad.php">Cambiar Contraseña</a></li>
                        <li>
                            <form action="" method="POST">
                                <button type="submit" id="btnSalir" name="btnSalir" class="dropdown-item text-danger">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>

        </nav>
    ';
}

/* ---------------- SIDEBAR ---------------- */
function showSideBar() {
    echo '<div id="layoutSidenav_nav">';
    echo '<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">';
    echo '<div class="sb-sidenav-menu">';
    echo '<div class="nav">';
    echo '<div class="sb-sidenav-menu-heading">Menú</div>';

    // Enlace Inicio
    echo '<a class="nav-link" href="../Inicio/Principal.php">';
    echo '<div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>';
    echo 'Inicio';
    echo '</a>';

    // Enlace Productos
    echo '<a class="nav-link" href="../Inicio/productos.php">';
    echo '<div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>';
    echo 'Productos';
    echo '</a>';

    // Si el usuario es administrador (perfil 1), mostrar enlaces de gestión
    if (isset($_SESSION['ConsecutivoPerfil']) && $_SESSION['ConsecutivoPerfil'] == '1') {
        // Gestión de cuentas de usuario
        echo '<a class="nav-link" href="../Inicio/usuarios.php">';
        echo '<div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>';
        echo 'Cuentas';
        echo '</a>';
        // Gestión de empleados
        echo '<a class="nav-link" href="../Inicio/empleados.php">';
        echo '<div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>';
        echo 'Empleados';
        echo '</a>';
        // Gestión de pedidos
        echo '<a class="nav-link" href="../Inicio/gestionPedidos.php">';
        echo '<div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>';
        echo 'Pedidos';
        echo '</a>';
    } else {
        // Si no es administrador, mostrar enlaces de cliente
        // Mis pedidos
        echo '<a class="nav-link" href="../Inicio/misPedidos.php">';
        echo '<div class="sb-nav-link-icon"><i class="fas fa-shopping-bag"></i></div>';
        echo 'Mis pedidos';
        echo '</a>';
        // Carrito
        echo '<a class="nav-link" href="../Inicio/carrito.php">';
        echo '<div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>';
        echo 'Carrito';
        echo '</a>';
    }

    echo '</div>'; // .nav
    echo '</div>'; // .sb-sidenav-menu

    // Footer con nombre de usuario
    echo '<div class="sb-sidenav-footer text-center">';
    echo '<div class="small">Sesión iniciada como:</div>';
    echo '<strong>' . htmlspecialchars($_SESSION['Nombre']) . '</strong>';
    echo '</div>';

    echo '</nav>';
    echo '</div>';
}

/* ---------------- FOOTER ---------------- */
function showFooter() {
    echo '
        <footer class="py-4 bg-light mt-auto border-top">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Distribuidora J.J © 2025 — Proyecto Ambiente Web</div>
                </div>
            </div>
        </footer>
    ';
}
?>

<script>
  window.addEventListener("pageshow", function (event) {
    if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
      window.location.reload(true);
    }
  });
</script>
