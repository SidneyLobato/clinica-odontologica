<div class="card"><h2>Relatório por Procedimentos</h2>
<form method="GET" action="<?=BASE_URL?>relatorios/procedimentos" class="card" style="margin-top:1rem;">
    <div style="display:flex;gap:1rem;align-items:center;">
        <div class="form-group"><label>Início</label><input type="date" name="inicio" value="<?=$ini?>"></div>
        <div class="form-group"><label>Fim</label><input type="date" name="fim" value="<?=$fim?>"></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>
<table class="mobile-card-table" style="margin-top:2rem;"><thead><tr><th>Procedimento</th><th>Vezes Executado</th><th>Representação</th><th>Valor Bruto</th></tr></thead><tbody>
<?php foreach($dados as $p):$pct=$total>0?($p['quantidade_executada']/$total)*100:0;?>
<tr><td><?=htmlspecialchars($p['procedimento_nome'])?></td><td><?=$p['quantidade_executada']?></td>
<td><?=number_format($pct,2,',','.')?>%</td>
<td style="color:var(--success-color);font-weight:bold;">R$ <?=number_format($p['valor_bruto_total'],2,',','.')?></td></tr>
<?php endforeach;?>
<?php if(!$dados):?><tr><td colspan="4" style="text-align:center;">Nenhum dado.</td></tr><?php endif;?>
</tbody><?php if($dados):?><tfoot style="background:#f8f9fa;font-weight:bold;"><tr><td>Total</td><td><?=$total?></td><td>100%</td><td>R$ <?=number_format(array_sum(array_column($dados,'valor_bruto_total')),2,',','.')?></td></tr></tfoot><?php endif;?>
</table></div>
