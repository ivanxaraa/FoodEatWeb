<?php
session_start();
require_once '../config.php';
require_once '../core.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



$numPedido = filter_input(INPUT_GET, 'numeroPedido');
//PRECO TOTAL
$PedidoPrecoTotal = 0.00;
foreach ($_GET as $key => $value) {
    if (is_array($value)) {
        $PedidoPrecoTotal = $PedidoPrecoTotal + $value['preco'];
    }
}

//selecionar dados do cliente
$pdo = connectDB($db);
$sql = "SELECT * FROM cliente WHERE id = :ID";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":ID", $_SESSION['uid'], PDO::PARAM_INT);
$result = $stmt->execute();
if ($result) {
    $cliente = $stmt->fetch();
}

$email = $cliente['email'];
$name = $cliente['username'];
$nomeRest = $_SESSION['restNome'];
$subject = $nomeRest . " - Pedido Feito! #" . $numPedido;
$emailUsername = "FoodEat - QrCode";


if ($email != '' && $name != '') {

    require './objects/PHPMailer/PHPMailer.php';
    require './objects/PHPMailer/SMTP.php';
    require './objects/PHPMailer/Exception.php';

    // Criação de um email. `true` ativa exceptions
    $mail = new PHPMailer(true);
    // $body .= "<h3>Pedido #$numPedido</h3>";
    // $body .= "<p>O seu pedido:</p>";
    // $body .= "-------------------<br>";

    // foreach ($_GET as $key => $value) {
    //     if (is_array($value)) {
    //         $body .= "Produto: " . $value['nome'] . "<br>";
    //         $body .= "Preço: " . $value['preco'] . " € <br>";
    //         $body .= "Quantidade: " . $value['quant'] . "<br>";
    //         $body .= "-------------------<br>";
    //     }
    // }
    // $body .= "<br>Total Pedido: " . $PedidoPrecoTotal . ' €';

    $idPedido = $numPedido;    
    ob_start();
    include('email-design.php');   
    $body = ob_get_contents();
    ob_get_clean();  
    

    try {
        /**
         Mailer:SMTP
        From email:[dep]-[nome]@ua.pt
        From Name : [nome que aparece nos e-mail enviados]
        SMTP Authentication: YES
        SMTP Security: TLS
        SMTP Port: 25
        SMPT Username: [dep]-[nome]@ua.pt
        SMTP Password: [senha de acesso à conta referida no SMTP Username]
        SMTP Host: smtp-servers.ua.pt
         *
        Nome:       Projeto Desenvolvimento de Software | ESAN
        e-mail:     esan-tesp-ds-paw@ua.pt
        login:      esan-tesp-ds-paw@ua.pt
        password:   8ee83a66c46001b7ee7b3ee886bf8375

         */
        if (DEBUG) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        $mail->CharSet = EMAIL_CHARSET;                                   // Charset
        $mail->Encoding = EMAIL_ENCODING;                                 // Encode
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host = EMAIL_HOST;                  // Set the SMTP server to send through
        $mail->SMTPAuth = EMAIL_SMTPAUTH;                       // Enable SMTP authentication
        $mail->Username = EMAIL_USERNAME;                // SMTP username
        $mail->Password = EMAIL_PASSWORD;       // SMTP password
        $mail->SMTPSecure = PHPMAILER::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_STARTTLS`
        $mail->Port = EMAIL_PORT;                                           // TCP port to connect to, use 587 for gmail
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;          // Enable SSL encryption; `PHPMailer::ENCRYPTION_SMTPS`
        //$mail->Port = 465;                                        // TCP port to connect to, use 465 for gmail
        // Destinatários
        $mail->setFrom(EMAIL_USERNAME, $emailUsername);              // Set From
        $mail->addAddress($email);              // Add a recipient
        //$mail->addReplyTo(EMAIL_USERNAME);
        //$mail->addCC(EMAIL_USERNAME);
        $mail->addBCC(EMAIL_USERNAME);
        // Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        // Content
        $mail->isHTML(true);                                    // Set email format to HTML
        $mail->Subject = $subject;                            // Set Subject
        $mail->Body = $body;                  // Set message body
        //$mail->AltBody = $body;

        $mail->send();                    // Send the email
        $response = '<div class="alert-success">Mensagem enviada!</div>';
        //header('Location: ../landing.php?rest_id=' . $_SESSION['rest'] . '&mesa=' . $_SESSION['mesa'] . '&pedidoFeito=1');
    } catch (Exception $e) {
        $response = '<div class="alert-danger">Mensagem não enviada. Mailer Error: ' . $mail->ErrorInfo . '</div>';
    }
    header('Location: ../landing.php?rest_id=' . $_SESSION['rest'] . '&mesa=' . $_SESSION['mesa'] . '&pedidoFeito=1');
    exit();
}
