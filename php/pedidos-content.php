<?php

if (!(isset($_SESSION['tipoPedido']))) {
    session_start();
}

require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);

$sql = "SELECT pedido.*, mesa.numero FROM pedido 
JOIN mesa on mesa.id = pedido.Mesa_id
WHERE mesa.Restaurante_id = :REST_ID AND pedido.estado = :ESTADO
ORDER BY estado, date DESC , time DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
$stmt->bindValue(":ESTADO", $_SESSION['tipoPedido'], PDO::PARAM_STR);
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
                <div class="global-alterardados-buttons">
                    <a class="global-confirmar-button" href="./crud/delete-pedido.php?id=<?= $pedido['id'];  ?>">Eliminar</a>
                </div>
            </div>
        </div>

    </div>
    <script>
        function toggleCollapseMenu() {
            const linkCollapse = document.querySelectorAll('.pedido-container');
            for (let i = 0; i < linkCollapse.length; i++) {
                linkCollapse[i].addEventListener("click", function() {
                    const collapseMenu = this.nextElementSibling;
                    collapseMenu.classList.toggle("openPedido");
                });
            }
        }

    </script>

<?php }
?>

