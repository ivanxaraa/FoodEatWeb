<?php
session_start();
/*

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
} 

*/

define('DESC', 'Aplicação Web');
$html = '';
$debug = '';

//ficheiros
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
//

require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if ($action == 'update') {

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $slogan = filter_input(INPUT_POST, 'slogan', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tel = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    $img = filter_input(INPUT_POST, 'fileToUpload', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    print_r("teste2 -> " . $img);
    //verificar se a img é diferente
    if ($img != '') {        
        $sql = "SELECT img FROM restaurante WHERE id = :REST_ID;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
        $stmt->execute();

        $filename = $stmt->fetch();
        $filepath = RESTIMG_PATH . $filename['img'];        
        if (is_file($filepath)) {
            //unlink($filepath);
        }

        //adicionar nova imagem
        $upload_name = "restaurante_img" . rand(9, 99999);        
        $upload_extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));        
        $upload_type = $_FILES["fileToUpload"]["type"];        
        $upload_tmp_name = $_FILES["fileToUpload"]["tmp_name"];        
        $upload_error = $_FILES["fileToUpload"]["error"];        
        $upload_size = $_FILES["fileToUpload"]["size"];        
        $filename = RESTIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
        if ($upload_name != "") {
            $dbfilename = slugify($upload_name) . '.' . $upload_extension;
        }

        if (!$errors) {

            if (is_file($filename) || is_dir($filename)) {
                $debug .= "File already exists on server: " . $filename . "\n";
                $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
            } else {
                if (@move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $filename)) {
                    $debug .= "New file uploaded: " . $filename . "\n";
                    $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';
                } else {
                    $debug .= "Error: " . error_get_last() . "\n";
                    die();
                }
            }
        }

        $sql = "UPDATE restaurante SET         
        `img`= :img          
        WHERE `id`=:id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(":img", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);        
        $stm->bindValue(':id', $_SESSION['uid']);
        $result = $stm->execute();
    }    
    if ($username != '') {
        $sql = "UPDATE restaurante SET 
        `nome` = :nome,
        `desc` = :slogan,
        `telefone` = :tel,
        `username` = :username,
        `email` = :email,
        `password` = :password,
        `status`= :status          
        WHERE `id`=:id";

        $stm = $pdo->prepare($sql);
        $stm->bindValue(':nome', $nome);
        $stm->bindValue(':slogan', $slogan);
        $stm->bindValue(':tel', $tel, PDO::PARAM_INT);
        $stm->bindValue(':username', $username);
        $stm->bindValue(':email', $email);
        $stm->bindValue(':password', $password);
        $stm->bindValue(':status', $status != null ? $status : "0");             
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
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard-settings.css'>
</head>

<body>

    <?php include('sidebar.php'); ?>


    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <div class="main-header">
                        <span class="main-title">As suas categorias</span>
                        <span class="main-subtitle">
                        </span>
                    </div>
                    <div class="main-content">


                        <form action="?" method="POST" enctype="multipart/form-data">
                            <div class="settings-fundo">

                                <?php

                                $pdo = connectDB($db);
                                $sql = "SELECT * FROM `restaurante` WHERE `id` = :id";

                                $stm = $pdo->prepare($sql);
                                $stm->bindValue(':id', $_SESSION['uid']);
                                $result = $stm->execute();

                                if ($result) {
                                    $row = $stm->fetch();
                                }  ?>
                                <div class="settings-imagens">
                                    <label class="settings-perfil" style="background-image: url('<?= RESTIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : RESTIMG_DEFAULT ?>');">
                                        <input type="file" name="fileToUpload" value="fileToUpload">
                                    </label>
                                    <laebl class="settings-header">
                                        <input type="file">
                                    </laebl>
                                </div>

                                <div class="settings-content">

                                    <div class="global-alterardados-header-space" style="align-items: start;">
                                        <div class="global-alterardados-header" style="width:100%">
                                            <img src="<?= RESTIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : RESTIMG_DEFAULT ?>">
                                            <span><?php echo $row['username'] ?><a id="alterardados-id">#<?php echo $row['id'] ?></a></span>
                                        </div>
                                        <label class="switch">
                                            <input value="1" name="status" type="checkbox" <?= $row['status'] == 1 ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>


                                    <div class="settings-inputs">

                                        <div class="global-span-input-box">
                                            <span>Nome</span>
                                            <input class="global-input" type="text" name="nome" id="nome" placeholder="Nome Restaurante" value="<?php echo $row['nome'] ?>">
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Slogan</span>
                                            <input class="global-input" type="text" name="slogan" id="slogan" placeholder="Slogan Restaurante" value="<?php echo $row['desc'] ?>">
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Telefone</span>
                                            <input class="global-input" type="number" name="telefone" id="telefone" placeholder="Telefone" value="<?php echo $row['telefone'] ?>">
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Username</span>
                                            <input class="global-input" type="text" name="username" id="username" placeholder="Username" value="<?php echo $row['username'] ?>">
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Email</span>
                                            <input class="global-input" type="email" name="email" id="email" placeholder="Email" value="<?php echo $row['email'] ?>">
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Password</span>
                                            <input class="global-input" type="password" name="password" id="password" placeholder="Password" value="<?php echo $row['password'] ?>">
                                        </div>

                                    </div>


                                    <div class="global-alterardados-buttons">
                                        <button class="global-confirmar-button" name="action" value="update">Guardar Alterações</button>
                                    </div>

                                </div>
                        </form>
                    </div>


                </div>
            </div>
        </div>


        <div class="all-main-right">

            <div class="menu-adiconar-categoria">
                <span class="main-title">Adicionar Categoria
                    <button onclick="closeSec()">
                        <svg class="categoria-alterar-title-icon" id="icon-rotate" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </span>
                <form action="?" method="POST">
                    <div class="global-alterardados-content" id="content">
                        <div class="global-alterardados-content-container">

                            <div class="global-alterardados-content-boxes">
                                <div class="global-span-input-box">
                                    <span>Nome Categoria</span>
                                    <input class="global-input" placeholder="Nome Categoria" name="nomecat" id="input-password">
                                </div>
                                <div class="global-alterar-box-status">
                                    <span>Status</span>
                                    <input class="clientes-status" value="1" name="status" type="checkbox">
                                </div>
                            </div>
                            <div class="global-alterardados-buttons">
                                <button class="global-confirmar-button" type="submit" name="addCategoriaPOST" value="addCategoriaPOST">Adicionar</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

        </div>

    </div>

    </div>


    <script src="../js/"></script>
</body>

</html>