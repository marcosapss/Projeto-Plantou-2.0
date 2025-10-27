<?php
// ===== 2FA_USER_PROCESS.PHP - Processador 2FA para Usuários =====

require_once 'db_connect.php';

// Proteção: Verifica se passou pelo primeiro passo do login
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['2fa_user_id'];
    $submitted_email = $_POST['email'] ?? '';

    // Busca o usuário no banco
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND is_admin = 0");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // Se for admin ou não existir, destrói a sessão
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

    // Comparar e-mail enviado com o e-mail do banco (case-insensitive)
    if (strtolower($submitted_email) === strtolower($user['email'])) {
        
        // Sucesso! E-mail correto
        
        // Extrair apenas o primeiro nome
        $nome_completo = $user['nome_completo'] ?? $user['username'];
        $nome_partes = explode(' ', $nome_completo);
        $primeiro_nome = $nome_partes[0];
        
        unset($_SESSION['2fa_user_id']); // Remove ID temporário
        
        // Define as sessões de login permanentes
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nome_usuario'] = $primeiro_nome;
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['is_admin_temp'] = null;
        
        header("Location: ../dashboard.php");
        exit;

    } else {
        // E-mail incorreto
        echo '<div style="text-align: center; padding: 20px; background: #ffe0e0;">';
        echo '<p style="color: #c00;">❌ E-mail de verificação incorreto!</p>';
        echo '<p><a href="../2fa_user_verify.php" style="color: #d97706; text-decoration: none; font-weight: bold;">Tentar novamente</a></p>';
        echo '</div>';
    }
}
?>
