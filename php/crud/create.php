<?php
define('DESC', 'Inserir registo numa tabela da Bases de Dados');
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
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="codPostal">Código Postal</label>
                        <input type="text" class="form-control" name="codPostal" id="codPostal" placeholder="Código Postal">
                    </div>
                    <div class="form-group">
                        <label for="code">Código de acesso</label>
                        <input type="password" class="form-control" name="code" id="code" aria-describedby="codeHelp" placeholder="Código">
                        <small id="codeHelp" class="form-text text-muted">Introduza o código para autorizar a criação da tabela</small>
                    </div>                    
                    <input type="submit" class="btn btn-primary" value="Inserir">
                </form>
            </div>
            <div>
                <?= $html ?>
            </div>
            <?= debug() ? '<div><code>POST: ' . print_r($_POST, true) . '<br>GET: ' . print_r($_GET, true) . '</code></div>' : '' ?>
        </div>
    </body>
</html>