<div class="card">
    <h2>⚙ Painel Administrativo</h2>
    
    <p style="margin-top:.75rem;">
        <strong>Cenário de comissão ativo:</strong>
        <span style="background:var(--primary-color);color:#fff;padding:3px 14px;border-radius:20px;margin-left:.5rem;font-size:.9rem;">
            <?= $cenario === 'individual' ? '🔵 Individual por Dentista' : '🟢 Global (toda a clínica)' ?>
        </span>
    </p>

    <div class="dashboard-grid" style="margin-top:2rem;">
        <div class="stat-card" style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>admin/taxas'">
            <h3>💳 Taxas de Cartão</h3>
            
            <a href="<?= BASE_URL ?>admin/taxas" class="btn btn-primary" style="margin-top:1rem;">Gerenciar Taxas</a>
        </div>
        <div class="stat-card" style="cursor:pointer;border-left-color:var(--success-color);" onclick="location.href='<?= BASE_URL ?>admin/comissoes'">
            <h3>💰 Comissões e Especialidades</h3>
            
            <a href="<?= BASE_URL ?>admin/comissoes" class="btn btn-success" style="margin-top:1rem;">Gerenciar Comissões</a>
        </div>
        <div class="stat-card" style="cursor:pointer;border-left-color:var(--danger-color);" onclick="location.href='<?= BASE_URL ?>admin/rateio'">
            <h3>📊 Rateio</h3>
            
            <a href="<?= BASE_URL ?>admin/rateio" class="btn btn-danger" style="margin-top:1rem;">Gerenciar Rateio</a>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="location.href='<?= BASE_URL ?>procedimentos'">
            <h3>🦷 Preços de Procedimentos</h3>
            
            <a href="<?= BASE_URL ?>procedimentos" class="btn btn-secondary" style="margin-top:1rem;">Gerenciar Procedimentos</a>
        </div>
    </div>
</div>
