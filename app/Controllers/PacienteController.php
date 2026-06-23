<?php
class PacienteController extends BaseController {
    private PacienteModel $m;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->m=new PacienteModel($pdo);}
    public function index(?string $p=null): void {
        requer_perfil(['proprietario','recepcionista','dentista']);
        $b=trim($_GET['busca']??'');$pg=max(1,(int)($_GET['pagina']??1));$pp=10;$off=($pg-1)*$pp;
        $pacientes=$this->m->listarPaginado($b,$off,$pp);$total=$this->m->contar($b);$totalPags=(int)ceil($total/$pp);
        $msg=$_GET['msg']??'';$erro=$_GET['erro']??'';
        $this->render('pacientes/index',compact('pacientes','b','pg','totalPags','msg','erro'));
    }
    public function criar(?string $p=null): void {
        requer_perfil(['proprietario','recepcionista','dentista']);
        if($this->isPost()){try{$this->m->salvar($_POST);$this->redirect('pacientes?msg=sucesso');}catch(\PDOException $e){$this->redirect('pacientes?erro=cpf_duplicado');}return;}
        $this->render('pacientes/form',['paciente'=>null,'titulo'=>'Novo Paciente']);
    }
    public function editar(?string $id=null): void {
        requer_perfil(['proprietario','recepcionista','dentista']);
        $id=(int)($id??$_GET['id']??0);$pac=$this->m->findById($id);if(!$pac){$this->redirect('pacientes');return;}
        if($this->isPost()){try{$this->m->salvar($_POST,$id);$this->redirect('pacientes?msg=sucesso');}catch(\PDOException $e){$this->redirect("pacientes/editar/$id?erro=cpf_duplicado");}return;}
        $this->render('pacientes/form',['paciente'=>$pac,'titulo'=>'Editar Paciente']);
    }
    public function excluir(?string $id=null): void {
        requer_perfil(['proprietario','recepcionista','dentista']);$id=(int)($id??$_GET['id']??0);
        if($this->m->temAtendimentos($id)){$this->redirect('pacientes?erro=conflito');return;}
        $this->m->delete($id);$this->redirect('pacientes?msg=excluido');
    }
    public function buscar(?string $p=null): void { requer_login();$this->json($this->m->autocomplete(trim($_GET['term']??''))); }
}
