<?php
define('DESC', 'Apagar registos duma tabela da Bases de Dados');
define('UC', 'PAW');
$html = '';


require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);


$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if($id != ''){
    $sql = "DELETE FROM `cliente` where `id` = :id";
    $html .= debug() ? "<code>$sql</code>" : '';
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    if($result){
        $html .= 'Sucesso!';
        header('Location: ../clientes.php');
    } else {
        $html .= 'Erro!';
    }
}


?>
<!DOCTYPE html>
<html>
    <!-- 
    <head>
        <title><?= UC . ' | ' . DESC . ' | ' . AUTHOR ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="../../common/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <h3><?= DESC ?></h3>
            <div>
                <?= $html ?>
            </div>
            <?= debug() ? '<div><code>POST: ' . print_r($_POST, true) . '<br>GET: ' . print_r($_GET, true) . '</code></div>' : '' ?>
        </div>
    </body>

-->
</html>