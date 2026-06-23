<div class="card"><h2>Relatório por Paciente</h2>
<div class="card" style="margin-top:1rem;">
    <div class="form-group" style="margin-bottom:0;position:relative;">
        <label>Buscar Paciente</label>
        <div style="display:flex;gap:1rem;align-items:center;">
            <div style="flex-grow:1;position:relative;">
                <input type="text" id="buscaPacienteInput" value="<?=htmlspecialchars($nome)?>" placeholder="Digite o nome do paciente..." autocomplete="off">
                <div id="listaSugestoes" class="lista-sugestoes"></div>
            </div>
            <button type="button" id="btnLimparBusca" class="btn btn-secondary" style="<?= $paciente ? '' : 'display:none;' ?>">Trocar paciente</button>
        </div>
    </div>
</div>

<style>
.lista-sugestoes{
    position:absolute;top:100%;left:0;right:0;z-index:20;
    background:#fff;border:1px solid #ddd;border-radius:8px;
    margin-top:4px;box-shadow:0 4px 12px rgba(0,0,0,.08);
    max-height:280px;overflow-y:auto;display:none;
}
.item-sugestao{
    padding:.6rem .9rem;cursor:pointer;border-bottom:1px solid #f0f0f0;
    display:flex;justify-content:space-between;align-items:center;font-size:.88rem;
}
.item-sugestao:last-child{border-bottom:none;}
.item-sugestao:hover{background:#f4f8fc;}
.item-sugestao .nome-pac{font-weight:600;color:#212529;}
.item-sugestao .info-pac{color:#6c757d;font-size:.78rem;}
.sugestao-vazia{padding:.8rem;text-align:center;color:#6c757d;font-size:.85rem;}
</style>

<script>
(function(){
    const input = document.getElementById('buscaPacienteInput');
    const lista = document.getElementById('listaSugestoes');
    const btnLimpar = document.getElementById('btnLimparBusca');
    let debounceTimer = null;

    function irParaPaciente(id) {
        window.location.href = '<?=BASE_URL?>relatorios/paciente?paciente_id=' + id;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderSugestoes(pacientes) {
        if (!pacientes.length) {
            lista.innerHTML = '<div class="sugestao-vazia">Nenhum paciente encontrado com esse nome.</div>';
            lista.style.display = 'block';
            return;
        }
        lista.innerHTML = pacientes.map(p =>
            '<div class="item-sugestao" data-id="' + p.id + '">' +
                '<span class="nome-pac">' + escapeHtml(p.nome) + '</span>' +
                '<span class="info-pac">' + escapeHtml(p.cpf || p.telefone || '') + '</span>' +
            '</div>'
        ).join('');
        lista.style.display = 'block';

        lista.querySelectorAll('.item-sugestao').forEach(el => {
            el.addEventListener('click', () => irParaPaciente(el.dataset.id));
        });
    }

    input.addEventListener('input', () => {
        const termo = input.value.trim();
        clearTimeout(debounceTimer);
        if (termo.length < 2) { lista.style.display = 'none'; return; }

        debounceTimer = setTimeout(() => {
            fetch('<?=BASE_URL?>buscar-paciente?term=' + encodeURIComponent(termo))
                .then(r => r.json())
                .then(renderSugestoes)
                .catch(() => { lista.style.display = 'none'; });
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#buscaPacienteInput') && !e.target.closest('#listaSugestoes')) {
            lista.style.display = 'none';
        }
    });

    btnLimpar.addEventListener('click', () => {
        window.location.href = '<?=BASE_URL?>relatorios/paciente';
    });
})();
</script>
<?php if($paciente):?>
<div style="margin-top:2rem;text-align:center;"><h3>Odontograma de <?=htmlspecialchars($paciente['nome'])?></h3>
<div class="canvas-container" style="position:relative;display:inline-block;max-width:100%;">
    <img src="<?=BASE_URL?>public/assets/img/odontograma.png" usemap="#image-map2" class="img-odontograma" id="odo-img" style="max-width:100%;">
    <svg id="odo-svg" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;"></svg>
    <map name="image-map2">
        <area alt="18" coords="53,251,99,157" shape="rect"><area alt="17" coords="147,156,103,249" shape="rect">
        <area alt="16" coords="207,155,151,246" shape="rect"><area alt="15" coords="241,152,209,242" shape="rect">
    </map>
</div>
<div style="display:flex;gap:1rem;justify-content:center;margin:.5rem 0;">
    <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:12px;height:12px;background:rgba(0,180,0,.6);border-radius:50%;display:inline-block;"></span> Concluído</span>
    <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:12px;height:12px;background:rgba(220,0,0,.6);border-radius:50%;display:inline-block;"></span> Pendente</span>
    <span style="display:inline-flex;align-items:center;gap:.3rem;"><span style="width:12px;height:12px;background:rgba(255,200,0,.6);border-radius:50%;display:inline-block;"></span> Misto</span>
</div>
</div>
<h3 style="margin-top:2rem;">Histórico de Procedimentos</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Data</th><th>Procedimento</th><th>Local</th><th>Status</th><th>Pagamento</th><th>Arquivo</th></tr></thead><tbody>
<?php foreach($procedimentos as $p):$cor=match($p['status_execucao']){'feito'=>'green','pendente'=>'orange','finalizado'=>'#3498db',default=>'#666'};?>
<tr><td><?=date('d/m/Y',strtotime($p['data_atendimento']))?></td><td><?=htmlspecialchars($p['procedimento_nome'])?></td>
<td><?=htmlspecialchars($p['local']??'')?></td>
<td><span style="color:<?=$cor?>;font-weight:bold;"><?=ucfirst($p['status_execucao'])?></span></td>
<td><?=ucfirst($p['status_pagamento']??'')?></td>
<td><?php if($p['url_arquivo']):?><a href="<?=BASE_URL.htmlspecialchars($p['url_arquivo'])?>" target="_blank" class="btn btn-secondary" style="padding:2px 8px;">Ver</a><?php endif;?></td></tr>
<?php endforeach;?>
<?php if(!$procedimentos):?><tr><td colspan="6" style="text-align:center;">Nenhum registro.</td></tr><?php endif;?>
</tbody></table>
<?php if($totalPags>1):?><div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
<?php for($i=1;$i<=$totalPags;$i++):$q=array_merge($_GET,['pagina'=>$i]);?><a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>"><?=$i?></a><?php endfor;?></div><?php endif;?>
<?php elseif($nome):?><p class="error" style="margin-top:1rem;">Paciente "<?=htmlspecialchars($nome)?>" não encontrado.</p><?php else: ?><p class="text-muted" style="margin-top:1.5rem;text-align:center;">Digite o nome do paciente acima e selecione na lista de sugestões.</p><?php endif;?>
</div>
<script>
var coresHist=<?=json_encode($cores)?>;
function colorir(){
    var img=document.getElementById('odo-img'),svg=document.getElementById('odo-svg');if(!img||!svg)return;
    svg.innerHTML='';var sx=img.clientWidth/img.naturalWidth,sy=img.clientHeight/img.naturalHeight;
    var mapa={18:[53,157,99,251],17:[103,156,147,249],16:[151,155,207,246],15:[209,152,241,242],14:[246,149,274,241],13:[277,148,314,238],12:[317,152,352,243],11:[355,153,397,246],21:[403,154,442,244],22:[446,153,479,243],23:[481,142,521,243],24:[525,146,561,239],25:[564,146,590,237],26:[593,148,648,238],27:[653,151,703,239],28:[705,149,741,241],48:[51,285,103,360],47:[109,284,160,363],46:[167,281,219,363],45:[221,278,258,378],44:[260,275,296,390],43:[298,276,336,384],42:[338,275,368,384],41:[370,276,395,383],31:[398,275,426,380],32:[428,275,454,382],33:[456,274,493,391],34:[496,274,531,383],35:[534,274,571,379],36:[575,274,636,384],37:[640,274,688,384],38:[694,272,742,375]};
    var cm={'green':'rgba(0,180,0,.4)','red':'rgba(220,0,0,.4)','yellow':'rgba(255,200,0,.5)'};
    for(var d in coresHist){var co=mapa[parseInt(d)];if(!co)continue;var r=document.createElementNS('http://www.w3.org/2000/svg','rect');r.setAttribute('x',co[0]*sx);r.setAttribute('y',co[1]*sy);r.setAttribute('width',(co[2]-co[0])*sx);r.setAttribute('height',(co[3]-co[1])*sy);r.setAttribute('fill',cm[coresHist[d]]||'transparent');r.setAttribute('rx','4');svg.appendChild(r);}
}
var img=document.getElementById('odo-img');if(img){if(img.complete)colorir();else img.addEventListener('load',colorir);}
window.addEventListener('resize',colorir);
</script>
