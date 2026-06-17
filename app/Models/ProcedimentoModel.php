<?php
class ProcedimentoModel extends BaseModel {
    protected string $table = 'procedimentos';
    public function listarTodos(): array { return $this->db->query("SELECT * FROM procedimentos ORDER BY nome")->fetchAll(); }
    public function temAtendimentos(int $id): bool { $s=$this->db->prepare("SELECT COUNT(*) FROM atendimento_procedimentos WHERE id_procedimento=?");$s->execute([$id]);return$s->fetchColumn()>0; }
    /** Atualiza preço com histórico — atendimentos anteriores jamais são recalculados */
    public function atualizarPreco(int $id, float $val, int $uid): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE historico_precos_procedimentos SET vigencia_fim=NOW() WHERE procedimento_id=:id AND vigencia_fim IS NULL")->execute(['id'=>$id]);
            $this->update($id, ['valor_base'=>$val]);
            $this->db->prepare("INSERT INTO historico_precos_procedimentos(procedimento_id,valor,vigencia_inicio,usuario_id)VALUES(:p,:v,NOW(),:u)")->execute(['p'=>$id,'v'=>$val,'u'=>$uid]);
            $this->db->commit();
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }
    public function historico(int $id): array {
        $s=$this->db->prepare("SELECT h.*,u.nome AS usuario_nome FROM historico_precos_procedimentos h LEFT JOIN usuarios u ON u.id=h.usuario_id WHERE h.procedimento_id=:id ORDER BY h.vigencia_inicio DESC");
        $s->execute(['id'=>$id]);return $s->fetchAll();
    }
    public function salvarNovo(array $d): int {
        $id=$this->insert(['nome'=>trim($d['nome']),'categoria'=>$d['categoria'],'tipo'=>$d['tipo']??null,'valor_base'=>(float)str_replace(',','.',$d['valor_base'])]);
        $this->db->prepare("INSERT INTO historico_precos_procedimentos(procedimento_id,valor,vigencia_inicio)VALUES(:id,:v,NOW())")->execute(['id'=>$id,'v'=>(float)str_replace(',','.',$d['valor_base'])]);
        return $id;
    }
}
