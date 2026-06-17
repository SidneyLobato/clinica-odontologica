<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <a href="<?=BASE_URL?>relatorios/diario?data=<?=$datAnt?>" class="btn btn-secondary">&lt;</a>
    <h2>Relatório do Dia: <?=date('d/m/Y',strtotime($data))?></h2>
    <a href="<?=BASE_URL?>relatorios/diario?data=<?=$datProx?>" class="btn btn-secondary">&gt;</a>
</div>
<form method="GET" action="<?=BASE_URL?>relatorios/diario" class="card" style="margin-bottom:1.5rem;">
    <div class="form-group" style="max-width:250px;margin:auto;"><label>Selecionar data</label><input type="date" name="data" value="<?=$data?>" onchange="this.form.submit()"></div>
</form>
<?php if(is_admin()):?>
<div class="dashboard-grid">
    <div class="stat-card"><h3>Entrada Bruta</h3><div class="stat-value">R$ <?=number_format($resumo['faturamento_bruto'],2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:var(--danger-color);"><h3>Taxas</h3><div class="stat-value" style="color:var(--danger-color);">- R$ <?=number_format($resumo['total_taxas'],2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:var(--danger-color);"><h3>Despesas</h3><div class="stat-value" style="color:var(--danger-color);">- R$ <?=number_format($resumo['total_despesas'],2,',','.')?></div></div>
    <div class="stat-card" style="border-left-color:var(--success-color);"><h3>Lucro Líquido</h3><div class="stat-value">R$ <?=number_format($resumo['lucro_liquido'],2,',','.')?></div></div>
</div>
<?php endif;?>
<div class="card" style="margin-top:2rem;"><h3>Pagamentos por Dentista</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Dentista</th><th>Comissão</th></tr></thead><tbody>
<?php foreach($dentistas as $d):?>
<tr><td><?=htmlspecialchars($d['nome'])?></td><td style="color:green;font-weight:bold;">R$ <?=number_format($d['total_comissao'],2,',','.')?></td></tr>
<?php endforeach;?>
<?php if(!$dentistas):?><tr><td colspan="2" style="text-align:center;">Nenhum atendimento.</td></tr><?php endif;?>
</tbody></table></div>
<?php if(is_admin()&&$despesas):?>
<div class="card" style="margin-top:1.5rem;"><h3>Despesas do Dia</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Descrição</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>
<?php foreach($despesas as $d):?><tr><td><?=htmlspecialchars($d['descricao'])?></td><td><?=ucfirst($d['tipo'])?></td><td>R$ <?=number_format($d['valor'],2,',','.')?></td></tr><?php endforeach;?>
</tbody><tfoot><tr style="font-weight:bold;"><td colspan="2">Total</td><td>R$ <?=number_format($resumo['total_despesas'],2,',','.')?></td></tr></tfoot></table></div>
<?php endif;?>
</div>
