<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/Controller/InicioController.php';

    if(session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }

    if(!isset($_SESSION["Nombre"]))
    {
      header("Location: ../../View/Inicio/InicioSesion.php");
      exit;
    }


    function showCss(){
        echo'
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
            <meta name="description" content="" />
            <meta name="Grupo1" content="" />
            <title>Distribuidora J.J</title>

            <link href="../css/bootstrap.css" rel="stylesheet" />
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
            <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>      
        ';
    }

    function showJs(){
        echo'
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        ';
    }    

    function showNavBar(){
        echo'
            <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
                <!-- Navbar Brand-->
                <a class="navbar-brand ps-3 marca-titulo" href="../Inicio/Principal.php">
                <img src="../img/Logo_Empresa.jpg" alt="Logo" style="height:30px; width:auto; margin-right:8px;">
                Distribuidora J.J</a>

                <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>

                <div class="flex-grow-1"></div>
                <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../Usuario/Perfil.php">Actualizar Perfil</a></li>
                            <li><a class="dropdown-item" href="../Usuario/Seguridad.php">Cambiar Contraseña</a></li>
                            <li>
                            <form action="" method="POST">
                                <button type ="submit" id="btnSalir" name="btnSalir" class="dropdown-item">Cerrar Sesión</button>
                            </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>          
        ';

    }

    function showSideBar(){
        echo'
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Core</div>
                            <a class="nav-link" href="Principal.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                            <div class="small">Sesión como:</div>
                            '.$_SESSION["Nombre"].'
                    </div>
                </nav>
            </div>   
        ';
    }

    function showFooter(){
        echo'
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Grupo1 &copy; ProyectoAmbieteWeb 2025</div>
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