<?php
if (!(isset($_SESSION['category'])) AND !(isset($_SESSION['rest']))){
    session_start();
}


require_once './config.php';
require_once './core.php';


$pdo = connectDB($db);
$sql = "SELECT produto.* from produto INNER JOIN categoria ON produto.Categoria_id = categoria.id WHERE categoria.id = :CAT_ID AND produto.status = 1";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":CAT_ID", $_SESSION['category'], PDO::PARAM_INT);
$stmt->execute();
while ($row2 = $stmt->fetch()) {
    if ($row2['nome']) { ?>
        <div class="menu-box" name="popup" value="popup" data-modal="<?= $row2['id']; ?>">
            <div class="menubox-container">
                <div class="menubox-spacebtw">
                    <div class="menubox-left">
                        <div class="menubox-image">
                            <img src="<?= PRODUTOIMG_WEB_PATH ?><?= $row2['img'] != NULL ? $row2['img'] : PRODUTOIMG_DEFAULT ?>" />
                        </div>
                        <div class="menubox-text">
                            <span class="menubox-title"><?= $row2['nome'];  ?></span>
                            <span class="menubox-price"><?= $row2['preco'];  ?> €</span>
                        </div>
                    </div>
                    <svg class="menubox-icon" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="chevron-up">
                            <path id="Vector" d="M6.75098 13.4993L11.251 8.99927L6.75098 4.49927" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                    </svg>
                </div>
            </div>
        </div>
<?php }
}
?>

<script>
    var modalBtns = document.querySelectorAll("div.menu-box");
    modalBtns.forEach(function(btn) {
        btn.onclick = function() {
            counts = 1;
            $(".counts").text(counts);
            var modal = btn.getAttribute("data-modal");
            document.getElementById(modal).style.display = "block";
        };
    });
</script>