<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analitiche - <?php echo $_SESSION['username'] ?></title>

    <?php
    include("../../libs/mysql.inc");
    $conn = connect();
    $uid = mysqli_escape_string($conn, $_SESSION['userID']);
    $service = "Andamenti";
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


        $totale = "0.00€";
        $ordini = "0";

        $product = "";
        $content = "";
        $st = 0;
        $ed = 0;
        if(isset($_GET["dateFrom"]) && isset($_GET["dateTo"])){
            $st = mysqli_escape_string($conn, $_GET["dateFrom"]);
            $ed = mysqli_escape_string($conn, $_GET["dateTo"]);
            $res = $conn->query("SELECT SUM(Ordini.quantita), m.Nome, m.Descrizione, m.Prezzo FROM Ordini INNER JOIN magazzino m on Ordini.productID = m.id WHERE timestamp*1000 > $st AND timestamp*1000 < $ed GROUP BY m.Nome order by SUM(quantita) DESC");
            while ($row = $res->fetch_row()) {
                $product .= "{\"qta\": \"$row[0]\", \"nome\": \"$row[1]\", \"prezzo\": \"" . round($row[3] * 100) / 100 . "\", \"desc\": \"$row[2]\" },";
            }
            $product = substr($product, 0, strlen($product));
            $res = $conn->query("SELECT SUM(Ordini.totale) FROM Ordini JOIN( SELECT MIN(id) AS id, MIN(timestamp) AS ts FROM Ordini GROUP BY orderID ) g ON Ordini.id = g.id AND g.ts*1000 > $st AND g.ts*1000< $ed");
            $totale = floor($res->fetch_row()[0] * 100) / 100 . "";
            $res = $conn->query("SELECT orderID FROM Ordini WHERE Ordini.timestamp*1000 > $st AND Ordini.timestamp*1000 < $ed GROUP BY orderID");
            $ordini = $res->num_rows;
            $res = $conn->query("SELECT u.username, SUM(Ordini.totale), COUNT(Ordini.orderID) FROM Ordini JOIN( SELECT MIN(id) AS id, MIN(timestamp) AS ts FROM Ordini GROUP BY orderID ) g ON Ordini.id = g.id AND g.ts*1000 > $st AND g.ts*1000 < $ed INNER JOIN utenti u on Ordini.user = u.id GROUP BY Ordini.user");
        } else {
            $res = $conn->query("SELECT SUM(Ordini.quantita), m.Nome, m.Descrizione, m.Prezzo FROM Ordini INNER JOIN magazzino m on Ordini.productID = m.id GROUP BY m.Nome order by SUM(quantita) DESC");
            while ($row = $res->fetch_row()) {
                $product .= "{\"qta\": \"$row[0]\", \"nome\": \"$row[1]\", \"prezzo\": \"" . round($row[3] * 100) / 100 . "\", \"desc\": \"$row[2]\" },";
            }
            $product = substr($product, 0, strlen($product));
            $res = $conn->query("SELECT SUM(Ordini.totale) FROM Ordini JOIN( SELECT MIN(id) AS id FROM Ordini GROUP BY orderID ) g ON Ordini.id = g.id");
            $totale = floor($res->fetch_row()[0] * 100) / 100 . "";
            $res = $conn->query("SELECT orderID FROM Ordini WHERE 1 GROUP BY orderID");
            $ordini = $res->num_rows;
            $res = $conn->query("SELECT MIN(timestamp), MAX(timestamp) FROM Ordini");
            $res = $res->fetch_row();
            $st = $res[0]*1000; $ed = $res[1]*1000;
            $res = $conn->query("SELECT u.username, SUM(Ordini.totale), COUNT(Ordini.orderID) FROM Ordini JOIN( SELECT MIN(id) AS id FROM Ordini GROUP BY orderID ) g ON Ordini.id = g.id INNER JOIN utenti u on Ordini.user = u.id GROUP BY Ordini.user");
        }

        while ($row = $res->fetch_row()) {
            $content .= "{\"user\": \"$row[0]\", \"total\": \"" . round($row[1] * 100) / 100 . "\", \"orders\": \"$row[2]\" },";
        }
        $content .= "{\"user\": \"Totale:\", \"total\": \"$totale\", \"orders\": \"$ordini\"}";

        ?>

        sap.ui.getCore().attachInit(function () {
            let defJson = {ciao: [<?php echo $content ?>]};

            let userToDate;
            let userFromDate = new sap.m.DatePicker({
                change: function (oEvt) {
                    let start = new Date(userFromDate.getDateValue()).getTime();
                    let end = new Date(userToDate.getDateValue()).getTime() + 86340000;

                    if(end > start)
                    window.location.href = location.protocol + '//' + location.host + location.pathname + "?dateFrom=" + start + "&dateTo=" + end;
                }
            });

            userToDate = new sap.m.DatePicker({
                change: function () {
                    let start = new Date(userFromDate.getDateValue()).getTime();
                    let end = new Date(userToDate.getDateValue()).getTime() + 86340000;

                    if(end > start)
                        window.location.href = location.protocol + '//' + location.host + location.pathname + "?dateFrom=" + start + "&dateTo=" + end;
                }
            });

            function format(d){
                var m_names = ["gen", "feb", "mar",
                    "apr", "mag", "giu", "lug", "ago", "set",
                    "ott", "nov", "dic"];

                var curr_date = d.getDate();
                var curr_month = d.getMonth();
                var curr_year = d.getFullYear();
                return curr_date + " " + m_names[curr_month]
                    + " " + curr_year;
            }

 function getcsv(intesta, data){
                let ret = intesta;
                data.forEach(function(e){
                        Object.keys(e).forEach(function(q){
                                ret += e[q] + ";";
                        });
                        ret += "\n";
                });
                                
                return ret.replace(";\n", "\n");
        }
            <?php

            echo "userFromDate.setValue(format(new Date($st))); userToDate.setValue(format(new Date($ed)));";

            ?>

            new sap.m.Page({
                title: "Analitiche - <?php echo $_SESSION['username']?>",
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
                    }),
		    new sap.m.Button("dwld", {
			icon: "sap-icon://download",
			tooltip: "Scarica i dati in CSV",
			press: function(){
				var textToSave = getcsv("Utente in cassa; Totale (euro); Numero di ordini;\n;;;\n", JSON.parse("[<?php echo str_replace("\"", "\\\"", substr($content, 0, strlen($content))) ?>]"));

var hiddenElement = document.createElement('a');

hiddenElement.href = 'data:attachment/text,' + encodeURI(textToSave);
hiddenElement.target = '_blank';
hiddenElement.download = 'Introiti_casse.csv';
hiddenElement.click();

textToSave = getcsv("Quantita; Nome; Prezzo unitario (euro); Descrizione;\n;;;;\n", JSON.parse("[<?php echo str_replace("\"", "\\\"", substr($product, 0, strlen($product)-1)) ?>]"));

hiddenElement = document.createElement('a');

hiddenElement.href = 'data:attachment/text,' + encodeURI(textToSave);
hiddenElement.target = '_blank';
hiddenElement.download = 'Merce_venduta.csv';
hiddenElement.click();
			}
})
],

                content: [
                    new sap.m.HBox({
                        items: [
                            new sap.m.VBox({
                                items: [
                                    new sap.m.HBox({
                                        items: [
                                            new sap.m.Title({
                                                text: "Totali per utente:",
                                                level: sap.ui.core.TitleLevel.H2
                                            }),
                                            new sap.m.HBox({
                                                items: [
                                                    new sap.m.Text({text: "da"}),
                                                    userFromDate,
                                                    new sap.m.Text({text: "a"}),
                                                    userToDate
                                                ]
                                            }).setJustifyContent(sap.m.FlexJustifyContent.End).setAlignItems("Center")
                                        ]
                                    }).setJustifyContent(sap.m.FlexJustifyContent.SpaceBetween).setAlignItems("Center"),
                                    new sap.m.Table({
                                        columns: [
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Utente in cassa'})
                                            }),
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Totale (€)'})
                                            }),
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Numero ordini'})
                                            })
                                        ]
                                    }).setModel(new sap.ui.model.json.JSONModel({ciao: [<?php echo $content ?>]}))
                                        .bindAggregation('items', {
                                            path: '/ciao',
                                            template: new sap.m.ColumnListItem(
                                                {
                                                    cells: [
                                                        new sap.m.Text({text: "{user}"}),
                                                        new sap.m.Text({text: "{total}"}),
                                                        new sap.m.Text({text: "{orders}"}),
                                                    ]
                                                }
                                            )
                                        }),
                                    new sap.m.Title({
                                        text: "Merce uscita:",
                                        level: sap.ui.core.TitleLevel.H2
                                    }),
                                    new sap.m.Table({
                                        columns: [
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Quantità'})
                                            }),
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Nome'})
                                            }),
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Descrizione'})
                                            }),
                                            new sap.m.Column({
                                                header: new sap.m.Text({text: 'Prezzo unitario (€)'})
                                            })
                                        ]
                                    }).setModel(new sap.ui.model.json.JSONModel({ciao: [<?php echo $product ?>]}))
                                        .bindAggregation('items', {
                                            path: '/ciao',
                                            template: new sap.m.ColumnListItem(
                                                {
                                                    cells: [
                                                        new sap.m.Text({text: "{qta}"}),
                                                        new sap.m.Text({text: "{nome}"}),
                                                        new sap.m.Text({text: "{desc}"}),
                                                        new sap.m.Text({text: "{prezzo}"}),
                                                    ]
                                                }
                                            )
                                        }),

                                ]
                            }).setJustifyContent(sap.m.FlexJustifyContent.Center)
                        ]
                    }).setJustifyContent(sap.m.FlexJustifyContent.Center)
                ]
            }).placeAt('content');
        })
        ;

	function getcsv(intesta, data){
		let ret = intesta;
		data.forEach(function(e){
			Object.keys(e).forEach(function(q){
				ret += e[q] + ";";
			});
			ret += "\n";
	                ret = ret.replace(";\n", "\n").replace(";;", "").replace(".", ",");
        	});
				
		return ret.replace(";\n", "\n").replace(".", ",");
	}

	console.log(getcsv("a; b; c;\n", JSON.parse("[<?php echo str_replace("\"", "\\\"", substr($content, 0, strlen($content))) ?>]")));
console.log(getcsv("a; b; c\n", JSON.parse("[<?php echo str_replace("\"", "\\\"", substr($product, 0, strlen($product)-1)) ?>]")));

    </script>

    <style>

        .lista {
            font-size: large !important;
        }

        .lista2 {
            font-size: xx-large !important;
            font-weight: bold;
        }

        .sapMPage > section {
            position: absolute;
            overflow-y: hidden;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            height: 92vh !important;
        }

    </style>
</head>
<body id="content" class="sapUiBody" role="application">

</body>
</html>
