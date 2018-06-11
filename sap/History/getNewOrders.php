<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/28/18
 * Time: 6:23 PM
 */

session_start();

$data = $_POST['times'];

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
$ret = array();
$res = $conn->query("SELECT MIN(Ordini.orderID), MIN(Ordini.productID), MIN(Ordini.quantita), MIN(Ordini.timestamp), MIN(Ordini.stato), Ordini.orderNo, CONCAT('[', GROUP_CONCAT('[', quantita, ',\"', magazzino.Nome, '\",\"', magazzino.Descrizione, '\",', magazzino.InGiacenza, ']'), ']'), MIN(utenti.username) FROM Ordini INNER JOIN magazzino ON Ordini.productID = magazzino.id INNER JOIN utenti on Ordini.user = utenti.id WHERE timestamp > $data GROUP BY orderNo ORDER BY MIN(stato) DESC, orderNo DESC");
while($row = $res->fetch_row()){
    $row[6] = json_decode($row[6]);
    array_push($ret, $row);
}

echo json_encode($ret);