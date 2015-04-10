<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$sql="select repair.lugares_shp.id as idold, citysens.lugares_shp.id as idnew, citysens.lugares_shp.nombre
. from repair.lugares_shp, citysens.lugares_shp
. where repair.lugares_shp.id like '701%' and citysens.lugares_shp.id like '701%' and citysens.lugares_shp.nombre=repair.lugares_shp.nombre;"
 ?>