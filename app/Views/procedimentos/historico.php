<div class="card"><h2>Histórico de Preços — <?=htmlspecialchars($pr['nome']??'')?></h2>
<a href="<?=BASE_URL?>procedimentos" class="btn btn-secondary" style="margin-bottom:1rem;">← Voltar</a>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Valor</th><th>Vigência Início</th><th>Vigência Fim</th><th>Alterado por</th></tr></thead><tbody>
<?php foreach($hist as $h):?>
<tr><td>R$ <?=number_format($h['valor'],2,',','.')?></td><td><?=date('d/m/Y H:i',strtotime($h['vigencia_inicio']))?></td>
<td><?=$h['vigencia_fim']?date('d/m/Y H:i',strtotime($h['vigencia_fim'])):'<span style="color:green;">Vigente</span>'?></td>
<td><?=htmlspecialchars($h['usuario_nome']??'—')?></td></tr>
<?php endforeach;?>
<?php if(!$hist):?><tr><td colspan="4" style="text-align:center;">Nenhum histórico.</td></tr><?php endif;?>
</tbody></table></div>
