<?php
  include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/View/layoutInterno.php';
  include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/Controller/InicioController.php';


   if($_SESSION["ConsecutivoPerfil"] == "1")
  {
    header("Location: PrincipalAdmin.php");
    exit;
  }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php showCss(); ?>
        <link href="../css/estilosInicio.css" rel="stylesheet" />            
    </head>
    
    <body class="sb-nav-fixed">
      <?php showNavBar(); ?>
      <div id="layoutSidenav">
        <?php showSideBar(); ?>

        <div id="layoutSidenav_content">
          <main>

          </main>

          <?php showFooter(); ?>
        </div>
      </div>


    <?php showJs(); ?>      
    </body>
</html>
