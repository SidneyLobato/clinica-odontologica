<?php
class UsuarioController extends BaseController {
    private UsuarioModel $m;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->m=new UsuarioModel($pdo);}
    public function index(?string $p=null): void {
        requer_admin();$u=$this->m->listarTodos();$msg=$_GET['msg']??'';$erro=$_GET['erro']??'';
        $this->render('usuarios/index',compact('u','msg','erro'));
    }
    public function criar(?string $p=null): void {
        requer_admin();if($this->isPost()){try{$this->m->salvar($_POST);$this->redirect('usuarios?msg=sucesso');}catch(\PDOException $e){$this->redirect('usuarios?erro=duplicado');}return;}
        $this->render('usuarios/form',['usuario'=>null,'titulo'=>'Novo Usuário']);
    }
    public function editar(?string $id=null): void {
        requer_admin();$id=(int)($id??$_GET['id']??0);$usr=$this->m->findById($id);if(!$usr){$this->redirect('usuarios');return;}
        if($this->isPost()){try{$this->m->salvar($_POST,$id);$this->redirect('usuarios?msg=sucesso');}catch(\PDOException $e){$this->redirect("usuarios/editar/$id?erro=duplicado");}return;}
        $this->render('usuarios/form',['usuario'=>$usr,'titulo'=>'Editar Usuário']);
    }
    public function excluir(?string $id=null): void {
        requer_admin();$id=(int)($id??$_GET['id']??0);
        if($id===(int)$_SESSION['usuario_id']){$this->redirect('usuarios?erro=autoexclusao');return;}
        if($this->m->temAtendimentos($id)){$this->redirect('usuarios?erro=conflito');return;}
        $this->m->delete($id);$this->redirect('usuarios?msg=excluido');
    }
}
