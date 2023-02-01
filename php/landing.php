<?php
ob_start();
session_start();
$html = '';
$debug = '';
require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$_SESSION['rest'] = filter_input(INPUT_GET, 'rest_id', FILTER_SANITIZE_NUMBER_INT);
$_SESSION['mesa'] = filter_input(INPUT_GET, 'mesa', FILTER_SANITIZE_NUMBER_INT);

if (isset($_SESSION['rest'])) {
    $pdo = connectDB($db);
    $sql = "SELECT * FROM restaurante WHERE id = :REST_ID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
    $stmt->execute();
    $restaurante = $stmt->fetch(); 
    $_SESSION['restNome'] = $restaurante['nome'];
}

//verificar se a mesa está no restaurante
$sql = "SELECT id FROM mesa WHERE numero = :numero AND Restaurante_id = :REST_ID LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":numero", $_SESSION['mesa'], PDO::PARAM_INT);
$stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() <= 0) {
    $notification = "Mesa não existe!";
    $errors = true;
}

//Adicionar ao array cart
$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
$produtoQuantidade = filter_input(INPUT_POST, 'quant', FILTER_SANITIZE_NUMBER_INT);
if ($id) {
    $sql = "SELECT * FROM produto WHERE id = :ID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":ID", $id, PDO::PARAM_INT);
    $stmt->execute();
    $prod = $stmt->fetch();
    $newProduto = [
        'id' => $prod['id'],
        'quant' => $produtoQuantidade,
        'stock' => $prod['stock'],
        'nome' => $prod['nome'],
        'preco' => $prod['preco'],
        'img' => $prod['img']
    ];


    function includesItem($array, $item)
    {
        foreach ($array as $arrayItem) {
            if ($arrayItem['id'] === $item['id']) {
                return true;
            }
        }
        return false;
    }
    if (!includesItem($_SESSION['cart'], $newProduto)) {
        array_push($_SESSION['cart'], $newProduto);
    }
}

$deleteItem = filter_input(INPUT_POST, 'deleteItem', FILTER_SANITIZE_NUMBER_INT);
if ($deleteItem) {

    foreach ($_SESSION['cart'] as $key => $produto) {
        if ($produto['id'] == $deleteItem) {
            unset($_SESSION['cart'][$key]);
        }
    }
}


if (isset($_GET['category'])) {
    $_SESSION['category'] = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_NUMBER_INT);
} else { // 1º a ser carregado
    $pdo = connectDB($db);
    $sql = "SELECT categoria.id FROM categoria WHERE Restaurante_id = :REST_ID LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
    $stmt->execute();

    $rowCategoria = $stmt->fetch();
    $_SESSION['category'] = $rowCategoria['id'];
}




//Texto/Imagens Restaurante
$pdo = connectDB($db);
$sql = "SELECT * FROM restaurante WHERE id = :REST_ID";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
$stmt->execute();

$rowRestaurante = $stmt->fetch();

$arrayRest = array(
    'nome' => $rowRestaurante['nome'],
    'img' => $rowRestaurante['img'],
    'header' => $rowRestaurante['header'],
    'desc' => $rowRestaurante['desc'],
    'status' => $rowRestaurante['status']
);

if (isset($_SESSION['uid'])) {
    $pdo = connectDB($db);
    $sql = "SELECT * FROM `cliente` WHERE `id` = :id";
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', $_SESSION['uid']);
    $result = $stm->execute();
    if ($result) {
        $cliente = $stm->fetch();
    }
}

//remover items do cart se não forem do restaurante selecionado
if ($_SESSION['cart']) {
    // print_r("entrou");
    // $sql = "SELECT p.id
    // FROM produto p
    // JOIN categoria c ON p.Categoria_id = c.id
    // JOIN restaurante r ON c.Restaurante_id = r.id
    // WHERE r.id = :REST_ID";
    // $stmt = $pdo->prepare($sql);
    // $stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
    // $stmt->execute();
    // $todosProdutos = array();
    // while ($row = $stmt->fetch()) {
    //     array_push($todosProdutos, $row[0]);
    // }



    // foreach ($_SESSION['cart'] as $produtoCart) {        

    //     if(array_search($produtoCart['id'], $todosProdutos)){
    //         print_r($produtoCart); print_r("\n");
    //         print_r($todosProdutos); print_r("\n");
    //     }else{
    //         $_SESSION['cart'] = array();
    //         return;
    //     }        
    // }
}


?>



<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title><?= $arrayRest['nome'] ?></title>
    <link rel="icon" href="<?= RESTIMG_WEB_PATH ?><?= $arrayRest['img'] != NULL ? $arrayRest['img'] : RESTIMG_DEFAULT ?>">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/variaveis.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/navbar.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/header.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/menu.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/modal.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/sidecart.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/landing/notification.css'>
    <script src="https://kit.fontawesome.com/c3634568e9.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
</head>

<?php

if ($_SESSION['rest']) {

    if ($arrayRest['status'] == 1) {
?>

        <body>


            <div class='navbar'>
                <div class='navbar-container'>
                    <svg onclick="openMenu();" class="navbar-icon" viewBox="0 0 24 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 12L3 12" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M3 6H21" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span class="navbar-name">
                        <?= $arrayRest['nome']; ?>
                    </span>
                    <?php if (isset($_SESSION['uid'])) { ?>
                        <a class="navbar-icon" href="./conta-cliente.php"><img src="<?= CLIENTEIMG_WEB_PATH ?><?= $cliente['img'] != NULL ? $cliente['img'] : CLIENTEIMG_DEFAULT ?>"></a>
                    <?php } else { ?>
                        <a href="cliente-register.php">
                            <svg class="navbar-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="user">
                                    <path id="Vector" d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path id="Vector_2" d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </g>
                            </svg>
                        </a>
                    <?php } ?>
                </div>
            </div>

            <div class='header'>

                <span class="header-title"><?php echo $arrayRest['desc']; ?></span>
                <img src="<?= RESTIMG_WEB_PATH ?><?= $arrayRest['header'] != NULL ? $arrayRest['header'] : RESTIMG_DEFAULT ?>" />
                <a class="header-circle" href="#menu">
                    <svg viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg" id="scroll">
                        <g id="arrow-down">
                            <path id="Vector" d="M16.5 6.875V26.125" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                            <path id="Vector_2" d="M26.125 16.5L16.5 26.125L6.875 16.5" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                    </svg>
                </a>
            </div>

            <div class="menu" id="menu">
                <div class="menu-container">
                    <div class="menu-circle-cart-pc" onclick="OpenSideCart()">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.96875 2.97827L3.96875 6.97827V20.9783C3.96875 21.5087 4.17946 22.0174 4.55454 22.3925C4.92961 22.7676 5.43832 22.9783 5.96875 22.9783H19.9688C20.4992 22.9783 21.0079 22.7676 21.383 22.3925C21.758 22.0174 21.9688 21.5087 21.9688 20.9783V6.97827L18.9688 2.97827H6.96875Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3.96875 6.97827H21.9688" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M16.9688 10.9783C16.9688 12.0391 16.5473 13.0566 15.7972 13.8067C15.047 14.5568 14.0296 14.9783 12.9688 14.9783C11.9079 14.9783 10.8905 14.5568 10.1403 13.8067C9.39018 13.0566 8.96875 12.0391 8.96875 10.9783" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>

                    </div>
                    <div class="menu-header">
                        <span>Menu</span>
                    </div>
                    <div class="menu-slider">
                        <?php
                        $pdo = connectDB($db);
                        $sql = "SELECT * FROM categoria WHERE Restaurante_id = :REST_ID AND categoria.status = 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
                        $stmt->execute();
                        while ($row = $stmt->fetch()) {
                            if ($row['nome']) { ?>
                                <button class="menu-chip" id="<?= $row['id'] ?>" onclick="ativarCor(this.id)" data-rest="<?= $_SESSION['rest'] ?>" type="button"><?php echo $row['nome'];  ?></button>

                        <?php }
                        }
                        ?>
                    </div>
                    <div class="menu-content">

                        <?php include './menu-content.php' ?>

                    </div>
                    <button class="menu-btn-mobile" onclick="OpenSideCartMobile()">Ver Pedido</button>
                </div>
            </div>

            <div class="popup-container">
                <?php
                $pdo = connectDB($db);
                $sql = "SELECT * from produto";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                while ($row3 = $stmt->fetch()) {
                    if ($row3['nome']) { ?>


                        <div class='overlay' id="<?= $row3['id']; ?>">
                            <div class='modal-adicionar'>
                                <div class="modal-adcionar-container">
                                    <div class='modal-iphone'></div>
                                    <div class='modal-adicionar-image'>
                                        <img src="<?= PRODUTOIMG_WEB_PATH ?><?= $row3['img'] != NULL ? $row3['img'] : PRODUTOIMG_DEFAULT ?>">
                                    </div>
                                    <div class='modal-adicionar-content'>
                                        <div class='modal-adicionar-reverse'>
                                            <!-- INGREDIENTES
                                            <div class='modal-adicionar-slider'>
                                                <button> <img src='https://source.unsplash.com/q54Oxq44MZs' /> </button>
                                                <button> <img src='https://source.unsplash.com/KhfwDYdhmN8' /> </button>
                                                <button> <img src='https://source.unsplash.com/LFpRvPxurnY' /> </button>
                                                <button> <img src='https://source.unsplash.com/PqiucwQwZQg' /> </button>
                                            </div> -->
                                            <div class='modal-adicionar-text'>
                                                <div class='modal-adicionar-text1'>
                                                    <span class='modal-adicionar-title'><?= $row3['nome']; ?></span>
                                                    <span class='modal-adicionar-price'><?= $row3['preco']; ?> €</span>
                                                </div>
                                                <div class='modal-adicionar-text2'>
                                                    <button class="menos" onclick="<?php $produtoQuantidade-- ?>">
                                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <g id="plus">
                                                                <path id="Vector_2" d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                            </g>
                                                        </svg>
                                                    </button>
                                                    <span id="counter" class="counts">1</span>
                                                    <button class="mais" onclick="<?php $produtoQuantidade++ ?>">
                                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <g id="plus">
                                                                <path id="Vector" d="M9 3.75V14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                                <path id="Vector_2" d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                            </g>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($row3['descricao']) { ?>
                                            <span class='modal-adicionar-desc'>
                                                <?= $row3['descricao']; ?>
                                            </span>
                                        <?php } ?>
                                        <button class="modal-adicionar-pc-btn" id="<?= $row3['id'] ?>" data-rest="<?= $_SESSION['rest'] ?>" data-mesa="<?= $_SESSION['mesa'] ?>" type="button" onclick="closeModal(<?= $row3['id']; ?>)">Adicionar</button>
                                    </div>
                                </div>
                            </div>
                            <div class='modal-adicionar-pc'>
                                <div class="modal-adicionar-pc-container">
                                    <div class='moda-adicionar-pc-img'>
                                        <img src="<?= PRODUTOIMG_WEB_PATH ?><?= $row3['img'] != NULL ? $row3['img'] : PRODUTOIMG_DEFAULT ?>">
                                    </div>
                                    <div class='moda-adicionar-pc-content'>

                                        <div class='moda-adicionar-pc-text'>
                                            <div class='moda-adicionar-pc-text1'>
                                                <span class='moda-adicionar-pc-title'><?= $row3['nome']; ?></span>
                                                <span class='moda-adicionar-pc-price'><?= $row3['preco']; ?> €</span>
                                            </div>
                                            <div class='moda-adicionar-pc-text2'>
                                                <button class="menos" onclick="<?php $produtoQuantidade-- ?>">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g id="plus">
                                                            <path id="Vector_2" d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </g>
                                                    </svg>
                                                </button>
                                                <span id="counter" class="counts">1</span>
                                                <button class="mais" onclick="<?php $produtoQuantidade++ ?>">
                                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <g id="plus">
                                                            <path id="Vector" d="M9 3.75V14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                            <path id="Vector_2" d="M3.75 9H14.25" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </g>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- INGREDIENTES
                                        <div class='modal-adicionar-pc-slider'>
                                            <button> <img src='https://source.unsplash.com/q54Oxq44MZs' /> </button>
                                            <button> <img src='https://source.unsplash.com/KhfwDYdhmN8' /> </button>
                                            <button> <img src='https://source.unsplash.com/LFpRvPxurnY' /> </button>
                                            <button> <img src='https://source.unsplash.com/PqiucwQwZQg' /> </button>
                                        </div> -->
                                        <div class='moda-adicionar-pc-content2'>

                                            <span class='modal-adicionar-pc-desc'>
                                                <?= $row3['descricao']; ?>
                                            </span>

                                            <button type="button" class='modal-adicionar-pc-btn' id="<?= $row3['id'] ?>" data-rest="<?= $_SESSION['rest'] ?>" onclick="OpenSideCart(<?= $row3['id']; ?>)">Adicionar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="overlay-fechar" onclick="FecharPopUp()"></div>
                        </div>


                <?php
                    }
                }
                ?>
            </div>

            <div class="sidecart">
                <?php include './sidecart.php' ?>
            </div>

            <div class="landing-noti" id="noti">
                <div class="landing-noti-container">
                    <span class="landing-noti-text">Pedido Feito!</span>
                </div>
            </div>

            <script>
                function ativarCor(id) {
                    const navLinks = document.querySelectorAll('.menu-chip');
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                    });

                    document.getElementById(id).classList.add("active");
                }
            </script>
            <script src="../js/landing.js"></script>

        </body>
<?php

    } else {
        include 'landing-fechado.php';
    }
} else {
    include 'landing-noRestaurante.php';
}

?>


</html>

<?php include './ativarNotification.php'; ?>