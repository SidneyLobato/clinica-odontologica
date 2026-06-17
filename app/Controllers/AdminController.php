<?php
class AdminController extends BaseController {
    private FinanceiroModel $fin;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->fin=new FinanceiroModel($pdo);}

    public function index(?string $p=null): void {
        requer_admin();$cenario=$this->fin->getConfig('cenario_comissao','global');
        $this->render('admin/index',compact('cenario'));
    }
    public function taxas(?string $p=null): void {
        requer_admin();$bandeiras=$this->fin->listaBandeiras();$matriz=$this->fin->matrizTaxas();$msg=$_GET['msg']??'';$erro=$_GET['erro']??'';
        $this->render('admin/taxas',compact('bandeiras','matriz','msg','erro'));
    }
    public function salvarTaxa(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/taxas');return;}
        $bid=(int)$_POST['bandeira_id'];$parc=(int)$_POST['parcelas'];$pct=(float)str_replace(',','.',$_POST['percentual']);
        if($parc<1||$parc>10||$pct<0){$this->redirect('admin/taxas?erro=invalido');return;}
        $this->fin->salvarTaxa($bid,$parc,$pct,(int)$_SESSION['usuario_id']);
        $this->redirect('admin/taxas?msg=sucesso');
    }
    /**
     * Salva várias taxas de uma vez (tela com abas por bandeira e botão único de salvar).
     * Espera JSON no corpo: { "itens": [ {bandeira_id, parcelas, percentual}, ... ] }
     * Responde em JSON — chamado via fetch() pela view admin/taxas.
     */
    public function salvarTaxasLote(?string $p=null): void {
        requer_admin();
        if (!$this->isPost()) { $this->json(['ok'=>false,'erro'=>'metodo_invalido']); return; }

        $body = json_decode(file_get_contents('php://input'), true);
        $itens = $body['itens'] ?? [];

        if (!is_array($itens) || count($itens) === 0) {
            $this->json(['ok'=>false,'erro'=>'nenhum_item']);
            return;
        }

        foreach ($itens as $item) {
            if (!isset($item['bandeira_id'], $item['parcelas'], $item['percentual'])) {
                $this->json(['ok'=>false,'erro'=>'item_invalido']);
                return;
            }
        }

        try {
            $qtd = $this->fin->salvarTaxasEmLote($itens, (int)$_SESSION['usuario_id']);
            $this->json(['ok'=>true,'atualizados'=>$qtd]);
        } catch (\Exception $e) {
            $this->json(['ok'=>false,'erro'=>'falha_ao_salvar']);
        }
    }
    public function comissoes(?string $p=null): void {
        requer_admin();$cenario=$this->fin->getConfig('cenario_comissao','global');$regra=$this->fin->getRegraComissaoGlobal();
        $dentistas=(new UsuarioModel($this->db))->listarDentistas();
        $ri=$this->db->query("SELECT ci.*,u.nome AS dentista_nome FROM config_comissoes_individuais ci JOIN usuarios u ON u.id=ci.dentista_id WHERE ci.ativo=1 ORDER BY u.nome")->fetchAll();
        $espec=$this->db->query("SELECT * FROM config_especialidades WHERE ativo=1 ORDER BY tipo")->fetchAll();
        $msg=$_GET['msg']??'';
        $this->render('admin/comissoes',compact('cenario','regra','dentistas','ri','espec','msg'));
    }
    public function salvarCenario(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/comissoes');return;}
        $this->fin->setConfig('cenario_comissao',$_POST['cenario']==='individual'?'individual':'global');
        $this->redirect('admin/comissoes?msg=sucesso');
    }
    public function salvarComissaoGlobal(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/comissoes');return;}
        if (isset($_POST['cenario'])) {
            $this->fin->setConfig('cenario_comissao', $_POST['cenario']==='individual'?'individual':'global');
        }
        $this->db->exec("UPDATE config_comissoes SET ativo=0");
        $this->db->prepare("INSERT INTO config_comissoes(percentual_ate_meta,percentual_acima_meta,valor_meta,ativo,vigencia_inicio,usuario_id)VALUES(?,?,?,1,NOW(),?)")->execute([(float)$_POST['percentual_ate_meta'],(float)$_POST['percentual_acima_meta'],(float)$_POST['valor_meta'],(int)$_SESSION['usuario_id']]);
        $this->redirect('admin/comissoes?msg=sucesso');
    }
    public function salvarComissaoIndividual(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/comissoes');return;}
        if (isset($_POST['cenario'])) {
            $this->fin->setConfig('cenario_comissao', $_POST['cenario']==='individual'?'individual':'global');
        }
        $did=(int)$_POST['dentista_id'];
        $this->db->prepare("UPDATE config_comissoes_individuais SET ativo=0 WHERE dentista_id=?")->execute([$did]);
        $this->db->prepare("INSERT INTO config_comissoes_individuais(dentista_id,percentual_ate_meta,percentual_acima_meta,valor_meta,ativo,vigencia_inicio,usuario_id)VALUES(?,?,?,?,1,NOW(),?)")->execute([$did,(float)$_POST['percentual_ate_meta'],(float)$_POST['percentual_acima_meta'],(float)$_POST['valor_meta'],(int)$_SESSION['usuario_id']]);
        $this->redirect('admin/comissoes?msg=sucesso');
    }
    public function salvarEspecialidade(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/comissoes');return;}
        $this->db->prepare("UPDATE config_especialidades SET percentual=?,ativo=1 WHERE tipo=?")->execute([(float)$_POST['percentual'],$_POST['tipo']]);
        $this->redirect('admin/comissoes?msg=sucesso');
    }
    public function rateio(?string $p=null): void {
        requer_admin();$regras=$this->db->query("SELECT * FROM config_rateio WHERE ativo=1 ORDER BY categoria_procedimento")->fetchAll();$msg=$_GET['msg']??'';$erro=$_GET['erro']??'';
        $this->render('admin/rateio',compact('regras','msg','erro'));
    }
    public function salvarRateio(?string $p=null): void {
        requer_admin();if(!$this->isPost()){$this->redirect('admin/rateio');return;}
        $cat=$_POST['categoria_procedimento'];$pe=(float)$_POST['percentual_especialista'];$pv=(float)$_POST['percentual_vendedor'];$pc=(float)$_POST['percentual_clinica'];
        if(abs($pe+$pv+$pc-100)>0.01){$this->redirect('admin/rateio?erro=soma');return;}
        $this->db->prepare("UPDATE config_rateio SET ativo=0 WHERE categoria_procedimento=?")->execute([$cat]);
        $this->db->prepare("INSERT INTO config_rateio(categoria_procedimento,percentual_especialista,percentual_vendedor,percentual_clinica,ativo,vigencia_inicio,usuario_id)VALUES(?,?,?,?,1,NOW(),?)")->execute([$cat,$pe,$pv,$pc,(int)$_SESSION['usuario_id']]);
        $this->redirect('admin/rateio?msg=sucesso');
    }
}
