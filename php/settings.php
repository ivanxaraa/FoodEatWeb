<?php
session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
} 
include 'verificarAdmin.php';
/*

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
} 

*/

$html = '';
$debug = '';
$errors = false;

//ficheiros
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
$filename = '';
$filename2 = '';
//


require_once './config.php';
require_once './core.php';

//ler dados para os inputs
$pdo = connectDB($db);
$sql = "SELECT * FROM `restaurante` WHERE `id` = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$result = $stm->execute();
if ($result) {
    $row = $stm->fetch();
}
//

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $slogan = filter_input(INPUT_POST, 'slogan', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tel = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);

    if(strlen($tel) != 9){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O telefone deve ter 9 digitos';
    }

    if(strlen($slogan) > 30){ 
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O slogan deve ter no maximo 30 digitos';
    }

   //duplicados
   $sql = "SELECT email FROM restaurante WHERE email = :EMAIL AND id != :ID LIMIT 1";
   $stmt = $pdo->prepare($sql);
   $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
   $stmt->bindValue(":ID", $_SESSION['uid'], PDO::PARAM_INT);
   $stmt->execute();
   if ($stmt->rowCount() > 0) {
       $errors = true;
       $notification = MSG_ERRO;
       $notimsg = 'O email que adicionou já existe';
   }

   $sql = "SELECT username FROM restaurante WHERE username = :USERNAME AND id != :ID LIMIT 1";
   $stmt = $pdo->prepare($sql);
   $stmt->bindValue(":USERNAME", $username, PDO::PARAM_STR);
   $stmt->bindValue(":ID", $_SESSION['uid'], PDO::PARAM_INT);
   $stmt->execute();
   if ($stmt->rowCount() > 0) {
       $errors = true;
       $notification = MSG_ERRO;
       $notimsg = 'O username que adicionou já existe';
   }
   // FIM duplicados

    if(!$errors){
        //verificar imagens
        $dbfilename = null;
        $dbfilename2 = null;
        $upload_name = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME));
        $upload_name2 = strtolower(pathinfo($_FILES["fileToUpload2"]["name"], PATHINFO_FILENAME));

        //

        //GET DATA IMAGEM PERFIL
        if ($upload_name != "") {

            $upload_name = "restaurante_img" . $_SESSION['uid'];
            $upload_extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
            $upload_type = $_FILES["fileToUpload"]["type"];
            $upload_tmp_name = $_FILES["fileToUpload"]["tmp_name"];
            $upload_error = $_FILES["fileToUpload"]["error"];
            $upload_size = $_FILES["fileToUpload"]["size"];
            $filename = RESTIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
            $dbfilename = slugify($upload_name) . '.' . $upload_extension;

            //IS FILE IMAGEM PERFIL
            if (is_file($filename) || is_dir($filename)) {
                $debug .= "File already exists on server: " . $filename . "\n";
                $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
            } else {
                if (@move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $filename)) {
                    $debug .= "New file uploaded: " . $filename . "\n";
                    $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';

                    //remover imagem de perfil antiga
                    $sql = "SELECT img FROM restaurante WHERE id = :id;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $_SESSION['uid']);
                    $stmt->execute();
                    $filename = $stmt->fetch();

                    $filepath = RESTIMG_PATH . $filename['img'];
                    if (is_file($filepath)) {
                        unlink($filepath);
                    }
                    //

                } else {
                    $debug .= "Error: " . error_get_last() . "\n";
                    die();
                }
            }

            $sql = "UPDATE restaurante SET
            `img`= :IMG_REST
            WHERE `id` = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(":IMG_REST", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();

        }

        //GET DATA IMAGEM HEADER
        if ($upload_name2 != "") {

            $upload_name2 = "restaurante_header" . $_SESSION['uid'];
            $upload_extension2 = strtolower(pathinfo($_FILES["fileToUpload2"]["name"], PATHINFO_EXTENSION));
            $upload_type2 = $_FILES["fileToUpload2"]["type"];
            $upload_tmp_name2 = $_FILES["fileToUpload2"]["tmp_name"];
            $upload_error2 = $_FILES["fileToUpload2"]["error"];
            $upload_size2 = $_FILES["fileToUpload2"]["size"];
            $filename2 = RESTIMG_PATH . slugify($upload_name2) . '.' . $upload_extension2;
            $dbfilename2 = slugify($upload_name2) . '.' . $upload_extension2;
            
            //IS FILE IMAGEM PERFIL
            if (is_file($filename2) || is_dir($filename2)) {
                $debug .= "File already exists on server: " . $filename2 . "\n";
                $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename2 . '</b></div>';
                
            } else {
                if (@move_uploaded_file($_FILES["fileToUpload2"]["tmp_name"], $filename2)) {
                    $debug .= "New file uploaded: " . $filename2 . "\n";
                    $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename2 . '</b></div>';

                    //remover imagem de perfil antiga
                    $sql = "SELECT header FROM restaurante WHERE id = :id;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $_SESSION['uid']);
                    $stmt->execute();
                    $filename2 = $stmt->fetch();

                    $filepath2 = RESTIMG_PATH . $filename2['header'];
                    if (is_file($filepath2)) {
                        unlink($filepath2);
                    }
                    //
                    print_r("dentro 2");
                } else {
                    $debug .= "Error: " . error_get_last() . "\n";
                    die();
                }
            }

            $sql = "UPDATE restaurante SET
            `header`= :HEADER_REST
            WHERE `id` = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(":HEADER_REST", $dbfilename2 != null ? $dbfilename2 : NULL, PDO::PARAM_STR);
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();
            print_r("dentro3");

        }

        //UPDATE TO DATABASE
        if ($username != '') {

            // //remover imagem de perfil antiga
            // $sql = "SELECT img FROM restaurante WHERE id = :REST_ID;";
            // $stmt = $pdo->prepare($sql);
            // $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
            // $stmt->execute();
            // $filename = $stmt->fetch();

            // $filepath = RESTIMG_PATH . $filename['img'];
            // if (is_file($filepath)) {
            //     unlink($filepath);
            // }
            // //

            // //remover imagem header antiga
            // $sql = "SELECT header FROM restaurante WHERE id = :REST_ID;";
            // $stmt = $pdo->prepare($sql);
            // $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
            // $stmt->execute();
            // $filename2 = $stmt->fetch();

            // $filepath2 = RESTIMG_PATH . $filename2['header'];
            // if (is_file($filepath2)) {
            //     unlink($filepath2);
            // }
            // //

            if ($row['password'] != $password && $password != "") {
                $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE restaurante SET             
                `password` = :password            
                WHERE `id`=:id";
                $stm = $pdo->prepare($sql);
                $stm->bindValue(':password', $password_hash_db);
                $stm->bindValue(':id', $_SESSION['uid']);
                $result = $stm->execute();
            }

            $sql = "UPDATE restaurante SET 
            `nome` = :nome,
            `desc` = :slogan,
            `telefone` = :tel,
            `username` = :username,
            `email` = :email,        
            `status`= :status    
            WHERE `id`=:id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(':nome', $nome);
            $stm->bindValue(':slogan', $slogan);
            $stm->bindValue(':tel', $tel, PDO::PARAM_INT);
            $stm->bindValue(':username', $username);
            $stm->bindValue(':email', $email);
            $stm->bindValue(':status', $status != null ? $status : "0");        
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();
        }
    }

    //fim update    
}


?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Settings</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard-settings.css'>
</head>

<body>


    <?php include('sidebar.php'); ?>


    <div class="main">

        <div class="main-double">
            <div class="all-main-left" id="settings-main-left">
                <div class="menu-categorias">
                    <div class="main-header">
                        <span class="main-title">Alterar Dados</span>
                        <span class="main-subtitle">
                        </span>
                    </div>
                    <div class="main-content">
                        <form action="?" method="POST" enctype="multipart/form-data">
                            <div class="settings-fundo">

                                <div class="settings-imagens">
                                    <label class="settings-perfil" id="overlay-images-flat" style="background-image: url('<?= RESTIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : RESTIMG_DEFAULT ?>');">
                                        <input type="file" name="fileToUpload" id="fileToUpload">
                                    </label>
                                    <label class="settings-header" id="overlay-images-flat" style="background-image: url('<?= RESTIMG_WEB_PATH ?><?= $row['header'] != NULL ? $row['header'] : RESTIMG_DEFAULT ?>');">
                                        <input type="file" name="fileToUpload2" id="fileToUpload2">
                                    </label>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="all-main-right" id="settings-main-right">

                <div class="menu-adiconar-categoria">
                    <span class="main-title">Outros
                        <button onclick="closeSec()">
                            <svg class="categoria-alterar-title-icon" id="icon-rotate" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </span>

                    <div class="global-alterardados-content" id="content">
                        <div class="global-alterardados-content-container">

                            <a class="sidebar-link" id="settings-link" href="../php/landing.php?rest_id=<?= $_SESSION['uid'] ?>">
                                <i class="fa-solid fa-eye"></i>
                                <span>Ver Website</span>
                            </a>

                        </div>
                    </div>


                </div>

            </div>
        </div>

    </div>

    <div class="notification" id="noti">
        <div class="notification-container">
            <div class="notification-left">
                <div class="notification-circle" style="background-color: <?= $notification == MSG_SUCESSO ? 'var(--corConfirmado)' : 'var(--corNegativo)' ?>;">
                    <?php if ($notification == MSG_SUCESSO) { ?>
                        <i class="fa-solid fa-check"></i> <?php } else { ?>
                        <i class="fa-solid fa-xmark"></i>
                    <?php } ?>
                </div>
            </div>
            <div class="notification-right">
                <span><?= $notification ?></span>
                <span><?= $notimsg ?></span>
            </div>

        </div>
        <div class="notification-bar" style="background-color: <?= $notification == MSG_SUCESSO ? 'var(--corConfirmado)' : 'var(--corNegativo)' ?>;"></div>
    </div>

    <script src="../js/settings.js"></script>
</body>

</html>

<?php include './ativarNotification.php'; ?>