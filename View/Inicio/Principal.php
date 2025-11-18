<?php
    include_once __DIR__ . '/../layoutInterno.php';
    include_once __DIR__ . '/../../Controller/InicioController.php';

    
    if ($_SESSION["ConsecutivoPerfil"] == "1") {
        header("Location: PrincipalAdmin.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php showCss(); ?>
    <link href="../css/estilosInicio.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed">

    <?php showNavBar(); ?>

    <div id="layoutSidenav">

        <?php showSideBar(); ?>

        <div id="layoutSidenav_content">
            <main>
                <!-- Contenido principal -->
            </main>

            <?php showFooter(); ?>
        </div>
    </div>

    <?php showJs(); ?>      

</body>
</html>
