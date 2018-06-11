<?php
session_start()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cassa - <?php echo $_SESSION['username'] ?></title>

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
        let lista = new Map();


        sap.ui.getCore().attachInit(function () {
            prodotti = new sap.ui.model.json.JSONModel({giac: 3}, true);
            let notes = new sap.m.TextArea({});
            let cb = new sap.m.CheckBox({text: "Asporto"});
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
            let data = [

                    <?php
                    $objcreate = "obj = {};\n";
                    $res = $conn->query("SELECT Gruppi_cassa.desc, icon FROM Gruppi_cassa WHERE 1");

                    $cnt = "";

                    while ($section = $res->fetch_row()) {
                        $ret = $conn->query("SELECT Nome, Descrizione, Prezzo, InGiacenza, id, gruppo FROM magazzino WHERE gruppo='$section[0]'");
                        if ($ret->num_rows == 0) continue;
                        $objcreate .= "obj['$section[0]'] = [];\n";
                        while ($row = $ret->fetch_row()) {
                            $objcreate .= "obj['$section[0]'].push({id: $row[4], nome: '$row[0]', desc: '$row[1]', prezzo: ($row[2]).toFixed(2), giac: $row[3], req: 0, disabled: false, group: '$row[5]'});\n";
                        }

                        $cnt .= "
                        new sap.m.VBox({
                            items:[
                                new sap.m.HBox({
                                    items: [
                                        new sap.ui.core.Icon({src: '$section[1]'}),
                                        new sap.m.Text({text: '$section[0]'}).addStyleClass(\"lista\")
                                    ]
                                }),
                                new sap.m.Table({
                                                columns: [
                                                    new sap.m.Column({
                                                        header: new sap.m.Text({text: 'Nome'})
                                                    }),
                                                    new sap.m.Column({
                                                        header: new sap.m.Text({text: 'Descrizione'})
                                                    }),
                                                    new sap.m.Column({
                                                        header: new sap.m.Text({text: 'Prezzo cad.'})
                                                    }),
                                                    new sap.m.Column({
                                                        header: new sap.m.Text({text: 'Quantità'})
                                                    }),
                                                    new sap.m.Column({
                                                        header: new sap.m.Text({text: 'Giacenza'})
                                                    }),
                                                ]
                                            }).bindAggregation('items', {
                                                path: '/ciao/$section[0]',
                                                template: new sap.m.ColumnListItem({
                                                    cells: [
                                                        new sap.m.Text({text: \"{nome}\"}),
                                                        new sap.m.Text({text: \"{desc}\"}),
                                                        new sap.m.Text({text: \"{prezzo}€\"}),
                                                        new sap.m.Input({
                                                            value: \"{req}\",
                                                            valueLiveUpdate: true,
                                                            liveChange: function(oArg){
                                                                let o = oArg.getSource().getBindingContext().getObject();
                                                                
                                                                if(!isNaN(o.req) && parseInt(o.req) <= o.giac){                                                               
                                                                    let key = o.id
                                                                    let val = '{\"id\": ' + o.id + ', \"nome\": \"' + o.nome + '\", \"qta\": ' + o.req + ', \"prezzo\": ' + o.prezzo + ', \"group\": \"' + o.group + '\"}';
                                                                    if(o.req == '0') lista.delete(key);
                                                                    else lista.set( key, val);
                                                                    let completa = '';
                                                                    let totale = 0;
                                                                    let json = '';
                                                                    lista.forEach(function(v, k){
                                                                        let e = JSON.parse(v);
                                                                        json += v + ', ';
                                                                        completa += e.qta + ' x ' + e.prezzo.toFixed(2) + '€ ' + e.nome + '\\n' + '\\n';
                                                                        totale += e.prezzo * e.qta; 
                                                                    });
                                                                    prodotti.setProperty('/send', json);
                                                                    prodotti.setProperty('/displist', completa);
                                                                    prodotti.setProperty('/totale', 'Totale: ' + (Math.round(totale*100)/100).toFixed(2) + '€');
addCurrency(parseFloat(prodotti.getProperty('/payed').replace('€', '')));                                                                
}
                                                            }
                                                        }),
                                                        new sap.m.Text({text: \"{giac}\"}),
                                                        new sap.m.Text({text: \"{id}\"}),
                                                        new sap.m.Text({text: \"{group}\"}),
                                            ]
                                        }
                                    )
                                })
                            ]
                        }),
                        
                        ";

                    }
                    echo $cnt;
                    ?>

                    new sap.m.VBox({
                        items: [
                            new sap.m.HBox({
                                items: [
                                    new sap.ui.core.Icon({src: 'sap-icon://money-bills'}),
                                    new sap.m.Text({text: 'Pagamento'}).addStyleClass("lista")
                                ]
                            }),
                            new sap.m.Table({
                                columns: [
                                    new sap.m.Column({
                                        header: new sap.m.Text({text: 'CONTO'})
                                    })
                                ],
                                growing: true,
                                growingThreshold: 1,
                                growingScrollToLoad: true
                            }).bindAggregation('items', {
                                path: '/ciao/<?php echo $section[0] ?>',
                                template: new sap.m.ColumnListItem({
                                        cells: [
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
                                                    cb,
                                                    new sap.m.Button({
                                                        type: sap.m.ButtonType.Accept,
                                                        text: "Invia",
                                                        press: function () {
                                                            let f = $("<form target='_blank'  method='POST' style='display:none;'></form>").attr({
                                                                action: "/cassa/sap/utils/bill_quasi_definitivo.php"
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

                                                            f.submit();
                                                            f.remove();

setTimeout(function(){window.location.reload()}, 800);
                                                        }
                                                    }),
                                                   /* new sap.m.Button({
                                                        type: sap.m.ButtonType.Accept,
                                                        text: "Nuovo ordine",
                                                        press: function () {
                                                            window.location.reload();
                                                        }
                                                    })*/
                                                ]
                                            })
                                        ]
                                    }
                                )
                            }),

                        ]
                    })

                ]
            ;


            <?php
            echo $objcreate;
            ?>
            obj = {ciao: obj, totale: "0.00€", resto: "---", payed: "0.00€"};
            prodotti.setData(obj);

            new sap.m.Page({
                title: "Cassa - <?php echo $_SESSION['username']?>",
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
                    new sap.m.HBox({
                        items: data
                    })
                ]
            }).placeAt("content").setModel(prodotti);
        })
        ;

        function updG () {
            $.post("./getGiacency.php", function (d, e) {
                console.log(d);
                let q = JSON.parse(d);
                Object.keys(obj.ciao).forEach(function(key) {                    
obj.ciao[key].forEach(function(e){
                        e.giac = q[e.id].giac;
                    })
                });
            });
            setTimeout(updG, <?php echo $properties["Giacency_update_interval"]?>);
        }

        setTimeout(updG, <?php echo $properties["Giacency_update_interval"]?>);

    </script>

</head>
<style>
        .sapMPage, .sapMPage > section {
            width: 100%;
            height: 92vh;
        }

        .sapUiBody {
            background-attachment: fixed;
            height: 99vh;
        }

        .sapMGTStateLoaded {
            margin: 0.5%;
        }

        .lista {
            font-size: large !important;
        }

        .lista2 {
            font-size: xx-large !important;
            font-weight: bold;
        }

        #__table3-listUl tl{
                display: none !important;
        }
</style>

<body id="content" class="sapUiBody" role="application">

</body>
</html>
