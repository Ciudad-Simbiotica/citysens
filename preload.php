<?php
include_once "db.php";

switch($_POST["post_form"])
{
	case "login_form":
		$user=getUser($_POST["input-email"],$_POST["input-password"]);
		if($user)
		{
			$_SESSION["user"]=$user;
		}
		else
		{
			//Mostrar error al intentar loguear
		}
		break;
	case "register_form":
		//createUser("Kikesa","correao@kikef.es","pentium");	
		break;
	case "logout_form":
		unset($_SESSION["user"]);
		break;
	default:
		break;
}

?>