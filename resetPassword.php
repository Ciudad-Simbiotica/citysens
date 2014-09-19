<?php
error_reporting(E_ERROR);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <link href="css/style.css" rel="stylesheet" type="text/css" />
 <link href="css/cabecera.css" rel="stylesheet" type="text/css" />
 <link href="css/grupos.css" rel="stylesheet" type="text/css" />

 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

</head>

<body>  

<input type="hidden" id="notificacion" value="<?php echo $_SESSION['notificacion']; unset($_SESSION['notificacion']); //Exponemos la variable notificación?>"></input>
<input type="hidden" id="logged" value="<?php echo $_SESSION['logged']; //Exponemos si estamos loggeados para JS?>"></input>

 <div class='darkOverlay'>
 	<div id='overlay' class='overlay'></div>
 </div>
 <div class='cabecera'>
 </div>

 <div class='cuerpo'>
 	<FORM METHOD=POST ACTION='/citysens/?idLugar=888004284' id='login_form'>
 		<input type=hidden name='email' value='<?php echo $_GET['email'];?>'>
 		<input type=hidden name='token' value='<?php echo $_GET['token'];?>'>
 		<input type=hidden name='post_form' value='reset_form'>

 		<div class='reestablece-header'>Restablece tu contraseña:</div>
 		<div class='reestablece-line'>
 		 <div class='reestablece-desc'>Contraseña:</div>
 		 <div class=''><INPUT TYPE=PASSWORD class='reestablece-input' id='input-password' NAME='input-password' placeholder='Introduce tu nueva contraseña'></div>
 		</div>
 		<div class='reestablece-line'>
 		 <div class='reestablece-desc'>Repetir contraseña:</div>
 		 <div class=''><INPUT TYPE=PASSWORD class='reestablece-input' id='input-password-2' NAME='input-password-2' placeholder='Introduce de nuevo tu nueva contraseña'></div>
 		</div>
 		<div class='reestablece-submit'>
	 		<div class='div-login-submit' id='div-login-submit'>Reestablecer</div> 
	 	</div>
 	</FORM>
 </div>

 <script src="js/resetPassword.js"></script>

 </body>
</html>