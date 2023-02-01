<?php

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';


$html = '';
$debug = '';
require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);

//Ler Vendas
$sql = "SELECT pedido.precototal FROM pedido
JOIN mesa on mesa.id = pedido.Mesa_id
WHERE mesa.Restaurante_id = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$stm->execute();
$vendas = 0.00;
while ($restaurante = $stm->fetch()) {
    $vendas = $vendas + $restaurante['precototal'];
}
//FIM Ler Vendas

//Ler Numero de Categorias
$sql = "SELECT COUNT(id) as numero FROM `categoria` WHERE `Restaurante_id` = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$result = $stm->execute();
if ($result) {
    $row2 = $stm->fetch();
}
// FIM Numero de Categorias 


//Ler Numero de Pedidos
$sql = "SELECT COUNT(pedido.id) as numero FROM `pedido`
JOIN mesa on mesa.id = pedido.Mesa_id
WHERE mesa.Restaurante_id = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(":id", $_SESSION['uid'], PDO::PARAM_INT);
$result = $stm->execute();
if ($result) {
    $pedido = $stm->fetch();
}
// FIM Numero de Pedidos 


//Ler Numero de Clientes
$sql = "SELECT DISTINCT COUNT(c.id) as numero FROM cliente as c
JOIN pedido ON Cliente_id = c.id
JOIN mesa ON mesa.id = pedido.Mesa_id
WHERE mesa.Restaurante_id = :id";
$stm = $pdo->prepare($sql);
$stm->bindValue(":id", $_SESSION['uid'], PDO::PARAM_INT);
$result = $stm->execute();
if ($result) {
    $cliente = $stm->fetch();
}
// FIM Numero de Clientes 




?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard-mesas.css'>
    <link rel='stylesheet' href='../css/dashboard-pedido/dashboard-pedido.css'>
    <link rel='stylesheet' href='../css/landing/menu.css'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
</head>

<body>

    <?php include('sidebar.php'); ?>


    <div class="main">
        <div class="dashboard-boxes">
            <div class="dashboard-box">
                <div class="dashboard-box-icon">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="dashboard-box-text">
                    <span class="num" data-target="<?= $pedido['numero'] ?>"><?= $pedido['numero'] ?></span>
                    <span>Pedidos</span>
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-box-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="dashboard-box-text">
                    <span class="numEuro" data-target="<?= $vendas ?>"><?= $vendas ?></span>
                    <span>Vendas</span>
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-box-icon">
                    <i class="fa-solid fa-book"></i>
                </div>
                <div class="dashboard-box-text">
                    <span class="num" data-target="<?= $row2['numero'] ?>"><?= $row2['numero'] ?></span>
                    <span>Categorias</span>
                </div>
            </div>
            <div class="dashboard-box">
                <div class="dashboard-box-icon">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="dashboard-box-text">
                    <span class="num" data-target="<?= $cliente['numero'] ?>"><?= $cliente['numero'] ?></span>
                    <span>Clientes</span>
                </div>
            </div>
        </div>

        <div class="dashboard-main">
            <div class="dashboard-main-left">
                <div class="dashboard-spacebtw">
                    <span class="dashboard-main-title">Mesas Abertas</span>
                </div>
                <div class="dashboard-mesas" style="height: 420px;">
                    <?php

                    $sql = "SELECT * FROM mesa WHERE Restaurante_id = :REST_ID AND estado = 'aberto'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                    $stmt->execute();
                    while ($row = $stmt->fetch()) { ?>

                        <div class="dashboard-mesa">
                            <div class="dashboard-mesa-container">
                                <a href="<?= $row['tableCode'] ?>" class="dashboard-mesa-image"><img src="<?= $row['tableCode'] ?>" alt="QrCode"></a>
                                <div class="dashboard-mesa-content">
                                    <?php

                                    if ($row['estado'] != "fechado") {
                                    ?> <div class="global-data-status"></div> <?php
                                                                            } else {
                                                                                ?> <div class="global-data-status" style="background-color: #0e0e0e;"></div> <?php
                                                                                                                                                            }

                                                                                                                                                                ?>
                                    <span class="dashboard-mesa-title">Mesa <?= $row['numero'];
                                                                            $row['estado']; ?> </span>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </div>

                <!-- <div class="dashboard-boxes2">
                    <div class="dashboard-box2">
                        Ainda
                    </div>
                    <div class="dashboard-box2">
                        Não
                    </div>
                    <div class="dashboard-box2">
                        Sei
                    </div>
                </div> -->

            </div>

            <div class="dashboard-main-right">
                <span class="dashboard-main-title">Historico de Pedidos</span>
                <div class="dashboard-main-right-history">


                    <?php

                    $sql = "SELECT pedido.*, mesa.numero FROM pedido 
                        JOIN mesa on mesa.id = pedido.Mesa_id
                        WHERE mesa.Restaurante_id = :REST_ID
                        ORDER BY estado, date DESC , time DESC LIMIT 3";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                    $stmt->execute();
                    while ($pedido = $stmt->fetch()) { ?>
                        <div class="pedido">

                            <div class="pedido-container">
                                <div class="pedido-left">
                                    <?php

                                    if ($pedido['estado'] == "Confirmado") {
                                    ?> <div class="global-data-status"></div> <?php
                                        } elseif ($pedido['estado'] == "porConfirmar") {
                                            ?> <div class="global-data-status" style="background-color: var(--corPorConfirmar);"></div>
                                    <?php } elseif ($pedido['estado'] == "Recusado") {
                                    ?> <div class="global-data-status" style="background-color: var(--corNegativo);"></div>
                                    <?php } elseif ($pedido['estado'] == "Entregue") { ?>
                                        <div class="global-data-status" style="background-color: var(--corPrincipal);"></div>
                                    <?php } ?>
                                    <div class="pedido-left-text">
                                        <span class="pedido-mesa">Mesa <?= $pedido['numero'] ?></span>
                                        <span class="pedido-id">#<?= $pedido['id'] ?></span>
                                    </div>
                                </div>
                                <div class="pedido-right">
                                    <span class="pedido-precototal"><?= $pedido['precototal'] ?> €</span>
                                    <svg viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.25 9.375L12.5 15.625L18.75 9.375" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <?php
                            $sql2 = "SELECT username FROM cliente
                                JOIN pedido on pedido.Cliente_id = cliente.id                     
                                WHERE pedido.id = :pedido_id";
                            $stm = $pdo->prepare($sql2);
                            $stm->bindValue(":pedido_id", $pedido['id'], PDO::PARAM_INT);
                            $stm->execute();
                            $cliente = $stm->fetch();
                            ?>
                            <div class="pedido-openned">
                                <div class="pedido-openned-bar-container">
                                    <div class="pedido-openned-bar"></div>
                                </div>
                                <!-- <div class="pedido-openned-header">
                                        <span>Nome Pessoa</span>
                                        <div class="pedido-openned-header-date">
                                            <span>Date</span>
                                            <span>12:13:45</span>
                                        </div>
                                    </div> -->
                                <div class="pedido-openned-vertical">
                                    <div class="pedido-openned-container">
                                        <?php
                                        $sql2 = "SELECT pp.* FROM pedido_has_produto as pp
                                            JOIN pedido on pedido.id = pp.Pedido_id
                                            WHERE pedido.id = :idPedido";
                                        $stm = $pdo->prepare($sql2);
                                        $stm->bindValue(":idPedido", $pedido['id'], PDO::PARAM_INT);
                                        $stm->execute();
                                        while ($pedido_has = $stm->fetch()) { ?>
                                            <?php
                                            $sql3 = "SELECT * FROM produto                                                        
                                            WHERE id = :idProduto";
                                            $stm2 = $pdo->prepare($sql3);
                                            $stm2->bindValue(":idProduto", $pedido_has['Produto_id'], PDO::PARAM_INT);
                                            $stm2->execute();
                                            while ($produto = $stm2->fetch()) { ?>
                                                <div class="menu-box">
                                                    <div class="menubox-container">
                                                        <div class="menubox-spacebtw">
                                                            <div class="menubox-left">
                                                                <div class="menubox-image">
                                                                    <img src="<?= PRODUTOIMG_WEB_PATH ?><?= $produto['img'] != NULL ? $produto['img'] : PRODUTOIMG_DEFAULT ?>" />

                                                                </div>
                                                                <div class="menubox-text">
                                                                    <span class="menubox-title"><?= $pedido_has['quantidade'] ?> x <?= $produto['nome'] ?></span>
                                                                    <span class="menubox-price"><?= $produto['preco'] ?> €</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                    <div class="pedido-openned-info">

                                        <?php if ($cliente['username']) { ?>
                                            <span class="pedido-nome-cliente"><i class="fa-solid fa-user"></i> <?= $cliente['username'] ?></span>
                                        <?php } ?>

                                        <!-- <span>Preço: <?= $pedido['precototal'] ?> €</span>-->
                                        <span><i class="fa-solid fa-clock"></i> <?= $pedido['date'] ?>, <?= $pedido['time'] ?></span>

                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php
                    }
                    ?>

                    <div class="dashboard-main-right-more">
                        <a href="./pedidos.php">Ver mais</a>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script type="text/javascript" src="../js/dashboard.js"></script>
</body>

</html>