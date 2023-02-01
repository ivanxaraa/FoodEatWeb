<?php
require_once '../config.php';
require_once '../core.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');



        body {
            --bordasNormal: 8px;
            --bordasMin: 4px;
            --landingTexto: #fff;
            --landingCorPrincipal: #0e0e0e;
            --landingCorBg: #F8F4F1;
            --landingCorBox: #ffffff3d;
            font-family: 'Poppins', sans-serif;
        }

        .container {
            --landingTexto: #fff;
            --landingCorPrincipal: #0e0e0e;
            --landingCorBg: #F8F4F1;
            --landingCorBox: #ffffff3d;
            --bordasNormal: 8px;
            --bordasMin: 4px;
            border-radius: 8px;
        }

        .header {
            text-align: center;
            padding-top: 20px;
            padding-bottom: 20px;
            background-color: #0e0e0e;
        }

        .header h1 {
            color: #fff;
            letter-spacing: 5px;
        }

        .content {
            padding: 40px;
        }

        .header svg {
            width: 200px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            line-height: 1.2;
        }

        .subtitle {
            font-size: 16px;
        }

        .details {
            margin-top: 18px;
        }

        .details-title {
            font-weight: 600;
        }

        .details-menu {
            margin-top: 10px;
            grid-template-columns: repeat(4, minmax(350px, 1fr));
            gap: 15px;
        }

        .produto {
            background-color: #0e0e0e;
            display: flex;
            flex-direction: column;
            width: 280px;
            color: #fff;
            padding: 10px 15px;
            border-radius: var(--bordasMin);
        }

        table.GeneratedTable {
            background-color: #ffffff;
            border-collapse: collapse;
            border-width: 0px;
            border-color: #ffcc00;
            border-style: solid;
            color: #000000;
        }

        table.GeneratedTable td,
        table.GeneratedTable th {
            border-width: 0px;
            border-color: #ffcc00;
            border-style: solid;
            padding: 10px 20px;
            min-width: 150px;
        }

        table.GeneratedTable thead {
            background-color: #0e0e0e;
            color: #fff;
        }

        table.GeneratedTable thead,
        tfoot {
            background-color: #0e0e0e;
            color: #fff;
            font-size: 12px;
        }

        .menu-box {
            position: relative;
            outline: none;
            border: none;
            width: 330px;
            border-radius: 8px;
            cursor: pointer;
            background-color: #ffffff3d;
            transition: 0.3s;
        }



        .menubox-container {
            padding: 10px;
        }



        .menubox-spacebtw {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menubox-left {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }

        .menubox-image {
            height: 60px;
            width: 60px;
            border-radius: 8px;
        }

        .menubox-image img {
            border-radius: 8px;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .menubox-text {
            margin-left: 10px;
            margin-top: 5px;            
        }

        .menubox-title {
            font-weight: 600;
            font-size: 16px;
        }

        .menubox-price {
            font-weight: 400;
            font-size: 14px;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g9/seraqe/img/FoodEatLogo.png">
        </div>
        <div class="content">
            <div class="title">Pedido Feito</div>
            <div class="subtitle">Aguarde a confirmação do pedido.</div>
            <div class="details">
                <span class="details-title">Restaurante: </span><span class="details-desc"><?= $nomeRest ?></span><br>
                <span class="details-title">Id Pedido: </span><span class="details-desc">#<?= $idPedido ?></span><br>
                <span class="details-title">O seu pedido: </span><br>

                <div class="details-menu">

                    <?php
                    $PedidoPrecoTotal = 0;
                    foreach ($_GET as $key => $produto) {
                        if (is_array($produto)) { 
                            $PedidoPrecoTotal = $PedidoPrecoTotal + ($produto['preco'] * $produto['quant'])?>                            
                            <div class="menu-box">
                                <div class="menubox-container">
                                    <div class="menubox-spacebtw">
                                        <div class="menubox-left">
                                            <div class="menubox-image">
                                                <img src="<?= WEB_ROOT_IMG_EMAIL . $produto['img'] ?>">
                                            </div>
                                            <div class="menubox-text">
                                                <span class="menubox-title"><?= $produto['quant']  ?> x <?= $produto['nome']  ?></span><br>
                                                <span class="menubox-price"><?= $produto['preco']  ?> €</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            ?>
                    <?php
                        }
                    }
                    ?>                   
                    <!-- ANTIGO
                    <table class="GeneratedTable">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($_GET as $key => $produto) {
                                if (is_array($produto)) { ?>

                                    <tr>
                                        <td><?= $produto['nome'] ?></td>
                                        <td><?= $produto['quant'] ?></td>
                                        <td><?= $produto['preco'] ?> €</td>
                                    </tr>

                                    <?php
                                    ?>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Preço Total: </td>
                                <td colspan="1"><?= $PedidoPrecoTotal ?> €</td>
                            </tr>
                        </tfoot>
                    </table> -->

                </div><br>
                <span class="details-title">Preço total: </span><span class="details-desc"><?= $PedidoPrecoTotal ?> €</span>
            </div>
        </div>
    </div>

</body>

</html>