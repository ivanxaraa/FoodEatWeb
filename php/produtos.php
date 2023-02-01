<?php
session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';

$html = '';
$errors = '';
$debug = '';

global $notification;
$notification = '';
$notimsg = '';
$alterarDadosHtml = null;
$errors = false;

require_once './config.php';
require_once './core.php';

$pdo = connectDB($db);


$notification = filter_input(INPUT_GET, 'deleted', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$notimsg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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


// erro porque nao le estes valores dentro do post
$cat_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$cat_nome = filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$addProdutoPOST = filter_input(INPUT_POST, 'addProdutoPOST');
if ($addProdutoPOST) {
    $cat_id = filter_input(INPUT_POST, 'idcat', FILTER_SANITIZE_NUMBER_INT);
    $cat_nome = filter_input(INPUT_POST, 'nomecat', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $prodnome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $prodpreco = filter_input(INPUT_POST, 'preco');
    $prodstock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);
    $proddesc = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $prodpreco = addDecimals($prodpreco);
    
    if($prodpreco >= 10000){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O valor maximo é 9999€';
    }

    if(strlen($prodstock) >= 5){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O stock maximo é 9999';
    }
    
    if(!$errors){
        //verificar imagem
        $dbfilename = null;
        $upload_name = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_FILENAME));
        //

        if ($upload_name != "") {
            $upload_name = "produto_img" . rand(9, 99999);
            $upload_extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
            $upload_type = $_FILES["fileToUpload"]["type"];
            $upload_tmp_name = $_FILES["fileToUpload"]["tmp_name"];
            $upload_error = $_FILES["fileToUpload"]["error"];
            $upload_size = $_FILES["fileToUpload"]["size"];
            $filename = PRODUTOIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
            $dbfilename = slugify($upload_name) . '.' . $upload_extension;
        }


        if ($prodnome != "" && $prodpreco != "" && $prodstock != "") {

            $sql = "SELECT id FROM produto WHERE nome = :NOMEPROD AND Categoria_id = :CAT_ID LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":NOMEPROD", $prodnome, PDO::PARAM_STR);
            if ($cat_id != null) {
                $stmt->bindValue(":CAT_ID", $cat_id, PDO::PARAM_INT);
            }
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $notification = MSG_ERRO;
                $notimsg = 'O produto' . MSG_ERRO_EXISTE;
                $errors = true;
            }


            if (!$errors) {

                if ($upload_name != "") {
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

                $pdo = connectDB($db);

                $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);
                $sql = "INSERT INTO produto(nome,preco,stock,descricao,status, Categoria_id, img) VALUES(:NOMEPROD, :PRECOPROD, :STOCKPROD, :DESCRICAOPROD, :STATU, $cat_id, :IMG_PROD)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(":NOMEPROD", $prodnome, PDO::PARAM_STR);
                $stmt->bindValue(":DESCRICAOPROD", $proddesc, PDO::PARAM_STR);
                $stmt->bindValue(":PRECOPROD", $prodpreco);
                $stmt->bindValue(":STOCKPROD", $prodstock, PDO::PARAM_INT);
                $stmt->bindValue(":STATU", $status != null ? $status : "0");
                $stmt->bindValue(":IMG_PROD", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);


                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $notification = MSG_SUCESSO;
                    $notimsg = 'Produto' . MSG_ADICIONADO;
                } else {
                    $notification = MSG_ERRO;
                    $notimsg = 'Erro ao inserir na Base de Dados';
                }
            }
        }
    }
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {

    $cat_id = filter_input(INPUT_POST, 'catid', FILTER_SANITIZE_NUMBER_INT);
    $idProduto = filter_input(INPUT_POST, 'idProduto', FILTER_SANITIZE_NUMBER_INT);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $preco = filter_input(INPUT_POST, 'preco');
    $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);        
    $preco = addDecimals($preco);    
    
    
    if($preco >= 10000){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O valor maximo é 9999€';
    }

    if(strlen($stock) >= 5){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O stock maximo é 9999';
    }

    if(!$errors){
        if ($nome && $preco && $stock != "") {

            $sql = "UPDATE produto SET 
            `nome` = :nome,        
            `preco`= :preco,
            `stock`= :stock,
            `descricao`= :descricao,
            `status`= :STATU
            WHERE `id` = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(':nome', $nome);
            $stm->bindValue(':preco', $preco);
            $stm->bindValue(':stock', $stock);
            $stm->bindValue(':descricao', $descricao);
            $stm->bindValue(':STATU', $status != null ? $status : "0");
            $stm->bindValue(':id', $idProduto);
            $result = $stm->execute();
        }

        $dbfilename = null;
        $upload_name = strtolower(pathinfo($_FILES["fotoProduto"]["name"], PATHINFO_FILENAME));

        $notification = MSG_SUCESSO;
        $notimsg = 'Produto' . MSG_EDITADO;

        //GET DATA IMAGEM PERFIL
        if ($upload_name != "") {
            $upload_name = "produto_img" . rand(9, 99999);
            $upload_extension = strtolower(pathinfo($_FILES["fotoProduto"]["name"], PATHINFO_EXTENSION));
            $upload_type = $_FILES["fotoProduto"]["type"];
            $upload_tmp_name = $_FILES["fotoProduto"]["tmp_name"];
            $upload_error = $_FILES["fotoProduto"]["error"];
            $upload_size = $_FILES["fotoProduto"]["size"];
            $filename = PRODUTOIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
            $dbfilename = slugify($upload_name) . '.' . $upload_extension;

            if (is_file($filename) || is_dir($filename)) {
                $debug .= "File already exists on server: " . $filename . "\n";
                $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
            } else {
                if (@move_uploaded_file($_FILES["fotoProduto"]["tmp_name"], $filename)) {
                    $debug .= "New file uploaded: " . $filename . "\n";
                    $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';

                    //remover imagem de perfil antiga
                    $sql = "SELECT img FROM produto WHERE id = :id;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $idProduto);
                    $stmt->execute();
                    $filename = $stmt->fetch();

                    $filepath = PRODUTOIMG_PATH . $filename['img'];
                    if (is_file($filepath)) {
                        unlink($filepath);
                    }
                    //

                } else {
                    $debug .= "Error: " . error_get_last() . "\n";
                    die();
                }
            }

            $sql = "UPDATE produto SET
            `img`= :IMG_PROD
            WHERE `id` = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(":IMG_PROD", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);
            $stm->bindValue(':id', $idProduto);
            $result = $stm->execute();
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Produtos</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>


    <link rel='stylesheet' href='./css/variaveis.css'>
    <link rel='stylesheet' href='./css/sidebar.css'>
    <link rel='stylesheet' href='./css/dashboard/dashboard.css'>
    <link rel='stylesheet' href='./css/dashboard/dashboard-pedido.css'>
    <link rel='stylesheet' href='../css/dashboard-menu/dashboard-menu.css'>
    <link rel='stylesheet' href='../css/dashboard-menu/dashboard-produtos.css'>
    <link rel='stylesheet' href='../css/dashboard-cliente/dashboard-cliente.css'>

</head>

<body>

    <?php include('sidebar.php'); ?>

    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <div class="main-header">                        
                        <div class="menu-title-btw">
                            <a href="./dashboard-menu.php?action=edit&id=<?php echo $cat_id; ?>" class="main-title"><?php echo $cat_nome; ?><span>> Produtos</span></a>
                            <div class="title-searchbar-container">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="searchBar" onkeyup="searchFunction()" placeholder="Procure aqui...">
                            </div>
                        </div>
                        <span class="main-subtitle">
                        </span>
                    </div>
                    <div class="main-content" id="main-content">
                        <?php

                            $pdo = connectDB($db);
                            $cat_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
                            $sql = "SELECT * FROM produto WHERE Categoria_id = :CAT_ID ORDER BY status DESC, preco";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(":CAT_ID", $cat_id, PDO::PARAM_INT);
                            $stmt->execute();
                            while ($row = $stmt->fetch()) {
                                if ($row['nome']) { ?>

                                    <div class="cliente-databox">
                                        <div class="categoria-databox-click">

                                            <div class="categoria-databox-button">
                                                <div class="cliente-databox-left">
                                                    <?php
                                                    if ($row['status'] == 1) {
                                                    ?> <div class="global-data-status"></div> <?php
                                                                                            } else {
                                                                                                ?> <div class="global-data-status" style="background-color: #0e0e0e;"></div> <?php
                                                                                                                                                                            }
                                                                                                                                                                                ?>
                                                    <div class="produto-data-img"><img src="<?= PRODUTOIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : PRODUTOIMG_DEFAULT ?>"></div>
                                                    <div class="produto-data-nome"><?php echo $row['nome'];  ?></div>
                                                    <div class="produto-data-preco"><?php echo $row['preco'];  ?> €</div>
                                                </div>
                                                <div class="cliente-databox-right">
                                                    <div class="categoria-data-quant">
                                                        <?php echo $row['stock'];  ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package">
                                                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="cliente-edit-container">
                                                <a class="cliente-edit" href="produtos.php?id=<?= $cat_id ?>&nome=<?= $cat_nome ?>&produto=<?= $row['id'] ?>&action=edit">
                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <a href="./crud/delete-produtos.php?id=<?php echo $row['id'];  ?>&id_cat=<?php echo $cat_id;  ?>&nome=<?php echo $cat_nome;  ?>" class="cliente-apagar">
                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3 6H5H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                        <?php }
                        }
                        ?>

                    </div>
                </div>
            </div>


            <div class="all-main-right" id="gap30">

                <?php

                //EDITAR
                $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                if ($action == 'edit') {

                    $prod_id = filter_input(INPUT_GET, 'produto', FILTER_SANITIZE_NUMBER_INT);

                    $sql = "SELECT * FROM `produto` WHERE `id` = :id AND Categoria_id = :CAT_ID";

                    $stm = $pdo->prepare($sql);
                    $stm->bindValue(":CAT_ID", $cat_id, PDO::PARAM_INT);
                    $stm->bindValue(':id', (int)$prod_id);
                    $result = $stm->execute();

                    if ($result) {
                        $row = $stm->fetch(); ?>
                        <div class="menu-alterar-categoria">
                            <span class="main-title">Alterar Dados</span>
                            <div class="global-alterardados-content">
                                <form action="?id=<?= $cat_id ?>&nome=<?= $cat_nome ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" class="global-input" name="id" id="catid" readonly value="<?= $cat_id ?>">
                                    <input type="hidden" class="global-input" name="idProduto" id="idProduto" readonly value="<?= $row['id'] ?>">
                                    <div class="global-alterardados-content-container">
                                        <div class="global-alterardados-header-space" style="align-items: start;">
                                            <div class="global-alterardados-header" style="width:100%">
                                                <label class="produto-foto-perfil" id="overlay-images-round" style="background-image: url('<?= PRODUTOIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : PRODUTOIMG_DEFAULT ?>');">
                                                    <input type="file" name="fotoProduto" id="fotoProduto">
                                                </label>
                                                <span><?= $row['nome'] ?><a id="alterardados-id">#<?= $row['id'] ?></a></span>
                                            </div>
                                            <label class="switch">
                                                <input value="1" name="status" type="checkbox" <?= $row['status'] == 1 ? 'checked' : ''; ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                        <div class="global-alterardados-content-boxes">
                                            <div class="global-span-input-box">
                                                <span>Nome</span>
                                                <input type="text" class="global-input" placeholder="Nome Produto" name="nome" value="<?= $row['nome'] ?>">
                                            </div>
                                            <div class="global-span-input-box-double">
                                                <div class="global-span-input-box">
                                                    <span>Preço</span>
                                                    <input type="number" step=".01" class="global-input" placeholder="Preço" name="preco" value="<?= $row['preco'] ?>">
                                                </div>
                                                <div class="global-span-input-box">
                                                    <span>Stock</span>
                                                    <input type="number" class="global-input" placeholder="Stock" name="stock" value="<?= $row['stock'] ?>">
                                                </div>
                                            </div>
                                            <div class="global-span-input-box">
                                                <span>Descrição</span>
                                                <input type="text" class="global-input" placeholder="Descrição" name="descricao" value="<?= $row['descricao'] ?>">
                                            </div>
                                            <div class="global-alterardados-buttons">
                                                <button class="global-confirmar-button" name="action" value="update">Confirmar</button>
                                                <a class="global-apagar-button" href="./crud/delete.php?id=' . $row['id'] . '">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                <?php
                    }
                }

                // FIM EDIT
                ?>

                <div class="menu-adiconar-categoria">

                    <span class="main-title">Adicionar Produto</span>
                    <form action="?id=<?= $cat_id  ?>&nome<?= $cat_nome  ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="idcat" value="<?php echo $cat_id  ?>">
                        <input type="hidden" name="nomecat" value="<?php echo $cat_nome  ?>">
                        <div class="global-alterardados-content">
                            <div class="global-alterardados-content-container">

                                <div class="global-alterardados-content-boxes">
                                    <div class="global-span-input-box">
                                        <span>Imagem</span>
                                        <input type="file" class="global-input-image" name="fileToUpload" id="fileToUpload">
                                    </div>
                                    <div class="global-span-input-box">
                                        <span>Nome</span>
                                        <input type="text" class="global-input" placeholder="Nome Produto" name="nome" id="input-password" required>
                                    </div>
                                    <div class="global-span-input-box-double">
                                        <div class="global-span-input-box">
                                            <span>Preço</span>
                                            <input type="number" step=".01" class="global-input" placeholder="Preço" name="preco" id="input-password" required>
                                        </div>
                                        <div class="global-span-input-box">
                                            <span>Stock</span>
                                            <input type="number" class="global-input" placeholder="Stock" name="stock" id="input-password" required>
                                        </div>
                                    </div>
                                    <div class="global-span-input-box">
                                        <span>Descrição</span>
                                        <input type="text" class="global-input" placeholder="Descrição" name="descricao" id="input-password">
                                    </div>
                                    <div class="global-span-input-box">
                                        <div class="global-alterar-box-status">
                                            <span>Status</span>
                                            <label class="switch">
                                                <input class="toggle-switch" value="1" name="status" type="checkbox" <?= $row['status'] == 1 ? 'checked' : ''; ?>>
                                                <span class="slider round" id="slider-darkmode"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button class="global-confirmar-button" type="submit" name="addProdutoPOST" value="addProdutoPOST">Adicionar</button>
                                </div>
                            </div>
                        </div>
                    </form>

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

    <script>
        function searchFunction() {
            var input, filter, lista, produto, nome, i;
            input = document.getElementById('searchBar');
            filter = input.value.toUpperCase();
            lista = document.getElementById('main-content');
            produto = lista.getElementsByClassName('cliente-databox');

            for (i = 0; i < produto.length; i++) {
                nome = produto[i].getElementsByClassName('produto-data-nome')[0];
                if (nome.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    produto[i].style.display = "";
                } else {
                    produto[i].style.display = 'none';
                }
            }
        }
    </script>

    <script src="dashboard.js"></script>
</body>

</html>

<?php include './ativarNotification.php'; ?>