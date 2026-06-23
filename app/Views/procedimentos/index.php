<div class="card"><h2>Gestão de Procedimentos</h2>
<?php $ms=['sucesso'=>'Salvo!','preco_ok'=>'Preço atualizado. Histórico anterior preservado.','excluido'=>'Removido.'];$er=['conflito'=>'Vinculado a atendimentos.'];?>
<?php if($msg&&isset($ms[$msg])):?><p class="success"><?=$ms[$msg]?></p><?php endif;?>
<?php if($erro&&isset($er[$erro])):?><p class="error"><?=$er[$erro]?></p><?php endif;?>
<div class="card" style="margin-top:1.5rem;"><h3>Novo Procedimento</h3>
<form action="<?=BASE_URL?>procedimentos/criar" method="POST">
    <div class="form-group"><label>Nome</label><input type="text" name="nome" required></div>
    <div class="form-group"><label>Categoria</label><select name="categoria" required><option value="geral">Geral</option><option value="especializado">Especializado</option><option value="protese">Prótese</option></select></div>
    <div class="form-group"><label>Requer Arquivo</label><select name="tipo"><option value="">Não</option><option value="1">Sim</option></select></div>
    <div class="form-group"><label>Valor (R$)</label><input type="number" step="0.01" name="valor_base" required></div>
    <button type="submit" class="btn btn-success">Salvar</button>
</form></div>
<h3 style="margin-top:2rem;">Procedimentos</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Nome</th><th>Categoria</th><th>Valor Atual</th><th>Atualizar Preço</th><th>Histórico</th><th>Excluir</th></tr></thead><tbody>
<?php foreach($pr as $p):?>
<tr><td><?=htmlspecialchars($p['nome'])?></td><td><?=ucfirst($p['categoria'])?></td><td>R$ <?=number_format($p['valor_base'],2,',','.')?></td>
<td><form action="<?=BASE_URL?>procedimentos/atualizarPreco" method="POST" style="display:inline-flex;gap:4px;">
    <input type="hidden" name="id" value="<?=$p['id']?>">
    <input type="number" step="0.01" name="valor_base" value="<?=number_format($p['valor_base'],2,'.','')?>" style="width:90px;padding:4px;">
    <button type="submit" class="btn btn-primary" style="padding:4px 8px;">✓</button>
</form></td>
<td><a href="<?=BASE_URL?>procedimentos/historico/<?=$p['id']?>" class="btn btn-secondary" style="padding:4px 8px;">Histórico</a></td>
<td><a href="<?=BASE_URL?>procedimentos/excluir/<?=$p['id']?>" class="btn btn-danger" style="padding:4px 8px;" onclick="return confirm('Remover?');">✕</a></td></tr>
<?php endforeach;?>
</tbody></table>
<p class="text-muted" style="font-size:.85rem;margin-top:.5rem;">⚠ Ao atualizar o preço, atendimentos anteriores mantêm o valor original.</p></div>
<style>.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}</style>
