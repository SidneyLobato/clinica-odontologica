<div class="card"><h2>Relatório por Dentista</h2>
<form method="GET" action="<?=BASE_URL?>relatorios/dentistas" class="card" style="margin-top:1rem;">
    <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
        <div class="form-group"><label>Mês</label><input type="month" name="mes" value="<?=$mes?>"></div>
        <?php if(is_admin()):?><div class="form-group"><label>Dentista</label><select name="dentista_id"><option value="todos">Todos</option>
            <?php foreach($allDent as $d):?><option value="<?=$d['id']?>" <?=$did==$d['id']?'selected':''?>><?=htmlspecialchars($d['nome'])?></option><?php endforeach;?></select></div><?php endif;?>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>
<table class="mobile-card-table" style="margin-top:2rem;"><thead><tr><th>Dentista</th><th>Atendimentos</th><th>Faturamento Bruto</th><th>Valor p/ Dentista</th><th>Valor p/ Clínica</th></tr></thead><tbody>
<?php foreach($relatorio as $r):?>
<tr><td><?=htmlspecialchars($r['dentista_nome'])?></td><td><?=$r['total_atendimentos']?></td>
<td>R$ <?=number_format($r['faturamento_bruto'],2,',','.')?></td>
<td style="color:var(--success-color);font-weight:bold;">R$ <?=number_format($r['valor_para_dentista'],2,',','.')?></td>
<td style="color:var(--success-color);font-weight:bold;">R$ <?=number_format($r['valor_para_clinica'],2,',','.')?></td></tr>
<?php endforeach;?>
<?php if(!$relatorio):?><tr><td colspan="5" style="text-align:center;">Nenhum dado.</td></tr><?php endif;?>
</tbody></table></div>
