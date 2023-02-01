<?php

require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);


$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if($id != ''){

    $sql = "SET FOREIGN_KEY_CHECKS = 0";
    $stm = $pdo->prepare($sql);
    $stm->execute();

    $sql = "DELETE FROM `mesa` where `id` = :id";
    $html .= debug() ? "<code>$sql</code>" : '';
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    $sql = "SELECT * FROM `pedido` WHERE Mesa_id = :id";
    $html .= debug() ? "<code>$sql</code>" : '';
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    $sql = "SELECT pp.* FROM `pedido_has_produto` as pp JOIN pedido on pedido.id = pp.Pedido_id JOIN mesa on mesa.id = :id";
    $html .= debug() ? "<code>$sql</code>" : '';
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    $sql = "SET FOREIGN_KEY_CHECKS = 1";
    $stm = $pdo->prepare($sql);
    $stm->execute();

    if($result){
        $html .= 'Sucesso!';
        header('Location: ../mesas.php?&deleted='.MSG_SUCESSO.'&msg='.MSG_APAGADO);
    } else {
        $html .= 'Erro!';
    }
}


?>