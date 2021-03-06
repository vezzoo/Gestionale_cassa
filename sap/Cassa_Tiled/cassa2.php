<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cassa Rapida - <?php echo $_SESSION['username'] ?></title>

    <?php
    include("../../libs/mysql.inc");
    $conn = connect();
    $uid = mysqli_escape_string($conn, $_SESSION['userID']);
    $service = "Cassa standard";
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
        //        $userid = mysqli_escape_string($conn, $_SESSION['userID']);
        //        $res = $conn->query("SELECT funzioni.Servizio, funzioni.Subheader, funzioni.Icon, funzioni.Gruppo, funzioni.Destination FROM funzioni INNER JOIN assegnazionePrivilegi ON funzioni.RequiredPriviledge = assegnazionePrivilegi.previlegeID WHERE assegnazionePrivilegi.userID = $userid;");
        //
        //        $gruppi = $conn->query("SELECT funzioni.Gruppo FROM funzioni WHERE 1 GROUP BY funzioni.Gruppo");
        //        $controlli = array();
        //        while ($row = $gruppi->fetch_row()) $controlli[$row[0]] = array();
        //
        //        while ($row = $res->fetch_row()) array_push($controlli[$row[3]], $row);

        $objcreate = "";
        ?>

        let prodotti;
        let obj = {};
        let subtract = false;
let pagato = 0;
let resto = 0;




        sap.ui.getCore().attachInit(function () {
            prodotti = new sap.ui.model.json.JSONModel({giac: 3}, true);
            let cb = new sap.m.CheckBox({text: "Asporto"});
            let notes = new sap.m.TextArea({});

            function addCurrency(val) {
                let tot = parseFloat(prodotti.getProperty("/totale").replace("€", "").replace("Totale: ", ""));
                let pay = parseFloat(prodotti.getProperty("/payed").replace("€", ""));               
let nope = val - tot;
                if(val >= 0){
pagato = val;
resto = nope;
                    prodotti.setProperty("/payed", (val).toFixed(2) + "€");
                    if(true || nope >= 0) prodotti.setProperty("/resto", nope.toFixed(2) + "€");
                    else prodotti.setProperty("/resto", "----");
                }
            }           
            let aaa = new sap.m.Wizard({
                finishButtonText: "Stampa",
                steps:[
                    <?php
                    $res = $conn->query("SELECT Gruppi_cassa.desc, icon FROM Gruppi_cassa WHERE 1");
                    while($section = $res->fetch_row()){

                        $cnt = "";

                        $ret = $conn->query("SELECT Nome, Descrizione, Prezzo, InGiacenza, id, gruppo FROM magazzino WHERE gruppo='$section[0]'");
                        if($ret->num_rows == 0) continue;
                        while($row = $ret->fetch_row()){
                            $row[2] = sprintf("%.2f", $row[2]);

                            $objcreate .= "obj['$row[4]'] = {giac: $row[3], req: 0, disabled: false};\n";

                            $cnt .= "new sap.m.GenericTile({
                                            class: 'sapUiTinyMarginBegin sapUiTinyMarginTop tile',
                                            header: '$row[0]',
                                            subheader: '$row[1] - {/$row[4]/giac}',
                                            tileContent: [
                                                new sap.m.TileContent({
                                                    footer: '€ $row[2]',
                                                    content:[
                                                        new sap.m.NumericContent({
                                                            value: '{/$row[4]/req}'
                                                        })                                                                                           
                                                    ]
                                                })
                                            ],
                                            press: function(){
                                                if(parseInt(prodotti.getProperty('/$row[4]/giac')) >= parseInt(this.getTileContent()[0].getContent().getValue())){
                                                    if(subtract && parseInt(this.getTileContent()[0].getContent().getValue()) > 0){
                                                        prodotti.setProperty('/$row[4]/req', parseInt(this.getTileContent()[0].getContent().getValue()) - 1);
                                                        prodotti.setProperty('/list', prodotti.getProperty('/list').replace('{\"id\": $row[4], \"nome\" : \"$row[0]\", \"desc\" : \"$row[1]\", \"prezzo\": $row[2], \"group\": \"$row[5]\"}, ', '', 1) );
                                                    } else if(!subtract && parseInt(prodotti.getProperty('/$row[4]/giac')) > parseInt(this.getTileContent()[0].getContent().getValue())) {
                                                        prodotti.setProperty('/$row[4]/req', parseInt(this.getTileContent()[0].getContent().getValue()) + 1);
                                                        prodotti.setProperty('/list', prodotti.getProperty('/list') + '{\"id\": $row[4], \"nome\" : \"$row[0]\", \"desc\" : \"$row[1]\", \"prezzo\": $row[2], \"group\": \"$row[5]\"}, ');
                                                    }
                                                    let a =  JSON.parse('[' + prodotti.getProperty('/list') + '{ \"a\": \"b\"}' + ']');
                                                    let q = new Map();
                                                    a.forEach(function(e){
                                                        let element = JSON.stringify(e);
                                                        let qta = q.has(element) ? q.get(element) : 0;
                                                        q.set(element, qta+1);
                                                    });
                                                    let totale = 0;
                                                    let completa = '';
                                                    let json = '';
                                                    q.forEach(function(v, k){
                                                        if(k !== '{\"a\":\"b\"}'){
                                                            let e = JSON.parse(k);
                                                            json += '{\"id\": ' + e.id +', \"nome\": \"' + e.nome + '\", \"qta\": ' + v + ', \"prezzo\": \"' + e.prezzo + '\", \"group\": \"' + e.group + '\"}, ';
                                                            completa += v + ' x ' + e.prezzo.toFixed(2) + '€ ' + e.nome + '\\n' + '        ' + e.desc + '\\n';
                                                            totale += e.prezzo * v;
                                                        }
                                                    });
                                                    //console.log(json);
                                                    prodotti.setProperty('/send', json);
                                                    prodotti.setProperty('/displist', completa);
                                                    prodotti.setProperty('/totale', 'Totale: ' + (Math.round(totale*100)/100).toFixed(2) + '€');
addCurrency(parseFloat(prodotti.getProperty('/payed').replace('€', '')));
                                                }
                                            }
                                        }),";
                        }

                        echo "new sap.m.WizardStep({
                                        title: '$section[0]',
                                        icon: '$section[1]',
                                        validated: true,
                                        content: [
                                            $cnt                                          
                                        ]
                                    }),\n";
                    }
                    $objcreate .= "obj['totale'] = '0.00€'; obj['list'] = ''; obj['displist'] = ''; obj['payed'] = '0.00€'; obj['resto'] = '---';";
                    ?>
                    new sap.m.WizardStep({
                        title: 'Conto',
                        icon: 'sap-icon://money-bills',
                        validated: true,
                        content: [
                            new sap.m.HBox({
                                items: [
                                    new sap.m.VBox({
                                        items: [
                                            new sap.m.Text("lista", {
                                                text: "{/displist}"

                                            }).addStyleClass("lista"),
                                            new sap.m.Text("total", {
                                                text: "{/totale}",
                                                class: 'lista2'
                                            }).addStyleClass("lista2"),
                                            new sap.m.Text({
                                                text: "Incassato: "
                                            }),
                                            new sap.m.Input("diocane", {
                                                valueLiveUpdate: true,
                                                liveChange: function (oArg) {
                                                    let payed = parseFloat(oArg.getSource().getValue());
                                                    if (!isNaN(payed))
                                                        addCurrency(payed);
                                                }
                                            }),

                                            new sap.m.Text("rest", {
                                                text: "Resto: {/resto}",
                                                class: 'lista2'
                                            }).addStyleClass("lista2"),
                                            new sap.m.Text({text: "Note per la cucina:"}),
                                            notes,
                                            cb
                                        ]
                                    }).setJustifyContent(sap.m.FlexJustifyContent.Center),
                                ]
                            }).setJustifyContent(sap.m.FlexJustifyContent.Center)
                        ]
                    })
                ],
                complete: function(){
                    let f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
                        action: "/cassa/sap/utils/bill_quasi_definitivo_spero.php"
                    }).appendTo(document.body);

                    $('<input type="hidden" />').attr({
                        name: "data",
                        value: prodotti.getProperty('/send')
                    }).appendTo(f);

                    $('<input type="hidden" />').attr({
                        name: "total",
                        value: prodotti.getProperty('/totale')
                    }).appendTo(f);

                    $('<input type="hidden" />').attr({
                        name: "user",
                        value: "<?php echo $_SESSION['username'] ?>"
                    }).appendTo(f);

                    $('<input type="hidden" />').attr({
                        name: "notes",
                        value: notes.getValue()
                    }).appendTo(f);

                    $('<input type="hidden" />').attr({
                        name: "asporto",
                        value: cb.getSelected()
                    }).appendTo(f);
                    $('<input type="hidden" />').attr({
                        name: "pagato", value: pagato.toFixed(2) + "€"
                    }).appendTo(f);
                    $('<input type="hidden" />').attr({
			name: "resto", value: resto.toFixed(2) + "€"
		    }).appendTo(f);

                    f.submit();
                    f.remove();

                    obj = {};
                    <?php
                    echo $objcreate;

                    ?>
		    sap.ui.getCore().byId("diocane").setValue("");
                    prodotti.setData(obj);
                    aaa.goToStep(aaa.getSteps()[0]);
///aaa.discardProgress(aaa.getSteps()[0]);

                    cb.setSelected(false);
                    notes.setValue("");
                }
            });

            console.log(aaa);

            new sap.m.Page({
                title: "Cassa rapida - <?php echo $_SESSION['username']?>",
                class: "sapUiContentPadding",
                showNavButton: false,
                headerContent: [
                    new sap.m.Text({
                        text: "Modalità rimozione elementi"
                    }),
                    new sap.m.Switch({
                        customTextOn: " ",
                        customTextOff: " ",
                        change: function (evt) {
                            if(evt.getParameters().state){
                                subtract = true;
                                $(".sapMGT").css("background", "#F33");
                            } else {
                                subtract = false;
                                $(".sapMGT").css("background", "#FFF");
                            }
                        }
                    }),
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
                    aaa
                ]
            }).placeAt("content").setModel(prodotti);

            <?php
            echo $objcreate;
            ?>
            prodotti.setData(obj);

            function updG () {
                $.post("./getGiacency.php", function (d, e) {
                    console.log(d);
                    let q = JSON.parse(d);
                    Object.keys(obj).forEach(function(key) {
                        if (key !== 'list' && key !== 'totale' && key !== 'displist' && key !== 'send' && key !== 'payed' && key !== 'resto')
                            prodotti.setProperty('/' + key + '/giac', q[key].giac);
                    });
                });
                setTimeout(updG, <?php echo $properties["Giacency_update_interval"]?>);
            }

            setTimeout(updG, <?php echo $properties["Giacency_update_interval"]?>);
        });

    </script>

    <style>
        .sapMPage, .sapMPage > section {
            width: 100%;
            height: 92vh;
        }

        .sapUiBody {
            background-attachment: fixed;
            height: 99vh;
        }

        .sapMGTStateLoaded{
            margin: 0.5%;
        }

        .lista{
            font-size: large !important;
        }

        .lista2{
            font-size: xx-large !important;
            font-weight: bold;
        }
    </style>
</head>
<body id="content" class="sapUiBody" role="application">

</body>
</html>
