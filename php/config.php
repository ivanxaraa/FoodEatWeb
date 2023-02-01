<?php


// admin@exemplo.pt
// abcd1234

//esan-dsg09-dbo
//GUQ5bXxyYQplf7Rl

$guru='09';
$dsg_dbo = [
    'host' => 'mysql-sa.mgmt.ua.pt',
    'port' => '3306',
    'charset' => 'utf8',    
    'dbname' => 'esan-dsg'.$guru,
    'username' => 'esan-dsg'.$guru.'-dbo',
    'password' => 'GUQ5bXxyYQplf7Rl'
];
$dsg_web = [
    'host' => 'mysql-sa.mgmt.ua.pt',
    'port' => '3306',
    'charset' => 'utf8',
    'dbname' => 'esan-dsg'.$guru,
    'username' => 'esan-dsg'.$guru.'-web',
    'password' => 'TJOUNbJO2AwnQaTo'
];


// Descomentar o utilizador pretendido: DBO ou WEB
$db = $dsg_dbo;
#$db = $dsg_web;

define('DEBUG', true);

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

//VARIAVEIS
define('MSG_SUCESSO', 'Sucesso!');

define('MSG_ADICIONADO', ' criado com sucesso!');
define('MSG_EDITADO', ' editado com sucesso!');
define('MSG_APAGADO', 'Eliminado com sucesso!');

define('MSG_ADICIONADO_FEM', ' criada com sucesso!');
define('MSG_EDITADO_FEM', ' editada com sucesso!');
define('MSG_APAGADO_FEM', 'Eliminada com sucesso!');

//ERRO
define('MSG_ERRO', 'Oops...!');
define('MSG_ERRO_DEFAULT', 'Não foi possivel!');
define('MSG_ERRO_EXISTE', ' que tentou criar já existe!');


//FIM VARIAVEIS
define('WEB_SERVER', 'https://esan-tesp-ds-paw.web.ua.pt');
define('WEB_ROOT', '/tesp-ds-g9/');
define('SERVER_FILE_ROOT', '//ARCA.STORAGE.UA.PT/HOSTING/esan-tesp-ds-paw.web.ua.pt' . WEB_ROOT);
define('UPLOAD_FOLDER', 'uploads/');
define('UPLOAD_PATH', SERVER_FILE_ROOT . UPLOAD_FOLDER);


define('PRODUTOIMG_FOLDER', UPLOAD_FOLDER . 'produtos/');
define('PRODUTOIMG_PATH', SERVER_FILE_ROOT . PRODUTOIMG_FOLDER);
define('PRODUTOIMG_WEB_PATH', WEB_ROOT . PRODUTOIMG_FOLDER);
define('PRODUTOIMG_DEFAULT', 'default.png');

define('RESTIMG_FOLDER', UPLOAD_FOLDER . 'restaurantes/');
define('RESTIMG_PATH', SERVER_FILE_ROOT . RESTIMG_FOLDER);
define('RESTIMG_WEB_PATH', WEB_ROOT . RESTIMG_FOLDER);
define('RESTIMG_DEFAULT', 'default.png');

define('CLIENTEIMG_FOLDER', UPLOAD_FOLDER . 'clientes/');
define('CLIENTEIMG_PATH', SERVER_FILE_ROOT . CLIENTEIMG_FOLDER);
define('CLIENTEIMG_WEB_PATH', WEB_ROOT . CLIENTEIMG_FOLDER);
define('CLIENTEIMG_DEFAULT', 'default.png');



define('ATTACHMENTS_PATH', SERVER_FILE_ROOT . UPLOAD_FOLDER . 'attach/');




//EMAIL 

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
    define('EMAIL_CHARSET', 'UTF-8');
    define('EMAIL_ENCODING', 'base64');
    define('EMAIL_HOST', 'smtp-servers.ua.pt');
    define('EMAIL_SMTPAUTH', true);
    define('EMAIL_USERNAME', 'ivanxara@ua.pt');
    define('EMAIL_PASSWORD', 'Vacamaluca123%');
    define('EMAIL_PORT', 25);
    define('EMAIL_FROM', 'Projeto Desenvolvimento de Software | ESAN');

    define('WEB_ROOT_IMG_EMAIL', 'https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g9/uploads/produtos/');



