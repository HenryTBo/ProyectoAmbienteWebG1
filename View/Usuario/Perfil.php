<?php
    include_once __DIR__ . '/../layoutInterno.php';
    include_once __DIR__ . '/../../Controller/UsuarioController.php';

    
   
    $resultado = ConsultarUsuario();
?>

<!DOCTYPE html>
<html lang="es">

<?php showCss(); ?>

<body class="sb-nav-fixed">

    <?php showNavBar(); ?>

    <div id="layoutSidenav">

        <?php showSideBar(); ?>

        <div id="layoutSidenav_content">

            <main class="container-fluid px-4 mt-4">

                <div class="card mb-4">
                    <h4 class="card-header">Información del Perfil</h4>

                    <div class="row">
                        <div class="col-md-1"></div>

                        <div class="col-md-10">
                            <div class="card-body">

                                <?php
                                if (isset($_POST["Mensaje"])) {
                                    echo '<div class="alert alert-primary">' . $_POST["Mensaje"] . '</div>';
                                }
                                ?>

                                <form id="formPerfil" action="" method="POST">

                                    <div class="mb-3">
                                        <label class="form-label">Identificación</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="Identificacion"
                                               name="Identificacion"
                                               value="<?php echo $resultado['Identificacion']; ?>"
                                               onkeyup="ConsultarNombre();" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="Nombre"
                                               name="Nombre"
                                               readonly
                                               value="<?php echo $resultado['Nombre']; ?>" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Correo Electrónico</label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="CorreoElectronico"
                                               name="CorreoElectronico"
                                               value="<?php echo $resultado['CorreoElectronico']; ?>" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Perfil</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="NombrePerfil"
                                               name="NombrePerfil"
                                               readonly
                                               value="<?php echo $resultado['NombrePerfil']; ?>" />
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary w-25"
                                                id="btnActualizarPerfil"
                                                name="btnActualizarPerfil"
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
    <script src="../js/Perfil.js"></script>

</body>
</html>
