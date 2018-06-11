<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Storico - <?php echo $_SESSION['username'] ?></title>

    <?php
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

        <?php
        $masters = "";
        $slaves = "";

        $lastTimestamp = 0;

        $res = $conn->query("SELECT MIN(Ordini.orderID), MIN(Ordini.productID), MIN(Ordini.quantita), MIN(Ordini.timestamp), MIN(Ordini.stato), Ordini.orderNo, CONCAT('[', GROUP_CONCAT('[', quantita, ',\"', magazzino.Nome, '\",\"', magazzino.Descrizione, '\",', magazzino.InGiacenza, ']'), ']'), MIN(utenti.username) FROM Ordini INNER JOIN magazzino ON Ordini.productID = magazzino.id INNER JOIN utenti on Ordini.user = utenti.id WHERE 1 GROUP BY orderNo ORDER BY MIN(stato) DESC, orderNo DESC");
        while ($row = $res->fetch_row()) {

            if ($lastTimestamp < $row[3]) $lastTimestamp = $row[3];

            $cnrt = json_decode($row[6]);

            $canary = true;
            $content = "";
            $id = "";
            $title = "";
            $user = "";
            foreach ($cnrt as $ord) {
                if ($canary) {
                    $id = $row[0];
                    $title = $row[5];
                    $canary = false;
                    $user = $row[7];
                    $masters .= " new sap.m.DisplayListItem('b$id', {
                            type: sap.m.ListType.Active,
                            label: '" . $row[5] . ($row[4] == "EVASO" ? "-CONCLUSO" : "") . "',
                            value: '" . date('H:i', $row[3]) . "'
                        }).addCustomData(new sap.ui.core.CustomData({key: 'id', value: '$row[0]'})),";
                }
                $content .= "{
                    qta: \"$ord[0]\",
                    prodotto: \"$ord[1]\",
                    descrizione: \"$ord[2]\",
                    inGiacenza: \"$ord[3]\",
                }, ";
            }

            $slaves .= "new sap.m.Page('a$id', {
                            title: '$title',
                            content: [
                                new sap.m.Title({
                                    text: \"Contenuto dell\'ordine\",
                                    level: sap.ui.core.TitleLevel.H1
                                }),
                                new sap.m.Table({
                                    columns: [
                                        new sap.m.Column({header: new sap.m.Text({text: ' '})}),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Q.ta'})                                           
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Prodotto'})                                           
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Descrizione'})                                           
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Giacenza'})                                           
                                        })
                                    ]
                                }).setModel(new sap.ui.model.json.JSONModel({ ciao: [$content]})).bindAggregation('items',
                                    {path: '/ciao',
                                    template: new sap.m.ColumnListItem(  
                                       {cells: [  
                                                new sap.m.CheckBox({}), 
                                                new sap.m.Text({text : \"{qta}\"}),
                                               new sap.m.Text({text : \"{prodotto}\"}),  
                                               new sap.m.Text({text : \"{descrizione}\"}),
                                               new sap.m.Text({text : \"{inGiacenza}\"})
                                               ]  
                                       }
                                    )}
                                ),
                                new sap.m.Text({
                                    text: 'Ordine effettuato da $user'
                                }),
                                new sap.m.Button({
                                    text: 'Ristampa scontrino',
                                    press: function(){
                                         window.location.replace('../utils/saved_bills/$row[3]_$row[5].pdf')
                                    }
                                }),
                                " . ($row[4] == "EVASO" ? "" : "new sap.m.Button({text: 'EVADI', press: function(){jQuery.post('./evadi.php', {ordid: '$id'}, function(){}); sap.ui.getCore().byId('b$id').setLabel('$row[5]-CONCLUSO')}})") . "
                                                             
                            ]
                        }),";


        }
        ?>

        let lastTimestamp = <?php echo $lastTimestamp; ?>;
        let slaves;
        let masters;
        let page;

        sap.ui.getCore().attachInit(function () {

            slaves = [<?php echo $slaves ?>];
            masters = [<?php echo $masters ?>];

            let splitapp = new sap.m.SplitApp("cba", {
                detailPages: slaves,
                masterPages: [
                    new sap.m.Page({
                        title: "Ordini",
                        content: [
                            new sap.m.List("abc", {
                                items: masters,
                                itemPress: function (o) {
                                    splitapp.toDetail("a" + o.getParameter("listItem").getCustomData()[0].getValue());
                                }
                            })

                        ]
                    })
                ]
            });

            page = new sap.m.Page({
                title: "Storico - <?php echo $_SESSION['username']?>",
                class: "sapUiContentPadding",
                showNavButton: false,
                headerContent: [
                    new sap.m.Button("dash", {
                        press: function () {
                            window.location.href = '../Dashboard/dashboard.php'
                        },
                        icon: "sap-icon://bbyd-dashboard",
                        tooltip: "Vai alla dashboard"
                    }),
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
                    splitapp
                ]
            });

            page.placeAt('content');
        });

        function upd() {

            $.post("./getNewOrders.php", {times: lastTimestamp}, function (d, e) {
                let data = JSON.parse(d);
                // console.log(data);
                data.forEach(function (e) {

                    let content = [];
                    e[6].forEach(function (ee) {
                        content.push({
                            qta: ee[0],
                            prodotto: ee[1],
                            descrizione: ee[2],
                            inGiacenza: ee[3],
                        })
                    });

                    sap.ui.getCore().byId("cba").addDetailPage(
                        new sap.m.Page('a' + e[0], {
                            title: e[5],
                            content: [
                                new sap.m.Title({
                                    text: "Contenuto dell\'ordine",
                                    level: sap.ui.core.TitleLevel.H1
                                }),
                                new sap.m.Table({
                                    columns: [
                                        new sap.m.Column({header: new sap.m.Text({text: ' '})}),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Q.ta'})
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Prodotto'})
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Descrizione'})
                                        }),
                                        new sap.m.Column({
                                            header: new sap.m.Text({text: 'Giacenza'})
                                        })
                                    ]
                                }).setModel(new sap.ui.model.json.JSONModel({
                                    ciao: content
                                })).bindAggregation('items',
                                    {
                                        path: '/ciao',
                                        template: new sap.m.ColumnListItem(
                                            {
                                                cells: [
                                                    new sap.m.CheckBox({}),
                                                    new sap.m.Text({text: "{qta}"}),
                                                    new sap.m.Text({text: "{prodotto}"}),
                                                    new sap.m.Text({text: "{descrizione}"}),
                                                    new sap.m.Text({text: "{inGiacenza}"})
                                                ]
                                            }
                                        )
                                    }
                                ),
                                new sap.m.Text({
                                    text: 'Ordine effettuato da ' + e[7]
                                }),
                                (e[4] === "EVASO" ? new sap.m.Text({
                                    text: ' '
                                }) : new sap.m.Button({text: 'EVADI', press: function(){jQuery.post('./evadi.php', {ordid: e[0]}, function(){}); sap.ui.getCore().byId("b" + e[0]).setLabel(e[4] + "-CONCLUSO");}}))
                            ]
                        }),
                    );

                    let date = new Date(parseInt(e[3]) * 1000);
                    let hours = "0" + date.getHours();
                    let minutes = "0" + date.getMinutes();
                    let formattedTime = hours.substr(-2) + ':' + minutes.substr(-2);

                    sap.ui.getCore().byId("abc").addItem(new sap.m.DisplayListItem("b" + e[0], {
                        type: sap.m.ListType.Active,
                        label: e[5] + (e[4] === "EVASO" ? "-CONCLUSO" : ""),
                        value: formattedTime
                    }).addCustomData(new sap.ui.core.CustomData({key: 'id', value: e[0]})));


                    if (lastTimestamp < parseInt(e[3])) lastTimestamp = parseInt(e[3]);
                });
            });

            setTimeout(upd, <?php echo $properties["Order_update_internal"]; ?>)
        }

        setTimeout(upd, <?php echo $properties["Order_update_internal"]; ?>)

    </script>

    <style>

        .done {
            display: none;
        !important;
        }

    </style>
</head>
<body id="content" class="sapUiBody" role="application">

</body>
</html>
