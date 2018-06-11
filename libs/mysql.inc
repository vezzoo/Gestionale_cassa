<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 28/05/17
 * Time: 15.28
 */

$host = 'localhost';
$database = "gestionale";

function connect(){
    global $host, $database;
    $user = 'root';
    $password = 'sangiovanni';

    $conn = mysqli_connect($host, $user, $password, $database);
    return $conn;
}


?>
