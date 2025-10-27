<?php
// ===== DB_CONNECT.PHP - Conexão com Banco de Dados Plantou! =====

// Habilitar exibição de erros (bom para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de conexão com MySQL
$host = '127.0.0.1'; // localhost
$db   = 'plantou_db'; // Nome do banco de dados
$port = 3307; // Porta do MySQL
$user = 'root'; // Usuário do MySQL
$pass = '123'; // Senha do MySQL (conforme sua configuração)
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

// Opções de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Conectar ao banco de dados
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Inicia a sessão em todos os arquivos que incluírem este
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
