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
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Crear Cuenta</h3></div>
                                    <div class="card-body">
                                    <?php
                                        if(isset($_POST["Mensaje"]))
                                        {
                                            echo '<div class="alert alert-primary centrado">' . $_POST["Mensaje"] . '</div>';
                                        }
                                    ?>
                                        <form id="formRegistro" action="" method="POST">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="Identificacion" name= "Identificacion" onkeyup ="ConsultarNombre();" placeholder="Identificacion" />
                                                <label for="inputEmail">Identificacion</label>
                                            </div>
                                                <div class="form-floating mb-3 ">
                                                    <input class="form-control" id="Nombre" name = "Nombre"  placeholder="Nombre" readOnly ="true" />
                                                     <label for="inputFirstName">Nombre</label>
                                                </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="CorreoElectronico" name ="CorreoElectronico" type="email" placeholder="CorreoElectronico" />
                                                <label for="inputEmail">Correo Electronico</label>
                                            </div>
                                                <div class="form-floating mb-3 ">
                                                    <input class="form-control" id="Contrasenna" name= "Contrasenna" type="password" placeholder="Contraseña" />
                                                    <label for="inputPassword">Contraseña</label>
                                                </div>
                                            <div class="mt-4 mb-0">
                                                <div class="d-grid"><button type="submit" class="btn btn-primary btn-block" id="btnCrearCuenta" name="btnCrearCuenta">Crear Cuenta</button></div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="InicioSesion.php">Tienes una cuenta? Iniciar sesión</a></div>
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
