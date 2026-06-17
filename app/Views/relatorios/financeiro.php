<div class="card"><h2>Relatório Financeiro Geral</h2>
<form method="GET" action="<?=BASE_URL?>relatorios/financeiro" class="card" style="margin-top:1rem;">
    <div style="display:flex;gap:1rem;align-items:center;">
        <div class="form-group"><label>Início</label><input type="date" name="inicio" value="<?=$ini?>"></div>
        <div class="form-group"><label>Fim</label><input type="date" name="fim" value="<?=$fim?>"></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>
<div class="dashboard-grid" style="margin-top:2rem;">
    <div class="stat-card"><h3>Faturamento Bruto</h3><div class="stat-value">R$ <?=number_format($totais['bruto'],2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:var(--danger-color);"><h3>Despesas</h3><div class="stat-value">R$ <?=number_format($totalDespesas,2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:var(--success-color);"><h3>Resultado Líquido</h3><div class="stat-value">R$ <?=number_format($totais['liquido']-$totalDespesas,2,',','.')?></div></div>
</div>
<div style="margin-top:2rem;"><canvas id="chart" style="max-height:380px;"></canvas></div>
<h3 style="margin-top:2rem;">Atendimentos</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Data</th><th>Paciente</th><th>Procedimento</th><th>Bruto</th><th>Líquido</th></tr></thead><tbody>
<?php foreach($atendimentos as $at):?>
<tr><td><?=date('d/m/Y',strtotime($at['data_atendimento']))?></td><td><?=htmlspecialchars($at['paciente']??'')?></td>
<td style="font-size:.85rem;"><?=htmlspecialchars($at['procedimentos']??'')?></td>
<td>R$ <?=number_format($at['valor_bruto'],2,',','.')?></td>
<td>R$ <?=number_format($at['valor_liquido_clinica'],2,',','.')?></td></tr>
<?php endforeach;?>
<?php if(!$atendimentos):?><tr><td colspan="5" style="text-align:center;">Nenhum.</td></tr><?php endif;?>
</tbody></table>
<?php if($totalPagAt>1):?><div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:.5rem;">
<?php for($i=1;$i<=$totalPagAt;$i++):$q=array_merge($_GET,['pagina_at'=>$i]);?><a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>"><?=$i?></a><?php endfor;?></div><?php endif;?>
</div>
<script>
new Chart(document.getElementById('chart').getContext('2d'),{type:'line',data:{labels:<?=json_encode($labels)?>,datasets:[{label:'Faturamento',data:<?=json_encode($fat)?>,borderColor:'rgba(54,162,235,1)',backgroundColor:'rgba(54,162,235,.15)',fill:true,tension:.1},{label:'Despesas',data:<?=json_encode($desp)?>,borderColor:'rgba(255,99,132,1)',backgroundColor:'rgba(255,99,132,.15)',fill:true,tension:.1},{label:'Lucro',data:<?=json_encode($lucro)?>,borderColor:'rgba(75,192,192,1)',backgroundColor:'rgba(75,192,192,.15)',fill:true,tension:.1}]},options:{responsive:true,plugins:{tooltip:{callbacks:{label:ctx=>'R$ '+ctx.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2})}}},scales:{y:{ticks:{callback:v=>'R$ '+v.toLocaleString('pt-BR')}}}}});
</script>
