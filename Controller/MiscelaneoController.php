<?php

    function GenerarContrasenna()
    {
        $length = 8;
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789';
        $max = strlen($chars) - 1;
        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            $pass .= $chars[random_int(0, $max)];
        }
        return $pass;
    }

    function EnviarCorreo($asunto, $contenido, $destinatario)
    {
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $correoSalida = "";
        $contrasennaSalida = "";

        if($contrasennaSalida == "")
        {
            return true; 
        }

        $mail = new PHPMailer();
        $mail -> CharSet = 'UTF-8';

        $mail -> IsSMTP();
        $mail -> IsHTML(true); 
        $mail -> Host = 'smtp.office365.com';
        $mail -> SMTPSecure = 'tls';
        $mail -> Port = 587;                      
        $mail -> SMTPAuth = true;
        $mail -> Username = $correoSalida;               
        $mail -> Password = $contrasennaSalida;                                
        
        $mail -> SetFrom($correoSalida);
        $mail -> Subject = $asunto;
        $mail -> MsgHTML($contenido);   
        $mail -> AddAddress($destinatario);

        try 
        {
            if ($mail->send()) 
            {
                return true; 
            } 
            else 
            {
                return true; 
            }
        } catch (Exception $e) 
        {
            return false;
        }
    }

?>