<?php
session_start();
require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);
$debug = '';
$html = '';
function slugify($text = ''){
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


$pdo = connectDB($db);
$sql = "SELECT * FROM `cliente` WHERE `id` = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$result = $stm->execute();
if ($result) {
    $cliente = $stm->fetch();
}



$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($action == 'update') {

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //alterar username & email
    if ($username != "" && $email != "") {
        $sql = "UPDATE cliente SET 
        `username` = :username,
        `email` = :email         
        WHERE `id`=:id";

        $stm = $pdo->prepare($sql);
        $stm->bindValue(':username', $username);
        $stm->bindValue(':email', $email);
        $stm->bindValue(':id', $_SESSION['uid']);
        $result = $stm->execute();
    }

    //alterar password
    if ($cliente['password'] != $password && $password != "") {
        $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE cliente SET             
        `password` = :password            
        WHERE `id`=:id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':password', $password_hash_db);
        $stm->bindValue(':id', $_SESSION['uid']);
        $result = $stm->execute();
    }

    $dbfilename = null;
    $upload_name = strtolower(pathinfo($_FILES["fotoAvatar"]["name"], PATHINFO_FILENAME));
    //IMG
    if ($upload_name != "") {

        $upload_extension = strtolower(pathinfo($_FILES["fotoAvatar"]["name"], PATHINFO_EXTENSION));
        $upload_type = $_FILES["fotoAvatar"]["type"];
        $upload_tmp_name = $_FILES["fotoAvatar"]["tmp_name"];
        $upload_error = $_FILES["fotoAvatar"]["error"];
        $upload_size = $_FILES["fotoAvatar"]["size"];
        $filename = CLIENTEIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
        $dbfilename = slugify($upload_name) . '.' . $upload_extension;

        if (is_file($filename) || is_dir($filename)) {
            $debug .= "File already exists on server: " . $filename . "\n";
            $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
        } else {
            if (@move_uploaded_file($_FILES["fotoAvatar"]["tmp_name"], $filename)) {
                $debug .= "New file uploaded: " . $filename . "\n";
                $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';

                //remover imagem de perfil antiga
                $sql = "SELECT img FROM cliente WHERE id = :id;";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $_SESSION['uid']);
                $stmt->execute();
                $filename = $stmt->fetch();

                $filepath = CLIENTEIMG_PATH . $filename['img'];
                if (is_file($filepath)) {
                    unlink($filepath);
                }
                //

            } else {
                $debug .= "Error: " . error_get_last() . "\n";
                die();
            }
        }

        $sql = "UPDATE cliente SET
        `img`= :avatar
        WHERE `id` = :id";

        $stm = $pdo->prepare($sql);
        $stm->bindValue(":avatar", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);
        $stm->bindValue(':id', $_SESSION['uid']);
        $result = $stm->execute();
    }
}


?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Alterar Dados</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/variaveis.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/conta-cliente/conta-cliente.css'>
</head>

<body>

    <div class="conta-cliente">
        <div class="conta-cliente-container">
            <div class="conta-cliente-alterar">
                <span class="main-title">A sua conta</span>
                <div class="global-alterardados-content">
                    <form action="?" method="POST" enctype="multipart/form-data">
                        <div class="global-alterardados-content-container">
                            <div class="global-alterardados-header-space" style="align-items: start;">
                                <div class="global-alterardados-header" style="width:100%">
                                    <label class="produto-foto-perfil" id="overlay-images-round" style="background-image: url('<?= CLIENTEIMG_WEB_PATH ?><?= $cliente['img'] != NULL ? $cliente['img'] : CLIENTEIMG_DEFAULT ?>');">
                                        <input type="file" name="fotoAvatar" id="fotoAvatar">
                                    </label>
                                    <span><?= $cliente['username'] ?><a id="alterardados-id">#<?= $cliente['id'] ?></a></span>
                                </div>
                            </div>
                            <div class="global-alterardados-content-boxes">
                                <div class="global-span-input-box">
                                    <span>Username</span>
                                    <input type="text" class="global-input" placeholder="Username" name="username" value="<?= $cliente['username'] ?>">
                                </div>
                                <div class="global-span-input-box">
                                    <span>Email</span>
                                    <input type="email" class="global-input" placeholder="Email" name="email" value="<?= $cliente['email'] ?>">
                                </div>
                                <div class="global-span-input-box">
                                    <span>Password</span>
                                    <input type="password" class="global-input" placeholder="Password" name="password" value="<?= $cliente['password'] ?>">
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button type="submit" class="global-apagar-button" name="action" value="update">Confirmar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                    <div class="global-alterardados-buttons">
                        <a href="./logout.php" class="global-confirmar-button" name="action" value="terminar">Terminar Sessão</a>                        
                    </div>
                
            </div>
        </div>
    </div>

</body>

</html>