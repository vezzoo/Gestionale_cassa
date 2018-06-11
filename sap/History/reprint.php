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
    $service = "Billing";
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
$order = $_POST['ord'];
exec("weasyprint ../utils/saved_bills/$order ../utils/saved_bills/$order.pdf");
exec("lpr -P HP_HP_LaserJet_P2015_Series ../utils/saved_bills/$order.pdf");
die("ok");
?>

</body>
</html>

