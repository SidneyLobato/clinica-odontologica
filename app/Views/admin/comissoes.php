<div class="card">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:.5rem;">
        <h2 style="margin:0;">📊 Comissões</h2>
    </div>

    <?php if (($msg ?? '') === 'sucesso'): ?><p class="success">Salvo com sucesso!</p><?php endif; ?>

    <div class="aviso-info">
        <span>ℹ️</span>
        <span>Existem duas regras de comissão independentes. A <strong>Regra A</strong> vale só para procedimentos gerais. A <strong>Regra B</strong> vale só para especializados e próteses. Um atendimento usa uma ou a outra, nunca as duas.</span>
    </div>

    <!-- ───────────── REGRA A — Por meta de faturamento ───────────── -->
    <div class="card secao-regra" style="margin-top:1.5rem;">
        <div class="cabecalho-regra">
            <span class="badge-regra badge-regra-a">A</span>
            <h3 style="margin:0;">Comissão por meta de faturamento</h3>
        </div>
        <p class="subtexto-regra">Aplica-se apenas a procedimentos gerais. O percentual muda conforme o dentista bate ou não a meta mensal.</p>

        <div style="margin:1rem 0;">
            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.5rem;">Quem usa essa meta?</label>
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.88rem;">
                    <input type="radio" class="radioCenario" data-valor="global" name="cenarioVisual" <?= $cenario==='global'?'checked':'' ?>>
                    <span>Todos os dentistas (regra única)</span>
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.88rem;">
                    <input type="radio" class="radioCenario" data-valor="individual" name="cenarioVisual" <?= $cenario==='individual'?'checked':'' ?>>
                    <span>Cada dentista com sua própria meta</span>
                </label>
            </div>
        </div>

        <!-- Painel: regra global -->
        <div id="painelGlobal" class="painel-regra" style="<?= $cenario==='individual' ? 'display:none;' : '' ?>border-top:1px solid #eee;padding-top:1rem;">
            <form method="POST" action="<?= BASE_URL ?>admin/salvarComissaoGlobal">
                <input type="hidden" name="cenario" class="inputCenarioOculto" value="<?= $cenario ?>">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label>% até a meta</label>
                        <input type="number" name="percentual_ate_meta" step="0.01" min="0" max="100" value="<?= $regra['percentual_ate_meta'] ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>% acima da meta</label>
                        <input type="number" name="percentual_acima_meta" step="0.01" min="0" max="100" value="<?= $regra['percentual_acima_meta'] ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Meta (R$)</label>
                        <input type="number" name="valor_meta" step="0.01" min="0" value="<?= $regra['valor_meta'] ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top:1rem;">Salvar regra geral</button>
            </form>
        </div>

        <!-- Painel: regra individual -->
        <div id="painelIndividual" class="painel-regra" style="<?= $cenario==='individual' ? '' : 'display:none;' ?>border-top:1px solid #eee;padding-top:1rem;">
            <form method="POST" action="<?= BASE_URL ?>admin/salvarComissaoIndividual">
                <input type="hidden" name="cenario" class="inputCenarioOculto" value="<?= $cenario ?>">
                <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr 1fr;gap:1rem;align-items:end;">
                    <div class="form-group" style="margin:0;">
                        <label>Dentista</label>
                        <select name="dentista_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($dentistas as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;"><label>% até meta</label><input type="number" name="percentual_ate_meta" step="0.01" value="20" required></div>
                    <div class="form-group" style="margin:0;"><label>% acima</label><input type="number" name="percentual_acima_meta" step="0.01" value="30" required></div>
                    <div class="form-group" style="margin:0;"><label>Meta (R$)</label><input type="number" name="valor_meta" step="0.01" value="10000" required></div>
                </div>
                <button type="submit" class="btn btn-success" style="margin-top:1rem;">Salvar regra individual</button>
            </form>

            <?php if ($ri): ?>
            <table class="mobile-card-table" style="margin-top:1rem;">
                <thead><tr><th>Dentista</th><th>% até meta</th><th>% acima</th><th>Meta</th></tr></thead>
                <tbody>
                    <?php foreach ($ri as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['dentista_nome']) ?></td>
                        <td><?= number_format($r['percentual_ate_meta'],2,',','.') ?>%</td>
                        <td><?= number_format($r['percentual_acima_meta'],2,',','.') ?>%</td>
                        <td>R$ <?= number_format($r['valor_meta'],2,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ───────────── REGRA B — Por tipo de procedimento ───────────── -->
    <div class="card secao-regra" style="margin-top:1.5rem;">
        <div class="cabecalho-regra">
            <span class="badge-regra badge-regra-b">B</span>
            <h3 style="margin:0;">Comissão por tipo de procedimento</h3>
        </div>
        <p class="subtexto-regra">Aplica-se a procedimentos especializados e próteses. Percentual fixo, não depende de meta nem do cenário acima.</p>

        <form method="POST" action="<?= BASE_URL ?>admin/salvarEspecialidade" style="border-top:1px solid #eee;padding-top:1rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:end;">
                <div class="form-group" style="margin:0;">
                    <label>Especialidade</label>
                    <select name="tipo" required>
                        <option value="canal">Canal</option>
                        <option value="orto">Ortodontia</option>
                        <option value="cirurgia_especializada">Cirurgia Especializada</option>
                        <option value="protese">Prótese</option>
                        <option value="implante">Implante</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;"><label>Percentual (%)</label><input type="number" name="percentual" step="0.01" min="0" max="100" required></div>
            </div>
            <button type="submit" class="btn btn-success" style="margin-top:1rem;">Salvar</button>
        </form>

        <?php if ($espec): ?>
        <table class="mobile-card-table" style="margin-top:1rem;">
            <thead><tr><th>Especialidade</th><th>%</th></tr></thead>
            <tbody>
                <?php foreach ($espec as $e): ?>
                <tr><td><?= ucfirst(str_replace('_',' ',$e['tipo'])) ?></td><td><?= number_format($e['percentual'],2,',','.') ?>%</td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}
.aviso-info{
    display:flex;gap:8px;align-items:flex-start;
    background:#eaf3fb;color:#0c5a8a;border-radius:8px;
    padding:.75rem 1rem;font-size:.85rem;margin-top:.75rem;
}
.cabecalho-regra{display:flex;align-items:center;gap:10px;margin-bottom:.25rem;}
.badge-regra{
    width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;
    font-size:.78rem;font-weight:700;flex-shrink:0;
}
.badge-regra-a{background:#dbeeff;color:#0c5a8a;}
.badge-regra-b{background:#fdebd2;color:#8a5a0c;}
.subtexto-regra{font-size:.82rem;color:#6c757d;margin:.25rem 0 0 36px;}
</style>

<script>
(function(){
    const radios = document.querySelectorAll('.radioCenario');
    const painelGlobal = document.getElementById('painelGlobal');
    const painelIndividual = document.getElementById('painelIndividual');
    const inputsOcultos = document.querySelectorAll('.inputCenarioOculto');

    function alternarPainel(novoValor) {
        painelGlobal.style.display     = novoValor === 'global'     ? '' : 'none';
        painelIndividual.style.display = novoValor === 'individual' ? '' : 'none';
        // Mantém os inputs ocultos sincronizados — o cenário só é
        // efetivamente salvo quando o usuário clicar em "Salvar regra geral"
        // ou "Salvar regra individual" do painel correspondente.
        inputsOcultos.forEach(inp => inp.value = novoValor);
    }

    radios.forEach(r => {
        r.addEventListener('change', () => alternarPainel(r.dataset.valor));
    });
})();
</script>
