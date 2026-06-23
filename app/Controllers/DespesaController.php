<?php
class DespesaController extends BaseController {
    private DespesaModel $m;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->m=new DespesaModel($pdo);}
    public function index(?string $p=null): void { requer_admin();$d=$this->m->listarTodas();$msg=$_GET['msg']??'';$this->render('despesas/index',compact('d','msg')); }
    public function criar(?string $p=null): void { requer_admin();if($this->isPost()){$this->m->salvar($_POST);$this->redirect('despesas?msg=sucesso');}else $this->redirect('despesas'); }
    public function excluir(?string $id=null): void { requer_admin();$this->m->delete((int)($id??$_GET['id']??0));$this->redirect('despesas?msg=excluido'); }
}
