<?php
error_reporting(E_ERROR);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <link href="../css/style.css" rel="stylesheet" type="text/css" />
 <link href="../css/cabecera.css" rel="stylesheet" type="text/css" />
 <link href="../css/grupos.css" rel="stylesheet" type="text/css" />
 <link href="admin.css" rel="stylesheet" type="text/css" />
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
 <link rel="stylesheet" href="../css/leafletCustom.css" />

 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
 <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
 <script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
 <script src="../js/leaflet-plugins-master/layer/Leaflet.ContinuousZoom.js"></script>
 <script src="../js/leaflet-plugins-master/layer/tile/Google.js"></script>

</head>

<body>  

<input type="hidden" id="notificacion" value="<?php echo $_SESSION['notificacion']; unset($_SESSION['notificacion']); //Exponemos la variable notificación?>"></input>

 <div class='darkOverlay'>
 	<div id='overlay' class='overlay'></div>
 </div>
 <div class='cabecera'>
 </div>

 <div class='cuerpo'>
	<?
		switch ($_GET["action"]) 
		{
			case 'pendientes':
				include "pendientes.php";
				break;
			
			default:
				include "adminLinks.html";
				break;
		}
	?>
 </div>


 </body>
</html>