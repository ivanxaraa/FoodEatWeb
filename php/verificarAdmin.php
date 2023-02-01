<?php
if (!isset($_SESSION['uid'])) {
    session_start();
}
require_once './config.php';
require_once './core.php';

$arrayRest = array();

$pdo = connectDB($db);
$sql = "SELECT * FROM restaurante";
$stm = $pdo->query($sql);
while ($row = $stm->fetch()) {
    array_push($arrayRest, $row['email']);
}

if (in_array($_SESSION['email'], $arrayRest)) {
    return true;
}else{
    header('Location: login.php');
}

?>