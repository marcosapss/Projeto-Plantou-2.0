<?php
// ===== LOGIN.INC.PHP - Autenticação com 2FA =====

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'] ?? $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar campos vazios
    if (empty($login) || empty($password)) {
        echo '<div class="erro">Preenc ha todos os campos.</div>';
        exit;
    }

    // Buscar usuário no banco por USERNAME OU EMAIL
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    // Validar usuário e senha
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // Extrair apenas o primeiro nome
        $nome_completo = $user['nome_completo'] ?? $login;
        $nome_partes = explode(' ', $nome_completo);
        $primeiro_nome = $nome_partes[0];
        
        // Salva dados na sessão temporária
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['temp_nome_usuario'] = $primeiro_nome;
        $_SESSION['temp_email'] = $user['email'];

        // Decidir qual 2FA aplicar
        if ($user['is_admin']) {
            // Admin: Manda para verificador de CÓDIGO ESTÁTICO (2FA)
            $_SESSION['2fa_admin_id'] = $user['id'];
            $_SESSION['is_admin_temp'] = 1;
            header("Location: ../2fa_admin_verify.php");
            exit;
        } else {
            // Usuário comum: Manda para verificador de E-MAIL
            $_SESSION['2fa_user_id'] = $user['id'];
            $_SESSION['is_admin_temp'] = 0;
            header("Location: ../2fa_user_verify.php");
            exit;
        }

    } else {
        // Falha no login
        echo '<div class="erro">Usuário ou senha inválidos!</div>';
    }
}
?>

