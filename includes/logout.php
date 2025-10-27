<?php
// ===== LOGOUT.PHP - Deslogar Usuário =====

// Inicia a sessão para poder destruí-la
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para a página inicial
header("Location: ../index.php");
exit;
?>
