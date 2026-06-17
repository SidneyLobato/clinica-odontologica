<style>
.modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.6);}
.modal-content{background:#fefefe;margin:5% auto;padding:25px;width:90%;max-width:700px;border-radius:8px;position:relative;max-height:85vh;overflow-y:auto;}
.modal-close{position:absolute;top:10px;right:20px;font-size:28px;cursor:pointer;color:#aaa;}
.stat-card-taxa{background:#fff;border-radius:8px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,.08);border-left:4px solid #e67e22;}
.stat-card-taxa h3{font-size:.85rem;color:#666;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;}
.stat-card-taxa .stat-value{font-size:1.75rem;font-weight:700;color:#e67e22;}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;">
    <h1>Dashboard Financeiro</h1>
    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="<?=BASE_URL?>dashboard?mes=<?=$mesAnt?>" class="btn btn-secondary">&lt;</a>
        <strong><?=$mesAtual?></strong>
        <a href="<?=BASE_URL?>dashboard?mes=<?=$mesProx?>" class="btn btn-secondary">&gt;</a>
    </div>
</div>

<?php $totalTaxas = $totalTaxas ?? 0.0; if(is_admin()):?>
<div class="dashboard-grid" style="margin-top:1.5rem;grid-template-columns:repeat(4,1fr);">
    <div class="stat-card">
        <h3>Faturamento Bruto</h3>
        <div class="stat-value">R$ <?=number_format($faturamentoBruto,2,',','.')?></div>
    </div>
    <div class="stat-card stat-card-taxa">
        <h3>Taxas de Cartão</h3>
        <div class="stat-value" style="color:#e67e22;">R$ <?=number_format($totalTaxas,2,',','.')?></div>
        <?php if($faturamentoBruto>0):?>
        <small style="color:#999;"><?=$faturamentoBruto>0?number_format(($totalTaxas/$faturamentoBruto)*100,2,',','.').'% do bruto':''?></small>
        <?php endif;?>
    </div>
    <div class="stat-card" style="border-left-color:var(--danger-color);">
        <h3>Despesas</h3>
        <div class="stat-value">R$ <?=number_format($totalDespesas,2,',','.')?></div>
    </div>
    <div class="stat-card" style="border-left-color:var(--success-color);">
        <h3>Resultado Líquido</h3>
        <div class="stat-value">R$ <?=number_format($lucroLiquido-$totalDespesas,2,',','.')?></div>
        <small style="color:#999;">Após taxas e comissões</small>
    </div>
</div>
<?php endif;?>

<div class="card" style="margin-top:2rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h3>Histórico de Atendimentos</h3>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <form method="GET" action="<?=BASE_URL?>dashboard" style="display:flex;gap:.5rem;">
                <input type="hidden" name="mes" value="<?=htmlspecialchars($mes)?>">
                <input type="text" name="busca" placeholder="Buscar paciente..." value="<?=htmlspecialchars($busca)?>" style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;">
                <button type="submit" class="btn btn-secondary">Buscar</button>
            </form>
            <a href="<?=BASE_URL?>atendimentos" class="btn btn-primary">+ Novo Atendimento</a>
        </div>
    </div>
    <table class="mobile-card-table" style="margin-top:1rem;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Paciente</th>
                <th>Procedimentos</th>
                <th>Dentista</th>
                <?php if(is_admin()):?>
                <th>Bruto</th>
                <th style="color:#e67e22;">Taxa Cartão</th>
                <th>Líquido</th>
                <?php endif;?>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($ultimosAtendimentos as $at):
            $naoApl = $at['status_pagamento']==='nao_aplicavel';
            $temTaxa = (float)$at['taxa_cartao'] > 0;
        ?>
        <tr class="clickable-row" style="cursor:pointer;"
            data-id="<?=$at['id']?>"
            data-data="<?=date('d/m/Y H:i',strtotime($at['data_atendimento']))?>"
            data-paciente="<?=htmlspecialchars($at['paciente_nome'])?>"
            data-dentista="<?=htmlspecialchars($at['dentista'])?>"
            data-procs="<?=htmlspecialchars($at['procedimentos']??'')?>"
            data-bruto="<?=$naoApl?'N/A':'R$ '.number_format($at['valor_bruto_total']??0,2,',','.')?>"
            data-taxa="<?=$naoApl?'N/A':'R$ '.number_format($at['taxa_cartao'],2,',','.')?>"
            data-comissao="<?=$naoApl?'N/A':'R$ '.number_format($at['comissao_dentista'],2,',','.')?>"
            data-custo="<?=$naoApl?'N/A':'R$ '.number_format($at['custo_auxiliar'],2,',','.')?>"
            data-liquido="<?=$naoApl?'N/A':'R$ '.number_format($at['valor_liquido_clinica'],2,',','.')?>">
            <td><?=date('d/m/Y H:i',strtotime($at['data_atendimento']))?></td>
            <td><?=htmlspecialchars($at['paciente_nome'])?></td>
            <td style="font-size:.85rem;"><?=htmlspecialchars($at['procedimentos']??'')?></td>
            <td><?=htmlspecialchars($at['dentista'])?></td>
            <?php if(is_admin()):?>
            <td style="font-weight:bold;">
                <?=$naoApl?'N/A':'R$ '.number_format($at['valor_bruto_total']??0,2,',','.')?>
            </td>
            <td style="color:<?=$temTaxa?'#e67e22':'#999'?>;font-weight:<?=$temTaxa?'bold':'normal'?>;">
                <?=$naoApl?'N/A':'R$ '.number_format($at['taxa_cartao'],2,',','.')?>
            </td>
            <td style="color:green;font-weight:bold;">
                <?=$naoApl?'N/A':'R$ '.number_format($at['valor_liquido_clinica'],2,',','.')?>
            </td>
            <?php endif;?>
            <td>
                <a href="<?=BASE_URL?>recibo?id=<?=$at['id']?>" class="btn btn-secondary" target="_blank"
                   onclick="event.stopPropagation();" style="padding:4px 10px;font-size:.8rem;">Recibo</a>
            </td>
        </tr>
        <?php endforeach;?>
        <?php if(!$ultimosAtendimentos):?>
        <tr><td colspan="8" style="text-align:center;padding:20px;">Nenhum atendimento.</td></tr>
        <?php endif;?>
        </tbody>
    </table>

    <?php if($totalPags>1):?>
    <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
        <?php for($i=1;$i<=$totalPags;$i++):$q=array_merge($_GET,['pagina'=>$i]);?>
        <a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>"><?=$i?></a>
        <?php endfor;?>
    </div>
    <?php endif;?>
</div>

<div id="detalhesModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" id="btnClose">&times;</span>
        <h2>Detalhes do Atendimento</h2>
        <div id="modalBody"></div>
    </div>
</div>

<script>
var modal=document.getElementById('detalhesModal'),
    body=document.getElementById('modalBody'),
    isAdm=<?=is_admin()?'true':'false'?>,
    base='<?=BASE_URL?>';

document.getElementById('btnClose').onclick=()=>{modal.style.display='none';body.innerHTML='';};
window.onclick=e=>{if(e.target===modal){modal.style.display='none';body.innerHTML='';}};

document.querySelectorAll('.clickable-row').forEach(r=>{
    r.addEventListener('click',function(){
        var d=this.dataset;
        var h='<p><b>Data:</b> '+d.data+'</p>'
             +'<p><b>Paciente:</b> '+d.paciente+'</p>'
             +'<p><b>Procedimentos:</b> '+d.procs+'</p>'
             +'<p><b>Dentista:</b> '+d.dentista+'</p>';
        if(isAdm){
            h+='<hr style="margin:.75rem 0;">'
              +'<p><b>Valor Bruto:</b> '+d.bruto+'</p>'
              +'<p><b>Taxa Cartão:</b> <span style="color:#e67e22;">'+d.taxa+'</span></p>'
              +'<p><b>Custo Auxiliar:</b> '+d.custo+'</p>'
              +'<p><b>Comissão Dentista:</b> '+d.comissao+'</p>'
              +'<p><b>Líquido Clínica:</b> <span style="color:green;font-weight:bold;">'+d.liquido+'</span></p>';
        }
        body.innerHTML=h;
        modal.style.display='block';
    });
});
</script>
