<?php
session_start();

$html = '';
$debug = '';


require_once './config.php';
require_once './core.php';

//ler dados para os inputs
$pdo = connectDB($db);
$sql = "SELECT * FROM `cliente` WHERE `id` = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$result = $stm->execute();
if ($result) {
    $row = $stm->fetch();
}
//

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $sql = "UPDATE cliente SET 
        `username` = :username,             
        `email` = :email        
        WHERE `id`=:id";

    $stm = $pdo->prepare($sql);    
    $stm->bindValue(':username', $username);
    $stm->bindValue(':email', $email, PDO::PARAM_STR);        
    $stm->bindValue(':id', $_SESSION['uid']);
    $result = $stm->execute();

    if ($row['password'] != $password && $password != "") {
        $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE cliente SET             
        `password` = :password            
        WHERE `id`=:id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':password', $password_hash_db);
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
    <title>FoodEat - Conta</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard-settings.css'>
</head>

<body>




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







    <script src="../js/settings.js"></script>
</body>

</html>