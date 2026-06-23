<div class="card">
    <h2>Configurações da Conta</h2>

    <?php if (($msg ?? '') === 'sucesso'): ?>
    <p class="success">Dados atualizados com sucesso!</p>
    <?php endif; ?>

    <?php
    $erros = [
        'senha_incorreta'   => 'A senha atual informada está incorreta.',
        'senhas_diferentes' => 'A nova senha e a confirmação não coincidem.',
        'campos_vazios'     => 'Preencha todos os campos de senha para alterá-la.',
        'geral'             => 'Ocorreu um erro ao salvar. Tente novamente.',
    ];
    if (!empty($erro) && isset($erros[$erro])): ?>
    <p class="error"><?= $erros[$erro] ?></p>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>configuracoes/salvar" method="POST" style="margin-top:1.5rem;">
        <div class="form-group">
            <label>Nome de Exibição</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Login (não pode ser alterado)</label>
            <input type="text" value="<?= htmlspecialchars($usuario['login'] ?? '') ?>" disabled style="background:#e9ecef;cursor:not-allowed;">
        </div>

        <hr style="margin:2rem 0;">
        <h3>Alterar Senha</h3>
        <p class="text-muted" style="font-size:.9rem;margin-bottom:1rem;">Deixe os campos em branco para manter a senha atual.</p>

        <div class="form-group"><label>Senha Atual</label><input type="password" name="senha_antiga"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group"><label>Nova Senha</label><input type="password" name="nova_senha"></div>
            <div class="form-group"><label>Confirmar Nova Senha</label><input type="password" name="confirmar_senha"></div>
        </div>

        <div style="margin-top:2rem;">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
<style>.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}</style>
