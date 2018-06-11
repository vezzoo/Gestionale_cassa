<?php
session_start();
//session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cassa - sagra</title>

    <link type="text/css" rel="stylesheet" href="stylesheets/home.css" id="style"/>
    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/animejs.min.js"></script>
</head>
<body id="body">

<?php
$alreadyLogged = true;
function load(){
    global $alreadyLogged;
    if (isset($_SESSION['userID']) && isset($_SESSION['privilegi']) && isset($_SESSION['username']) && $_SESSION['username'] != "") {
        ?>
        <p><?php echo $_SESSION['username'] ?></p>
        <div class="wrapper"></div>
        <script src="scripts/loading.js"></script>
        <script>
            $("#body").css('display', 'flex');
            setTimeout(function () {
                window.location.href = "./sap/Dashboard/dashboard.php";
            }, 1000);
        </script>
        <?php
        $alreadyLogged = false;
    }
}

load();

if ((isset($_POST['username']) && isset($_POST['password']))) {
    include("libs/mysql.inc");
    $conn = connect();
    $user = mysqli_escape_string($conn, $_POST['username']);
    $pass = hash("sha256", mysqli_escape_string($conn, $_POST['password']));
    $res = $conn->query("SELECT utenti.id, utenti.username, assegnazionePrivilegi.previlegeID FROM utenti INNER JOIN assegnazionePrivilegi ON utenti.id = assegnazionePrivilegi.userID WHERE utenti.username = '$user' AND utenti.password = '$pass';");
    if ($res->num_rows == 0) {
        $_SESSION['errored'] = "Errore nel login";
        echo "<script>window.location.href = './index.php';</script>";
        die("wrong login");
    }

    $username = "";
    $userid = 0;
    $privilegi = array();
    while ($row = $res->fetch_row()) {
        $username = $row[1];
        $userid = $row[0];
        array_push($privilegi, $row[2]);
    }

    $_SESSION['userID'] = $userid;
    $_SESSION['privilegi'] = $privilegi;
    $_SESSION['username'] = $username;
    load();
} else if ($alreadyLogged) {

    ?>
    <div class="page">
        <div class="container">
            <div class="left">
                <div class="login">Login</div>
                <div class="eula">Per proseguire inserire le credenziali di un operatore<br/><b
                            style="color: #F00;"><?php echo $_SESSION['errored'];
                        $_SESSION['errored'] = ""; ?></b></div>
            </div>
            <div class="right">
                <svg viewBox="0 0 320 300">
                    <defs>
                        <linearGradient
                                inkscape:collect="always"
                                id="linearGradient"
                                x1="13"
                                y1="193.49992"
                                x2="307"
                                y2="193.49992"
                                gradientUnits="userSpaceOnUse">
                            <stop
                                    style="stop-color:#ffee00;"
                                    offset="0"
                                    id="stop876"/>
                            <stop
                                    style="stop-color:#0000ff;"
                                    offset="1"
                                    id="stop878"/>
                        </linearGradient>
                    </defs>
                    <path d="m 40,120.00016 239.99984,-3.2e-4 c 0,0 24.99263,0.79932 25.00016,35.00016 0.008,34.20084 -25.00016,35 -25.00016,35 h -239.99984 c 0,-0.0205 -25,4.01348 -25,38.5 0,34.48652 25,38.5 25,38.5 h 215 c 0,0 20,-0.99604 20,-25 0,-24.00396 -20,-25 -20,-25 h -190 c 0,0 -20,1.71033 -20,25 0,24.00396 20,25 20,25 h 168.57143"></path>
                </svg>
                <div class="form">
                    <form action="index.php" method="post">
                        <label for="email">Username</label>
                        <input class="input" type="text" id="username" name="username">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password">
                        <input type="submit" class="submit input" id="submit" value="Login">
                    </form>
                    <input type="button" class="submit input" id="sub" value="KeyRing login" onclick="window.location.href = './keyring.php'">
                </div>
            </div>
        </div>
    </div>
    <script src="scripts/home.js"></script>
    <?php
}
?>

</body>



</html>