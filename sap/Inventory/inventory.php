<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gestione Inventario - <?php echo $_SESSION['username'] ?></title>

    <?php
    include("../../libs/mysql.inc");
    $conn = connect();
    $uid = mysqli_escape_string($conn, $_SESSION['userID']);
    $service = "Gestione Inventario";
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
    <script
            id="sap-ui-bootstrap"
            src="../ui5/sap-ui-core.js"
            data-sap-ui-theme="<?php echo $properties["theme"] ?>"
            data-sap-ui-libs="sap.m"
            data-sap-ui-xx-bindingSyntax="complex"
            data-sap-ui-preload="async"
            data-sap-ui-compatVersion="edge"
            data-sap-ui-resourceroots='{"App": "./"}'
            displayBlock="true">
    </script>
    <script>

        sap.ui.getCore().attachInit(function () {

            var a = [];

            <?php

            $res = $conn->query("SELECT id, Nome, Descrizione, Prezzo, InGiacenza, gruppo FROM magazzino WHERE 1 ORDER BY InGiacenza ASC ");
            while ($row = $res->fetch_row()) echo "a.push({
                    id: $row[0],
                    nome: '$row[1]',
                    desc: '$row[2]',
                    prezzo: $row[3],
                    restano: $row[4],
                    gruppo: '$row[5]'
                });\n";
            ?>

            sap.ui.xmlview({
                viewName: "App.Inventory",
                viewData: {prodotti: a, timestamp: Date.now()}
            }).placeAt("content");
        });

    </script>

    <style>
        .sapMPage, .sapMPage > section {
            width: 100%;
            height: 90vh;
        }

        .sapUiBody {
            background-attachment: fixed;
            height: 99vh;
        }
    </style>
</head>
<body id="content" class="sapUiBody" role="application">

</body>
</html>