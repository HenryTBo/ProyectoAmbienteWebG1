<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/View/layoutInterno.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/ProyectoAmbienteWebG1/Controller/UsuarioController.php';
?>

<!DOCTYPE html>
<html lang="en">

<?php showCss(); ?>

<body class="sb-nav-fixed">

    <?php showNavBar(); ?>

    <div id="layoutSidenav">

        <?php showSideBar(); ?>

        <div id="layoutSidenav_content">

            <main class="container-fluid px-4 mt-4">

                <div class="card mb-4">
                    <h4 class="card-header">Información de Seguridad</h4>

                    <div class="row">
                        <div class="col-md-1"></div>

                        <div class="col-md-10">
                            <div class="card-body">

                                <?php
                                if(isset($_POST["Mensaje"]))
                                {
                                    echo '<div class="alert alert-primary">' . $_POST["Mensaje"] . '</div>';
                                }
                                ?>

                                <form id="formSeguridad" action="" method="POST">

                                    <div class="mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="Contrasenna" 
                                               name="Contrasenna" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Contraseña</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="ConfirmarContrasenna" 
                                               name="ConfirmarContrasenna" />
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary w-25"
                                                id="btnActualizarSeguridad"
                                                name="btnActualizarSeguridad" 
                                                type="submit">
                                            Aplicar Cambios
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </main>

            <?php showFooter(); ?>

        </div>
    </div>

    <?php showJs(); ?>
    <script src="../js/Seguridad.js"></script>

</body>
</html>