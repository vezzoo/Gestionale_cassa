<?php
require_once __DIR__ . '/vendor/autoload.php';
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KeyRingLogin</title>
    <link type="text/css" rel="stylesheet" href="stylesheets/home.css" id="style"/>
    <script src="scripts/jquery.min.js"></script>
</head>

<?php
$err = false;
$errmsg = "";

function rndString($length = 10, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    global $err;
    global $errmsg;
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        try {
            $pieces [] = $keyspace[random_int(0, $max)];
        } catch (Exception $e) {
            $err = true;
            $errmsg = "Errore di siurezza interno.";
            return "";
        }
    }
    return implode('', $pieces);
}

include("libs/mysql.inc");
$conn = connect();

$pr = $conn->query("SELECT prop, valore FROM proprieta WHERE 1 GROUP BY prop");
$properties = array();
while ($a = $pr->fetch_row()) $properties[$a[0]] = $a[1];

$sessionID = hash("sha512", time() . rndString(30));
$random = rndString(40);
$unt = time() + 60;
$res = $conn->query("INSERT INTO keyring_sessions(sessionID, validUntil, randomKey) VALUES ('$sessionID', $unt, '$random')");
if ($res != 1) {
    $err = true;
    $errmsg = "Impossibile creare la sessione keyring. ";
}

$data = array();
$data['sessionID'] = $sessionID;
$data['random'] = $random;
$data['hostname'] = $properties['hostName'];
$data = json_encode($data);
?>

<body>

<div class="page">
    <div class="container">
        <div class="left">
            <div class="login">Keyring</div>
            <div class="eula">Scannerizza il codice QR con il tuo smartphone e procedi come indicato
                dall'applicazione<br/>Non hai l'applicazione? <a href="app.apk">Scaricala!</a></div>
        </div>
        <div class="right">
                <?php
                if ($err) echo "<b>$errmsg</b>";
                else {
                    $qrCode = new QrCode($data);
                    $qrCode->setSize(290);

                    $qrCode->setMargin(10);
                    $qrCode->setEncoding('UTF-8');
                    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
                    $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
                    $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
                    $qrCode->setValidateResult(false);
                    $data = base64_encode($qrCode->writeString());
                    echo "<img src='data:image/png;base64, $data' style='position: absolute; top:5px; left: 5px'/>";
                }
                ?>

        </div>
    </div>
</div>
<script>
    function upd(){
        $.post("/cassa/twostepaccess.php", {check: "", sessionid: "<?php echo $sessionID ?>"}, function (d, e) {
            if(d === "true"){
                $.post("/cassa/twostepaccess.php", {login: "", sessionid: "<?php echo $sessionID ?>"}, function (d, e) {
                    if(d === "DONE") window.location.href = "./sap/Dashboard/dashboard.php";
                    else window.location.reload();
                });
            }
        });

        setTimeout(upd, 2000);
    }

    setTimeout(upd, 2000);
</script>
</body>
</html>