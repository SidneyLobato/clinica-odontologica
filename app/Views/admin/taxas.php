<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:1.25rem;">
        <h2 style="margin:0;">💳 Taxas de Cartão</h2>
        <button type="button" id="btnSalvarLote" disabled
            style="display:flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--border-radius);
                   border:1px solid var(--border-color);background:#f1f3f5;color:#9aa3ab;
                   font-size:.9rem;font-weight:600;cursor:not-allowed;transition:.15s;">
            <span id="iconSalvarLote">✓</span>
            <span id="labelSalvarLote">Nenhuma alteração</span>
        </button>
    </div>

    <div id="msgArea"></div>

    <!-- Abas por bandeira -->
    <div id="tabsBandeiras" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:1rem;"></div>

    <!-- Débito (à vista) -->
    <div id="blocoDebito" style="margin-bottom:1rem;"></div>

    <!-- Tabela de crédito (parcelado) -->
    <div id="tabelaBandeira" style="overflow-x:auto;"></div>

    <p class="text-muted" style="margin-top:1.25rem;font-size:.85rem;display:flex;gap:6px;align-items:flex-start;">
        <span>ℹ️</span>
        <span>Os valores são percentuais (ex: 3,00 = 3%). Débito normalmente não tem parcelamento — é uma taxa única "à vista". Ao salvar, a vigência anterior é encerrada e um novo registro é criado — garantindo rastreabilidade total.</span>
    </p>

    <a href="<?= BASE_URL ?>admin" class="btn btn-secondary" style="margin-top:1rem;">← Voltar ao Painel</a>
</div>

<style>
.tab-bandeira{
    display:flex;align-items:center;gap:8px;padding:8px 14px;
    border-radius:var(--border-radius);border:1px solid var(--border-color);
    background:#fff;color:var(--text-color);font-size:.85rem;font-weight:600;
    cursor:pointer;transition:.15s;position:relative;
}
.tab-bandeira.ativa{
    background:#eef6fc;border-color:var(--primary-color);color:var(--primary-color);
}
.tab-bandeira .dot-alterado{
    width:7px;height:7px;border-radius:50%;background:#f0a500;display:inline-block;margin-left:2px;
}
.input-taxa{
    width:62px;padding:6px 5px;text-align:center;border:1px solid var(--border-color);
    border-radius:6px;font-size:.85rem;transition:.15s;
}
.input-taxa.alterado{
    border-color:#f0a500;background:#fff8e8;
}
.celula-taxa{position:relative;display:inline-block;}
.celula-taxa .dot-alterado-input{
    position:absolute;top:-3px;right:-3px;width:7px;height:7px;border-radius:50%;background:#f0a500;
}
.bloco-debito-card{border:1px solid #c8e6c9;border-radius:10px;overflow:hidden;background:#f3faf4;}
.bloco-debito-head{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#e3f4e4;}
.bloco-debito-head span{font-weight:700;color:#1d6b2e;}
.bloco-debito-body{padding:14px;display:flex;align-items:center;gap:10px;}
.success{color:#1d7a3c;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}
.error{color:#b3261e;background:#fdecea;padding:.75rem;border-radius:6px;margin-bottom:1rem;}
</style>

<script>
(function(){
    const bandeiras = <?= json_encode(array_map(fn($b) => [
        'id'   => (int)$b['id'],
        'nome' => $b['nome'],
    ], $bandeiras)) ?>;

    const matrizCredito = <?= json_encode($matriz ?: new stdClass()) ?>;
    const matrizDebito  = <?= json_encode($matrizDebito ?: new stdClass()) ?>;

    function estiloBandeira(nome) {
        const n = nome.toLowerCase();
        if (n.includes('visa'))        return { bg: '#e6f1fb', fg: '#0c447c' };
        if (n.includes('master'))      return { bg: '#faeeda', fg: '#633806' };
        if (n.includes('elo'))         return { bg: '#faece7', fg: '#712b13' };
        if (n.includes('hiper'))       return { bg: '#fbeaf0', fg: '#72243e' };
        if (n.includes('american') || n.includes('amex')) return { bg: '#e1f5ee', fg: '#085041' };
        return { bg: '#f1efe8', fg: '#444441' };
    }

    const PARCELAS = Array.from({length:10}, (_,i) => i+1);

    // Estado crédito: chave "bid-parc"
    const valoresCredito = {}, originaisCredito = {};
    PARCELAS.forEach(p => bandeiras.forEach(b => {
        const v = (matrizCredito[b.id] && matrizCredito[b.id][p] !== undefined) ? parseFloat(matrizCredito[b.id][p]) : 0;
        valoresCredito[b.id+'-'+p] = v;
        originaisCredito[b.id+'-'+p] = v;
    }));

    // Estado débito: chave "bid"
    const valoresDebito = {}, originaisDebito = {};
    bandeiras.forEach(b => {
        const v = matrizDebito[b.id] !== undefined ? parseFloat(matrizDebito[b.id]) : 0;
        valoresDebito[b.id] = v;
        originaisDebito[b.id] = v;
    });

    // alterados guarda chaves no formato "credito:bid-parc" ou "debito:bid"
    const alterados = new Set();

    let abaAtiva = bandeiras.length ? bandeiras[0].id : null;

    const elTabs       = document.getElementById('tabsBandeiras');
    const elTabela      = document.getElementById('tabelaBandeira');
    const elDebito       = document.getElementById('blocoDebito');
    const btnSalvar     = document.getElementById('btnSalvarLote');
    const labelSalvar   = document.getElementById('labelSalvarLote');
    const iconSalvar    = document.getElementById('iconSalvarLote');

    function renderTabs() {
        elTabs.innerHTML = '';
        bandeiras.forEach(b => {
            const ativa = b.id === abaAtiva;
            const temAlteracao = [...alterados].some(k => k.endsWith(':'+b.id) || k.includes(':'+b.id+'-'));

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tab-bandeira' + (ativa ? ' ativa' : '');
            btn.innerHTML =
                '<span>'+b.nome+'</span>' +
                (temAlteracao ? '<span class="dot-alterado"></span>' : '');
            btn.onclick = () => { abaAtiva = b.id; renderTabs(); renderDebito(); renderTabela(); };
            elTabs.appendChild(btn);
        });
    }

    function renderDebito() {
        const b = bandeiras.find(x => x.id === abaAtiva);
        if (!b) { elDebito.innerHTML = ''; return; }
        const key = 'debito:'+b.id;
        const v = valoresDebito[b.id];
        const isAlt = alterados.has(key);

        elDebito.innerHTML =
            '<div class="bloco-debito-card">' +
              '<div class="bloco-debito-head"><span>💵 '+b.nome+' — Débito (à vista)</span></div>' +
              '<div class="bloco-debito-body">' +
                '<label style="font-size:.85rem;color:#444;">Taxa cobrada pela operadora:</label>' +
                '<span class="celula-taxa">' +
                  '<input type="number" step="0.01" min="0" max="99.99" value="'+v.toFixed(2)+'" ' +
                  'data-bid="'+b.id+'" class="input-taxa input-debito'+(isAlt?' alterado':'')+'">' +
                  (isAlt ? '<span class="dot-alterado-input"></span>' : '') +
                '</span>' +
                '<span style="font-size:.78rem;color:#5a7a5a;">% sobre o valor — na hora do lançamento, você escolhe se repassa essa taxa ao cliente ou se a clínica absorve.</span>' +
              '</div>' +
            '</div>';

        elDebito.querySelector('.input-debito').addEventListener('input', e => {
            const bid = e.target.dataset.bid;
            const novo = parseFloat(e.target.value);
            valoresDebito[bid] = isNaN(novo) ? 0 : novo;
            const k = 'debito:'+bid;
            if (Math.abs(valoresDebito[bid] - originaisDebito[bid]) > 0.001) alterados.add(k);
            else alterados.delete(k);
            renderTabs(); renderDebito(); atualizarBotaoSalvar();
        });
    }

    function renderTabela() {
        const b = bandeiras.find(x => x.id === abaAtiva);
        if (!b) { elTabela.innerHTML = '<p>Nenhuma bandeira cadastrada na tabela <code>bandeiras_cartao</code>.</p>'; return; }
        const est = estiloBandeira(b.nome);

        let html = '<div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">';
        html += '<div style="display:flex;align-items:center;padding:10px 14px;background:'+est.bg+';">';
        html += '<span style="font-weight:700;color:'+est.fg+';">'+b.nome+' — Crédito (parcelado)</span>';
        html += '</div>';
        html += '<table style="border-collapse:collapse;width:100%;font-size:.85rem;">';
        html += '<tr style="background:#f8f9fa;">';
        PARCELAS.forEach(p => html += '<th style="padding:9px 4px;text-align:center;color:#6c757d;font-weight:600;">'+p+'x</th>');
        html += '</tr><tr>';
        PARCELAS.forEach(p => {
            const ckey = b.id+'-'+p;
            const key = 'credito:'+ckey;
            const v = valoresCredito[ckey];
            const isAlt = alterados.has(key);
            html += '<td style="padding:8px 5px;text-align:center;border-top:1px solid var(--border-color);">';
            html += '<span class="celula-taxa">';
            html += '<input type="number" step="0.01" min="0" max="99.99" value="'+v.toFixed(2)+'" ' +
                     'data-key="'+ckey+'" class="input-taxa'+(isAlt ? ' alterado' : '')+'">';
            if (isAlt) html += '<span class="dot-alterado-input"></span>';
            html += '</span></td>';
        });
        html += '</tr></table></div>';
        elTabela.innerHTML = html;

        elTabela.querySelectorAll('.input-taxa').forEach(inp => {
            inp.addEventListener('input', e => {
                const ckey = e.target.dataset.key;
                const key = 'credito:'+ckey;
                const novo = parseFloat(e.target.value);
                valoresCredito[ckey] = isNaN(novo) ? 0 : novo;

                if (Math.abs(valoresCredito[ckey] - originaisCredito[ckey]) > 0.001) alterados.add(key);
                else alterados.delete(key);

                renderTabs();
                renderTabela();
                atualizarBotaoSalvar();
            });
        });
    }

    function atualizarBotaoSalvar() {
        const n = alterados.size;
        if (n === 0) {
            btnSalvar.disabled = true;
            btnSalvar.style.cursor = 'not-allowed';
            btnSalvar.style.background = '#f1f3f5';
            btnSalvar.style.color = '#9aa3ab';
            btnSalvar.style.borderColor = 'var(--border-color)';
            labelSalvar.textContent = 'Nenhuma alteração';
            iconSalvar.textContent = '✓';
        } else {
            btnSalvar.disabled = false;
            btnSalvar.style.cursor = 'pointer';
            btnSalvar.style.background = 'var(--primary-color)';
            btnSalvar.style.color = '#fff';
            btnSalvar.style.borderColor = 'var(--primary-color)';
            labelSalvar.textContent = 'Salvar ' + n + ' alteraç' + (n > 1 ? 'ões' : 'ão');
            iconSalvar.textContent = '💾';
        }
    }

    function mostrarMensagem(tipo, texto) {
        const el = document.getElementById('msgArea');
        el.innerHTML = '<p class="'+(tipo === 'ok' ? 'success' : 'error')+'">'+texto+'</p>';
        setTimeout(() => { el.innerHTML = ''; }, 4000);
    }

    btnSalvar.addEventListener('click', async () => {
        if (alterados.size === 0) return;

        const itens = [...alterados].map(key => {
            const [tipo, resto] = key.split(':');
            if (tipo === 'debito') {
                return { bandeira_id: Number(resto), parcelas: 1, percentual: valoresDebito[resto], tipo: 'debito' };
            }
            const [bid, parc] = resto.split('-').map(Number);
            return { bandeira_id: bid, parcelas: parc, percentual: valoresCredito[resto], tipo: 'credito' };
        });

        btnSalvar.disabled = true;
        labelSalvar.textContent = 'Salvando...';
        iconSalvar.textContent = '⏳';

        try {
            const resp = await fetch('<?= BASE_URL ?>admin/salvarTaxasLote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ itens })
            });
            const data = await resp.json();

            if (data.ok) {
                alterados.forEach(key => {
                    const [tipo, resto] = key.split(':');
                    if (tipo === 'debito') originaisDebito[resto] = valoresDebito[resto];
                    else originaisCredito[resto] = valoresCredito[resto];
                });
                alterados.clear();
                renderTabs();
                renderDebito();
                renderTabela();
                atualizarBotaoSalvar();
                mostrarMensagem('ok', '✓ ' + data.atualizados + ' taxa(s) atualizada(s) com sucesso! Apenas novos lançamentos usarão os novos valores.');
            } else {
                mostrarMensagem('erro', 'Não foi possível salvar. Verifique os valores e tente novamente.');
                atualizarBotaoSalvar();
            }
        } catch (e) {
            mostrarMensagem('erro', 'Erro de conexão ao salvar. Tente novamente.');
            atualizarBotaoSalvar();
        }
    });

    renderTabs();
    renderDebito();
    renderTabela();
    atualizarBotaoSalvar();
})();
</script>
