<?php
session_start()
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Amministrazione - <?php echo $_SESSION['username'] ?></title>

    <?php
    include("../../libs/mysql.inc");
    $conn = connect();
    $uid = mysqli_escape_string($conn, $_SESSION['userID']);
    $service = "Amministrazione utenti";
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
        $p = $conn->query("SELECT privilegi.descrizione, privilegi.id FROM privilegi");
        $privs = array();
        $privid = array();
        while ($r = $p->fetch_row()){
            array_push($privs, $r[0]);
            array_push($privid, $r[1]);
        }

        $res = $conn->query("SELECT CONCAT('[', better_result, ']') AS best_result
FROM (SELECT GROUP_CONCAT('{', my_json, '}' SEPARATOR ',') AS better_result
      FROM (SELECT
              CONCAT('\"userid\":', '\"', utenti.id, '\"', ',' '\"username\":', '\"', MIN(username), '\"', ',' '\"password\":\"',
                     MIN(password), '\"', ',' '\"privs\":\"', GROUP_CONCAT(p.descrizione), '\"', ',\"new\":', utenti.new) AS my_json
            FROM utenti
              JOIN assegnazionePrivilegi ON utenti.id = assegnazionePrivilegi.userID OR utenti.new = 1
              INNER JOIN privilegi p on assegnazionePrivilegi.previlegeID = p.id
            GROUP BY utenti.id) AS more_json) AS yet_more_json");
        $json = json_decode($res->fetch_row()[0]);
        $newjson = array();
        foreach ($json as $user) {
            $user = get_object_vars($user);
            $vals = array();
            if ($user["new"] == 1)
                $user["privs"] = [];
            else
                $user["privs"] = explode(",", $user["privs"]);
            for($i = 0; $i < sizeof($privs); $i++) $user[$privid[$i]] = in_array($privs[$i], $user["privs"]);
            array_push($newjson, $user);
        }
        $arr = array();
        $arr["ciao"] = $newjson;
        $newjson = json_encode($arr);
        ?>

        sap.ui.getCore().attachInit(function () {

            let model = new sap.ui.model.json.JSONModel(JSON.parse('<?php echo $newjson ?>'));

            function getChecks(a) {
                console.log(a);
                return [new sap.m.Text({text: "hi"}), new sap.m.Text({text: "hi"}), new sap.m.Text({text: "hi"})];
            }

            let nwUser = new sap.m.Input({});
            let nwPsw = new sap.m.Input({type: sap.m.InputType.Password});
            let nw2Psw = new sap.m.Input({type: sap.m.InputType.Password});

            new sap.m.Page({
                title: "Amministrazione - <?php echo $_SESSION['username']?>",
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
                    })
                ],

                content: [
                    new sap.m.Table({
                        columns: [
                            new sap.m.Column({
                                header: new sap.m.Text({text: ''})
                            }),
                            new sap.m.Column({
                                header: new sap.m.Text({text: 'ID utente'}),
                                footer: new sap.m.Button({
                                    icon: "sap-icon://add",
                                    type: sap.m.ButtonType.Accept,
                                    press: function () {
                                        var oDialog = new sap.m.Dialog({
                                            title: "Dialog",
                                            modal: true,
                                            contentWidth: "1em",
                                            buttons: [
                                                new sap.m.Button({
                                                    text: "Aggiungi Utente",
                                                    type: sap.m.ButtonType.Accept,
                                                    press: function () {
                                                        if (nwPsw.getValue() === nw2Psw.getValue()) {

                                                            //if(nwPsw.getValue() === nwUser.getValue()){
                                                              //  sap.m.MessageToast.show("Impossibile salvare nome utente e password uguali.");
                                                              //  return;
                                                            //}

                                                            $.post("./newUser.php", {data: [nwUser.getValue(), nwPsw.getValue()]}, function (d, e) {
                                                                window.location.reload();
                                                                // console.log(d);
                                                            })
                                                        } else {
                                                            sap.m.MessageToast.show("Le password inserite non corrispondono");
                                                        }
                                                    }
                                                }),
                                                new sap.m.Button({
                                                    text: "Annulla",
                                                    type: sap.m.ButtonType.Reject,
                                                    press: function () {
                                                        oDialog.close();
                                                    }
                                                })
                                            ],
                                            content: [
                                                new sap.m.VBox({
                                                    items: [
                                                        new sap.m.HBox({
                                                            items: [
                                                                new sap.m.Text({
                                                                    text: "Username: "
                                                                }),
                                                                nwUser
                                                            ]
                                                        }).setJustifyContent(sap.m.FlexJustifyContent.SpaceBetween).setAlignItems("Center"),
                                                        new sap.m.HBox({
                                                            items: [
                                                                new sap.m.Text({
                                                                    text: "Password: "
                                                                }),
                                                                nwPsw
                                                            ]
                                                        }).setJustifyContent(sap.m.FlexJustifyContent.SpaceBetween).setAlignItems("Center"),
                                                        new sap.m.HBox({
                                                            items: [
                                                                new sap.m.Text({
                                                                    text: "Ripeti Password: "
                                                                }),
                                                                nw2Psw
                                                            ]
                                                        }).setJustifyContent(sap.m.FlexJustifyContent.SpaceBetween).setAlignItems("Center")
                                                    ]
                                                })
                                            ]
                                        });
                                        oDialog.open()
                                    }
                                })
                            }),
                            new sap.m.Column({
                                header: new sap.m.Text({text: 'Username'}),
                                footer: new sap.m.Button({
                                    icon: "sap-icon://save",
                                    type: sap.m.ButtonType.Accept,
                                    press: function () {
                                        $.post("./updUsers.php", {data: model.getJSON()}, function (d, e) {
                                            // window.location.reload();
                                            // console.log(d);
                                            sap.m.MessageToast.show(d)
                                        })
                                    }
                                })
                            }),
                            new sap.m.Column({
                                header: new sap.m.Text({text: 'Password'})
                            }),
                            <?php
                            foreach ($privs as $p) {
                                echo "new sap.m.Column({
                                header: new sap.m.Text({text: '$p'})
                            }),";
                            }
                            ?>
                        ]
                    }).setModel(model)
                        .bindAggregation('items', {
                            path: '/ciao',
                            template: new sap.m.ColumnListItem(
                                {
                                    cells: [
                                        new sap.m.Button({
                                            icon: "sap-icon://delete",
                                            type: sap.m.ButtonType.Reject,
                                            press: function(oArg){
                                                let deleteRecord = oArg.getSource().getBindingContext().getObject();
                                                let dataset = model.getData().ciao;
                                                let len = dataset.length;
                                                for (let i = 0; i < len; i++) {
                                                    if (dataset[i] === deleteRecord) {
                                                        model.getData().ciao.splice(i, 1);
                                                        model.refresh();
                                                        break;
                                                    }
                                                }
                                            }
                                        }),
                                        new sap.m.Text({text: "{userid}"}),
                                        new sap.m.Text({text: "{username}"}),
                                        new sap.m.Text({text: "{password}"}),

                                        <?php
                                        foreach ($privid as $p) {
                                            echo "new sap.m.CheckBox({
                                                    selected: '{" . $p . "}'
                                                }),";
                                        }
                                        ?>

                                    ]
                                }
                            )
                        }),
                ]
            }).placeAt("content");
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
    </style>
</head>
<body id="content" class="sapUiBody" role="application">

</body>
</html>
