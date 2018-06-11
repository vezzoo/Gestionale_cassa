<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/16/18
 * Time: 3:45 PM
 */
session_start();

$data = $_POST['data'];


include("../../libs/mysql.inc");
$conn = connect();
$uid = mysqli_escape_string($conn, $_SESSION['userID']);
$service = "Aggiorna magazzino";
$a = $conn->query("select * from assegnazionePrivilegi inner join funzioni on funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID where Servizio = '$service' AND userID = $uid");

if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "" && $a->num_rows > 0)) {
    $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
    echo "<script>window.location.href = '../../index.php';</script>";
    die("wrong login");
}
$pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
$properties = array();
while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];


foreach ($data['prodotti'] as $i) {
    $nome = mysqli_escape_string($conn, $i['nome']);
    $desc = mysqli_escape_string($conn, $i['desc']);
    $prezzo = mysqli_escape_string($conn, $i['prezzo']);
    $giac = mysqli_escape_string($conn, $i['restano']);
    $gr = mysqli_escape_string($conn, $i['gruppo']);

    $res = $conn->query("INSERT INTO magazzino(Nome, Descrizione, Prezzo, InGiacenza, gruppo, updated) VALUES ('$nome', '$desc', $prezzo, $giac, '$gr', 1) ON DUPLICATE KEY UPDATE Nome = '$nome', Descrizione = '$desc', Prezzo = $prezzo, InGiacenza = $giac, gruppo = '$gr', updated = 1");
    if ($res != 1) die("Errore nell'update del database, contattare l'amministratore di rete, " . mysqli_error($conn));
}

$res = $conn->query("DELETE FROM magazzino WHERE updated = 0");
if ($res != 1) die("Errore nell'update del database, contattare l'amministratore di rete, " . mysqli_error($conn));

$res = $conn->query("UPDATE magazzino SET updated = 0 WHERE updated = 1");
if ($res != 1) die("Errore nell'update del database, contattare l'amministratore di rete, " . mysqli_error($conn));

//TODO: Update degli ordini effettuati da $data['timestamp'] ad attuale, (js Date.now()) ed eventuale errore se negativo;
//ALTER TABLE magazzino ADD updated bool DEFAULT 0 NULL;

die("Successo!");