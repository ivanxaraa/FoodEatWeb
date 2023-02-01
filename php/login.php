<?php

// admin@exemplo.ptrestaurante
// abcd1234

session_start();


require_once './config.php';
require_once './core.php';
$html = "";
$arrayRest = array();

//TODOS OS RESTAURANTES
$pdo = connectDB($db);
$sql = "SELECT * FROM restaurante";
$stm = $pdo->query($sql);
while ($row = $stm->fetch()) {
    array_push($arrayRest, $row['email']);
}

$login = filter_input(INPUT_POST, 'login');
if ($login) {
    $pdo = connectDB($db);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
    $html .= debug() ? "<br><code>FORMULÁRIO:<br>email: $email <br> pwd: $password <br> hash: $password_hash_db</code>" : '';


    $verificarCliente = false;
    $errors = false;
    if (!$errors) {
        $sql = "SELECT * FROM `restaurante` WHERE `email` = :EMAIL OR `username` = :EMAIL LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $verificarCliente = true;
        } else {
            $row = $stmt->fetch();
        }
    }

    if ($verificarCliente) {
        //SELECT CLIENTE
        $sql = "SELECT * FROM `cliente` WHERE `email` = :EMAIL OR `username` = :EMAIL LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $notification = MSG_ERRO;
            $notimsg = 'Email/Username não se encontra registado';
            $errors = true;
        } else {
            $row = $stmt->fetch();
        }
    }

    if (!$errors) {
        if (!password_verify($password, $row['password'])) {
            $notification = MSG_ERRO;
            $notimsg = 'Palavra-passe incorreta';
            sleep(random_int(1, 3));
        } else {
            $_SESSION['uid'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['username'] = $row['username'];
            if($row['img']){
                $_SESSION['img'] = $row['img'];
            }

            if (is_adminLogin($arrayRest)) {
                header('Location: dashboard.php');
            } else {
                header('Location: landing.php?rest_id=' . $_SESSION['rest'] . '&mesa=' . $_SESSION['mesa']);
            }

            exit();
        }
    }
}

if (isset($_SESSION['rest'])) {
    $pdo = connectDB($db);
    $sql = "SELECT nome FROM restaurante WHERE id = :REST_ID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":REST_ID", $_SESSION['rest'], PDO::PARAM_INT);
    $stmt->execute();
    $restaurante = $stmt->fetch();
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../css/dashboard-login/login-dashboard.css'>
    <title>FoodEat</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
    <script src="https://kit.fontawesome.com/c3634568e9.js" crossorigin="anonymous"></script>
</head>

<body>


    <div class="login">
        <div class="login-container">        
            <a href="./landing.php" class="login-header">
                <svg width="286" height="44" viewBox="0 0 286 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path id="xxx" d="M20.0352 11.6309H39.1484V15.4316H24.3691V21.0234H36.9199V24.5918H24.3691V33H20.0352V11.6309ZM54.5342 22.2539C54.5342 20.6406 54.8304 19.1641 55.4229 17.8242C56.0153 16.4844 56.8448 15.3359 57.9112 14.3789C58.9867 13.4128 60.2718 12.6654 61.7666 12.1367C63.2705 11.6081 64.934 11.3438 66.7569 11.3438C68.5707 11.3438 70.2295 11.6081 71.7334 12.1367C73.2373 12.6654 74.5225 13.4128 75.5889 14.3789C76.6644 15.3359 77.4984 16.4844 78.0909 17.8242C78.6833 19.1641 78.9795 20.6406 78.9795 22.2539C78.9795 23.8763 78.6833 25.3665 78.0909 26.7246C77.4984 28.0736 76.6644 29.2357 75.5889 30.2109C74.5225 31.1862 73.2373 31.9473 71.7334 32.4941C70.2295 33.0319 68.5707 33.3008 66.7569 33.3008C64.934 33.3008 63.2705 33.0319 61.7666 32.4941C60.2718 31.9473 58.9867 31.1862 57.9112 30.2109C56.8448 29.2357 56.0153 28.0736 55.4229 26.7246C54.8304 25.3665 54.5342 23.8763 54.5342 22.2539ZM58.8682 22.2539C58.8682 23.3568 59.0642 24.3503 59.4561 25.2344C59.8571 26.1185 60.4086 26.875 61.1104 27.5039C61.8213 28.1237 62.6553 28.6022 63.6123 28.9395C64.5785 29.2767 65.6267 29.4453 66.7569 29.4453C67.8871 29.4453 68.9307 29.2767 69.8877 28.9395C70.8539 28.6022 71.6879 28.1237 72.3897 27.5039C73.0915 26.875 73.6429 26.1185 74.044 25.2344C74.445 24.3503 74.6455 23.3568 74.6455 22.2539C74.6455 21.151 74.445 20.1621 74.044 19.2871C73.6429 18.4121 73.0915 17.6738 72.3897 17.0723C71.6879 16.4616 70.8539 15.9967 69.8877 15.6777C68.9307 15.3587 67.8871 15.1992 66.7569 15.1992C65.6267 15.1992 64.5785 15.3587 63.6123 15.6777C62.6553 15.9967 61.8213 16.4616 61.1104 17.0723C60.4086 17.6738 59.8571 18.4121 59.4561 19.2871C59.0642 20.1621 58.8682 21.151 58.8682 22.2539ZM94.9942 22.2539C94.9942 20.6406 95.2904 19.1641 95.8829 17.8242C96.4753 16.4844 97.3048 15.3359 98.3712 14.3789C99.4467 13.4128 100.732 12.6654 102.227 12.1367C103.731 11.6081 105.394 11.3438 107.217 11.3438C109.031 11.3438 110.69 11.6081 112.193 12.1367C113.697 12.6654 114.982 13.4128 116.049 14.3789C117.124 15.3359 117.958 16.4844 118.551 17.8242C119.143 19.1641 119.44 20.6406 119.44 22.2539C119.44 23.8763 119.143 25.3665 118.551 26.7246C117.958 28.0736 117.124 29.2357 116.049 30.2109C114.982 31.1862 113.697 31.9473 112.193 32.4941C110.69 33.0319 109.031 33.3008 107.217 33.3008C105.394 33.3008 103.731 33.0319 102.227 32.4941C100.732 31.9473 99.4467 31.1862 98.3712 30.2109C97.3048 29.2357 96.4753 28.0736 95.8829 26.7246C95.2904 25.3665 94.9942 23.8763 94.9942 22.2539ZM99.3282 22.2539C99.3282 23.3568 99.5242 24.3503 99.9161 25.2344C100.317 26.1185 100.869 26.875 101.57 27.5039C102.281 28.1237 103.115 28.6022 104.072 28.9395C105.038 29.2767 106.087 29.4453 107.217 29.4453C108.347 29.4453 109.391 29.2767 110.348 28.9395C111.314 28.6022 112.148 28.1237 112.85 27.5039C113.552 26.875 114.103 26.1185 114.504 25.2344C114.905 24.3503 115.106 23.3568 115.106 22.2539C115.106 21.151 114.905 20.1621 114.504 19.2871C114.103 18.4121 113.552 17.6738 112.85 17.0723C112.148 16.4616 111.314 15.9967 110.348 15.6777C109.391 15.3587 108.347 15.1992 107.217 15.1992C106.087 15.1992 105.038 15.3587 104.072 15.6777C103.115 15.9967 102.281 16.4616 101.57 17.0723C100.869 17.6738 100.317 18.4121 99.9161 19.2871C99.5242 20.1621 99.3282 21.151 99.3282 22.2539ZM136.712 11.6309H144.546C146.834 11.6309 148.821 11.877 150.507 12.3691C152.193 12.8613 153.588 13.5677 154.691 14.4883C155.803 15.3997 156.627 16.5117 157.165 17.8242C157.712 19.1276 157.985 20.5951 157.985 22.2266C157.985 23.776 157.717 25.2116 157.179 26.5332C156.641 27.8457 155.821 28.985 154.718 29.9512C153.615 30.9082 152.22 31.6602 150.534 32.207C148.848 32.7448 146.861 33.0137 144.573 33.0137L136.712 33V11.6309ZM145.612 29.2539C146.907 29.2539 148.041 29.0898 149.017 28.7617C149.992 28.4245 150.803 27.9505 151.45 27.3398C152.107 26.7292 152.599 25.9909 152.927 25.125C153.255 24.2591 153.419 23.293 153.419 22.2266C153.419 21.1784 153.255 20.235 152.927 19.3965C152.599 18.5488 152.107 17.8333 151.45 17.25C150.803 16.6576 149.992 16.2018 149.017 15.8828C148.041 15.5638 146.907 15.4043 145.612 15.4043H141.046V29.2539H145.612ZM174.793 11.6309H192.99V15.4316H179.127V19.6973H191.404V23.2656H179.127V29.2129H193.277V33H174.793V11.6309ZM217.837 11.6309H222.431L232.767 33H228.214L226.505 29.4043H214.023L212.368 33H207.802L217.837 11.6309ZM224.796 25.8359L220.175 16.1289L215.677 25.8359H224.796ZM254.989 15.4316H247.1V11.6309H267.211V15.4316H259.322V33H254.989V15.4316Z" fill="black" />
                    <path d="M272 1H285V11" stroke="black" stroke-width="2" />
                    <path d="M272 43H285V33" stroke="black" stroke-width="2" />
                    <path d="M14 1H1V11" stroke="black" stroke-width="2" />
                    <path d="M14 43H1V33" stroke="black" stroke-width="2" />
                </svg>
                <? if (isset($_SESSION['rest'])) { ?>
                    <span class="login-header-rest"><?= $restaurante['nome'] ?></span>
                <? } ?>
            </a>
            <form action="?" method="POST">
                <div class="login-content">
                    <span>Login</span>
                    <div class="login-input">
                        <input type="text" placeholder="* Username/Email" name="email" id="input-text">
                        <input type="password" placeholder="* Password" name="password" id="input-password">
                    </div>
                    <button type="submit" name="login" value="login">Entrar</button>
            </form>
            <div class="login-register">
                <span>Não tem conta? <a href="cliente-register.php">registar</a></span>
                <span>Quer trabalhar conosco? <a href="register.php">começar</a></span>
                <!-- <?php if(isset($_SESSION['rest'])){ ?>
                    <span>Não tem conta? <a href="./cliente-register.php">registar</a></span>
                <?php }else{ ?>
                    <span>Quer começar a utilizar? <a href="register.php">registar</a></span>
                <?php } ?> -->
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

    <script src="../js/errors.js"></script>
</body>

</html>

<?php include './ativarNotification.php'; ?>