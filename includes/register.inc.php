<?php
// ===== REGISTER.INC.PHP - Registro de Novos Usuários =====

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados pessoais
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $motherName = $_POST['motherName'] ?? '';
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    
    // Endereço
    $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $neighborhood = $_POST['neighboorhood'] ?? '';
    $street = $_POST['street'] ?? '';
    $number = $_POST['number'] ?? '';
    $complement = $_POST['complement'] ?? '';
    $uf = $_POST['uf'] ?? '';
    
    // Autenticação
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    // Validar campos vazios (obrigatórios)
    $campos_obrigatorios = [
        'name' => $name,
        'email' => $email,
        'birth' => $birth,
        'gender' => $gender,
        'motherName' => $motherName,
        'cpf' => $cpf,
        'phone' => $phone,
        'cep' => $cep,
        'city' => $city,
        'state' => $state,
        'neighborhood' => $neighborhood,
        'street' => $street,
        'number' => $number,
        'uf' => $uf,
        'password' => $password,
        'confirmPassword' => $confirmPassword
    ];

    $campos_faltando = [];
    foreach ($campos_obrigatorios as $campo => $valor) {
        if (empty($valor)) {
            $campos_faltando[] = $campo;
        }
    }

    if (!empty($campos_faltando)) {
        echo '<div class="erro">Preencha todos os campos! Faltando: ' . implode(', ', $campos_faltando) . '</div>';
        exit;
    }

    // Validar senhas iguais
    if ($password !== $confirmPassword) {
        echo '<div class="erro">As senhas não coincidem!</div>';
        exit;
    }

    // Gerar username baseado no email
    $username = explode('@', $email)[0];
    
    // Extrair apenas o primeiro nome
    $nome_partes = explode(' ', $name);
    $primeiro_nome = $nome_partes[0];

    // Validar se usuário ou e-mail já existem
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        echo '<div class="erro">Erro: Nome de usuário ou e-mail já existe!</div>';
        exit;
    }

    // Criar hash da senha
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserir no banco
    try {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (
                username, email, password_hash, nome_completo, 
                data_nascimento, genero, nome_mae, cpf, celular, 
                cep, cidade, bairro, rua, numero, complemento, uf, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $username, $email, $password_hash, $name,
            $birth, $gender, $motherName, $cpf, $phone,
            $cep, $city, $neighborhood, $street, $number, $complement, $uf, $state
        ]);
        
        // Redirecionar para login com mensagem de sucesso
        $_SESSION['registro_sucesso'] = 'Usuário registrado com sucesso! Faça login agora.';
        echo '<script>
            alert("✅ Registro realizado com sucesso!\\n\\nUsername: ' . $username . '\\nEmail: ' . $email . '\\n\\nAgora faça login com suas credenciais.");
            window.location.href = "../login.php";
        </script>';
        exit;
    } catch (PDOException $e) {
        echo '<div class="erro">Erro ao registrar: ' . $e->getMessage() . '</div>';
    }
}
?>

