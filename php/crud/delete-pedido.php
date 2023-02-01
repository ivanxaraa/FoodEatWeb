<?php

require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);


$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if($id != ''){
    
    $sql = "SET FOREIGN_KEY_CHECKS = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $sql = "DELETE FROM `pedido` where `id` = :id";
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    $sql = "DELETE FROM `pedido_has_produto` where `Pedido_id` = :id";
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    $sql = "SET FOREIGN_KEY_CHECKS = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    if($result){
        $html .= 'Sucesso!';
        header('Location: ../pedidos.php?&deleted='.MSG_SUCESSO.'&msg='.MSG_APAGADO);
    } else {
        $html .= 'Erro!';
    }
}
