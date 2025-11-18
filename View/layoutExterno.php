<?php
    function showCss(){
        echo'
        <head>
            <meta charset="utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
            <meta name="description" content="" />
            <meta name="Grupo1" content="" />
            <title>Distribuidora J.J</title>
            
            <link href="../css/bootstrap.css" rel="stylesheet" />
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
            <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>   
            <link href="../css/estilosInicio.css" rel="stylesheet" />   
        </head>
        ';
    }

    function showJs(){
        echo'
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/jquery.validation/1.19.5/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="../js/Registro.js"></script>   
        <script src="../js/RecuperarAcceso.js"></script> 
        <script src="../js/InicioSesion.js"></script>    
        ';
    }
?>