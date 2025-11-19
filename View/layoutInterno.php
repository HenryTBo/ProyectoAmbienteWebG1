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
            .marca-titulo {
                font-weight: 600;
                font-size: 18px;
                letter-spacing: 0.5px;
                font-family: Poppins, sans-serif;
            }
            .sb-sidenav-dark {
                background: #1f1f1f !important;
            }
            .sb-sidenav-footer {
                background: #101010 !important;
                color: #ddd;
            }
            .sb-sidenav-menu-heading {
                color: #bbb !important;
            }
            .sb-nav-link-icon {
                color: #f39c12 !important;
            }
            .nav-link.active {
                background-color: #343a40 !important;
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
                <img src="../img/Logo_Empresa.jpg" alt="Logo" style="height:32px; width:auto; margin-right:10px;">
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
    echo '
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">

                <div class="sb-sidenav-menu">
                    <div class="nav">

                        <div class="sb-sidenav-menu-heading">Menú</div>

                        <a class="nav-link" href="Principal.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Inicio
                        </a>

                        <a class="nav-link" href="productos.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                            Productos
                        </a>

                    </div>
                </div>

                <div class="sb-sidenav-footer text-center">
                    <div class="small">Sesión iniciada como:</div>
                    <strong>' . $_SESSION["Nombre"] . '</strong>
                </div>

            </nav>
        </div>
    ';
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
