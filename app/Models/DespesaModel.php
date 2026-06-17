<?php
class DespesaModel extends BaseModel {
    protected string $table = 'despesas';
    public function listarTodas(): array { return $this->db->query("SELECT * FROM despesas ORDER BY data_despesa DESC")->fetchAll(); }
    public function totalPeriodo(string $i, string $f): float { $s=$this->db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa BETWEEN :i AND :f");$s->execute(['i'=>$i,'f'=>$f]);return(float)$s->fetchColumn(); }
    public function porPeriodo(string $i, string $f): array { $s=$this->db->prepare("SELECT * FROM despesas WHERE data_despesa BETWEEN :i AND :f ORDER BY data_despesa DESC");$s->execute(['i'=>$i,'f'=>$f]);return $s->fetchAll(); }
    public function salvar(array $d): int { return $this->insert(['descricao'=>trim($d['descricao']),'valor'=>(float)$d['valor'],'tipo'=>$d['tipo'],'data_despesa'=>$d['data_despesa']]); }
}
