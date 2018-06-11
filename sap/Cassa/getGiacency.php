<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/17/18
 * Time: 9:11 AM
 */

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}

session_start();

$data = $_POST['data'];

include("../../libs/mysql.inc");
$conn = connect();
$uid = mysqli_escape_string($conn, $_SESSION['userID']);
$service = "Giacenze";
$a = $conn->query("select * from assegnazionePrivilegi inner join funzioni on funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID where Servizio = '$service' AND userID = $uid");

if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "" && $a->num_rows > 0)) {
    $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
    echo "<script>window.location.href = '../../index.php';</script>";
    die("wrong login");
}
$pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
$properties = array();
while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];

$res = $conn->query("SELECT magazzino.id, magazzino.InGiacenza, magazzino.gruppo FROM magazzino WHERE 1");
$ret = "{";
while ($row = $res->fetch_row()) $ret .= "\"$row[0]\": {\"giac\": $row[1], \"group\": \"$row[2]\"}, ";
$ret .= " \"a\":\"b\" }";

die($ret);