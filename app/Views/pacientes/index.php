<div class="card">
    <h2>Gestão de Pacientes</h2>
    <?php $ms=['sucesso'=>'Salvo!','excluido'=>'Removido!']; $er=['conflito'=>'Vinculado a atendimentos.','cpf_duplicado'=>'CPF já cadastrado.']; ?>
    <?php if($msg&&isset($ms[$msg])):?><p class="success"><?=$ms[$msg]?></p><?php endif;?>
    <?php if($erro&&isset($er[$erro])):?><p class="error"><?=$er[$erro]?></p><?php endif;?>
    <div class="card" style="margin-top:1.5rem;"><h3>Novo Paciente</h3><?php $paciente=null;include __DIR__.'/_form.php';?></div>
    <h3 style="margin-top:2rem;">Pacientes Cadastrados</h3>
    <form method="GET" action="<?=BASE_URL?>pacientes" style="display:flex;gap:.5rem;margin-bottom:1rem;">
        <input type="text" name="busca" value="<?=htmlspecialchars($b)?>" placeholder="Nome ou CPF..." style="flex-grow:1;padding:6px 10px;border:1px solid #ddd;border-radius:6px;">
        <button type="submit" class="btn btn-secondary">Buscar</button>
    </form>
    <table class="mobile-card-table"><thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>Ações</th></tr></thead><tbody>
    <?php foreach($pacientes as $p):?>
    <tr><td><?=htmlspecialchars($p['nome'])?></td><td><?=htmlspecialchars($p['cpf']??'')?></td><td><?=htmlspecialchars($p['telefone']??'')?></td>
    <td style="display:flex;gap:.5rem;"><a href="<?=BASE_URL?>pacientes/editar/<?=$p['id']?>" class="btn btn-primary" style="padding:4px 10px;">Editar</a>
    <a href="<?=BASE_URL?>pacientes/excluir/<?=$p['id']?>" class="btn btn-danger" style="padding:4px 10px;" onclick="return confirm('Remover?');">Remover</a></td></tr>
    <?php endforeach;?>
    <?php if(!$pacientes):?><tr><td colspan="4" style="text-align:center;">Nenhum paciente.</td></tr><?php endif;?>
    </tbody></table>
    <?php if($totalPags>1):?><div style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:1rem;">
    <?php for($i=1;$i<=$totalPags;$i++):$q=array_merge($_GET,['pagina'=>$i]);?>
    <a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>"><?=$i?></a>
    <?php endfor;?></div><?php endif;?>
</div>
<style>.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}.grid-container{display:grid;grid-template-columns:repeat(6,1fr);gap:1rem;margin-bottom:1rem;}.grid-col-2{grid-column:span 2;}.grid-col-3{grid-column:span 3;}.grid-col-4{grid-column:span 4;}.grid-col-6{grid-column:span 6;}@media(max-width:768px){.grid-col-2,.grid-col-3,.grid-col-4,.grid-col-6{grid-column:span 6;}}</style>
