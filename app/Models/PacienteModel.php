<?php
class PacienteModel extends BaseModel {
    protected string $table = 'pacientes';

    public function listarPaginado(string $b, int $off, int $pp): array {
        $like = "%$b%";
        [$where, $params] = $this->filtro('nome LIKE :b1 OR cpf LIKE :b2', [':b1' => $like, ':b2' => $like]);
        $s = $this->db->prepare("SELECT * FROM pacientes $where ORDER BY nome LIMIT :l OFFSET :o");
        foreach ($params as $k => $v) $s->bindValue($k, $v);
        $s->bindValue(':l', $pp, PDO::PARAM_INT);
        $s->bindValue(':o', $off, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function contar(string $b): int {
        $like = "%$b%";
        [$where, $params] = $this->filtro('nome LIKE :b1 OR cpf LIKE :b2', [':b1' => $like, ':b2' => $like]);
        $s = $this->db->prepare("SELECT COUNT(*) FROM pacientes $where");
        $s->execute($params);
        return (int)$s->fetchColumn();
    }

    public function autocomplete(string $t): array {
        $like = "%$t%";
        [$where, $params] = $this->filtro('nome LIKE :t1 OR cpf LIKE :t2', [':t1' => $like, ':t2' => $like]);
        $s = $this->db->prepare(
            "SELECT id,nome,cpf,telefone,email,cep,endereco,numero,bairro,cidade,estado,data_nascimento
               FROM pacientes $where LIMIT 10"
        );
        $s->execute($params);
        return $s->fetchAll();
    }

    public function temAtendimentos(int $id): bool {
        $eidClause = $this->empresaId !== null ? ' AND empresa_id = ?' : '';
        $params    = $this->empresaId !== null ? [$id, $this->empresaId] : [$id];
        $s = $this->db->prepare("SELECT COUNT(*) FROM atendimentos WHERE paciente_id=?$eidClause");
        $s->execute($params);
        return $s->fetchColumn() > 0;
    }

    public function salvar(array $d, ?int $id = null): int {
        $c = [
            'nome'            => $d['paciente_nome']            ?? '',
            'cpf'             => $d['paciente_cpf']             ?: null,
            'data_nascimento' => $d['paciente_data_nascimento'] ?: null,
            'email'           => $d['paciente_email']           ?: null,
            'telefone'        => $d['paciente_telefone']        ?: null,
            'cep'             => $d['paciente_cep']             ?: null,
            'endereco'        => $d['paciente_endereco']        ?: null,
            'numero'          => $d['paciente_numero']          ?: null,
            'bairro'          => $d['paciente_bairro']          ?: null,
            'cidade'          => $d['paciente_cidade']          ?: null,
            'estado'          => $d['paciente_estado']          ?: null,
        ];
        if ($id) { $this->update($id, $c); return $id; }
        return $this->insert($c);
    }
}
