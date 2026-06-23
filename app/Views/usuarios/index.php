<div class="card"><h2>Gestão de Usuários</h2>
<?php $ms=['sucesso'=>'Salvo!','excluido'=>'Removido!'];$er=['autoexclusao'=>'Não pode excluir seu próprio usuário.','conflito'=>'Vinculado a atendimentos.','duplicado'=>'Login já em uso.'];?>
<?php if($msg&&isset($ms[$msg])):?><p class="success"><?=$ms[$msg]?></p><?php endif;?>
<?php if($erro&&isset($er[$erro])):?><p class="error"><?=$er[$erro]?></p><?php endif;?>
<div class="card" style="margin-top:1.5rem;"><h3>Novo Usuário</h3>
<form action="<?=BASE_URL?>usuarios/criar" method="POST">
    <div class="form-group"><label>Nome</label><input type="text" name="nome" required></div>
    <div class="form-group"><label>Login</label><input type="text" name="login" required></div>
    <div class="form-group"><label>Senha</label><input type="password" name="senha" required></div>
    <div class="form-group"><label>Perfil</label><select name="perfil" required><option value="recepcionista">Recepcionista</option><option value="dentista">Dentista</option><option value="proprietario">Proprietário</option></select></div>
    <button type="submit" class="btn btn-success">Salvar</button>
</form></div>
<h3 style="margin-top:2rem;">Usuários</h3>
<table class="mobile-card-table" style="margin-top:1rem;"><thead><tr><th>Nome</th><th>Login</th><th>Perfil</th><th>Ações</th></tr></thead><tbody>
<?php foreach($u as $us):?>
<tr><td><?=htmlspecialchars($us['nome'])?></td><td><?=htmlspecialchars($us['login'])?></td><td><?=ucfirst($us['perfil'])?></td>
<td style="display:flex;gap:.5rem;"><a href="<?=BASE_URL?>usuarios/editar/<?=$us['id']?>" class="btn btn-primary" style="padding:4px 10px;">Editar</a>
<?php if($us['id']!==(int)$_SESSION['usuario_id']):?><a href="<?=BASE_URL?>usuarios/excluir/<?=$us['id']?>" class="btn btn-danger" style="padding:4px 10px;" onclick="return confirm('Remover?');">Remover</a><?php endif;?>
</td></tr>
<?php endforeach;?>
</tbody></table></div>
<style>.success{color:green;background:#e8f5e9;padding:.75rem;border-radius:6px;margin-bottom:1rem;}</style>
