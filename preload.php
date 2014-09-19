<?php
include_once "db.php";

//print_r($_POST);

switch($_POST["post_form"])
{
	case "login_form":
		$user=getUser($_POST["input-email"],$_POST["input-password"]);
		if($user)
		{
			//TODO: Regenerar sesiones
			session_regenerate_id();
			$_SESSION["notificacion"]="loginCorrecto";
			$_SESSION["user"]=$user;
			$_SESSION["logged"]=1;
		}
		else
		{
			//Mostrar error al intentar loguear
			$_SESSION["notificacion"]="loginError";
		}
		break;
	case "reset_form":
		$resetCorrecto=true;
		if($_POST["input-password"]===$_POST["input-password-2"])
		{
			$resetCorrecto=changeUserPassword($_POST["email"],$_POST["token"],$_POST["input-password"]);		
		}
		else
		{
			$resetCorrecto=false;
		}

		if($resetCorrecto)
		{
			$_SESSION["notificacion"]="resetCorrecto";
		}
		else
		{
			//Mostrar error al intentar resetear
			$_SESSION["notificacion"]="resetError";
		}
		break;
	case "logout_form":
		session_regenerate_id();
		unset($_SESSION["user"]);
		unset($_SESSION["logged"]);
		
		/*-----TODO-----
		Invalidate Session ID
		You should invalidate (unset cookie, unset session storage, remove traces) of a session whenever a violation occurs (e.g 2 IP addresses are observed). A log event would prove useful. Many applications also notify the logged in user (e.g GMail).
		*/

		break;
	default:
		break;
}

?>