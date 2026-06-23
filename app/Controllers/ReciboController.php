<?php
class ReciboController extends BaseController {
    public function index(?string $id=null): void {
        requer_login();$id=(int)($id??$_GET['id']??0);
        $s=$this->db->prepare("SELECT a.id,a.data_atendimento,p.nome AS paciente_nome,p.cpf AS paciente_cpf,p.endereco,p.numero,p.bairro,p.cidade,p.estado,u.nome AS dentista_nome,SUM(ap.valor_procedimento) AS valor_total FROM atendimentos a JOIN pacientes p ON a.paciente_id=p.id JOIN usuarios u ON a.id_dentista=u.id LEFT JOIN atendimento_procedimentos ap ON a.id=ap.id_atendimento WHERE a.id=? AND a.empresa_id=? AND ap.status_execucao='feito' GROUP BY a.id");
        $s->execute([$id, $this->empresaId]);$at=$s->fetch();if(!$at){echo"<p>Recibo não encontrado.</p>";return;}
        $sp=$this->db->prepare("SELECT proc.nome,ap.valor_procedimento FROM atendimento_procedimentos ap JOIN procedimentos proc ON ap.id_procedimento=proc.id WHERE ap.id_atendimento=? AND ap.status_execucao='feito'");
        $sp->execute([$id]);$procs=$sp->fetchAll();
        $nomeClinica = (new FinanceiroModel($this->db))->getConfig('nome_clinica', $this->empresa['nome'] ?? 'Clínica');
        try{$f=new IntlDateFormatter('pt_BR',IntlDateFormatter::FULL,IntlDateFormatter::NONE,'America/Sao_Paulo',IntlDateFormatter::GREGORIAN,"d 'de' MMMM 'de' yyyy");$dataFmt=$f->format(strtotime($at['data_atendimento']));}
        catch(\Exception $e){$dataFmt=date('d/m/Y',strtotime($at['data_atendimento']));}
        $this->renderSemLayout('recibo/index',['at'=>$at,'procs'=>$procs,'dataFmt'=>$dataFmt,'nomeClinica'=>$nomeClinica]);
    }
}
