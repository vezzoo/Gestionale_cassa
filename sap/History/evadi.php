<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/30/18
 * Time: 12:50 PM
 */
session_start();

$data = $_POST['ordid'];

include("../../libs/mysql.inc");
$conn = connect();
$uid = mysqli_escape_string($conn, $_SESSION['userID']);
$service = "Storico ordini";
$a = $conn->query("select * from assegnazionePrivilegi inner join funzioni on funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID where Servizio = '$service' AND userID = $uid");

if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "" && $a->num_rows > 0)) {
    $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
    echo "<script>window.location.href = '../../index.php';</script>";
    die("wrong login");
}
$oid = $conn->escape_string($data);
$conn->query("UPDATE Ordini SET stato = 'EVASO' WHERE orderID = '$oid'");