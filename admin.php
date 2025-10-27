<?php
session_start();

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// Incluir conexão com banco de dados
require_once 'includes/db_connect.php';

// Obter todas as doações, árvores e projetos
try {
    $stmt = $pdo->prepare("SELECT * FROM doacoes ORDER BY data_doacao DESC");
    $stmt->execute();
    $doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM arvores ORDER BY id DESC");
    $stmt->execute();
    $arvores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM projetos ORDER BY id DESC");
    $stmt->execute();
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar dados: " . $e->getMessage());
    $doacoes = [];
    $arvores = [];
    $projetos = [];
}

// Determinar qual aba mostrar
$aba_ativa = $_GET['aba'] ?? 'doacoes';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Plantou!</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>

    <section class="hero" style="background: var(--deep-wood); padding: 40px 0; text-align: center;">
        <div class="container">
            <h1 style="color: var(--cream-white); margin-bottom: 10px;">
                PAINEL ADMINISTRATIVO
            </h1>
            <p style="color: var(--light-soil); font-size: 1.1rem;">
                Gerenciar Plantou!
            </p>
        </div>
    </section>

    <section class="content" style="padding: 40px 0;">
        <div class="container">
            <!-- ABAS DE NAVEGAÇÃO -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
                <a href="admin.php?aba=doacoes" class="btn <?php echo ($aba_ativa === 'doacoes') ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none; display: inline-block;">DOAÇÕES</a>
                <a href="admin.php?aba=arvores" class="btn <?php echo ($aba_ativa === 'arvores') ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none; display: inline-block;">ÁRVORES</a>
                <a href="admin.php?aba=projetos" class="btn <?php echo ($aba_ativa === 'projetos') ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none; display: inline-block;">PROJETOS</a>
                <a href="admin.php?aba=admins" class="btn <?php echo ($aba_ativa === 'admins') ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none; display: inline-block; background: var(--accent-orange);">👨‍💼 ADMINS</a>
            </div>

            <!-- MENSAGENS DE SUCESSO/ERRO -->
            <?php 
            $success = $_GET['success'] ?? null;
            $error = $_GET['error'] ?? null;
            
            $messages = [
                'admin_criado' => '✅ Admin criado com sucesso!',
                'admin_removido' => '✅ Admin removido com sucesso!',
                'usuario_email_existem' => '❌ Este usuário ou email já existe',
                'senhas_nao_conferem' => '❌ As senhas não conferem',
                'senha_muito_curta' => '❌ A senha deve ter pelo menos 8 caracteres',
                'email_invalido' => '❌ Email inválido',
                'nao_pode_deletar_a_si' => '❌ Você não pode remover sua própria conta',
                'code_2fa_invalido' => '❌ Código 2FA inválido. Use apenas números (máximo 6 dígitos)',
            ];
            
            if ($success && isset($messages[$success])): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
                    <?php echo $messages[$success]; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error && isset($messages[$error])): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #c62828;">
                    <?php echo $messages[$error]; ?>
                </div>
            <?php endif; ?>

            <!-- ABA DOAÇÕES -->
            <?php if ($aba_ativa === 'doacoes'): ?>
            <div>
                <h2 class="section-title">GERENCIAR DOAÇÕES</h2>
                
                <!-- BOTÃO ADICIONAR -->
                <div style="margin-bottom: 20px;">
                    <button type="button" id="btn-add-doacao" class="btn btn-primary" onclick="toggleForm('novo-doacao')">+ ADICIONAR DOAÇÃO</button>
                </div>

                <!-- FORMULÁRIO ADICIONAR DOAÇÃO -->
                <div id="novo-doacao" class="form-card" style="display: none; margin-bottom: 30px; padding: 20px;">
                    <h3>Nova Doação</h3>
                    <form action="includes/crud_process.php" method="POST">
                        <input type="hidden" name="acao" value="create_doacao">
                        
                        <div class="form-group">
                            <label for="user_id">ID do Usuário:</label>
                            <input type="number" id="user_id" name="user_id" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_doacao">Valor (R$):</label>
                            <input type="number" id="valor_doacao" name="valor_doacao" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="pendente">Pendente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('novo-doacao')">Cancelar</button>
                    </form>
                </div>

                <!-- TABELA DOAÇÕES -->
                <?php if (count($doacoes) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--dark-forest); color: var(--cream-white);">
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">ID</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">User ID</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Valor</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Data</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Status</th>
                                <th style="padding: 12px; text-align: center; border: 3px solid var(--dark-forest);">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doacoes as $doacao): ?>
                                <tr style="background: var(--cream-white); border: 3px solid var(--dark-forest);">
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($doacao['id']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($doacao['user_id']); ?></td>
                                    <td style="padding: 12px;">R$ <?php echo number_format($doacao['valor_doacao'], 2, ',', '.'); ?></td>
                                    <td style="padding: 12px;"><?php echo date('d/m/Y', strtotime($doacao['data_doacao'])); ?></td>
                                    <td style="padding: 12px;">
                                        <span style="background: <?php echo ($doacao['status'] === 'confirmado') ? 'var(--moss-green)' : 'var(--accent-orange)'; ?>; color: var(--cream-white); padding: 5px 10px; font-weight: bold; border: 2px solid <?php echo ($doacao['status'] === 'confirmado') ? 'var(--moss-green)' : 'var(--accent-orange)'; ?>;">
                                            <?php echo strtoupper($doacao['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <button type="button" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px;" onclick="editarDoacao(<?php echo htmlspecialchars(json_encode($doacao)); ?>)">Editar</button>
                                        <a href="includes/crud_process.php?acao=delete_doacao&id=<?php echo $doacao['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px; text-decoration: none; display: inline-block;" onclick="return confirm('Tem certeza?');">Deletar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="form-card" style="text-align: center; padding: 30px;">
                        <p>Nenhuma doação registrada.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ABA ÁRVORES -->
            <?php elseif ($aba_ativa === 'arvores'): ?>
            <div>
                <h2 class="section-title">GERENCIAR ÁRVORES</h2>
                
                <!-- BOTÃO ADICIONAR -->
                <div style="margin-bottom: 20px;">
                    <button type="button" id="btn-add-arvore" class="btn btn-primary" onclick="toggleForm('novo-arvore')">+ ADICIONAR ÁRVORE</button>
                </div>

                <!-- FORMULÁRIO ADICIONAR ÁRVORE -->
                <div id="novo-arvore" class="form-card" style="display: none; margin-bottom: 30px; padding: 20px;">
                    <h3>Nova Árvore</h3>
                    <form action="includes/crud_process.php" method="POST">
                        <input type="hidden" name="acao" value="create_arvore">
                        
                        <div class="form-group">
                            <label for="arvore_nome">Nome:</label>
                            <input type="text" id="arvore_nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="arvore_tipo">Tipo:</label>
                            <input type="text" id="arvore_tipo" name="tipo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="arvore_local">Local:</label>
                            <input type="text" id="arvore_local" name="local" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="arvore_co2">CO₂ Absorvido (kg):</label>
                            <input type="number" id="arvore_co2" name="co2_absorvido" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="arvore_status">Status:</label>
                            <select id="arvore_status" name="status" required>
                                <option value="viva">Viva</option>
                                <option value="seca">Seca</option>
                                <option value="plantada">Plantada</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('novo-arvore')">Cancelar</button>
                    </form>
                </div>

                <!-- TABELA ÁRVORES -->
                <?php if (count($arvores) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--dark-forest); color: var(--cream-white);">
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">ID</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Nome</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Tipo</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Local</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">CO₂ (kg)</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Status</th>
                                <th style="padding: 12px; text-align: center; border: 3px solid var(--dark-forest);">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($arvores as $arvore): ?>
                                <tr style="background: var(--cream-white); border: 3px solid var(--dark-forest);">
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($arvore['id']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($arvore['nome']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($arvore['tipo']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($arvore['local']); ?></td>
                                    <td style="padding: 12px;"><?php echo number_format($arvore['co2_absorvido'], 2, ',', '.'); ?></td>
                                    <td style="padding: 12px;">
                                        <span style="background: var(--moss-green); color: var(--cream-white); padding: 5px 10px; font-weight: bold; border: 2px solid var(--moss-green);">
                                            <?php echo strtoupper($arvore['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <button type="button" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px;" onclick="editarArvore(<?php echo htmlspecialchars(json_encode($arvore)); ?>)">Editar</button>
                                        <a href="includes/crud_process.php?acao=delete_arvore&id=<?php echo $arvore['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px; text-decoration: none; display: inline-block;" onclick="return confirm('Tem certeza?');">Deletar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="form-card" style="text-align: center; padding: 30px;">
                        <p>Nenhuma árvore registrada.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ABA PROJETOS -->
            <?php elseif ($aba_ativa === 'projetos'): ?>
            <div>
                <h2 class="section-title">GERENCIAR PROJETOS</h2>
                
                <!-- BOTÃO ADICIONAR -->
                <div style="margin-bottom: 20px;">
                    <button type="button" id="btn-add-projeto" class="btn btn-primary" onclick="toggleForm('novo-projeto')">+ ADICIONAR PROJETO</button>
                </div>

                <!-- FORMULÁRIO ADICIONAR PROJETO -->
                <div id="novo-projeto" class="form-card" style="display: none; margin-bottom: 30px; padding: 20px;">
                    <h3>Novo Projeto</h3>
                    <form action="includes/crud_process.php" method="POST">
                        <input type="hidden" name="acao" value="create_projeto">
                        
                        <div class="form-group">
                            <label for="projeto_nome">Nome:</label>
                            <input type="text" id="projeto_nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="projeto_descricao">Descrição:</label>
                            <textarea id="projeto_descricao" name="descricao" rows="4" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="projeto_localizacao">Localização:</label>
                            <input type="text" id="projeto_localizacao" name="localizacao" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="projeto_progresso">Progresso (%):</label>
                            <input type="number" id="projeto_progresso" name="progresso_percentual" min="0" max="100" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('novo-projeto')">Cancelar</button>
                    </form>
                </div>

                <!-- TABELA PROJETOS -->
                <?php if (count($projetos) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--dark-forest); color: var(--cream-white);">
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">ID</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Nome</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Localização</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Progresso</th>
                                <th style="padding: 12px; text-align: center; border: 3px solid var(--dark-forest);">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projetos as $projeto): ?>
                                <tr style="background: var(--cream-white); border: 3px solid var(--dark-forest);">
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($projeto['id']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($projeto['nome']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($projeto['localizacao']); ?></td>
                                    <td style="padding: 12px;">
                                        <div style="background: var(--stone-gray); border: 2px solid var(--dark-forest); width: 100px; height: 20px; position: relative;">
                                            <div style="background: var(--accent-orange); width: <?php echo $projeto['progresso_percentual']; ?>%; height: 100%;"></div>
                                        </div>
                                        <small><?php echo $projeto['progresso_percentual']; ?>%</small>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <button type="button" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px;" onclick="editarProjeto(<?php echo htmlspecialchars(json_encode($projeto)); ?>)">Editar</button>
                                        <a href="includes/crud_process.php?acao=delete_projeto&id=<?php echo $projeto['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px; text-decoration: none; display: inline-block;" onclick="return confirm('Tem certeza?');">Deletar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="form-card" style="text-align: center; padding: 30px;">
                        <p>Nenhum projeto registrado.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ABA ADMINS -->
            <?php elseif ($aba_ativa === 'admins'): ?>
            <div>
                <h2 class="section-title">GERENCIAR CONTAS DE ADMIN</h2>
                
                <!-- BOTÃO ADICIONAR -->
                <div style="margin-bottom: 20px;">
                    <button type="button" id="btn-add-admin" class="btn btn-primary" onclick="toggleForm('novo-admin')">+ CRIAR NOVO ADMIN</button>
                </div>

                <!-- FORMULÁRIO ADICIONAR ADMIN -->
                <div id="novo-admin" class="form-card" style="display: none; margin-bottom: 30px; padding: 20px;">
                    <h3>Nova Conta de Admin</h3>
                    <form action="includes/crud_process.php" method="POST">
                        <input type="hidden" name="acao" value="create_admin">
                        
                        <div class="form-group">
                            <label for="admin_username">Nome de Usuário:</label>
                            <input type="text" id="admin_username" name="username" required minlength="3">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_nome_completo">Nome Completo:</label>
                            <input type="text" id="admin_nome_completo" name="nome_completo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Email:</label>
                            <input type="email" id="admin_email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">Senha:</label>
                            <input type="password" id="admin_password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres">
                        </div>

                        <div class="form-group">
                            <label for="admin_password_confirm">Confirmar Senha:</label>
                            <input type="password" id="admin_password_confirm" name="password_confirm" required minlength="8" placeholder="Repita a senha">
                        </div>

                        <div class="form-group">
                            <label for="admin_2fa_code">Código 2FA:</label>
                            <input type="text" id="admin_2fa_code" name="code_2fa" placeholder="Ex: 999888" maxlength="6" pattern="[0-9]*" inputmode="numeric">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Criar Admin</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('novo-admin')">Cancelar</button>
                    </form>
                </div>

                <!-- BUSCAR ADMINS -->
                <?php 
                try {
                    $stmt = $pdo->prepare("SELECT id, username, nome_completo, email, code_2fa, data_criacao FROM usuarios WHERE is_admin = 1 ORDER BY data_criacao DESC");
                    $stmt->execute();
                    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $admins = [];
                }
                ?>

                <!-- TABELA ADMINS -->
                <?php if (count($admins) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--dark-forest); color: var(--cream-white);">
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">ID</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Usuário</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Nome Completo</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Email</th>
                                <th style="padding: 12px; text-align: center; border: 3px solid var(--dark-forest);">🔐 Código 2FA</th>
                                <th style="padding: 12px; text-align: left; border: 3px solid var(--dark-forest);">Criado em</th>
                                <th style="padding: 12px; text-align: center; border: 3px solid var(--dark-forest);">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr style="background: var(--cream-white); border: 3px solid var(--dark-forest);">
                                    <td style="padding: 12px;"><strong><?php echo htmlspecialchars($admin['id']); ?></strong></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($admin['nome_completo']); ?></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 3px; font-weight: bold; color: var(--dark-forest);">
                                            <?php echo htmlspecialchars($admin['code_2fa']); ?>
                                        </code>
                                    </td>
                                    <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($admin['data_criacao'])); ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <?php if ($admin['id'] !== $_SESSION['user_id']): ?>
                                            <a href="includes/crud_process.php?acao=delete_admin&id=<?php echo $admin['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 5px 10px; text-decoration: none; display: inline-block;" onclick="return confirm('Tem certeza que deseja remover este admin?');">Remover</a>
                                        <?php else: ?>
                                            <span style="color: var(--moss-green); font-weight: bold;">👤 Você</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="form-card" style="text-align: center; padding: 30px;">
                        <p>Nenhum admin encontrado.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container foot-min">
            <a class="brand-min" href="index.php">
                <span>🌱</span><strong>Plantou!</strong>
            </a>
            <div class="foot-meta">
                <span>© <span id="year"></span> Plantou!</span>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        function toggleForm(formId) {
            const form = document.getElementById(formId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function editarDoacao(doacao) {
            alert('Função de edição em desenvolvimento.\n\nID: ' + doacao.id + '\nValor: R$ ' + doacao.valor_doacao);
        }

        function editarArvore(arvore) {
            alert('Função de edição em desenvolvimento.\n\nID: ' + arvore.id + '\nNome: ' + arvore.nome);
        }

        function editarProjeto(projeto) {
            alert('Função de edição em desenvolvimento.\n\nID: ' + projeto.id + '\nNome: ' + projeto.nome);
        }
    </script>
</body>
</html>
