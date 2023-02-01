<?php

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';

define('DESC', 'Aplicação Web');
$html = '';
$debug = '';
$alterarDadosHtml = '';
$errors = false;

require_once './config.php';
require_once './core.php';


$pdo = connectDB($db);
$notification = filter_input(INPUT_GET, 'deleted', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$notimsg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$addEmpregadoPOST = filter_input(INPUT_POST, 'addEmpregadoPOST');
if ($addEmpregadoPOST) {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'tel', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $errors = false;

    $password = "123";
    $password_hash_db = password_hash($password, PASSWORD_DEFAULT);

    //duplicados add
    $sql = "SELECT email FROM empregado WHERE email = :EMAIL LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O email que adicionou já existe';
    }

    $sql = "SELECT telefone FROM empregado WHERE telefone = :TEL LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":TEL", $telefone, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O telefone que adicionou já existe';
    }

    if(strlen($telefone) != 9){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O telefone deve ter 9 digitos';
    }

    if ($nome != "") {

        if (!$errors) {

            $sql = "INSERT INTO empregado(nome, email, telefone, password, Restaurante_id) VALUES(:NOME,:EMAIL,:TELE,:PASSWORD,:REST_ID )";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":NOME", $nome, PDO::PARAM_STR);
            $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
            $stmt->bindValue(":TELE", $telefone, PDO::PARAM_INT);
            $stmt->bindValue(":PASSWORD", $password_hash_db, PDO::PARAM_STR);
            $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $notification = MSG_SUCESSO;
                $notimsg = 'Funcionário' . MSG_ADICIONADO;
            } else {
                $notification = MSG_ERRO;
                $notimsg = 'Erro ao inserir na Base de Dados';
            }
        }
    }
}

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //duplicados
    $sql = "SELECT email FROM empregado WHERE email = :EMAIL AND id != :ID LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
    $stmt->bindValue(":ID", $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O email que adicionou já existe';
    }

    $sql = "SELECT telefone FROM empregado WHERE telefone = :TEL AND id != :ID LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":TEL", $telefone, PDO::PARAM_INT);
    $stmt->bindValue(":ID", $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O telefone que adicionou já existe';
    }
    // FIM duplicados

    if(strlen($telefone) != 9){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'O telefone deve ter 9 digitos';
    }

    if (!$errors) {

        $sql = "UPDATE `empregado` SET
        `nome`=:NOME,
        `email`=:EMAIL,
        `telefone`=:TEL
        WHERE `id`=:id";

        $stm = $pdo->prepare($sql);
        $stm->bindValue(':NOME', $nome, PDO::PARAM_STR);
        $stm->bindValue(':EMAIL', $email, PDO::PARAM_STR);
        $stm->bindValue(':TEL', $telefone, PDO::PARAM_INT);
        $stm->bindValue(':id', $id);
        $result = $stm->execute();

        $notification = MSG_SUCESSO;
        $notimsg = 'Funcionário' . MSG_EDITADO;
    }
    
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Funcionários</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='./css/dashboard/dashboard-pedido.css'>
    <link rel='stylesheet' href='../css/dashboard-menu/dashboard-menu.css'>
    <link rel='stylesheet' href='../css/dashboard-cliente/dashboard-cliente.css'>
</head>

<body>


    <?php include('sidebar.php'); ?>



    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <div class="menu-title-btw">
                        <span class="main-title">Funcionários</span>
                        <div class="title-searchbar-container">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchBar" onkeyup="searchFunction()" placeholder="Procure aqui...">
                        </div>
                    </div>
                    <div class="main-content" id="main-content">

                        <?php
                        $pdo = connectDB($db);
                        $sql = "SELECT * FROM empregado WHERE Restaurante_id = :REST_ID";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                        $stmt->execute();
                        while ($row = $stmt->fetch()) {
                            if ($row['nome']) { ?>

                                <div class="cliente-databox">
                                    <div class="categoria-databox-click">
                                        <a class="categoria-databox-button">
                                            <div class="cliente-databox-left">
                                                <div class="categoria-data-nome"><?= $row['nome'];  ?></div>
                                            </div>
                                            <div class="cliente-databox-right" style="gap: 10px;">
                                                <div class="categoria-data-quant" style="text-align: end; margin-right: 5px;"><?= $row['telefone'] ? $row['telefone'] : "0"  ?></div>
                                                <i class="fa-solid fa-phone"></i>
                                            </div>
                                        </a>
                                        <div class="cliente-edit-container">
                                            <a class="cliente-edit" href="empregados.php?action=edit&id=<?= $row['id'];  ?>">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                            <a href="./crud/delete-empregado.php?id=<?= $row['id'];  ?>" class="cliente-apagar">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 6H5H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                </a>


                        <?php }
                        }
                        ?>

                    </div>
                </div>
            </div>


            <div class="all-main-right">

                <div class="menu-adiconar-categoria">
                    <!-- EDITAR -->

                    <?php

                    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    if ($action == 'edit') {
                        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
                        $sql = "SELECT * FROM `empregado` WHERE `id` = :id";

                        $stm = $pdo->prepare($sql);
                        $stm->bindValue(':id', (int)$id);
                        $result = $stm->execute();

                        if ($result) {
                            $row = $stm->fetch();


                    ?>

                            <span class="main-title">Alterar Dados</span>
                            <div class="global-alterardados-content">
                                <form action="?" method="POST">
                                    <div class="global-alterardados-content-container">
                                        <div class="global-alterardados-header">
                                            <span><?= $row['nome'] ?><a id="alterardados-id">#<?= $row['id'] ?></a></span>
                                        </div>
                                        <div class="global-alterardados-content-boxes">
                                            <input type="hidden" name="id" id="id" readonly value="<?= $row['id'] ?>">
                                            <div class="global-span-input-box">
                                                <span>Nome</span>
                                                <input class="global-input" type="text" name="nome" id="nome" placeholder="Nome funcionário" value="<?= $row['nome'] ?>" required>
                                            </div>
                                            <div class="global-span-input-box">
                                                <span>Email</span>
                                                <input class="global-input" type="email" name="email" id="email" placeholder="Email funcionário" value="<?= $row['email'] ?>" required>
                                            </div>
                                            <div class="global-span-input-box">
                                                <span>Telefone</span>
                                                <input class="global-input" type="number" name="telefone" id="telefone" placeholder="Número de telefone" value="<?= $row['telefone'] ?>" required>
                                            </div>
                                            <div class="global-alterardados-buttons">
                                                <button class="global-confirmar-button" name="action" value="update">Confirmar</button>
                                                <a class="global-apagar-button" href="./crud/delete-empregado.php?id=<?php echo $row['id'];  ?>">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div><?php


                                }
                            }


                                    ?>

                    <!-- FIM EDITAR -->
                </div>

                <div class="menu-adiconar-categoria">
                    <span class="main-title">Adicionar Funcionário
                        <button onclick="closeSec()">
                            <svg class="categoria-alterar-title-icon" id="icon-rotate" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7.5L10 12.5L15 7.5" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </span>
                    <form action="?" method="POST">
                        <div class="global-alterardados-content" id="content">
                            <div class="global-alterardados-content-container">

                                <div class="global-alterardados-content-boxes">
                                    <div class="global-span-input-box">
                                        <span>Nome</span>
                                        <input type="text" class="global-input" placeholder="Nome Funcionário" name="nome" id="input-password" required>
                                    </div>
                                    <div class="global-span-input-box">
                                        <span>Email</span>
                                        <input class="global-input" type="email" name="email" id="email" placeholder="Email funcionário" value="<?= $row['email'] ?>" required>
                                    </div>
                                    <div class="global-span-input-box">
                                        <span>Telefone</span>
                                        <input type="number" class="global-input" placeholder="Número de telefone" name="tel" id="input-password">
                                    </div>
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button class="global-confirmar-button" type="submit" name="addEmpregadoPOST" value="addEmpregadoPOST">Adicionar</button>
                                </div>
                            </div>
                        </div>
                    </form>

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
            produto = lista.getElementsByClassName('cliente-databox');

            for (i = 0; i < produto.length; i++) {
                nome = produto[i].getElementsByClassName('categoria-data-nome')[0];
                if (nome.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    produto[i].style.display = "";
                } else {
                    produto[i].style.display = 'none';
                }
            }
        }
    </script>
    <script src="../js/clientes.js"></script>
</body>

</html>

<?php include './ativarNotification.php'; ?>