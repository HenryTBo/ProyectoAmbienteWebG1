<?php
// View/layoutInterno.php
// Layout interno (usuario logueado) + Navbar/Sidebar/Footer
// Incluye salida segura, sesión consistente y paleta corporativa.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Para que el botón "Cerrar Sesión" funcione en cualquier pantalla que incluya este layout
include_once __DIR__ . '/../Controller/InicioController.php';

/**
 * Normaliza sesión: soporta tanto llaves legacy como la llave moderna $_SESSION['User'].
 * Esto evita que algunas pantallas crean que no estás logueado.
 */
if (!isset($_SESSION['User']) && isset($_SESSION['ConsecutivoUsuario'])) {
    $_SESSION['User'] = [
        'ConsecutivoUsuario' => (int)($_SESSION['ConsecutivoUsuario'] ?? 0),
        'Nombre'             => (string)($_SESSION['Nombre'] ?? ''),
        'ConsecutivoPerfil'  => (int)($_SESSION['ConsecutivoPerfil'] ?? 0),
        'NombrePerfil'       => (string)($_SESSION['NombrePerfil'] ?? ''),
        'CorreoElectronico'  => (string)($_SESSION['CorreoElectronico'] ?? '')
    ];
} elseif (isset($_SESSION['User']) && !isset($_SESSION['ConsecutivoUsuario'])) {
    $_SESSION['ConsecutivoUsuario'] = $_SESSION['User']['ConsecutivoUsuario'] ?? null;
    $_SESSION['Nombre']             = $_SESSION['User']['Nombre'] ?? null;
    $_SESSION['ConsecutivoPerfil']  = $_SESSION['User']['ConsecutivoPerfil'] ?? null;
    $_SESSION['NombrePerfil']       = $_SESSION['User']['NombrePerfil'] ?? null;
    $_SESSION['CorreoElectronico']  = $_SESSION['User']['CorreoElectronico'] ?? null;
}

// Validación login
$uid = (int)($_SESSION['User']['ConsecutivoUsuario'] ?? 0);
if ($uid <= 0) {
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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Salsa&display=swap" rel="stylesheet">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <style>
            :root{
                --jj-navy:   #0c2c3c;
                --jj-navy-2: #143044;
                --jj-gold:   #cca44c;
                --jj-gold-2: #a97823;
                --jj-maroon: #8c1c1c;
                --jj-cream:  #f7f5f0;
                --jj-text:   #1c2430;
            }

            body{ font-family: Poppins, sans-serif; }

            .marca-titulo{
                font-weight: 700;
                font-size: 18px;
                letter-spacing: .3px;
                color: var(--jj-gold) !important;
                display:flex;
                align-items:center;
                gap:10px;
                text-decoration:none;
            }
            .logo-navbar{
                height: 56px;
                width: auto;
                filter: drop-shadow(0 6px 12px rgba(0,0,0,.18));
            }

            /* TopNav */
            .sb-topnav.navbar-dark{
                background: rgba(12,44,60,.95) !important;
                border-bottom: 1px solid rgba(255,255,255,.08);
            }
            .sb-topnav .navbar-nav .nav-link,
            .sb-topnav .navbar-brand{
                color: var(--jj-cream) !important;
            }

            /* Sidebar */
            .sb-sidenav-dark{
                background: linear-gradient(180deg, var(--jj-navy) 0%, var(--jj-navy-2) 100%) !important;
            }
            .sb-sidenav-footer{
                background: rgba(0,0,0,.12) !important;
                color: var(--jj-cream);
            }
            .sb-sidenav-menu-heading{
                color: rgba(204,164,76,.85) !important;
                font-weight: 700;
            }
            .sb-nav-link-icon{
                color: var(--jj-gold) !important;
            }
            .nav-link{
                color: var(--jj-cream) !important;
                border-radius: 10px;
            }
            .nav-link:hover{
                background: rgba(204,164,76,.10);
            }
            .nav-link.active{
                background: rgba(204,164,76,.18) !important;
                font-weight: 700;
            }

            /* Footer */
            footer{
                border-top: 1px solid rgba(0,0,0,.08);
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
        <nav class="sb-topnav navbar navbar-expand navbar-dark shadow-sm">
            <a class="navbar-brand ps-3 marca-titulo" href="../Inicio/Principal.php">
                <img src="../img/Logo_Empresa.png" alt="Logo" class="logo-navbar" />
                Distribuidora J.J
            </a>

            <button class="btn btn-link btn-sm me-4 text-white" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>

            <div class="flex-grow-1"></div>

            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../Usuario/Perfil.php">Actualizar Perfil</a></li>
                        <li><a class="dropdown-item" href="../Usuario/Seguridad.php">Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="" method="POST" class="m-0">
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
    $perfil = (string)($_SESSION['ConsecutivoPerfil'] ?? ($_SESSION['User']['ConsecutivoPerfil'] ?? '2'));

    echo '<div id="layoutSidenav_nav">';
    echo '<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">';
    echo '<div class="sb-sidenav-menu"><div class="nav">';
    echo '<div class="sb-sidenav-menu-heading">Menú</div>';

    echo '<a class="nav-link" href="../Inicio/Principal.php">
            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
            Inicio
          </a>';

    echo '<a class="nav-link" href="../Inicio/productos.php">
            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
            Productos
          </a>';

    if ($perfil === '1') {
        echo '<a class="nav-link" href="../Inicio/usuarios.php">
                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                Cuentas
              </a>';

        echo '<a class="nav-link" href="../Inicio/empleados.php">
                <div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>
                Empleados
              </a>';

        echo '<a class="nav-link" href="../Inicio/gestionPedidos.php">
                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                Pedidos
              </a>';
    } else {
        echo '<a class="nav-link" href="../Inicio/misPedidos.php">
                <div class="sb-nav-link-icon"><i class="fas fa-shopping-bag"></i></div>
                Mis pedidos
              </a>';

        echo '<a class="nav-link" href="../Inicio/carrito.php">
                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                Carrito
              </a>';
    }

    echo '</div></div>';

    echo '<div class="sb-sidenav-footer text-center">';
    echo '<div class="small">Sesión iniciada como:</div>';
    echo '<strong>' . htmlspecialchars((string)($_SESSION['Nombre'] ?? $_SESSION['User']['Nombre'] ?? 'Usuario')) . '</strong>';
    echo '</div>';

    echo '</nav>';
    echo '</div>';
}

/* ---------------- FOOTER ---------------- */
function showFooter() {
    echo '
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Distribuidora J.J © 2025 — Proyecto Ambiente Web</div>
                </div>
            </div>
        </footer>
    ';
}
?>
