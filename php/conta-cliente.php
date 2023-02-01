<?php
session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
require_once './config.php';
require_once './core.php';
$pdo = connectDB($db);
$debug = '';
$html = '';
$errors = '';
function slugify($text = ''){
    if ($text != '') {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        return $text;
    }
    return FALSE;
}


$sql = "SELECT * FROM `cliente` WHERE `id` = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $_SESSION['uid']);
$result = $stmt->execute();
if ($result) {
    $cliente = $stmt->fetch();
}



$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //duplicados
    $sql = "SELECT email FROM cliente WHERE email = :EMAIL AND id != :ID LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
    $stmt->bindValue(":ID", $_SESSION['uid'], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O email que adicionou já existe';
    }

    $sql = "SELECT username FROM cliente WHERE username = :USERNAME AND id != :ID LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":USERNAME", $username, PDO::PARAM_STR);
    $stmt->bindValue(":ID", $_SESSION['uid'], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O username que adicionou já existe';
    }
    // FIM duplicados

    if (!$errors) {

        //alterar username & email
        if ($username != "" && $email != "") {
            $sql = "UPDATE cliente SET 
            `username` = :username,
            `email` = :email         
            WHERE `id`=:id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(':username', $username);
            $stm->bindValue(':email', $email);
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();
        }
        

        //alterar password
        if ($cliente['password'] != $password && $password != "") {
            $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE cliente SET             
            `password` = :password            
            WHERE `id`=:id";
            $stm = $pdo->prepare($sql);
            $stm->bindValue(':password', $password_hash_db);
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();
        }

        //alterar imagem
        $dbfilename = null;
        $upload_name = strtolower(pathinfo($_FILES["fotoAvatar"]["name"], PATHINFO_FILENAME));
        if ($upload_name != "") {

            $upload_extension = strtolower(pathinfo($_FILES["fotoAvatar"]["name"], PATHINFO_EXTENSION));
            $upload_type = $_FILES["fotoAvatar"]["type"];
            $upload_tmp_name = $_FILES["fotoAvatar"]["tmp_name"];
            $upload_error = $_FILES["fotoAvatar"]["error"];
            $upload_size = $_FILES["fotoAvatar"]["size"];
            $filename = CLIENTEIMG_PATH . slugify($upload_name) . '.' . $upload_extension;
            $dbfilename = slugify($upload_name) . '.' . $upload_extension;

            if (is_file($filename) || is_dir($filename)) {
                $debug .= "File already exists on server: " . $filename . "\n";
                $html .= '<div class="alert alert-error">Ficheiro já existe: <b>' . $filename . '</b></div>';
            } else {
                if (@move_uploaded_file($_FILES["fotoAvatar"]["tmp_name"], $filename)) {
                    $debug .= "New file uploaded: " . $filename . "\n";
                    $html .= '<div class="alert alert-error">Ficheiro enviado com sucesso: <b>' . $filename . '</b></div>';

                    //remover imagem de perfil antiga
                    $sql = "SELECT img FROM cliente WHERE id = :id;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':id', $_SESSION['uid']);
                    $stmt->execute();
                    $filename = $stmt->fetch();

                    $filepath = CLIENTEIMG_PATH . $filename['img'];
                    if (is_file($filepath)) {
                        unlink($filepath);
                    }
                    //

                } else {
                    $debug .= "Error: " . error_get_last() . "\n";
                    die();
                }
            }

            $sql = "UPDATE cliente SET
            `img`= :avatar
            WHERE `id` = :id";

            $stm = $pdo->prepare($sql);
            $stm->bindValue(":avatar", $dbfilename != null ? $dbfilename : NULL, PDO::PARAM_STR);
            $stm->bindValue(':id', $_SESSION['uid']);
            $result = $stm->execute();
        }

        // BUG - sem isto é preciso dar refresh para atualizar os dados ( nao sei pq )
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Alterar Dados</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/variaveis.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='./../css/conta-cliente/conta-cliente.css'>
    <link rel='stylesheet' href='../css/landing/menu.css'>
    <link rel='stylesheet' href='../css/dashboard-pedido/dashboard-pedido.css'>
    <script src="https://kit.fontawesome.com/c3634568e9.js" crossorigin="anonymous"></script>
</head>

<body>
    
    <div class="conta-cliente">
        <div class="voltar">
            <a class="main-title" href="./landing.php?rest_id=<?= $_SESSION['rest'] ?>&mesa=<?= $_SESSION['mesa'] ?>">
                <i class="fa-solid fa-chevron-left"></i>
                Voltar
            </a>
        </div>
        <div class="conta-cliente-container">
            <div class="conta-cliente-alterar">                
                <span class="main-title">A sua conta</span>
                <div class="global-alterardados-content">
                    <form action="?" method="POST" enctype="multipart/form-data">
                        <div class="global-alterardados-content-container">
                            <div class="global-alterardados-header-space" style="align-items: start;">
                                <div class="global-alterardados-header" style="width:100%">
                                    <label class="produto-foto-perfil" id="overlay-images-round" style="background-image: url('<?= CLIENTEIMG_WEB_PATH ?><?= $cliente['img'] != NULL ? $cliente['img'] : CLIENTEIMG_DEFAULT ?>');">
                                        <input type="file" name="fotoAvatar" id="fotoAvatar">
                                    </label>
                                    <span><?= $cliente['username'] ?><a id="alterardados-id">#<?= $cliente['id'] ?></a></span>
                                </div>
                            </div>
                            <div class="global-alterardados-content-boxes">
                                <div class="global-span-input-box">
                                    <span>Username</span>
                                    <input type="text" class="global-input" placeholder="Username" name="username" value="<?= $cliente['username'] ?>">
                                </div>
                                <div class="global-span-input-box">
                                    <span>Email</span>
                                    <input type="email" class="global-input" placeholder="Email" name="email" value="<?= $cliente['email'] ?>">
                                </div>
                                <div class="global-span-input-box">
                                    <span>Password</span>
                                    <input type="password" class="global-input" placeholder="Password" name="password" value="<?= $cliente['password'] ?>">
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button type="submit" class="global-apagar-button" name="action" value="update">Alterar Dados</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="global-alterardados-buttons">
                    <a href="./logout.php" class="global-confirmar-button" name="action" value="terminar">Terminar Sessão</a>
                </div>

            </div>
            <div class="conta-cliente-lastPedidos">
                <span class="main-title">Ultimos Pedidos</span>
                <div class="conta-lastPedidos-content">

                    <?php
                    $sql = "SELECT pedido.*, mesa.numero FROM pedido 
                    JOIN mesa on mesa.id = pedido.Mesa_id
                    WHERE pedido.Cliente_id = :cliente_id
                    ORDER BY estado, date DESC , time DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(":cliente_id", $_SESSION['uid'], PDO::PARAM_INT);
                    $stmt->execute();
                    while ($pedido = $stmt->fetch()) { ?>

                        <div class="pedido">

                            <div class="pedido-container">
                                <div class="pedido-left">
                                    <?php

                                    if ($pedido['estado'] != "porConfirmar") {
                                    ?> <div class="global-data-status"></div> <?php
                                                } else {
                                                    ?> <div class="global-data-status" style="background-color: var(--corPorConfirmar);"></div>

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
                                        <span><i class="fa-solid fa-clock"></i> <?= $pedido['date'] ?>, <?= $pedido['time'] ?></span>
                                    </div>                                    
                                </div>
                            </div>

                        </div>

                    <?php }
                    ?>

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

    <script src="../js/pedidos.js"></script>
</body>

</html>
<?php include './ativarNotification.php'; ?>