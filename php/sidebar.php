<?php 
if(!(isset($_SESSION['uid']))){
session_start();
}
include 'verificarAdmin.php';

//ler dados restaurante
$pdo = connectDB($db);
$sql = "SELECT * FROM `restaurante` WHERE `id` = :id";

$stm = $pdo->prepare($sql);
$stm->bindValue(':id', $_SESSION['uid']);
$result = $stm->execute();

if ($result) {
    $row = $stm->fetch();
}
//

$current_url = basename($_SERVER['PHP_SELF']);
$current_url = str_replace(' ', '', $current_url);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>    
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='../css/variaveis.css'>
    <link rel='stylesheet' href='../css/sidebar.css'>    
    <script src="https://kit.fontawesome.com/c3634568e9.js" crossorigin="anonymous"></script>
    <title>FoodEat</title>
    <link rel="icon" href="../img/LogoFoodEatPequeno.svg">
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-container">
            <div class="siderbar-top">
                <div class="sidebar-profile">
                    <div class="sidebar-image"> <img src="<?= RESTIMG_WEB_PATH ?><?= $row['img'] != NULL ? $row['img'] : RESTIMG_DEFAULT ?>"> </div>
                    <div class="sidebar-text">                                     
                        <span class="sidebar-text-1"><?php echo $_SESSION['username'] ?></span>                    
                        <span class="sidebar-text-2"><?php echo $_SESSION['email'] ?></span>                    
                    </div>
                </div>

                <div class="sidebar-links">
                    <a class="sidebar-link <?= $current_url == 'dashboard.php' ? 'active' : ''; ?>" id="sidebar-link1" href="../php/dashboard.php">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>Dashboard</span>
                    </a>
                    <a class="sidebar-link <?= $current_url == 'dashboard-menu.php' ? 'active' : ''; ?>" id="sidebar-link2" href="../php/dashboard-menu.php">
                        <i class="fa-solid fa-book"></i>
                        <span>Menu</span>
                    </a>
                    <a class="sidebar-link <?= $current_url == 'mesas.php' ? 'active' : ''; ?>" id="sidebar-link3" href="../php/mesas.php">
                        <i class="fa-sharp fa-solid fa-qrcode"></i>
                        <span>Mesas</span>
                    </a>
                    <a class="sidebar-link <?= $current_url == 'pedidos.php' ? 'active' : ''; ?>" id="sidebar-link4" href="../php/pedidos.php">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>Pedidos</span>
                    </a>
                    <a class="sidebar-link <?= $current_url == 'empregados.php' ? 'active' : ''; ?>" id="sidebar-link5" href="../php/empregados.php">
                        <i class="fa-solid fa-users"></i>
                        <span>Funcionários</span>
                    </a>
                    <a class="sidebar-link <?= $current_url == 'clientes.php' ? 'active' : ''; ?>" id="sidebar-link6" href="../php/clientes.php">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Clientes</span>
                    </a>
                </div>
            </div>
            <div class="sidebar-end">
                <a class="sidebar-link <?= $current_url == 'settings.php' ? 'active' : ''; ?>" id="sidebar-link7" href="../php/settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
                <a class="sidebar-link" href="./logout.php" id="sidebar-link8" href="../php/dashboard.php">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        
    </script>
    
</body>
</html>