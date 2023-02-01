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

require_once '../phpqrcode/qrlib.php';
$criarMesa = filter_input(INPUT_POST, 'criarMesa');
if ($criarMesa) {

    $link = WEB_SERVER . dirname($_SERVER['PHP_SELF']) . '/landing.php';
    $mesa = filter_input(INPUT_POST, 'numeroMesa', FILTER_SANITIZE_NUMBER_INT);

    if(strlen($mesa) > 3){
        $errors = true;
        $notification = MSG_ERRO;
        $notimsg = 'A mesa deve ter no maximo 3 digitos';
    }
    
    if(!$errors){

        $link .= '?rest_id=' . $_SESSION['uid'];
        $link .= '&mesa=' . $mesa;
        

        $sql = "SELECT id FROM mesa WHERE numero = :numero AND Restaurante_id = :REST_ID LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":numero", $mesa, PDO::PARAM_INT);
        $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);        
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $notification = MSG_ERRO;
            $notimsg = 'A mesa' . MSG_ERRO_EXISTE;
            $errors = true;
        }

        if($errors != true){
            $qrCodeImage = generateQRCode($link);
            $estado = "fechado";
            $sql = "INSERT INTO mesa(numero, tableCode, link, estado, Restaurante_id) VALUES(:numero, :qrcode, :link, :estado, :REST_ID)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":numero", $mesa, PDO::PARAM_INT);
            $stmt->bindValue(":qrcode", $qrCodeImage);
            $stmt->bindValue(":link", $link);
            $stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
            $stmt->execute();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
    }
}   

function generateQRCode($link)
{

    QRcode::png($link);
    ob_start();
    QRcode::png($link);
    $imageData = ob_get_clean();
    $imageData = base64_encode($imageData);
    return 'data:image/png;base64,' . $imageData;
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>FoodEat - Mesas</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>
    <link rel='stylesheet' href='../css/dashboard/dashboard-mesas.css'>
</head>

<body>


    <?php include('sidebar.php'); ?>



    <div class="main">

        <div class="main-double">

            <div class="all-main-left">
                <div class="menu-categorias">
                    <span class="main-title">Mesas</span>
                    <div class="main-content">
                    <div class="dashboard-mesas">
                        <?php

                            $sql = "SELECT * FROM mesa WHERE Restaurante_id = :REST_ID ORDER BY estado";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindValue(":REST_ID", $_SESSION['uid'], PDO::PARAM_INT);
                            $stmt->execute();
                            while ($row = $stmt->fetch()) { ?>

                                <div class="dashboard-mesa">
                                    <div class="dashboard-mesa-details">
                                        <a class="dashboard-mesa-delete" id="IconDelete" href="mesas.php?confirmar=<?= $row['id'];  ?>">
                                            <i class="fa-solid fa-xmark"></i>
                                        </a>
                                        <a class="dashboard-mesa-delete" id="IconDefault" target="_blank" href="<?= $row['link'] ?>">
                                            <i class="fa-solid fa-link"></i>
                                        </a>
                                        <a class="dashboard-mesa-delete" id="IconDefault" download href="<?= $row['tableCode'] ?>">
                                            <i class="fa-solid fa-download"></i>
                                        </a> 
                                    </div>
                                    <div class="dashboard-mesa-container">
                                        <a href="<?= $row['tableCode'] ?>" download class="dashboard-mesa-image"><img src="<?= $row['tableCode'] ?>" alt="QrCode"></a>
                                        <div class="dashboard-mesa-content">
                                            <?php

                                            if ($row['estado'] != "fechado") {
                                            ?> <div class="global-data-status"></div> <?php
                                                            } else {
                                                                ?> <div class="global-data-status" style="background-color: #0e0e0e;"></div> <?php
                                                                                                        }

                                                                                                            ?>
                                            <div class="dashboard-mesa-title">Mesa <?= $row['numero']; $row['estado']; ?> </div>
                                        </div>
                                    </div>
                                </div>

                        <?php } ?>
                    </div>
                    </div>
                </div>
            </div>


            <div class="all-main-right">

                <div class="menu-adiconar-categoria">
                    <span class="main-title">Adicionar Mesa
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
                                        <span>Número de mesa</span>
                                        <input type="number" class="global-input" placeholder="Numero Mesa" name="numeroMesa" id="numeroMesa" required>
                                    </div>                                    
                                </div>
                                <div class="global-alterardados-buttons">
                                    <button class="global-confirmar-button" type="submit" name="criarMesa" value="criarMesa">Adicionar</button>
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
                <span class="confimar-title">Ao eliminar esta mesa, todos os pedidos nela serão eliminados!</span>
                <span class="confimar-subtitle">Deseja continuar?</span>
                <div class="confimar-buttons">
                    <a class="global-confirmar-button" href="mesas.php">Cancelar</a>
                    <a class="global-apagar-button" href="./crud/delete-mesa.php?id=<?= $confirmar  ?>">Eliminar</a>
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



    <script src="../js/clientes.js"></script>
</body>

</html>

<?php include './ativarNotification.php'; ?>