<?php
error_reporting(E_ERROR);
include "loadSession.php";
include "preload.php";



switch($_POST["action"])
{
	case "subscribe":
		follow($_SESSION["user"]["idUser"],$_POST["params"],$_POST["clase"]);
		break;
	case "unsubscribe":
		unfollow($_SESSION["user"]["idUser"],$_POST["params"],$_POST["clase"]);
		break;
	default:
		break;
}
?>