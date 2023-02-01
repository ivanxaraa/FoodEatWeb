<?php
define('DESC', 'Apagar registos duma tabela da Bases de Dados');
define('UC', 'PAW');
$html = '';

function slugify($text = '')
{
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
$filename = '';

require_once '../config.php';
require_once '../core.php';

$pdo = connectDB($db);


$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$cat_id = filter_input(INPUT_GET, 'id_cat', FILTER_SANITIZE_NUMBER_INT);
$cat_nome = filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if($id != ''){
    $sql = "DELETE FROM `produto` where `id` = :id";    
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':id', (int)$id);

    // APAGAR IMAGEM DA PASTA
    $sql = "SELECT img FROM produto WHERE id = :id;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', (int)$id);
    $stmt->execute();
    $filename = $stmt->fetch();

    if ($filename['img']) {

        $filepath = PRODUTOIMG_PATH . $filename['img'];
    
        if (is_file($filepath)) {
            unlink($filepath);            
        }
    }
    // FIM APAGAR IMAGEM DA PASTA

    $result = $stm->execute();

    if($result){
        $notification = MSG_SUCESSO;
        $notimsg = 'Erro ao inserir na Base de Dados';
        header('Location: ../produtos.php?id='.$cat_id.'&nome='.$cat_nome.'&deleted='.MSG_SUCESSO.'&msg='.MSG_APAGADO);
    } else {
        $html .= 'Erro!';
    }
}


?>
<!DOCTYPE html>
<html>
    <!-- 
    <head>        
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

-->
</html>