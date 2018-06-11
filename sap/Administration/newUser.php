<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/19/18
 * Time: 10:55 PM
 */
session_start();
include("../../libs/mysql.inc");
$conn = connect();
$uid = mysqli_escape_string($conn, $_SESSION['userID']);
$service = "Crea utente";
$a = $conn->query("select * from assegnazionePrivilegi inner join funzioni on funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID where Servizio = '$service' AND userID = $uid");

if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "" && $a->num_rows > 0)) {
    $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
    echo "<script>window.location.href = '../../index.php';</script>";
    die("wrong login");
}
$pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
$properties = array();
while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];

$data = $_POST['data'];
$us = mysqli_escape_string($conn, $data[0]);
$ps = mysqli_escape_string($conn, hash("sha256", $data[1]));

$r = $conn->query("INSERT INTO utenti(username, password) VALUES ('$us', '$ps') ");
if($r != 1) die("Errore: " . mysqli_error($conn));
die("Success");