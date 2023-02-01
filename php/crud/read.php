<?php
define('DESC', 'Ver registo da Bases de Dados');
define('UC', 'PAW');
$html = '';


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