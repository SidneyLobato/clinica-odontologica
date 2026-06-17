<?php
class DashboardController extends BaseController {
    public function index(?string $p=null): void {
        requer_login();
        $mes=$_GET['mes']??date('Y-m');
        if(!preg_match('/^\d{4}-\d{2}$/',$mes)) $mes=date('Y-m');
        $ini=date('Y-m-01',strtotime($mes));
        $fim=date('Y-m-t',strtotime($mes));
        $mesAnt=date('Y-m',strtotime($ini.' -1 month'));
        $mesProx=date('Y-m',strtotime($ini.' +1 month'));
        $busca=trim($_GET['busca']??'');
        $pg=max(1,(int)($_GET['pagina']??1));
        $pp=10; $off=($pg-1)*$pp;

        // Faturamento bruto
        $s1=$this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento='pago' AND ap.status_execucao='feito'");
        $s1->execute([$ini.' 00:00:00',$fim.' 23:59:59']); $faturamentoBruto=(float)$s1->fetchColumn();

        // Lucro líquido (já descontado taxa+comissão)
        $s2=$this->db->prepare("SELECT COALESCE(SUM(valor_liquido_clinica),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago'");
        $s2->execute([$ini.' 00:00:00',$fim.' 23:59:59']); $lucroLiquido=(float)$s2->fetchColumn();

        // Total taxas de cartão
        $s2t=$this->db->prepare("SELECT COALESCE(SUM(taxa_cartao),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago'");
        $s2t->execute([$ini.' 00:00:00',$fim.' 23:59:59']); $totalTaxas=(float)$s2t->fetchColumn();

        // Despesas
        $s3=$this->db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa BETWEEN ? AND ?");
        $s3->execute([$ini,$fim]); $totalDespesas=(float)$s3->fetchColumn();

        $blike="%$busca%";

        $sc=$this->db->prepare("SELECT COUNT(DISTINCT a.id) FROM atendimentos a JOIN pacientes p ON a.paciente_id=p.id WHERE a.status_pagamento='pago' AND p.nome LIKE :b");
        $sc->execute([':b'=>$blike]); $totalReg=(int)$sc->fetchColumn(); $totalPags=(int)ceil($totalReg/$pp);

        $sl=$this->db->prepare("SELECT a.id,a.data_atendimento,a.status_pagamento,a.taxa_cartao,a.valor_liquido_clinica,a.custo_auxiliar,a.comissao_dentista,a.url_arquivo,p.nome AS paciente_nome,u.nome AS dentista,SUM(CASE WHEN ap.status_execucao='feito' THEN ap.valor_procedimento ELSE 0 END) AS valor_bruto_total,GROUP_CONCAT(CASE WHEN ap.status_execucao='feito' THEN proc.nome END SEPARATOR ', ') AS procedimentos FROM atendimentos a JOIN pacientes p ON a.paciente_id=p.id JOIN usuarios u ON a.id_dentista=u.id LEFT JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento LEFT JOIN procedimentos proc ON ap.id_procedimento=proc.id WHERE a.status_pagamento='pago' AND p.nome LIKE :b GROUP BY a.id ORDER BY a.data_atendimento DESC LIMIT :l OFFSET :o");
        $sl->bindValue(':b',$blike); $sl->bindValue(':l',$pp,PDO::PARAM_INT); $sl->bindValue(':o',$off,PDO::PARAM_INT); $sl->execute();
        $ultimosAtendimentos=$sl->fetchAll();

        try {
            $f=new IntlDateFormatter('pt_BR',IntlDateFormatter::FULL,IntlDateFormatter::NONE,'America/Sao_Paulo',IntlDateFormatter::GREGORIAN,"MMMM 'de' yyyy");
            $mesAtual=ucfirst($f->format(strtotime($ini)));
        } catch(\Exception $e) { $mesAtual=date('m/Y',strtotime($ini)); }

        $this->render('dashboard/index', compact(
            'faturamentoBruto','lucroLiquido','totalTaxas','totalDespesas',
            'ultimosAtendimentos','mes','mesAnt','mesProx','mesAtual',
            'busca','pg','totalPags','pp'
        ));
    }
}
