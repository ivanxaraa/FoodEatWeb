<?php
    if (!isset($_SESSION['cart'])) {
        session_start();
    }
    require_once './config.php';
    require_once './core.php';
    $error = false;

    $pedidoFeito = filter_input(INPUT_GET, 'pedidoFeito');
    if ($pedidoFeito) {
        $_SESSION['cart'] = array();
        $notification = "Pedido feito!";
    }

    $fazerPedido = filter_input(INPUT_POST, 'fazerPedido');
    if ($fazerPedido) {


        if ($_SESSION['mesa'] && count($_SESSION['cart']) > 0) {

            $sql = "SELECT id,estado FROM `mesa` WHERE `numero` = :numMesa AND `Restaurante_id` = :id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':numMesa', $_SESSION['mesa']);
            $stm->bindValue(':id', $_SESSION['rest']);
            $result = $stm->execute();
            if ($result) {
                $mesa = $stm->fetch();
            }

            $newEstado = "aberto";

            if ($mesa['estado'] == "fechado") {
                $sql = "UPDATE `mesa` SET
                `estado`= :estado
                WHERE `id` = :id";

                $stm = $pdo->prepare($sql);
                $stm->bindValue(':estado', $newEstado);
                $stm->bindValue(':id', $mesa['id']);
                $result = $stm->execute();
            }


            //criar pedido
            $precoTotal = 0.00;
            $quantidade = count($_SESSION['cart']);
            foreach ($_SESSION['cart'] as $produto) {
                $precoTotal = $precoTotal + ($produto['preco'] * $produto['quant']);
                if ($produto['quant'] > $produto['stock']) {
                    $error = true;
                    $notification = "Não há stock suficiente de " . $produto['nome'];
                }
                if ($produto['stock'] <= 0) {
                    $error = true;
                    $notification = "Acabou o stock de " . $produto['nome'];
                }
            }

            if (!$error) {
                $estadoPedido = "porConfirmar"; //Confirmado 
                $mesaPedido = $_SESSION['mesa'];
                if (isset($_SESSION['uid'])) {
                    $cliente = $_SESSION['uid'];
                } else {
                    $cliente = NULL;
                }
                $date = date("Y-m-d");
                $time = date("H:i");

                $errors = false;
                if ($mesaPedido != "") {

                    $sql = "SELECT id FROM mesa WHERE numero = :numero AND Restaurante_id = :rest_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":numero", $_SESSION['mesa'], PDO::PARAM_INT);
                    $stmt->bindValue(":rest_id", $_SESSION['rest'], PDO::PARAM_INT);
                    $stmt->execute();
                    $mesaID = $stmt->fetch();


                    $sql = "INSERT INTO pedido(quantidade, precototal, estado, Mesa_id, cliente_id, date, time) VALUES(:quant, :precoTotal, :estado, :mesa, :cliente, :date, :time)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":quant", $quantidade, PDO::PARAM_INT);
                    $stmt->bindValue(":precoTotal", $precoTotal);
                    $stmt->bindValue(":estado", $estadoPedido, PDO::PARAM_STR);
                    $stmt->bindValue(":mesa", $mesaID['id'], PDO::PARAM_INT);
                    $stmt->bindValue(':cliente', $cliente != null ? $cliente : NULL); //unnecessary
                    $stmt->bindValue(':date', $date);
                    $stmt->bindValue(':time', $time);
                    $stmt->execute();


                    $pedidoID = $pdo->lastInsertId();

                    foreach ($_SESSION['cart'] as $produto) {
                        $sql = "INSERT INTO pedido_has_produto(Pedido_id, Produto_id, quantidade) VALUES(:idPedido, :idProduto, :quant)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idPedido", $pedidoID, PDO::PARAM_INT);
                        $stmt->bindValue(":idProduto", $produto['id'], PDO::PARAM_INT);
                        $stmt->bindValue(":quant", $produto['quant'], PDO::PARAM_INT);
                        $stmt->execute();
                    }

                    //remover do stock
                    foreach ($_SESSION['cart'] as $produto) {
                        $newStock = 0;
                        $sql = "SELECT stock FROM produto WHERE id = :idProduto";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":idProduto", $produto['id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $produtoStock = $stmt->fetch();                    
                        $newStock = $produtoStock['stock'] - $produto['quant'];

                        $sql = "UPDATE `produto` SET
                        `stock` = :newStock
                        WHERE `id`= :id";
                        $stm = $pdo->prepare($sql);
                        $stm->bindValue(':newStock', $newStock);
                        $stm->bindValue(":id", $produto['id'], PDO::PARAM_INT);
                        $result = $stm->execute();
                    }

                    //enviar email apos 3segundos
                    if (isset($_SESSION['uid'])) {
                        $pedido = http_build_query($_SESSION['cart']);                                          
                        header("Location: ./mail/send.php?numeroPedido=$pedidoID&pedido=$pedido");    
                        ob_end_flush();                      
                    } else {
                        $_SESSION['cart'] = array();
                        $notification = "Pedido feito!";
                    }
                }
            }
        } else {
            $notification = "Não tem mesa selecionada!";
        }
    }
    $PedidoPrecoTotal = 0.00;
    foreach ($_SESSION['cart'] as $produto) {        
        $PedidoPrecoTotal = $PedidoPrecoTotal + ($produto['preco'] * $produto['quant']);    
    }
?>

<div class="sidecart-container">
    <div class="sidecart-header">
        <svg onclick="CloseSideCart()" viewBox="0 0 68 68" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M24.875 34.5H44.125" stroke="white" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M34.5 24.875L44.125 34.5L34.5 44.125" stroke="white" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="34" cy="34" r="33.5" transform="rotate(-90 34 34)" stroke="white" />
        </svg>
        <div class="sidecart-header-text">
            <span>Pedido</span>
            <?php if ($_SESSION['mesa']) { ?>
                <div class="sidecart-mesa">
                    #<?= $_SESSION['mesa'] ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="sidecart-spacebtw">
        <div class="sidecart-content" style="min-width: 200px;">
            <?php foreach ($_SESSION['cart'] as $produto) { ?>
                <div class="menu-box">
                    <div class="menubox-container">
                        <div class="menubox-spacebtw">
                            <div class="menubox-left">
                                <div class="menubox-image">
                                    <img src="<?= PRODUTOIMG_WEB_PATH ?><?= $produto['img'] != NULL ? $produto['img'] : PRODUTOIMG_DEFAULT ?>" />

                                </div>
                                <div class="menubox-text">
                                    <span class="menubox-title"><?= $produto['quant']  ?> x <?= $produto['nome']  ?></span>
                                    <span class="menubox-price"><?= $produto['preco']  ?> €</span>
                                </div>
                            </div>
                            <button type="button" class="menu-box-delete" id="<?= $produto['id']; ?>" data-rest="<?= $_SESSION['rest'] ?>" data-mesa="<?= $_SESSION['mesa'] ?>">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18.8809 6.98779L6.88086 18.9878" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M6.88086 6.98779L18.8809 18.9878" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php if (count($_SESSION['cart']) >= 1) { ?>
            <div class="global-alterardados-buttons" style="min-width: 320px;">
                <div class="sidecart-total">
                    <span>Total:</span>
                    <span><?= addDecimals($PedidoPrecoTotal); ?></span>
                </div>
                <form action="?rest_id=<?= $_SESSION['rest']; ?>&mesa=<?= $_SESSION['mesa']; ?>" method="POST" onsubmit="antiSubmit();">
                    <button class="sidecart-btn" name="fazerPedido" value="fazerPedido">Fazer pedido (<?= count($_SESSION['cart']) ?>)</button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>



<div class="landing-noti" id="noti">
    <div class="landing-noti-container">
        <span class="landing-noti-text"><?= $notification ?></span>
    </div>
</div>

<script src="../js/landing.js"></script>
<script>
    //Remover item do cart 
    $(document).ready(function() {
        $('.menu-box-delete').click(function() {
            var rest_id = $('.menu-box-delete').attr("data-rest");
            var mesa = $('.menu-box-delete').attr("data-mesa");
            $.ajax({
                url: "landing.php?rest_id=" + rest_id + "&mesa=" + mesa,
                type: "POST",
                data: {
                    deleteItem: this.id
                },
                success: function() {
                    $('.sidecart').load('sidecart.php');
                },
            });
        });
    });
</script>

<?php include './ativarNotification.php'; ?>