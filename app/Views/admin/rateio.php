<div class="card">
    <h2 style="margin:0;">📊 Rateio entre Dentistas</h2>

    <?php if (($msg ?? '') === 'sucesso'): ?>
    <p class="success">Rateio salvo! Apenas novos atendimentos são afetados.</p>
    <?php endif; ?>
    <?php if (($erro ?? '') === 'soma'): ?>
    <p class="error">A soma dos percentuais deve ser exatamente 100%.</p>
    <?php endif; ?>

    <div class="aviso-info">
        <span>ℹ️</span>
        <span>O rateio divide o valor de procedimentos especializados e próteses entre quem executou, quem captou o paciente e a clínica. Procedimentos gerais não usam rateio.</span>
    </div>

    <div id="cardsRateio" style="margin-top:1rem;"></div>

    <a href="<?= BASE_URL ?>admin" class="btn btn-secondary" style="margin-top:1rem;">← Voltar ao Painel</a>
</div>

<style>
.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}
.error{color:#b3261e;background:#fdecea;padding:.75rem;border-radius:6px;margin-bottom:1rem;}
.aviso-info{
    display:flex;gap:8px;align-items:flex-start;
    background:#eaf3fb;color:#0c5a8a;border-radius:8px;
    padding:.75rem 1rem;font-size:.85rem;margin-top:.75rem;
}
.card-rateio{
    border:1px solid #eee;border-radius:10px;padding:1.1rem 1.25rem;margin-bottom:1rem;background:#fff;
}
.card-rateio-topo{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;}
.card-rateio-nome{font-weight:700;font-size:.95rem;}
.pill-soma{font-size:.75rem;padding:3px 10px;border-radius:6px;font-weight:600;}
.card-rateio-desc{font-size:.8rem;color:#6c757d;margin:0 0 .85rem;}
.barra-rateio{display:flex;height:8px;border-radius:4px;overflow:hidden;margin:.6rem 0;}
.exemplo-rateio{font-size:.82rem;color:#6c757d;margin:0 0 .6rem;}
.exemplo-rateio strong{color:#212529;}
.aviso-orto{
    font-size:.8rem;color:#8a5a0c;display:flex;gap:6px;align-items:flex-start;
    margin-bottom:.6rem;background:#fdf3e3;padding:.5rem .7rem;border-radius:6px;
}
</style>

<script>
(function(){
    // Categorias fixas suportadas pelo sistema, com metadados de exibição
    const META = {
        especializado: { nome: 'Especializado', desc: 'Canal, cirurgia especializada, implante', bg:'#e6f1fb', fg:'#0c447c', barEsp:'#378add', barVend:'#fac775', barClin:'#9fe1ce', obsOrto:true },
        protese:       { nome: 'Prótese',        desc: 'Próteses dentárias',                     bg:'#faece7', fg:'#712b13', barEsp:'#d85a30', barVend:'#fac775', barClin:'#9fe1ce', obsOrto:false },
        todas:         { nome: 'Padrão (fallback)', desc: 'Usada só se a categoria acima não tiver regra própria', bg:'#f1efe8', fg:'#444441', barEsp:'#888780', barVend:'#fac775', barClin:'#9fe1ce', obsOrto:false }
    };
    const ORDEM = ['especializado', 'protese', 'todas'];

    // Regras vindas do banco (PHP) — pode ter 0, 1 ou várias por categoria; usamos a mais recente de cada
    const regrasBanco = <?= json_encode(array_map(fn($r) => [
        'categoria' => $r['categoria_procedimento'],
        'esp'       => (float)$r['percentual_especialista'],
        'vend'      => (float)$r['percentual_vendedor'],
        'clin'      => (float)$r['percentual_clinica'],
        'vigencia'  => $r['vigencia_inicio'],
    ], $regras)) ?>;

    // Valores padrão de fallback caso a categoria não tenha nenhuma regra salva ainda
    const PADRAO = {
        especializado: { esp: 50, vend: 10, clin: 40 },
        protese:       { esp: 10, vend: 0,  clin: 90 },
        todas:         { esp: 50, vend: 10, clin: 40 }
    };

    function valoresAtuais(cat) {
        const doBanco = regrasBanco.find(r => r.categoria === cat);
        return doBanco ? { esp: doBanco.esp, vend: doBanco.vend, clin: doBanco.clin } : PADRAO[cat];
    }

    const container = document.getElementById('cardsRateio');

    ORDEM.forEach(cat => {
        const m = META[cat];
        const v = valoresAtuais(cat);
        const valorExemplo = 1000;

        const div = document.createElement('div');
        div.className = 'card-rateio';
        div.innerHTML = `
            <div class="card-rateio-topo">
                <span class="card-rateio-nome">${m.nome}</span>
                <span class="pill-soma" data-cat="${cat}" style="background:#eaf3de;color:#27500a;">soma <span class="soma-valor">100</span>%</span>
            </div>
            <p class="card-rateio-desc">${m.desc}</p>

            <form method="POST" action="<?= BASE_URL ?>admin/salvarRateio" class="form-rateio-card">
                <input type="hidden" name="categoria_procedimento" value="${cat}">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label>Especialista (%)</label>
                        <input type="number" name="percentual_especialista" class="inp-esp" step="0.01" min="0" max="100" value="${v.esp}" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Captador (%)</label>
                        <input type="number" name="percentual_vendedor" class="inp-vend" step="0.01" min="0" max="100" value="${v.vend}" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Clínica (%)</label>
                        <input type="number" name="percentual_clinica" class="inp-clin" step="0.01" min="0" max="100" value="${v.clin}" required>
                    </div>
                </div>

                <div class="barra-rateio">
                    <div class="bar-esp"  style="background:${m.barEsp};  width:${v.esp}%;"></div>
                    <div class="bar-vend" style="background:${m.barVend}; width:${v.vend}%;"></div>
                    <div class="bar-clin" style="background:${m.barClin}; width:${v.clin}%;"></div>
                </div>

                <p class="exemplo-rateio">
                    Exemplo: um procedimento de R$ ${valorExemplo.toLocaleString('pt-BR')} líquido vira
                    <strong class="ex-esp">R$ ${(valorExemplo*v.esp/100).toFixed(2).replace('.',',')}</strong> para o especialista,
                    <strong class="ex-vend">R$ ${(valorExemplo*v.vend/100).toFixed(2).replace('.',',')}</strong> para o captador e
                    <strong class="ex-clin">R$ ${(valorExemplo*v.clin/100).toFixed(2).replace('.',',')}</strong> para a clínica.
                </p>

                ${m.obsOrto ? `<div class="aviso-orto"><span>⚠️</span><span>Para ortodontia, o captador recebe automaticamente 0%, mesmo que o campo acima diga outro valor.</span></div>` : ''}

                <button type="submit" class="btn btn-success">Salvar ${m.nome.toLowerCase()}</button>
            </form>
        `;
        container.appendChild(div);

        // Recalcula barra, exemplo e soma ao digitar
        const inpEsp  = div.querySelector('.inp-esp');
        const inpVend = div.querySelector('.inp-vend');
        const inpClin = div.querySelector('.inp-clin');
        const pill    = div.querySelector('.pill-soma');
        const somaSpan= div.querySelector('.soma-valor');
        const barEsp  = div.querySelector('.bar-esp');
        const barVend = div.querySelector('.bar-vend');
        const barClin = div.querySelector('.bar-clin');
        const exEsp   = div.querySelector('.ex-esp');
        const exVend  = div.querySelector('.ex-vend');
        const exClin  = div.querySelector('.ex-clin');

        function recalcular() {
            const esp  = parseFloat(inpEsp.value)  || 0;
            const vend = parseFloat(inpVend.value) || 0;
            const clin = parseFloat(inpClin.value) || 0;
            const soma = esp + vend + clin;

            somaSpan.textContent = soma.toFixed(0);
            const ok = Math.abs(soma - 100) < 0.01;
            pill.style.background = ok ? '#eaf3de' : '#fcebeb';
            pill.style.color      = ok ? '#27500a' : '#791f1f';

            barEsp.style.width  = esp  + '%';
            barVend.style.width = vend + '%';
            barClin.style.width = clin + '%';

            exEsp.textContent  = 'R$ ' + (valorExemplo*esp/100).toFixed(2).replace('.',',');
            exVend.textContent = 'R$ ' + (valorExemplo*vend/100).toFixed(2).replace('.',',');
            exClin.textContent = 'R$ ' + (valorExemplo*clin/100).toFixed(2).replace('.',',');
        }

        [inpEsp, inpVend, inpClin].forEach(inp => inp.addEventListener('input', recalcular));

        // Bloqueia o envio se a soma não for 100%, com feedback imediato (evita ida e volta ao servidor)
        div.querySelector('.form-rateio-card').addEventListener('submit', (e) => {
            const soma = (parseFloat(inpEsp.value)||0) + (parseFloat(inpVend.value)||0) + (parseFloat(inpClin.value)||0);
            if (Math.abs(soma - 100) > 0.01) {
                e.preventDefault();
                alert('A soma dos percentuais deve ser exatamente 100%. Atualmente está em ' + soma.toFixed(2) + '%.');
            }
        });
    });
})();
</script>
