<?php

include './vendor/autoload.php';
include './php/influxdb_connection.php';
include './php/functions.php';
$client = get_influxdb_connection();

echo "<a href='./php/manager.php?operation=new_data&filename=example.csv'>Manager Example csv</a><br>";