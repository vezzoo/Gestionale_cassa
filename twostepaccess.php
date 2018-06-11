<?php
/**
 * Created by PhpStorm.
 * User: stefano
 * Date: 4/25/18
 * Time: 2:30 PM
 */
session_start();
include("libs/mysql.inc");
$conn = connect();
if (isset($_POST['username']) && isset($_POST['psw']) && isset($_POST['sessionid'])) {
    $user = mysqli_escape_string($conn, $_POST['username']);
    $psw = mysqli_escape_string($conn, $_POST['psw']);
    $sid = mysqli_escape_string($conn, $_POST['sessionid']);
    $phpsid = session_id();

    $conn->query("UPDATE keyring_sessions SET userID = CASE WHEN (select id from (select (
                                                               select randomKey from keyring_sessions
                                                               where sessionID = '$sid' AND logged = 0 AND validUntil > UNIX_TIMESTAMP())
  as C from keyring_sessions) as rnd JOIN utenti ON SHA2(CONCAT(utenti.password, C), 256) = '$psw' WHERE username = '$user' AND new = 0
) IS NOT NULL THEN (select id from (select (
                                                               select randomKey from keyring_sessions
                                                               where sessionID = '$sid' AND logged = 0 AND validUntil > UNIX_TIMESTAMP())
  as C from keyring_sessions) as rnd JOIN utenti ON SHA2(CONCAT(utenti.password, C), 256) = '$psw' WHERE username = '$user' AND new = 0
) ELSE 'a' END, logged = 1, sessid = '$phpsid'");
} else if(isset($_POST['sessionid']) && isset($_POST['check'])){
    $sid = mysqli_escape_string($conn, $_POST['sessionid']);
    $r = $conn->query("SELECT * FROM keyring_sessions WHERE logged = 1 AND sessionID = '$sid'");
    echo $r->fetch_row() > 0 ? "true" : "false";
} else if(isset($_POST['sessionid']) && isset($_POST['login'])){
    $sid = mysqli_escape_string($conn, $_POST['sessionid']);
    $res = $conn->query("SELECT userID, used, sessid FROM keyring_sessions WHERE sessionID = '$sid' AND logged = 1");
    $res = $res->fetch_row();
    if($res[1] == 1){

        unlink(session_save_path() . "/sess_" . $res[2]);
        die("CORRUPTED");

    } else {
        $uid = $res[0];
        $re = $conn->query("SELECT utenti.id, utenti.username, assegnazionePrivilegi.previlegeID FROM utenti INNER JOIN assegnazionePrivilegi ON utenti.id = assegnazionePrivilegi.userID WHERE utenti.id = '$uid'");

        $username = "";
        $userid = 0;
        $privilegi = array();
        while ($row = $re->fetch_row()) {
            $username = $row[1];
            $userid = $row[0];
            array_push($privilegi, $row[2]);
        }

        $_SESSION['userID'] = $userid;
        $_SESSION['privilegi'] = $privilegi;
        $_SESSION['username'] = $username;
        $re = $conn->query("UPDATE keyring_sessions SET used = 1 WHERE sessionID = '$sid' AND logged = 1");
        die("DONE");
    }
}