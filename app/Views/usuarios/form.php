<?php $us=$usuario??[];$action=isset($us['id'])?BASE_URL.'usuarios/editar/'.$us['id']:BASE_URL.'usuarios/criar';?>
<div class="card"><h2><?=htmlspecialchars($titulo)?></h2>
<?php if(isset($_GET['erro'])&&$_GET['erro']==='duplicado'):?><p class="error">Login já em uso.</p><?php endif;?>
<form action="<?=$action?>" method="POST" style="margin-top:1rem;">
    <?php if(!empty($us['id'])):?><input type="hidden" name="id" value="<?=$us['id']?>"><?php endif;?>
    <div class="form-group"><label>Nome</label><input type="text" name="nome" required value="<?=htmlspecialchars($us['nome']??'')?>"></div>
    <div class="form-group"><label>Login</label><input type="text" name="login" required value="<?=htmlspecialchars($us['login']??'')?>"></div>
    <div class="form-group"><label>Senha <?=isset($us['id'])?'(em branco = manter)':''?></label><input type="password" name="senha" <?=!isset($us['id'])?'required':''?>></div>
    <div class="form-group"><label>Perfil</label><select name="perfil" required>
        <?php foreach(['recepcionista'=>'Recepcionista','dentista'=>'Dentista','proprietario'=>'Proprietário'] as $v=>$l):?>
        <option value="<?=$v?>" <?=($us['perfil']??'')===$v?'selected':''?>><?=$l?></option>
        <?php endforeach;?></select></div>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="<?=BASE_URL?>usuarios" class="btn btn-secondary">Cancelar</a>
</form></div>
