<?php
// Autoload stuff
require( 'vendor/autoload.php' );

require( 'vendor/php-google-maps/php-google-maps/PHPGoogleMaps/Core/Autoloader.php' );
$map_loader = new SplClassLoader('PHPGoogleMaps', '../');
$map_loader->register();


//https://github.com/galen/PHPGoogleMaps-Examples/blob/master/places.php

$map = new \PHPGoogleMaps\Map;

$map->setCenter( 'Bolarque 3, Alcalá de Henares' );
$map->setZoom( 15 );
$map->enablePlacesAutocomplete(array('autocomplete_input_id' => 'places_input'));
$map->enableInfoWindows();
$map->setMapTypes(array('roadmap'));
$map->setWidth(320);
$map->setHeight(240);
?>