<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - <?php echo $_SESSION['username'] ?></title>

    <?php
    if (!(isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "")) {
        $_SESSION['errored'] = "Non disponi delle autorizzazioni necessarie";
        echo "<script>window.location.href = '../../index.php';</script>";
        die("wrong login");
    }
    include("../../libs/mysql.inc");
    $conn = connect();

    $pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
    $properties = array();
    while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];

    ?>
    <script
            id="sap-ui-bootstrap"
            src="../ui5/sap-ui-core.js"
            data-sap-ui-theme="<?php echo $properties["theme"] ?>"
            data-sap-ui-libs="sap.m"
            displayBlock="true">
    </script>
    <script>

        <?php
        $userid = mysqli_escape_string($conn, $_SESSION['userID']);
        $res = $conn->query("SELECT funzioni.Servizio, funzioni.Subheader, funzioni.Icon, funzioni.Gruppo, funzioni.Destination FROM funzioni INNER JOIN assegnazionePrivilegi ON funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID WHERE assegnazionePrivilegi.userID = $userid AND funzioni.isALink = 1;");

        $gruppi = $conn->query("SELECT funzioni.Gruppo FROM funzioni WHERE isALink = 1 GROUP BY funzioni.Gruppo");
        $controlli = array();
        while ($row = $gruppi->fetch_row()) $controlli[$row[0]] = array();

        while ($row = $res->fetch_row()) array_push($controlli[$row[3]], $row);
        ?>

        sap.ui.getCore().attachInit(function () {
            new sap.m.Page({
                title: "Dashboard - <?php echo $_SESSION['username']?>",
                class: "sapUiContentPadding",
                showNavButton: false,
                headerContent: [
                    new sap.m.Button("exit", {
                        press: function () {
                            $.post("../../exit.php", function (d, e) {
                                window.location.href = '../../index.php';
                            })
                        },
                        icon: "sap-icon://visits",
                        tooltip: "Esci"
                    })],
                content: [
                    <?php
                    foreach ($controlli as $i) {
                        $groupName = $i[0][3];

                        $content = "[";

                        foreach ($i as $item) {
                            $content .= "
                                new sap.m.GenericTile({
                                    class: 'sapUiTinyMarginBegin sapUiTinyMarginTop tileLayout',
                                    header: '$item[0]',
                                    subHeader: '$item[1]',
                                    tileContent: new sap.m.TileContent({
                                        footer: 'Vai alla pagina',
                                        content: [new sap.m.ImageContent({src: '$item[2]'})]
                                    }),
                                    press: function(){
                                        //window.open(
                                         // '$item[4]',
                                          //'_blank' // <- This is what makes it open in a new window.
                                        //);
                                        window.location.href = '$item[4]';
                                    }
                                }),
                                ";
                        }

                        $content .= "]";

                        echo "new sap.m.Panel({
                            headerText: '$groupName',
                            content: $content
                            }),";
                    }
                    ?>
                ]
            }).placeAt("content");
        });

    </script>

    <style>
        .sapUiView {
            display: inline-block;
            height: 100%;
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