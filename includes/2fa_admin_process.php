<?php
session_start();

// Verificar se existe um 2fa_user_id na sessão
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Incluir conexão com banco de dados
require_once 'db_connect.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = trim($_POST['2fa_code'] ?? '');
    
    // Obter dados do usuário da sessão temporária
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND is_admin = 1");
        $stmt->execute([$_SESSION['2fa_user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header("Location: ../2fa_user_verify.php?error=usuario_nao_encontrado");
            exit;
        }
        
        // Verificar o código 2FA
        // O código estático para admin é armazenado no banco de dados
        if ($submitted_code === $user['2fa_secret_code']) {
            // Código correto - setar sessão permanente
            
            // Extrair apenas o primeiro nome
            $nome_completo = $user['nome_completo'] ?? $user['username'];
            $nome_partes = explode(' ', $nome_completo);
            $primeiro_nome = $nome_partes[0];
            
            unset($_SESSION['2fa_user_id']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nome_usuario'] = $primeiro_nome;
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['is_admin_temp'] = null;
            
            // Redirecionar para dashboard do admin
            header("Location: ../admin.php");
            exit;
        } else {
            // Código incorreto
            header("Location: ../2fa_user_verify.php?error=codigo_incorreto");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro 2FA Admin: " . $e->getMessage());
        header("Location: ../2fa_user_verify.php?error=erro_sistema");
        exit;
    }
} else {
    header("Location: ../2fa_admin_verify.php");
    exit;
}
?>
