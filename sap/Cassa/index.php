<?php
session_start();
$_SESSION['errored'] = "Non è permessa la navigazione tra le directory";
echo "<script>window.location.href = '../index.php'</script>";
die("");