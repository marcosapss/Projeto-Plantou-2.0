<?php
session_start();
require_once 'includes/db_connect.php';

// Verificar se está na etapa correta
if (!isset($_SESSION['2fa_admin_id']) || !$_SESSION['is_admin_temp']) {
    header("Location: login.php");
    exit;
}

$error_message = '';
$success_message = '';

// Processar verificação de código 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    
    if (empty($code)) {
        $error_message = "Por favor, insira o código de verificação.";
    } else {
        // Buscar código 2FA do admin no banco de dados
        $stmt = $pdo->prepare("SELECT code_2fa FROM usuarios WHERE id = ? AND is_admin = 1");
        $stmt->execute([$_SESSION['2fa_admin_id']]);
        $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin_data) {
            $error_message = "❌ Admin não encontrado.";
        } else {
            $valid_code = $admin_data['code_2fa'] ?? '123456';
            
            if ($code === $valid_code) {
                // Código correto - estabelecer sessão
                $stmt = $pdo->prepare("SELECT id, username, email FROM usuarios WHERE id = ? AND is_admin = 1");
                $stmt->execute([$_SESSION['2fa_admin_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Estabelecer sessão do admin
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nome_usuario'] = explode(' ', $user['username'])[0]; // Primeiro nome
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['is_admin'] = 1;
                    
                    // Limpar variáveis temporárias
                    unset($_SESSION['2fa_admin_id']);
                    unset($_SESSION['is_admin_temp']);
                    
                    $success_message = "✅ Verificação bem-sucedida! Redirecionando...";
                    header("Refresh: 2; url=admin.php");
                } else {
                    $error_message = "Erro ao recuperar dados do usuário.";
                }
            } else {
                $error_message = "❌ Código de verificação incorreto. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação 2FA - Plantou</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .verify-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .verify-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .verify-header h1 {
            font-size: 24px;
            color: #1a1a1a;
            margin-bottom: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        .verify-header p {
            color: #666;
            font-size: 14px;
        }

        .verify-icon {
            font-size: 48px;
            color: #4caf50;
            margin-bottom: 15px;
        }

        .admin-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
            letter-spacing: 2px;
            text-align: center;
            font-weight: 600;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
        }

        .btn-verify {
            width: 100%;
            padding: 12px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-verify:hover {
            background: #45a049;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .info-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            line-height: 1.6;
        }

        .info-box strong {
            color: #333;
        }

        .loading {
            display: none;
            text-align: center;
            color: #4caf50;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4caf50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .verify-card {
                padding: 30px 20px;
            }

            .verify-header h1 {
                font-size: 20px;
            }

            .verify-icon {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="verify-header">
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i> ADMIN
                </div>
                <div class="verify-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Verificação 2FA</h1>
                <p>Insira o código de segurança</p>
            </div>

            <?php if ($error_message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
                <div class="loading" style="display: block;">
                    <div class="spinner"></div>
                    Redirecionando para o painel...
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="code">Código de Verificação:</label>
                        <input 
                            type="text" 
                            id="code" 
                            name="code" 
                            placeholder="000000" 
                            maxlength="6" 
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn-verify">
                        <i class="fas fa-check"></i> Verificar Código
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
