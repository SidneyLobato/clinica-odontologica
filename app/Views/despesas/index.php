<div class="card"><h2>Gestão de Despesas</h2>
<?php if(($msg??'')==='sucesso'):?><p class="success">Salvo!</p><?php elseif(($msg??'')==='excluido'):?><p class="success">Removido.</p><?php endif;?>
<div class="card" style="margin-top:1.5rem;"><h3>Nova Despesa</h3>
<form action="<?=BASE_URL?>despesas/criar" method="POST">
    <div class="form-group"><label>Descrição</label><input type="text" name="descricao" required></div>
    <div class="form-group"><label>Valor (R$)</label><input type="number" step="0.01" name="valor" required></div>
    <div class="form-group"><label>Tipo</label><select name="tipo"><option value="fixa">Fixa</option><option value="variavel">Variável</option></select></div>
    <div class="form-group"><label>Data</label><input type="date" name="data_despesa" required value="<?=date('Y-m-d')?>"></div>
    <button type="submit" class="btn btn-success">Salvar</button>
</form></div>
<h3 style="margin-top:2rem;">Despesas Lançadas</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Data</th><th>Descrição</th><th>Valor</th><th>Tipo</th><th>Ação</th></tr></thead><tbody>
<?php foreach($d as $dp):?>
<tr><td><?=date('d/m/Y',strtotime($dp['data_despesa']))?></td><td><?=htmlspecialchars($dp['descricao'])?></td><td>R$ <?=number_format($dp['valor'],2,',','.')?></td><td><?=ucfirst($dp['tipo'])?></td>
<td><a href="<?=BASE_URL?>despesas/excluir/<?=$dp['id']?>" class="btn btn-danger" style="padding:4px 10px;" onclick="return confirm('Remover?');">Remover</a></td></tr>
<?php endforeach;?>
<?php if(!$d):?><tr><td colspan="5" style="text-align:center;">Nenhuma despesa.</td></tr><?php endif;?>
</tbody></table></div>
<style>.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}</style>
