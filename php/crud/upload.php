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

$debug = '';
$html = '';

$form_submited = filter_input(INPUT_POST, 'addProdutoPOST');

if ($form_submited) {
    $upload_name = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME));
    $debug .= "\t Uploaded name: " . $upload_name . "\n";
    

    $upload_extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    $debug .= "\t Uploaded extension: " . $upload_extension . "\n";

    $upload_type = $_FILES["fileToUpload"]["type"];
    $debug .= "\t Uploaded type: " . $upload_type . "\n";

    $upload_tmp_name = $_FILES["fileToUpload"]["tmp_name"];
    $debug .= "\t Uploaded tmp_name: " . $upload_tmp_name . "\n";

    $upload_error = $_FILES["fileToUpload"]["error"];
    $debug .= "\t Uploaded error: " . $upload_error . "\n";

    $upload_size = $_FILES["fileToUpload"]["size"];
    $debug .= "\t Uploaded size: " . $upload_size . "\n";

    $filename = PRODUTOIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
    $dbfilename = slugify($upload_name) . '.' . $upload_extension;

    if (is_file($filename) || is_dir($filename)) {
        $debug .= "File already exists on server: " . $filename . "\n";
        $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
    } else {
        if (@move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $filename)) {
            $debug .= "New file uploaded: " . $filename . "\n";
            $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';
            $pdo = connectDB($db);
            $sql = "UPDATE produto SET img = :IMG_PROD";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":IMG_PROD", $dbfilename, PDO::PARAM_STR);
            
            $stmt->execute();
            
            //header('Location: ../dashboard/dashboard.php?m=dashboard&a=settings');
            exit();
        } else {
            $debug .= "Error: " . error_get_last() . "\n";
            die();
        }
    }
} 


?>