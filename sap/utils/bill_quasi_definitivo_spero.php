<!DOCTYPE html>
<?php session_start(); ?>
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

    <style>
        tr:nth-child(even) {background-color: #f2f2f2;}

        .chiudi {
            width: 1%;
            white-space: nowrap;
        }

        tr {
            height: 20px;
        }

        .piccolo {
            font-size: 15px;
        }

        .BAR {
            background-color: #1bb1ff;
        }

        th, td {
            border-bottom: 1px solid #888;
        }
    </style>
</head>


<body style="background-color: #FFF">

<div id="cacca" style="top: 0; left: 0; width: 148mm; min-height: 210mm; background-color: #FFF">

    <?php
    function rnds($l)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $l; $i++) {
            $randstring = $characters[rand(0, strlen($characters))];
        }
        return $randstring;
    }

    if (!isset($_POST["donotregister"])) {
        $maxno = 22;
        $data = json_decode("[" . substr($_POST['data'], 0, strlen($_POST['data']) - 2) . "]");

        $next = $conn->query("SELECT MAX(incrementalNo) FROM Ordini LIMIT 1");
        $next = $next->fetch_row()[0];
        $azzeratore = $properties["zeroer"];
        if ($next == null) $next = 0;
        else $next++;
        $order_num = sprintf('%04d', $next-intval($azzeratore));
        $orderid = hash('md5', $order_num . rnds(5));

        $setG = $conn->prepare("UPDATE magazzino SET InGiacenza = InGiacenza - ? WHERE id = ?");
        $addE = $conn->prepare("INSERT INTO Ordini(orderID, productID, quantita, timestamp, orderNo, incrementalNo, user, totale) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $now = time();
        foreach ($data as $i) {
            $nome = get_object_vars($i)["nome"];
            $qta = get_object_vars($i)["qta"];
            $pr = get_object_vars($i)["prezzo"];
            $id = get_object_vars($i)['id'];

            $setG->bind_param("ii", $qta, $id);
            $setG->execute();
            $addE->bind_param("siiisiid", $orderid, $id, $qta, $now, $order_num, $next, $_SESSION['userID'], str_replace("Totale: ", "", str_replace("€", "", $_POST['total'])));
            $addE->execute();
        }
    }

    ?>

    <div style="float: left;"><h1 style="margin: 20px; margin-bottom: 1px"><?php echo $order_num ?></h1></div>
    <div style="float: right;"><h1 style="float: right; margin: 20px; margin-bottom: 1px; margin-top: 8px"><?php echo $properties["event_name"]; ?></h1></div>
<!--    <p style="border-bottom-style: solid; border-bottom-width: thin; width: 100%;"></p>-->

    <table style="width: 90%">
        <tr style="height: 45px; background: #CCC">
            <td><b>Descrizione</b></td>
            <td class="chiudi"><b>Qta</b></td>
            <td class="chiudi"><b>Prezzo</b></td>
        </tr>
        <?php
        $n = 0;
        $gruppi = array();
        foreach ($data as $i) {
            $nome = get_object_vars($i)["nome"];
            $qta = get_object_vars($i)["qta"];
            $pr = sprintf("%.2f", get_object_vars($i)["prezzo"]);
            $gr = get_object_vars($i)["group"];

            $pre = sprintf("%.2f", floatval($pr) * floatval($qta));

            if ($gr == 'MENU') $gr = 'BAR';

            if(!in_array($gr, $gruppi)) array_push($gruppi, $gr);

            echo "<tr class=\"piccolo\"><td>$nome</td><td class=\"chiudi\">$qta</td><td class=\"chiudi\">€ $pre</td></tr>";
            $n++;
        }
        ?>
        <tr style="background-color: #FFF">
            <td></td>
            <td class="chiudi"></td>
            <td class="chiudi"></td>
        </tr>
        <tr style="height: 45px; background: #CCC">
            <td><b>TOTALE</b></td>
            <td class="chiudi"></td>
            <td class="chiudi"><b><?php echo str_replace("Totale: ", "", $_POST['total']) ?></b></td>
        </tr>
        <tr>
            <td><b>PAGATO</b></td>
            <td class="chiudi"></td>
            <td class="chiudi"><?php echo $_POST['pagato'] ?></td>
        </tr>
        <tr>
            <td><b>RESTO</b></td>
            <td class="chiudi"></td>
            <td class="chiudi"><?php echo $_POST['resto'] ?></td>
        </tr>
    </table>

    <p style="float: right; margin: 10px; margin-top: 4px;" id="DATE">DD/MM/AAAA ore: hh</p>
</div>

<div style=" width: 148mm; height: 210mm; background-color: #FFF; display: <?php  if(in_array("BAR", $gruppi)) echo "block"; else echo "none"; ?>">

    <div style="float: left;"><h1 style="margin: 20px; margin-bottom: 1px"><?php echo $order_num ?></h1></div>
    <div style="float: right;"><h1 style="float: right; margin: 20px; margin-bottom: 1px; margin-top: 8px">BAR</h1></div>
<!--    <p style="border-bottom-style: solid; border-bottom-width: thin; width: 100%;"></p>-->

    <table style="width: 90%">
        <tr style="height: 45px; background: #CCC">
            <td><b>Descrizione</b></td>
            <td class="chiudi"><b>Qta</b></td>
        </tr>
        <?php
        $n = 0;
        foreach ($data as $i) {
            $nome = get_object_vars($i)["nome"];
            $qta = get_object_vars($i)["qta"];
            $gr = get_object_vars($i)["group"];
            if($gr == "BAR" || $gr == "MENU") echo "<tr class=\"piccolo \"><td>$nome</td><td class=\"chiudi\">$qta</td></tr>";
            $n++;
        }
        ?>
        <tr style="background-color: #FFF">
            <td></td>
            <td class="chiudi"></td>
        </tr>
    </table>

    <p style="float: right; margin: 10px; margin-top: 4px;" id="DATE_BRUTTO">DD/MM/AAAA ore: hh</p>
</div>

</body>

<?php

function fill($mstr, $n){
                $str = $mstr;
                if(mb_strlen($str) >= $n) return $str;
                for($i = mb_strlen($str); $i < $n; $i++) $str .= " ";
                return $str;
        }

                function toNumber($dest)
    {
        if ($dest)
            return ord(strtolower($dest)) - 48;
        else
            return 0;
    }

$lines = [
            ["  █████  ", "  ██  ", " ███████ ", " ███████ ", "██       ", "████████", " ███████ ", "████████", " ███████ ", " ███████ "],
            [" ██   ██ ", "████  ", "██     ██", "██     ██", "██    ██ ", "██      ", "██     ██", "██    ██", "██     ██", "██     ██"],
            ["██     ██", "  ██  ", "       ██", "       ██", "██    ██ ", "██      ", "██       ", "    ██  ", "██     ██", "██     ██"],
            ["██     ██", "  ██  ", " ███████ ", " ███████ ", "██    ██ ", "███████ ", "████████ ", "   ██   ", " ███████ ", " ████████"],
            ["██     ██", "  ██  ", "██       ", "       ██", "█████████", "      ██", "██     ██", "  ██    ", "██     ██", "       ██"],
            [" ██   ██ ", "  ██  ", "██       ", "██     ██", "      ██ ", "██    ██", "██     ██", "  ██    ", "██     ██", "██     ██"],
            ["  █████  ", "██████", "█████████", " ███████ ", "      ██ ", " ██████ ", " ███████ ", "  ██    ", " ███████ ", " ███████ "]
        ];

$asporto = [
	["   ███   "],
	["  ██ ██  "],
	[" ██   ██ "],
	["██     ██"],
	["█████████"],
	["██     ██"],
	["██     ██"],
	[""],
	[" ██████ "],
	["██    ██"],
	["██      "],
	[" ██████ "],
	["      ██"],
	["██    ██"],
	[" ██████ "],
        [""],
	["████████ "],
	["██     ██"],
	["██     ██"],
	["████████ "],
	["██       "],
	["██       "],
	["██       "],
        [""],
	[" ███████ "],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	[" ███████ "],
        [""],
	["████████ "],
	["██     ██"],
	["██     ██"],
	["████████ "],
	["██   ██  "],
	["██    ██ "],
	["██     ██"],
        [""],
	["████████"],
	["   ██   "],
	["   ██   "],
	["   ██   "],
	["   ██   "],
	["   ██   "],
	["   ██   "],
        [""],
	[" ███████"],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	["██     ██"],
	[" ███████"]
];



if(in_array("CUCINA", $gruppi)){

    $html = "";

        foreach ($lines as $line) {
        foreach (str_split($order_num) as $ch) {
            $html .= $line[toNumber($ch)] . " ";
        }
        $html .= "\n";
    }

$html .= "\n\nPRODOTTO                                    |QTA\n\n";

        foreach ($data as $i) {
            $nome = fill(get_object_vars($i)["nome"], 42);
            $qta = get_object_vars($i)["qta"];
            $gr = get_object_vars($i)["group"];
            if($gr == "CUCINA" || $gr == "MENU" ) $html .= "  $nome|  $qta\n--------------------------------------------+-----\n";
            $n++;
        }


if($_POST["notes"] != "")
        $html .= "\n\n\n--------------Note per la cucina--------------\n". $_POST["notes"];

   $html .= "";
        $mhtml = "";

for($i = count(preg_split("/((\r?\n)|(\r\n?))/", $html)); $i < count($asporto)+10; $i++) $html.="\n";

$aaa = -9;
foreach(preg_split("/((\r?\n)|(\r\n?))/", $html) as $line){
	if($aaa == count($asporto)) $aaa = -1;
    if($_POST["asporto"] == "true" && $aaa++ >= 0)
		$mhtml .= fill("           " . $line, 65) . $asporto[$aaa-1][0] . "\n";
	else
		$mhtml .= "           " . $line . "\n";
}


    $f = $properties["bill_save_folder"] . "/" . time() . "_" . $order_num;
    file_put_contents($f, $mhtml);
//    $printer = "HP_HP_LaserJet_P2015_Series";
    $printer = $properties["printer_name"];
    exec("weasyprint $f $f.pdf");
//    exec("mv $f.pdf $f.pdf.unprinted");
//    usleep (2000000);
    exec("lpr -o scaling=130 -P $printer $f");
//    exec("lpr -P HP_HP_LaserJet_P2015_Series $f.pdf");
}
?>

<script>
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //January is 0!
    var hh = today.getHours();
    var min = today.getMinutes();

    var yyyy = today.getFullYear();
    if (dd < 10) {
        dd = '0' + dd;
    }
    if (mm < 10) {
        mm = '0' + mm;
    }
    if (min < 10) {
        min = '0' + min;
    }

    var today = dd + '/' + mm + '/' + yyyy + "  ore " + hh + ":" + min;
    document.getElementById("DATE").innerHTML = today;
    document.getElementById("DATE_BRUTTO").innerHTML = today;

    window.print();

    var d = document.getElementById("cacca");
    if( true || d.clientHeight > 210){
        d.style.height = Math.ceil(d.clientHeight / 794 ) * 794 + "px";
    }
</script>

</html>


