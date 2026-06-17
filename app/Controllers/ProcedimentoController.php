<?php
class ProcedimentoController extends BaseController {
    private ProcedimentoModel $m;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->m=new ProcedimentoModel($pdo);}
    public function index(?string $p=null): void { requer_admin();$pr=$this->m->listarTodos();$msg=$_GET['msg']??'';$erro=$_GET['erro']??'';$this->render('procedimentos/index',compact('pr','msg','erro')); }
    public function criar(?string $p=null): void { requer_admin();if($this->isPost()){$this->m->salvarNovo($_POST);$this->redirect('procedimentos?msg=sucesso');}else $this->redirect('procedimentos'); }
    public function atualizarPreco(?string $p=null): void { requer_admin();if($this->isPost()){$this->m->atualizarPreco((int)$_POST['id'],(float)str_replace(',','.',$_POST['valor_base']),(int)$_SESSION['usuario_id']);$this->redirect('procedimentos?msg=preco_ok');}else $this->redirect('procedimentos'); }
    public function excluir(?string $id=null): void { requer_admin();$id=(int)($id??$_GET['id']??0);if($this->m->temAtendimentos($id)){$this->redirect('procedimentos?erro=conflito');return;}$this->m->delete($id);$this->redirect('procedimentos?msg=excluido'); }
    public function historico(?string $id=null): void { requer_admin();$id=(int)($id??$_GET['id']??0);$pr=$this->m->findById($id);$hist=$this->m->historico($id);$this->render('procedimentos/historico',compact('pr','hist')); }
}
