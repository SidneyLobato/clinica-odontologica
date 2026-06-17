<?php $p=$paciente??[];$action=isset($p['id'])?BASE_URL.'pacientes/editar/'.$p['id']:BASE_URL.'pacientes/criar';?>
<form action="<?=$action?>" method="POST">
<?php if(!empty($p['id'])):?><input type="hidden" name="paciente_id" value="<?=$p['id']?>"><?php endif;?>
<div class="grid-container">
    <div class="form-group grid-col-6"><label>Nome</label><input type="text" name="paciente_nome" required value="<?=htmlspecialchars($p['nome']??'')?>"></div>
    <div class="form-group grid-col-3"><label>CPF</label><input type="text" name="paciente_cpf" maxlength="14" oninput="mascaraCPF(this)" value="<?=htmlspecialchars($p['cpf']??'')?>"></div>
    <div class="form-group grid-col-3"><label>Nascimento</label><input type="date" name="paciente_data_nascimento" value="<?=htmlspecialchars($p['data_nascimento']??'')?>"></div>
    <div class="form-group grid-col-3"><label>Telefone</label><input type="text" name="paciente_telefone" oninput="mascaraTel(this)" value="<?=htmlspecialchars($p['telefone']??'')?>"></div>
    <div class="form-group grid-col-3"><label>E-mail</label><input type="email" name="paciente_email" value="<?=htmlspecialchars($p['email']??'')?>"></div>
    <div class="form-group grid-col-2"><label>CEP</label><input type="text" name="paciente_cep" oninput="mascaraCEP(this)" value="<?=htmlspecialchars($p['cep']??'')?>"></div>
    <div class="form-group grid-col-4"><label>Endereço</label><input type="text" name="paciente_endereco" value="<?=htmlspecialchars($p['endereco']??'')?>"></div>
    <div class="form-group grid-col-2"><label>Número</label><input type="text" name="paciente_numero" value="<?=htmlspecialchars($p['numero']??'')?>"></div>
    <div class="form-group grid-col-4"><label>Bairro</label><input type="text" name="paciente_bairro" value="<?=htmlspecialchars($p['bairro']??'')?>"></div>
    <div class="form-group grid-col-4"><label>Cidade</label><input type="text" name="paciente_cidade" value="<?=htmlspecialchars($p['cidade']??'')?>"></div>
    <div class="form-group grid-col-2"><label>Estado</label><input type="text" name="paciente_estado" maxlength="2" value="<?=htmlspecialchars($p['estado']??'')?>"></div>
</div>
<button type="submit" class="btn btn-success">Salvar</button>
<a href="<?=BASE_URL?>pacientes" class="btn btn-secondary">Cancelar</a>
</form>
<script>
function mascaraCPF(i){var v=i.value.replace(/\D/g,'');v=v.replace(/(\d{3})(\d)/,'$1.$2');v=v.replace(/(\d{3})(\d)/,'$1.$2');v=v.replace(/(\d{3})(\d{1,2})$/,'$1-$2');i.value=v;}
function mascaraTel(i){var v=i.value.replace(/\D/g,'');v=v.replace(/^(\d{2})(\d)/g,'($1) $2');v=v.replace(/(\d)(\d{4})$/,'$1-$2');i.value=v;}
function mascaraCEP(i){var v=i.value.replace(/\D/g,'');v=v.replace(/(\d{5})(\d)/,'$1-$2');i.value=v;}
</script>
