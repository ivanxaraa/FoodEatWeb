<?php

require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);


$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if($id != ''){
    $sql = "DELETE FROM `empregado` where `id` = :id";
    $html .= debug() ? "<code>$sql</code>" : '';
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    if($result){
        $html .= 'Sucesso!';
        header('Location: ../empregados.php?&deleted='.MSG_SUCESSO.'&msg='.MSG_APAGADO);
    } else {
        $html .= 'Erro!';
    }
}


?>