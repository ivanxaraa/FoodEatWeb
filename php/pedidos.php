<?php

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';



require_once './config.php';
require_once './core.php';


$pdo = connectDB($db);
$notification = filter_input(INPUT_GET, 'deleted', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$notimsg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (isset($_GET['tipoPedido'])) {
    $_SESSION['tipoPedido'] = filter_input(INPUT_GET, 'tipoPedido');
} else {
    $_SESSION['tipoPedido'] = 'porConfirmar';
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Pedidos</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/landing/menu.css'>
    <link rel='stylesheet' href='../css/dashboard-pedido/dashboard-pedido.css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
</head>

<body>


    <?php include('sidebar.php'); ?>


    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <div class="menu-title-btw">
                        <span class="main-title">Pedidos</span>
                    </div>
                    <div class="menu-title-btw">
                        <div class="menu-slider">
                            <button class="menu-chip active" onclick="ativarCor(this.id)" id="porConfirmar" type="button">Por Confirmar</button>
                            <button class="menu-chip" onclick="ativarCor(this.id)" id="Confirmado" type="button">Confirmados</button>
                            <button class="menu-chip" onclick="ativarCor(this.id)" id="Entregue" type="button">Entregue</button>
                            <button class="menu-chip" onclick="ativarCor(this.id)" id="Recusado" type="button">Recusados</button>
                            <button class="menu-chip" onclick="ativarCor(this.id)" id="Pago" type="button">Pagos</button>
                        </div>
                        <div class="title-searchbar-container">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchBar" onkeyup="searchFunction()" placeholder="Procure aqui...">
                        </div>
                    </div>
                    <div class="main-content" id="main-content">

                        <?php include 'pedidos-content.php' ?>

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

    <script>
        function searchFunction() {
            var input, filter, lista, produto, nome, i;
            input = document.getElementById('searchBar');
            filter = input.value.toUpperCase();
            lista = document.getElementById('main-content');
            produto = lista.getElementsByClassName('pedido');

            for (i = 0; i < produto.length; i++) {
                nome = produto[i].getElementsByClassName('pedido-mesa')[0];
                if (nome.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    produto[i].style.display = "";
                } else {
                    produto[i].style.display = 'none';
                }
            }
        }        
    </script>

    
</body>

</html>

<script src="../js/pedidos.js"></script>
<?php include './ativarNotification.php'; ?>