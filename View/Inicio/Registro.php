<?php
    include_once __DIR__ . '/../layoutExterno.php';
    include_once __DIR__ . '/../../Controller/InicioController.php';
    
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <?php showCss(); ?>   
    </head>
    <body class="bg-custom">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Crear Cuenta</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                            if (isset($_POST["Mensaje"])) {
                                                echo '<div class="alert alert-primary centrado">' . $_POST["Mensaje"] . '</div>';
                                            }
                                        ?>
                                        <form id="formRegistro" action="" method="POST">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" 
                                                       id="Identificacion" 
                                                       name="Identificacion" 
                                                       onkeyup="ConsultarNombre();" 
                                                       placeholder="Identificación" />
                                                <label for="Identificacion">Identificación</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input class="form-control" 
                                                       id="Nombre" 
                                                       name="Nombre"  
                                                       placeholder="Nombre" 
                                                       readonly="readonly" />
                                                <label for="Nombre">Nombre</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input class="form-control" 
                                                       id="CorreoElectronico" 
                                                       name="CorreoElectronico" 
                                                       type="email" 
                                                       placeholder="Correo Electrónico" />
                                                <label for="CorreoElectronico">Correo Electrónico</label>
                                            </div>

                                            <div class="form-floating mb-3">
                                                <input class="form-control" 
                                                       id="Contrasenna" 
                                                       name="Contrasenna" 
                                                       type="password" 
                                                       placeholder="Contraseña" />
                                                <label for="Contrasenna">Contraseña</label>
                                            </div>

                                            <div class="mt-4 mb-0">
                                                <div class="d-grid">
                                                    <button type="submit" 
                                                            class="btn btn-primary btn-block" 
                                                            id="btnCrearCuenta" 
                                                            name="btnCrearCuenta">
                                                        Crear Cuenta
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small">
                                            <a href="InicioSesion.php">¿Tienes una cuenta? Inicia sesión</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <?php showJs(); ?>
    </body>
</html>
