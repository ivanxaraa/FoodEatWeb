<?php

session_start();

require_once '../config.php';
require_once '../core.php';

function slugify($text = '')
{
    if ($text != '') {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        return $text;
    }
    return FALSE;
}

$filename = '';

$pdo = connectDB($db);
$sql = "SELECT Avatar FROM user WHERE ID = :REST_ID;";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
$stmt->execute();

$filename = $stmt->fetch();

if ($filename['img']) {

    $filepath = PRODUTOIMG_PATH . $filename['img'];

    if (is_file($filepath)) {
        unlink($filepath);

        $pdo = connectDB($db);
        $sql = "UPDATE user
                SET Avatar = 'default.png'
                WHERE ID = :REST_ID;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
        $stmt->execute();
    }
}


header('Location: ../dashboard/dashboard.php?m=dashboard&a=settings');
exit();