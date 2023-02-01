<?php

// admin@exemplo.ptrestaurante
// abcd1234

session_start();
$html = '';

require_once '../config.php';
require_once '../core.php';

$login = filter_input(INPUT_POST, 'login');
if ($login) {
    $pdo = connectDB($db);
    $html .= debug() ? '<p>Utilizador: <code>' . $db['username'] . '</code> Base de Dados: <code>' . $db['dbname'] . '</code></p>' : '';

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
    $html .= debug() ? "<br><code>FORMULÁRIO:<br>email: $username <br> pwd: $password <br> hash: $password_hash_db</code>" : '';

    $errors = false;
    if (!filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS)) {
        $html .= '<div class="container alert-danger">O username não é válido.</div>';
        $errors = true;
    }

    if (!$errors) {
        $sql = "SELECT * FROM `cliente` WHERE `username` = :USER OR `email` = :USER LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":USER", $username, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $html .= '<div class="container alert-danger">O username indicado não se encontra registado.</div>';
            $errors = true;
        } else {
            $row = $stmt->fetch();
            $html .= debug() ? '<br><code>BASE DE DADOS:<br>id: ' . $row['id'] . '<br> username: ' . $row['username'] . '<br> password: ' . $row['password'] . '</code>' : '';
        }
    }

    if (!$errors) {
        if (!password_verify($password, $row['password'])) {
            $html .= '<div class="container alert-danger">Palavra-passe incorreta.</div>';
            sleep(random_int(1, 3));
        } else {
            $_SESSION['uid'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['username'] = $row['username'];
            $html .= '<div class="container alert-success">Login com sucesso! <br> <b>' . $_SESSION['username'] . '</b></div>';
            $html .= '<div class="container alert-success"><a href="index.php" class="btn btn-primary">Continuar</a></div>';
            header('Location: ../landing.php');
            exit();
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Login</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>    
    <link rel='stylesheet' type='text/css' media='screen' href='../../css/variaveis.css'>
    <link rel='stylesheet' type='text/css' media='screen' href='../../css/cliente-login/cliente-login.css'>    
</head>
<body>

    <div class="login">  
        <div class="login-double">
            <div class="login-container">
                <div class="login-header">
                    <img src="../../img/img2.png">
                    <span>Chentric</span>                
                </div>
                <div class="login-container-padding">             
                    <form action="?" method="POST">
                        <div class="login-content">
                            <span>Login</span>
                            <div class="login-input">
                                <input type="text" placeholder="* Username" name="username" id="username" require>
                                <input type="password" placeholder="* Password" name="password" id="password" require>
                            </div>                
                        <button type="submit" name="login" value="login">Entrar</button>
                    </form>            
                    <div class="login-register">
                        <span>Não tem conta? <a href="./cliente-register.php">registar-se</a></span>
                    </div>
                </div>
            </div>            
        </div> 
          
        <?= $html ?>
        <?= debug() ? '<div><code>POST: ' . print_r($_POST, true) . '<br>GET: ' . print_r($_GET, true) . '<br>SESSION: ' . print_r($_SESSION, true) . '</code></div>' : '' ?>
    </div> 
    
    <script src=''></script>
</body>
</html>