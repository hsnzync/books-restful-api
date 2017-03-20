<?php

$db_host='sql.hosted.hr.nl'; //Localhost
$db_user='0892980'; //Database user
$db_password='6b950d8a'; // Database password
$db_database='0892980'; //Database name

$db = mysqli_connect($db_host, $db_user, $db_password, $db_database) or die(mysqli_connect_error()); //Connecting to the database

?>