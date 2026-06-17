<?php
class ConfiguracaoController extends BaseController {
    public function index(?string $p=null): void {
        requer_login();
        $s=$this->db->prepare("SELECT nome,login FROM usuarios WHERE id=?");$s->execute([$_SESSION['usuario_id']]);$usuario=$s->fetch();
        $msg=$_GET['msg']??'';$erro=$_GET['erro']??'';
        $this->render('configuracoes/index',compact('usuario','msg','erro'));
    }
    public function salvar(?string $p=null): void {
        requer_login();if(!$this->isPost()){$this->redirect('configuracoes');return;}
        $nome=trim($_POST['nome']??'');if(!$nome){$this->redirect('configuracoes?erro=geral');return;}
        $s=$this->db->prepare("SELECT senha FROM usuarios WHERE id=?");$s->execute([$_SESSION['usuario_id']]);$atual=$s->fetch();
        $sql="UPDATE usuarios SET nome=?";$params=[$nome];
        $ant=$_POST['senha_antiga']??'';$nov=$_POST['nova_senha']??'';$con=$_POST['confirmar_senha']??'';
        if($ant||$nov||$con){
            if(!$ant||!$nov||!$con){$this->redirect('configuracoes?erro=campos_vazios');return;}
            if(!password_verify($ant,$atual['senha'])){$this->redirect('configuracoes?erro=senha_incorreta');return;}
            if($nov!==$con){$this->redirect('configuracoes?erro=senhas_diferentes');return;}
            $sql.=",senha=?";$params[]=password_hash($nov,PASSWORD_BCRYPT);
        }
        $sql.=" WHERE id=?";$params[]=$_SESSION['usuario_id'];
        $this->db->prepare($sql)->execute($params);$_SESSION['usuario_nome']=$nome;
        $this->redirect('configuracoes?msg=sucesso');
    }
}
