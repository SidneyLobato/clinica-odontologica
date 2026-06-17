<?php
class RelatorioController extends BaseController {
    private RelatorioModel $m;
    public function __construct(PDO $pdo){parent::__construct($pdo);$this->m=new RelatorioModel($pdo);}

    public function diario(?string $p=null): void {
        requer_perfil(['proprietario','dentista','recepcionista']);
        $data=$_GET['data']??date('Y-m-d');
        $datAnt=(new DateTime($data))->modify('-1 day')->format('Y-m-d');
        $datProx=(new DateTime($data))->modify('+1 day')->format('Y-m-d');
        $did=is_dentista()&&!is_admin()?(int)$_SESSION['usuario_id']:null;
        $resumo=$this->m->resumoDiario($data,$did);$dentistas=$this->m->pagDentistas($data,$did);$despesas=$this->m->despesasDia($data);
        $this->render('relatorios/diario',compact('data','datAnt','datProx','resumo','dentistas','despesas'));
    }
    public function dentistas(?string $p=null): void {
        requer_perfil(['proprietario','dentista']);
        $mes=$_GET['mes']??date('Y-m');$ini=date('Y-m-01',strtotime($mes));$fim=date('Y-m-t',strtotime($mes));
        $did=is_dentista()&&!is_admin()?(int)$_SESSION['usuario_id']:($_GET['dentista_id']??null);
        $allDent=is_admin()?(new UsuarioModel($this->db))->listarDentistas():[];
        $relatorio=$this->m->resumoMensal($ini,$fim,$did&&$did!=='todos'?(int)$did:null);
        $this->render('relatorios/dentistas',compact('mes','did','allDent','relatorio'));
    }
    public function paciente(?string $p=null): void {
        requer_perfil(['proprietario','dentista']);
        $pid=(int)($_GET['paciente_id']??0);
        $nome=trim($_GET['paciente_nome']??'');
        $pg=max(1,(int)($_GET['pagina']??1));$pp=20;
        $paciente=null;$procedimentos=[];$totalPags=0;$cores=[];

        if($pid){
            $s=$this->db->prepare("SELECT * FROM pacientes WHERE id=? LIMIT 1");$s->execute([$pid]);$paciente=$s->fetch()?:null;
            if($paciente)$nome=$paciente['nome'];
        } elseif($nome){
            // Compatibilidade com links antigos que ainda usem busca por nome (texto livre)
            $s=$this->db->prepare("SELECT * FROM pacientes WHERE LOWER(nome) LIKE LOWER(?) LIMIT 1");$s->execute(["%$nome%"]);$paciente=$s->fetch()?:null;
        }

        if($paciente){
            $tc=$this->db->prepare("SELECT COUNT(ap.id) FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id WHERE a.paciente_id=?");$tc->execute([$paciente['id']]);$totalPags=(int)ceil($tc->fetchColumn()/$pp);
            $procedimentos=$this->m->historicoPac($paciente['id'],$pg,$pp);
            $dentes=$this->m->statusDentes($paciente['id']);$m=[];
            foreach($dentes as $d){$loc=$d['local'];if(!isset($m[$loc]))$m[$loc]=['f'=>false,'p'=>false];if($d['status_execucao']==='feito')$m[$loc]['f']=true;else $m[$loc]['p']=true;}
            foreach($m as $loc=>$st){if($st['f']&&$st['p'])$cores[$loc]='yellow';elseif($st['f'])$cores[$loc]='green';else $cores[$loc]='red';}
        }
        $this->render('relatorios/paciente',compact('nome','paciente','procedimentos','pg','totalPags','cores'));
    }
    public function procedimentos(?string $p=null): void {
        requer_admin();$ini=$_GET['inicio']??date('Y-m-01');$fim=$_GET['fim']??date('Y-m-t');
        $dados=$this->m->procedimentos($ini,$fim);$total=array_sum(array_column($dados,'quantidade_executada'));
        $this->render('relatorios/procedimentos',compact('ini','fim','dados','total'));
    }
    public function financeiro(?string $p=null): void {
        requer_admin();
        $ini=$_GET['inicio']??date('Y-m-01');$fim=$_GET['fim']??date('Y-m-t');
        $pg=max(1,(int)($_GET['pagina_at']??1));$pgD=max(1,(int)($_GET['pagina_de']??1));$pp=10;
        $totAt=$this->m->contarPago($ini,$fim);$totalPagAt=(int)ceil($totAt/$pp);
        $atendimentos=$this->m->financeiroGeral($ini,$fim,$pg,$pp);
        $despModel=new DespesaModel($this->db);$totalDespesas=$despModel->totalPeriodo($ini,$fim);
        $listaDespesas=$despModel->porPeriodo($ini,$fim);$totalPagDe=(int)ceil(count($listaDespesas)/$pp);
        $totais=['bruto'=>0,'liquido'=>0];
        $sb=$this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento='pago' AND ap.status_execucao='feito'");
        $sb->execute([$ini.' 00:00:00',$fim.' 23:59:59']);$totais['bruto']=(float)$sb->fetchColumn();
        $sl=$this->db->prepare("SELECT COALESCE(SUM(valor_liquido_clinica),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago'");
        $sl->execute([$ini.' 00:00:00',$fim.' 23:59:59']);$totais['liquido']=(float)$sl->fetchColumn();
        // Gráfico
        $raw=$this->m->grafico($ini,$fim);$gr=[];foreach($raw as $r)$gr[$r['dia']]=$r;
        $labels=$fat=$desp=$lucro=[];
        $begin=new DateTime($ini);$end=new DateTime($fim);$end->setTime(23,59,59);
        foreach(new DatePeriod($begin,new DateInterval('P1D'),$end) as $dt){
            $d=$dt->format('Y-m-d');$labels[]=$dt->format('d/m');$fat[]=(float)($gr[$d]['faturamento']??0);$desp[]=(float)($gr[$d]['despesa']??0);$lucro[]=(float)($gr[$d]['faturamento']??0)-(float)($gr[$d]['despesa']??0);
        }
        $dadosPag=$this->m->distPagamentos($ini,$fim);$pagLabels=array_map('ucfirst',array_keys($dadosPag));$pagData=array_values($dadosPag);
        $this->render('relatorios/financeiro',compact('ini','fim','totais','totalDespesas','atendimentos','listaDespesas','pg','pgD','totalPagAt','totalPagDe','pp','labels','fat','desp','lucro','pagLabels','pagData'));
    }
    public function rateio(?string $p=null): void {
        requer_admin();
        $ini=$_GET['de']??date('Y-m-01');$fim=$_GET['ate']??date('Y-m-t');
        $pg=max(1,(int)($_GET['pagina']??1));$pp=15;
        $total=$this->m->contarPago($ini,$fim);$totalPags=(int)ceil($total/$pp);
        $registros=$this->m->financeiroGeral($ini,$fim,$pg,$pp);$totais=$this->m->financeiroTotais($ini,$fim);
        $this->render('relatorios/rateio',compact('ini','fim','registros','totais','pg','totalPags'));
    }
}
