<div class="card"><h2>📊 Relatório Financeiro — Rateio Detalhado</h2>
<p class="text-muted">Exibe separadamente: especialista | clínico vendedor | clínica | taxa cartão | líquido</p>
<form method="GET" action="<?=BASE_URL?>relatorios/rateio" style="display:flex;gap:1rem;margin:1rem 0;flex-wrap:wrap;">
    <div class="form-group" style="margin:0;"><label>De</label><input type="date" name="de" value="<?=$ini?>"></div>
    <div class="form-group" style="margin:0;"><label>Até</label><input type="date" name="ate" value="<?=$fim?>"></div>
    <button type="submit" class="btn btn-primary">Filtrar</button>
</form>
<div class="dashboard-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card"><h3>Bruto</h3><div class="stat-value">R$ <?=number_format($totais['bruto']??0,2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:#e74c3c;"><h3>Taxa Cartão</h3><div class="stat-value" style="color:#e74c3c;">R$ <?=number_format($totais['taxa']??0,2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:#9b59b6;"><h3>Especialista</h3><div class="stat-value" style="color:#9b59b6;">R$ <?=number_format($totais['esp']??0,2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:#f39c12;"><h3>Vendedor</h3><div class="stat-value" style="color:#f39c12;">R$ <?=number_format($totais['vend']??0,2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:#27ae60;"><h3>Clínica (Líquido)</h3><div class="stat-value" style="color:#27ae60;">R$ <?=number_format($totais['liquido']??0,2,',','.')?></div></div>
</div>
<div style="overflow-x:auto;">
<table class="mobile-card-table"><thead><tr>
    <th>Data</th><th>Paciente</th><th>Procedimentos</th><th>Executor</th><th>Vendedor</th>
    <th>Bruto</th><th style="color:#e74c3c;">Taxa</th><th style="color:#9b59b6;">Especialista</th>
    <th style="color:#f39c12;">Vendedor R$</th><th>Comissão</th><th style="color:#27ae60;font-weight:bold;">Líquido</th>
</tr></thead><tbody>
<?php foreach($registros as $r):?>
<tr>
    <td><?=date('d/m/Y',strtotime($r['data_atendimento']))?></td>
    <td><?=htmlspecialchars($r['paciente']??'')?></td>
    <td style="font-size:.85rem;"><?=htmlspecialchars($r['procedimentos']??'')?></td>
    <td><?=htmlspecialchars($r['dentista_executor']??'')?></td>
    <td><?=htmlspecialchars($r['dentista_vendedor']??'—')?></td>
    <td>R$ <?=number_format($r['valor_bruto'],2,',','.')?></td>
    <td style="color:#e74c3c;">R$ <?=number_format($r['taxa_cartao'],2,',','.')?></td>
    <td style="color:#9b59b6;">R$ <?=number_format($r['total_especialista'],2,',','.')?></td>
    <td style="color:#f39c12;">R$ <?=number_format($r['total_vendedor'],2,',','.')?></td>
    <td>R$ <?=number_format($r['comissao_dentista'],2,',','.')?></td>
    <td style="font-weight:bold;color:#27ae60;">R$ <?=number_format($r['valor_liquido_clinica'],2,',','.')?></td>
</tr>
<?php endforeach;?>
<?php if(!$registros):?><tr><td colspan="11" style="text-align:center;padding:20px;">Nenhum registro no período.</td></tr><?php endif;?>
</tbody>
<?php if($registros):?><tfoot style="background:#2c3e50;color:#fff;font-weight:bold;"><tr>
    <td colspan="5">TOTAIS</td>
    <td>R$ <?=number_format($totais['bruto']??0,2,',','.')?></td>
    <td>R$ <?=number_format($totais['taxa']??0,2,',','.')?></td>
    <td>R$ <?=number_format($totais['esp']??0,2,',','.')?></td>
    <td>R$ <?=number_format($totais['vend']??0,2,',','.')?></td>
    <td>R$ <?=number_format($totais['comissao']??0,2,',','.')?></td>
    <td>R$ <?=number_format($totais['liquido']??0,2,',','.')?></td>
</tr></tfoot><?php endif;?>
</table></div>
<?php if($totalPags>1):?><div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
<?php for($i=1;$i<=$totalPags;$i++):$q=array_merge($_GET,['pagina'=>$i]);?><a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>"><?=$i?></a><?php endfor;?></div><?php endif;?>
</div>
