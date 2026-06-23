<?php
class DashboardController extends BaseController {
    public function index(?string $p=null): void {
        requer_login();
        $eid=$this->empresaId;
        $mes=$_GET['mes']??date('Y-m');
        if(!preg_match('/^\d{4}-\d{2}$/',$mes)) $mes=date('Y-m');
        $ini=date('Y-m-01',strtotime($mes));
        $fim=date('Y-m-t',strtotime($mes));
        $mesAnt=date('Y-m',strtotime($ini.' -1 month'));
        $mesProx=date('Y-m',strtotime($ini.' +1 month'));

        $s=$this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento='pago' AND ap.status_execucao='feito' AND a.empresa_id=?");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid]); $faturamentoBruto=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COALESCE(SUM(valor_liquido_clinica),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago' AND empresa_id=?");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid]); $lucroLiquido=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COALESCE(SUM(taxa_cartao),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago' AND empresa_id=?");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid]); $totalTaxas=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COALESCE(SUM(comissao_dentista),0) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago' AND empresa_id=?");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid]); $totalComissoes=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa BETWEEN ? AND ? AND empresa_id=?");
        $s->execute([$ini,$fim,$eid]); $totalDespesas=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COUNT(DISTINCT id) FROM atendimentos WHERE data_atendimento BETWEEN ? AND ? AND status_pagamento='pago' AND empresa_id=?");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid]); $totalAtendimentos=(int)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COUNT(DISTINCT id) FROM atendimentos WHERE status_pagamento='pendente' AND empresa_id=?");
        $s->execute([$eid]); $totalPendentes=(int)$s->fetchColumn();

        $s=$this->db->prepare("SELECT COALESCE(SUM(valor_total),0) FROM atendimentos WHERE status_pagamento='pendente' AND empresa_id=?");
        $s->execute([$eid]); $valorPendente=(float)$s->fetchColumn();

        $s=$this->db->prepare("SELECT a.id,a.data_atendimento,p.nome AS paciente_nome,u.nome AS dentista,SUM(ap.valor_procedimento) AS valor_bruto,a.status_pagamento FROM atendimentos a JOIN pacientes p ON a.paciente_id=p.id JOIN usuarios u ON a.id_dentista=u.id LEFT JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.empresa_id=? GROUP BY a.id ORDER BY a.data_atendimento DESC LIMIT 8");
        $s->execute([$eid]); $ultimosAtendimentos=$s->fetchAll();

        try {
            $f=new IntlDateFormatter('pt_BR',IntlDateFormatter::FULL,IntlDateFormatter::NONE,'America/Sao_Paulo',IntlDateFormatter::GREGORIAN,"MMMM 'de' yyyy");
            $mesAtual=ucfirst($f->format(strtotime($ini)));
        } catch(\Exception $e) { $mesAtual=date('m/Y',strtotime($ini)); }

        $this->render('dashboard/index', compact(
            'faturamentoBruto','lucroLiquido','totalTaxas','totalComissoes','totalDespesas',
            'totalAtendimentos','totalPendentes','valorPendente',
            'ultimosAtendimentos','mes','mesAnt','mesProx','mesAtual'
        ));
    }
}
