<?php
class DespesaModel extends BaseModel {
    protected string $table = 'despesas';

    public function listarTodas(): array {
        [$where, $params] = $this->filtro();
        $s = $this->db->prepare("SELECT * FROM despesas $where ORDER BY data_despesa DESC");
        $s->execute($params);
        return $s->fetchAll();
    }

    public function totalPeriodo(string $i, string $f): float {
        [$where, $params] = $this->filtro('data_despesa BETWEEN :i AND :f', ['i' => $i, 'f' => $f]);
        $s = $this->db->prepare("SELECT COALESCE(SUM(valor),0) FROM despesas $where");
        $s->execute($params);
        return (float)$s->fetchColumn();
    }

    public function porPeriodo(string $i, string $f): array {
        [$where, $params] = $this->filtro('data_despesa BETWEEN :i AND :f', ['i' => $i, 'f' => $f]);
        $s = $this->db->prepare("SELECT * FROM despesas $where ORDER BY data_despesa DESC");
        $s->execute($params);
        return $s->fetchAll();
    }

    public function salvar(array $d): int {
        return $this->insert([
            'descricao'    => trim($d['descricao']),
            'valor'        => (float)$d['valor'],
            'tipo'         => $d['tipo'],
            'data_despesa' => $d['data_despesa'],
        ]);
    }
}
