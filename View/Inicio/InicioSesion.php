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
                    <div class="col-lg-6 col-md-8 col-sm-10">
                        <div class="card shadow-lg border-0 rounded-lg">
                            <div class="card-header">
                                <img class="login-logo" src="../img/Logo_Empresa.png" alt="Logo Distribuidora J.J">
                                <h3>Inicio de sesión</h3>
                            </div>

                            <div class="card-body">
                                <?php
                                if (isset($_POST["Mensaje"])) {
                                    echo '<div class="alert alert-primary text-center">' . $_POST["Mensaje"] . '</div>';
                                }
                                ?>

                                <form id="formInicioSesion" action="" method="POST">
                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="CorreoElectronico" name="CorreoElectronico"
                                               type="email" placeholder="correo@ejemplo.com" required />
                                        <label for="CorreoElectronico">Correo electrónico</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input class="form-control" id="Contrasenna" name="Contrasenna"
                                               type="password" placeholder="Contraseña" required />
                                        <label for="Contrasenna">Contraseña</label>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <a class="small" href="RecuperarAcceso.php">¿Olvidaste tu contraseña?</a>
                                        <button class="btn btn-primary" id="btnIniciarSesion" name="btnIniciarSesion" type="submit">
                                            Ingresar
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="card-footer text-center py-3">
                                <div class="small">
                                    <a href="Registro.php">¿Necesitas una cuenta? Regístrate</a>
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
