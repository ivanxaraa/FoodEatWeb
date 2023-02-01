<?php

session_start();
if (!isset($_SESSION['uid'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
include 'verificarAdmin.php';

$html = '';
$alterarDadosHtml = null;

require_once './config.php';
require_once './core.php';


$pdo = connectDB($db);
$notification = filter_input(INPUT_GET, 'deleted', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$notimsg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS);





$addCategoriaPOST = filter_input(INPUT_POST, 'addCategoriaPOST');
if ($addCategoriaPOST) {
    $pdo = connectDB($db);
    $nomecat = filter_input(INPUT_POST, 'nomecat', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $errors = false;

    if ($nomecat != "") {

        $sql = "SELECT id FROM categoria WHERE nome = :NOMECAT AND Restaurante_id = :REST_ID LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":NOMECAT", $nomecat, PDO::PARAM_STR);
        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $notification = MSG_ERRO;
            $notimsg = 'Categoria' . MSG_ERRO_EXISTE;
            $errors = true;
        }


        if (!$errors) {

            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);

            $sql = "INSERT INTO categoria(nome, status, Restaurante_id) VALUES(:NOMECAT,:STATU,:REST_ID )";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":NOMECAT", $nomecat, PDO::PARAM_STR);
            $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
            $stmt->bindValue(":STATU", $status != null ? $status : "0");
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $notification = MSG_SUCESSO;
                $notimsg = 'Categoria' . MSG_ADICIONADO_FEM;
            } else {
                $notification = MSG_ERRO;
                $notimsg = 'Erro ao inserir na Base de Dados';
            }
        }
    }
}
?>

<?php
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'edit') {

    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM `categoria` WHERE `id` = :id AND Restaurante_id = :REST_ID";

    $stm = $pdo->prepare($sql);
    $stm->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
    $stm->bindValue(':id', (int)$id);
    $result = $stm->execute();

    if ($result) {
        $row = $stm->fetch();

        $alterarDadosHtml .= '       
                        <div class="menu-alterar-categoria">
                            <span class="main-title">Alterar Dados
                                <button onclick="closeSec2()">
                                        <svg class="categoria-alterar-title-icon" id="icon-rotate2" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                </button>                            
                            </span>
                            <div class="global-alterardados-content" id="content2">
                                <form action="?" method="POST">
                                    <div class="global-alterardados-content-container">
                                        <div class="global-alterardados-header">    
                                             <div class="global-alterardados-header-space">                           
                                                <span>' . $row['nome'] . '<a id="alterardados-id">#' . $row['id'] . ' </a></span>                                                
                                             </div>
                                             <label class="switch">
                                                    <input value="1" name="status" type="checkbox" ' . ($row['status'] == 1 ? 'checked' : '') . '>
                                                    <span class="slider round"></span>
                                                </label>     
                                        </div>
                                        <div class="global-alterardados-content-boxes">
                                            <input type="hidden" class="global-input" name="id" id="id" readonly value="' . $row['id'] . '">
                                            <div class="global-span-input-box">
                                                <span>Nome Categoria</span>
                                                <input class="global-input" type="text" name="nomecat" id="nomecat" placeholder="Nome Categoria" value="' . $row['nome'] . '">
                                            </div>                                           
                                            <div class="global-alterardados-buttons">
                                                <button class="global-confirmar-button" name="action" value="update">Confirmar</button>
                                                <a class="global-apagar-button" href="./crud/delete-menu.php?id=' . $row['id'] . '">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

            ';
    }
}
?>

<?php

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($action == 'update') {

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nomecat = filter_input(INPUT_POST, 'nomecat', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_NUMBER_INT);



    if ($status !== false) {
        $sql = "UPDATE `categoria` SET `status`=:STATU WHERE `id`=:id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':STATU', $status != null ? $status : "0");
        $stm->bindValue(':id', $id);
        $result = $stm->execute();
    }

    if ($nomecat != '') {
        $sql = "UPDATE `categoria` SET `nome`=:NOME WHERE `id`=:id";
        $stm = $pdo->prepare($sql);
        $stm->bindValue(':NOME', $nomecat);
        $stm->bindValue(':id', $id);
        $result = $stm->execute();

        $notification = MSG_SUCESSO;
        $notimsg = 'Categoria' . MSG_EDITADO_FEM;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Menu</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='./css/variaveis.css'>
    <link rel='stylesheet' href='./css/sidebar.css'>
    <link rel='stylesheet' href='./css/dashboard/dashboard.css'>
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
                    <div class="main-header">
                        <div class="menu-title-btw">
                            <span class="main-title">As suas categorias</span>
                            <div class="title-searchbar-container">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="searchBar" onkeyup="searchFunction()" placeholder="Procure aqui...">
                            </div>
                        </div>
                        <span class="main-subtitle">
                        </span>
                    </div>
                    <div class="main-content" id="main-content">

                        <?php

                        $pdo = connectDB($db);

                        $sql = "SELECT categoria.*, COUNT(produto.id) as 'total'
                        FROM categoria
                        LEFT JOIN produto
                        ON categoria.id = produto.Categoria_id
                        WHERE categoria.Restaurante_id = :REST_ID
                        GROUP BY categoria.id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                        $stmt->execute();
                        while ($row = $stmt->fetch()) {
                            if ($row['nome']) { ?>


                                <div class="cliente-databox">
                                    <div class="categoria-databox-click">
                                        <a class="categoria-databox-button" href="./produtos.php?id=<?php echo $row['id'];  ?>&nome=<?php echo $row['nome'];  ?>">
                                            <div class="cliente-databox-left">
                                                <?php
                                                if ($row['status'] == 1) {
                                                ?> <div class="global-data-status"></div> <?php
                                                                                        } else {
                                                                                            ?> <div class="global-data-status" style="background-color: #0e0e0e;"></div> <?php
                                                                                                                                                                        }
                                                                                                                                                                            ?>
                                                <div class="categoria-data-nome"><?php echo $row['nome'];  ?></div>
                                            </div>
                                            <div class="cliente-databox-right">
                                                <div class="categoria-data-quant"><?php echo $row['total'] ? $row['total'] : "0"  ?></div>
                                                <!--             <svg class="categoria-data-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="black" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg> -->
                                            </div>
                                        </a>
                                        <div class="cliente-edit-container">
                                            <a class="cliente-edit" href="dashboard-menu.php?action=edit&id=<?php echo $row['id'];  ?>">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                            <a href="dashboard-menu.php?confirmar=<?= $row['id'];  ?>" class="cliente-apagar">
                                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 6H5H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>



                        <?php }
                        }
                        ?>

                    </div>
                </div>
            </div>


            <div class="all-main-right" id="gap30">

                <?= $alterarDadosHtml ?>


                <div class="menu-adiconar-categoria">
                    <span class="main-title">Adicionar Categoria
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
                                        <span>Nome Categoria</span>
                                        <input class="global-input" placeholder="Nome Categoria" name="nomecat" id="input-password">
                                    </div>
                                    <div class="global-alterar-box-status">
                                        <span>Status</span>
                                        <label class="switch">
                                            <input value="1" name="status" type="checkbox" <?= $row['status'] == 1 ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button class="global-confirmar-button" type="submit" name="addCategoriaPOST" value="addCategoriaPOST">Adicionar</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

            </div>

        </div>

    </div>

    <?php

    $confirmar = filter_input(INPUT_GET, 'confirmar', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($confirmar) { ?>

        <div class="confirmar">
            <div class="confimar-container">
                <span class="confimar-title">Ao eliminar esta categoria, todos os produtos nela serão eliminados!</span>
                <span class="confimar-subtitle">Deseja continuar?</span>
                <div class="confimar-buttons">
                    <a class="global-confirmar-button" href="dashboard-menu.php">Cancelar</a>
                    <a class="global-apagar-button" href="./crud/delete-menu.php?id=<?= $confirmar  ?>">Eliminar</a>
                </div>
            </div>
        </div>

    <?php
    }
    ?>


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

    <script src="../js/dashboard.js"></script>
</body>

</html>


<?php include './ativarNotification.php'; ?>