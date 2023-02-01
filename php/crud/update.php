<?php
define('DESC', 'Editar um registo da Bases de Dados');
define('UC', 'PAW');
$html = '';

require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);

$action = filter_input(INPUT_GET, 'action' ,FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if($action == 'edit') {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM `cliente` WHERE `id` = :id";
    $html .= debug() ? "<code>$sql</code>" : '';

    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    if($result){
        $row = $stm->fetch();
        $html .= '

        <form action="?" method="POST">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="id">ID</Label>
                <input type="text" class="form-control"
                        name="id" id="id" readonly
                        value="'. $row['id'] . '">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control"
                        name="username" id="username" placeholder="username"
                        value="' . $row['username'] . '">
            </div>
            <input type="submit" class="btn btn-primary" value="Editar">
            <a href="list.php" class="btn btn-secondary" >Voltar</a>
        </form>';
       

    } else{
        $html .= "eroro";
    }
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if($action == 'update'){

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if($username != ''){
        $sql = "UPDATE `cliente` SET `username`=:NOME WHERE `id`=:id";
        $html .= debug() ? "<code>$sql</code>" : '';

        $stm = $pdo->prepare($sql);
        $stm->bindValue(':NOME', $username);
        $stm->bindValue(':id', $id);
        $result = $stm->execute();
    }

    if($result){
        $html .= 'Sucesso!';
        header('Location: ../clientes.php');
    }

}



?>
<!DOCTYPE html>
<html>
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
</html>