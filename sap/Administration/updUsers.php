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

$data = get_object_vars(json_decode($data));
$data = $data["ciao"];

$p = $conn->query("SELECT privilegi.descrizione, privilegi.id FROM privilegi");
$privid = array();
while ($r = $p->fetch_row())
    array_push($privid, $r[1]);
$a = "";

foreach ($data as $item) {
    $it = get_object_vars($item);
    $uid = mysqli_real_escape_string($conn, $it["userid"]);
    $res = $conn->query("DELETE FROM assegnazionePrivilegi WHERE userID=$uid");if($res != 1) die("Error: " . mysqli_error($conn));
    $res = $conn->query("UPDATE utenti SET new = 1 WHERE id=$uid");if($res != 1) die("Error: " . mysqli_error($conn));
    foreach ($privid as $pr) {
        if ($it[$pr]) {
            $res = $conn->query("INSERT INTO assegnazionePrivilegi(previlegeID, userID) VALUES($pr, $uid)");
            if($res != 1) die("Error: " . mysqli_error($conn));
        }
    }
}
$res = $conn->query("DELETE FROM assegnazionePrivilegi WHERE userId = (SELECT id FROM utenti WHERE new = 0)");if($res != 1) die("Error: " . mysqli_error($conn));
$res = $conn->query("DELETE FROM utenti WHERE new=0");if($res != 1) die("Error: " . mysqli_error($conn));
$res = $conn->query("UPDATE utenti SET new=0 WHERE 1");if($res != 1) die("Error: " . mysqli_error($conn));

die("Successo");

