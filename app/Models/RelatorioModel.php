<?php
class RelatorioModel extends BaseModel {
    protected string $table = 'atendimentos';

    public function resumoDiario(string $d, ?int $did=null): array {
        $eid = $this->empresaId;
        $f = $did ? "AND a.id_dentista=$did" : '';
        $sb = $this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE DATE(a.data_atendimento)=? AND a.status_pagamento='pago' AND ap.status_execucao='feito' AND a.empresa_id=? $f");
        $sb->execute([$d, $eid]); $bruto=(float)$sb->fetchColumn();
        $s2 = $this->db->prepare("SELECT COALESCE(SUM(taxa_cartao),0),COALESCE(SUM(custo_auxiliar),0),COALESCE(SUM(comissao_dentista),0) FROM atendimentos a WHERE DATE(a.data_atendimento)=? AND a.status_pagamento='pago' AND a.empresa_id=? AND EXISTS(SELECT 1 FROM atendimento_procedimentos ap WHERE ap.id_atendimento=a.id AND ap.status_execucao='feito') $f");
        $s2->execute([$d, $eid]); [$taxa,$custo,$com]=array_values($s2->fetch(PDO::FETCH_NUM));
        $sd = $this->db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa=? AND empresa_id=?");
        $sd->execute([$d, $eid]); $desp=(float)$sd->fetchColumn();
        return['faturamento_bruto'=>$bruto,'total_taxas'=>(float)$taxa,'total_custo_aux'=>(float)$custo,'total_comissoes'=>(float)$com,'total_despesas'=>$desp,'lucro_liquido'=>$bruto-(float)$taxa-(float)$com-(float)$custo-$desp];
    }

    public function pagDentistas(string $d, ?int $did=null): array {
        $eid = $this->empresaId;
        $f = $did ? "AND a.id_dentista=$did" : '';
        $s = $this->db->prepare("SELECT u.nome,SUM(a.comissao_dentista) AS total_comissao FROM atendimentos a JOIN usuarios u ON a.id_dentista=u.id WHERE DATE(a.data_atendimento)=? AND a.status_pagamento='pago' AND a.empresa_id=? AND EXISTS(SELECT 1 FROM atendimento_procedimentos ap WHERE ap.id_atendimento=a.id AND ap.status_execucao='feito') $f GROUP BY u.nome HAVING total_comissao>0 ORDER BY u.nome");
        $s->execute([$d, $eid]); return $s->fetchAll();
    }

    public function despesasDia(string $d): array {
        $s = $this->db->prepare("SELECT * FROM despesas WHERE data_despesa=? AND empresa_id=? ORDER BY descricao");
        $s->execute([$d, $this->empresaId]); return $s->fetchAll();
    }

    public function resumoMensal(string $ini, string $fim, ?int $did=null): array {
        $eid = $this->empresaId;
        $f = $did ? "AND u.id=$did" : '';
        $s = $this->db->prepare("SELECT u.id AS dentista_id,u.nome AS dentista_nome,COUNT(DISTINCT a.id) AS total_atendimentos,SUM(ap.valor_procedimento) AS faturamento_bruto,SUM(a.valor_liquido_clinica) AS valor_para_clinica,SUM(a.comissao_dentista) AS valor_para_dentista FROM usuarios u JOIN atendimentos a ON u.id=a.id_dentista JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.data_atendimento BETWEEN :ini AND :fim AND ap.status_execucao IN('finalizado','feito') AND a.status_pagamento='pago' AND a.empresa_id=:eid AND u.empresa_id=:eid2 $f GROUP BY u.id,u.nome ORDER BY faturamento_bruto DESC");
        $s->execute(['ini'=>$ini.' 00:00:00','fim'=>$fim.' 23:59:59','eid'=>$eid,'eid2'=>$eid]); return $s->fetchAll();
    }

    public function procedimentos(string $ini, string $fim): array {
        $s = $this->db->prepare("SELECT p.nome AS procedimento_nome,SUM(ap.quantidade) AS quantidade_executada,SUM(ap.valor_procedimento) AS valor_bruto_total FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id JOIN procedimentos p ON ap.id_procedimento=p.id WHERE a.data_atendimento BETWEEN ? AND ? AND ap.status_execucao='feito' AND a.status_pagamento='pago' AND a.empresa_id=? GROUP BY p.id,p.nome ORDER BY quantidade_executada DESC");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59', $this->empresaId]); return $s->fetchAll();
    }

    public function financeiroGeral(string $ini, string $fim, int $pg, int $pp): array {
        $off=($pg-1)*$pp;
        $s = $this->db->prepare("SELECT a.id,a.data_atendimento,pac.nome AS paciente,u_ex.nome AS dentista_executor,u_vd.nome AS dentista_vendedor,a.valor_total AS valor_bruto,a.taxa_cartao,a.comissao_dentista,a.custo_auxiliar,a.valor_liquido_clinica,COALESCE(SUM(r.valor_especialista),0) AS total_especialista,COALESCE(SUM(r.valor_vendedor),0) AS total_vendedor,COALESCE(SUM(r.valor_clinica),0) AS total_clinica,GROUP_CONCAT(DISTINCT proc.nome SEPARATOR ', ') AS procedimentos FROM atendimentos a JOIN pacientes pac ON pac.id=a.paciente_id JOIN usuarios u_ex ON u_ex.id=a.id_dentista LEFT JOIN usuarios u_vd ON u_vd.id=a.id_dentista_vendedor LEFT JOIN rateios_atendimento r ON r.id_atendimento=a.id LEFT JOIN atendimento_procedimentos ap ON ap.id_atendimento=a.id LEFT JOIN procedimentos proc ON proc.id=ap.id_procedimento WHERE DATE(a.data_atendimento) BETWEEN :ini AND :fim AND a.status_pagamento='pago' AND a.empresa_id=:eid GROUP BY a.id ORDER BY a.data_atendimento DESC LIMIT :l OFFSET :o");
        $s->bindValue(':ini',$ini);$s->bindValue(':fim',$fim);$s->bindValue(':eid',$this->empresaId);$s->bindValue(':l',$pp,PDO::PARAM_INT);$s->bindValue(':o',$off,PDO::PARAM_INT);$s->execute();return $s->fetchAll();
    }

    public function financeiroTotais(string $ini, string $fim): array {
        $s = $this->db->prepare("SELECT COALESCE(SUM(a.valor_total),0) AS bruto,COALESCE(SUM(a.taxa_cartao),0) AS taxa,COALESCE(SUM(a.comissao_dentista),0) AS comissao,COALESCE(SUM(a.custo_auxiliar),0) AS custo_aux,COALESCE(SUM(a.valor_liquido_clinica),0) AS liquido,COALESCE(SUM(r.valor_especialista),0) AS esp,COALESCE(SUM(r.valor_vendedor),0) AS vend,COALESCE(SUM(r.valor_clinica),0) AS clin FROM atendimentos a LEFT JOIN rateios_atendimento r ON r.id_atendimento=a.id WHERE DATE(a.data_atendimento) BETWEEN :ini AND :fim AND a.status_pagamento='pago' AND a.empresa_id=:eid");
        $s->execute(['ini'=>$ini,'fim'=>$fim,'eid'=>$this->empresaId]);return $s->fetch();
    }

    public function contarPago(string $ini, string $fim): int {
        $s = $this->db->prepare("SELECT COUNT(DISTINCT id) FROM atendimentos WHERE DATE(data_atendimento) BETWEEN :ini AND :fim AND status_pagamento='pago' AND empresa_id=:eid");
        $s->execute(['ini'=>$ini,'fim'=>$fim,'eid'=>$this->empresaId]);return(int)$s->fetchColumn();
    }

    public function historicoPac(int $pid, int $pg, int $pp): array {
        $off=($pg-1)*$pp;
        $s = $this->db->prepare("SELECT ap.id AS ap_id,proc.nome AS procedimento_nome,ap.local,ap.descricao,ap.status_execucao,a.data_atendimento,a.status_pagamento,ap.url_arquivo FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id JOIN procedimentos proc ON ap.id_procedimento=proc.id WHERE a.paciente_id=:pid AND a.empresa_id=:eid ORDER BY a.data_atendimento DESC LIMIT :l OFFSET :o");
        $s->bindValue(':pid',$pid,PDO::PARAM_INT);$s->bindValue(':eid',$this->empresaId,PDO::PARAM_INT);$s->bindValue(':l',$pp,PDO::PARAM_INT);$s->bindValue(':o',$off,PDO::PARAM_INT);$s->execute();return $s->fetchAll();
    }

    public function statusDentes(int $pid): array {
        $s = $this->db->prepare("SELECT ap.local,ap.status_execucao FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id WHERE a.paciente_id=? AND a.empresa_id=? AND ap.local IS NOT NULL AND ap.local!=''");
        $s->execute([$pid, $this->empresaId]);return $s->fetchAll();
    }

    public function grafico(string $ini, string $fim): array {
        $eid = $this->empresaId;
        $s = $this->db->prepare("SELECT dia,SUM(fat) AS faturamento,SUM(desp) AS despesa FROM(SELECT DATE(a.data_atendimento) AS dia,SUM(ap.valor_procedimento) AS fat,0 AS desp FROM atendimentos a JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento='pago' AND ap.status_execucao='feito' AND a.empresa_id=? GROUP BY DATE(a.data_atendimento) UNION ALL SELECT DATE(data_despesa),0,SUM(valor) FROM despesas WHERE data_despesa BETWEEN ? AND ? AND empresa_id=? GROUP BY DATE(data_despesa))T GROUP BY dia ORDER BY dia");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59',$eid,$ini,$fim,$eid]);return $s->fetchAll();
    }

    public function distPagamentos(string $ini, string $fim): array {
        $s = $this->db->prepare("SELECT ap.forma_pagamento,SUM(ap.valor) AS total FROM atendimento_pagamentos ap JOIN atendimentos a ON ap.id_atendimento=a.id WHERE a.data_atendimento BETWEEN ? AND ? AND a.empresa_id=? GROUP BY ap.forma_pagamento");
        $s->execute([$ini.' 00:00:00',$fim.' 23:59:59', $this->empresaId]);return $s->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
