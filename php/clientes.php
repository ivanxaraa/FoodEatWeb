<?php

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';

define('DESC', 'Aplicação Web');
$html = '';
$debug = '';
$alterarDadosHtml = '';


require_once './config.php';
require_once './core.php';



$pdo = connectDB($db);

//NÃO FAZ SENTIDO O RESTAURANTE ALTERAR OS DADOS DO CLIENTE

// $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
// if ($action == 'update') {

//     $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
//     $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
//     $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
//     $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
    

//     if ($status !== false) {
//         $sql = "UPDATE `cliente` SET `status`=:STATU WHERE `id`=:id";
//         $stm = $pdo->prepare($sql);
//         $stm->bindValue(':STATU', $status != null ? $status : "0");
//         $stm->bindValue(':id', $id);
//         $result = $stm->execute();
//     }

//     if ($username != "" && $email != "") {

//         $sql = "UPDATE `cliente` SET
//         `username`= :username,
//         `email`= :email
//         WHERE `id`=:id";

//         $stm = $pdo->prepare($sql);
//         $stm->bindValue(':username', $username);
//         $stm->bindValue(':email', $email);
//         $stm->bindValue(':id', $id);
//         $result = $stm->execute();

//         $notification = MSG_SUCESSO;
//         $notimsg = 'Cliente' . MSG_EDITADO;
//     }

//     $dbfilename = null;
//     $upload_name = strtolower($_FILES["fotoProduto"]["name"], PATHINFO_FILENAME);

//     $notification = MSG_SUCESSO;
//     $notimsg = 'Cliente' . MSG_EDITADO;

//     //GET DATA IMAGEM PERFIL
//     if ($upload_name != "") {
//         $upload_name = "perfil-" . $id;
//         $upload_extension = strtolower(pathinfo($_FILES["fotoProduto"]["name"], PATHINFO_EXTENSION));
//         $upload_type = $_FILES["fotoProduto"]["type"];
//         $upload_tmp_name = $_FILES["fotoProduto"]["tmp_name"];
//         $upload_error = $_FILES["fotoProduto"]["error"];
//         $upload_size = $_FILES["fotoProduto"]["size"];
//         $filename = CLIENTEIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
//         $dbfilename = slugify($upload_name) . '.' . $upload_extension;

//         if (is_file($filename) || is_dir($filename)) {
//             $debug .= "File already exists on server: " . $filename . "\n";
//             $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
//         } else {
//             if (@move_uploaded_file($_FILES["fotoProduto"]["tmp_name"], $filename)) {
//                 $debug .= "New file uploaded: " . $filename . "\n";
//                 $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';

//                 //remover imagem de perfil antiga
//                 $sql = "SELECT img FROM cliente WHERE id = :id;";
//                 $stmt = $pdo->prepare($sql);
//                 $stmt->bindValue(':id', $id);
//                 $stmt->execute();
//                 $filename = $stmt->fetch();

//                 $filepath = CLIENTEIMG_PATH . $filename['img'];
//                 if (is_file($filepath)) {
//                     unlink($filepath);
//                 }
//                 //

//             } else {
//                 $debug .= "Error: " . error_get_last() . "\n";
//                 die();
//             }
//         }

//         $sql = "UPDATE cliente SET
//         `img`= :IMG_PROD
//         WHERE `id` = :id";

//         $stm = $pdo->prepare($sql);
//         $stm->bindValue(":IMG_PROD", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);
//         $stm->bindValue(':id', $id);
//         $result = $stm->execute();
//     }
    
// }
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Clientes</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard-cliente/dashboard-cliente.css'>
    <link rel='stylesheet' href='../css/dashboard-cliente/dashboard-cliente-alterardados.css'>
</head>

<body>


    <?php include('sidebar.php'); ?>



    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <span class="main-title">Clientes</span>
                    <div class="main-content">

                        <?php

                        //por restaurante
                        $sql = "SELECT DISTINCT c.* FROM cliente as c
                        JOIN pedido ON Cliente_id = c.id
                        JOIN mesa ON mesa.id = pedido.Mesa_id
                        WHERE mesa.Restaurante_id = :REST_ID";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                        $stmt->execute();
                        while ($cliente = $stmt->fetch()) { ?>

                            <button class="cliente-databox">

                                <div class="cliente-databox-click" id="noMoving">
                                    <div class="cliente-data-id"> <?php echo $cliente['id'];  ?> </div>
                                    <div class="cliente-data-img"><img src="<?= CLIENTEIMG_WEB_PATH ?><?= $cliente['img'] != NULL ? $cliente['img'] : CLIENTEIMG_DEFAULT ?>"></div>
                                    <div class="cliente-data-username"> <?php echo $cliente['username'];  ?> </div>
                                    <div class="cliente-data-email"> <?php echo $cliente['email'];  ?> </div>
                                    <?php

                                    if ($cliente['status'] == 1) {
                                    ?> <div class="global-data-status"></div> <?php
                                                                            } else {
                                                                                ?> <div class="global-data-status" style="background-color: #0e0e0e;"></div> <?php
                                                                                                                                                            }

                                                                                                                                                                ?>

                                    
                                    <!-- <div class="cliente-edit-container">
                                        <a class="cliente-edit" href="clientes.php?action=edit&id=<?php echo $cliente['id'];  ?>">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                        <a href="./crud/delete.php?id=<?php echo $cliente['id'];  ?>" class="cliente-apagar">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 6H5H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </div> -->
                                </div>
                            </button>

                        <?php }
                        ?>

                    </div>
                </div>
            </div>


            <!-- <div class="all-main-right">

                <div class="menu-ingredientes">
                    <?php
                    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    if ($action == 'edit') {
                        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
                        $sql = "SELECT * FROM `cliente` WHERE `id` = :id";
                        $stm = $pdo->prepare($sql);
                        $stm->bindValue(':id', (int)$id);
                        $result = $stm->execute();

                        if ($result) {
                            $cliente = $stm->fetch(); ?>

                            <span class="main-title">Alterar Dados</span>
                            <div class="global-alterardados-content">
                                <form action="?" method="POST">
                                    <div class="global-alterardados-content-container">
                                        <div class="global-alterardados-header-space" style="align-items: start;">
                                            <div class="global-alterardados-header" style="width:100%">
                                                <label class="produto-foto-perfil" id="overlay-images-round" style="background-image: url('<?= CLIENTEIMG_WEB_PATH ?><?= $cliente['img'] != NULL ? $cliente['img'] : CLIENTEIMG_DEFAULT ?>');">
                                                    <input type="file" name="fotoProduto" id="fotoProduto">
                                                </label>
                                                <span><?= $cliente['username'] ?><a id="alterardados-id">#<?= $cliente['id'] ?></a></span>
                                            </div>
                                            <label class="switch">
                                                <input value="1" name="status" type="checkbox" <?= $cliente['status'] == 1 ? 'checked' : ''; ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="global-alterardados-content-boxes">
                                            <input type="hidden" class="global-input" name="id" id="id" readonly value="<?= $cliente['id'] ?>">
                                            <div class="global-span-input-box">
                                                <span>Username</span>
                                                <input class="global-input" type="text" name="username" id="username" placeholder="username" value="<?= $cliente['username'] ?>">
                                            </div>
                                            <div class="global-span-input-box">
                                                <span>Email</span>
                                                <input class="global-input" type="email" name="email" id="email" placeholder="email" value="<?= $cliente['email'] ?>">
                                            </div>
                                            <div class="global-alterardados-buttons">
                                                <button class="global-confirmar-button" name="action" value="update">Confirmar</button>
                                                <a class="global-apagar-button" href="./crud/delete.php?id=<?= $cliente['id'] ?>">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                    <?php }
                    } ?>

                </div>

            </div> -->

        </div>

    </div>


    <script src="../js/clientes.js"></script>
</body>

</html>