<?php session_start(); ?>
<DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Scontrino</title>

    <?php
    include("../../libs/mysql.inc");
    $conn = connect();
    $uid = mysqli_escape_string($conn, $_SESSION['userID']);
    $service = "ELIMINA TUTTI GLI ORDINI";
    $a = $conn->query("select * from assegnazionePrivilegi inner join funzioni on funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID where Servizio = '$service' AND userID = $uid");

    if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "" && $a->num_rows > 0)) {
        $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
        echo "<script>window.location.href = '../../index.php';</script>";
        die("wrong login");
    }
    $pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
    $properties = array();
    while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];

    ?>

</head>


<body style="background-color: #333; color: #FFF">

<?php
$res = $conn->query("truncate Ordini");
if($res == 1){
echo "DONE\n<script>window.location.back()</script>";
die();
}
echo $res;
?>

</body>
</html>

