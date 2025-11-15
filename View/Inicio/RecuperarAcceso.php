<?php
  include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/View/layoutExterno.php';
  include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/Controller/InicioController.php';

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php showCss() ?>        
    </head>
    <body class="bg-custom">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Recuperar Acceso</h3></div>
                                    <div class="card-body">
                                    <?php
                                        if(isset($_POST["Mensaje"]))
                                        {
                                            echo '<div class="alert alert-primary centrado">' . $_POST["Mensaje"] . '</div>';
                                        }
                                    ?>
                                        <form id="formRecuperarAcceso" action="" method="POST">
                                            <div class="form-floating mb-3">
                                                <input type =text class="form-control"  id="CorreoElectronico" name="CorreoElectronico"  type="email" placeholder="CorreoElectronico" />
                                                <label for="email">Correo Electronico</label>
                                            </div>                                                                                     
                                            <div class="d-flex align-items-center justify-content-center mt-4 mb-0">
                                                <button class="btn btn-primary" id="btnRecuperarAcceso" name="btnRecuperarAcceso" type="submit">Procesar</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="InicioSesion.php">Regresar</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <?php showJs() ?>    
    </body>
</html>
